# Verra — Product Requirements Document (PRD) v1.0
> Platform SaaS CS AI berbasis WhatsApp, multi-tenant, dengan RBAC menggunakan Shield CI4.
> Dokumen ini adalah sumber kebenaran tunggal untuk development. Baca seluruh dokumen sebelum menulis satu baris kode pun.

---

## 1. Gambaran Produk

**Verra** adalah platform SaaS yang memungkinkan banyak perusahaan (tenant) mengoperasikan CS AI WhatsApp mereka sendiri secara independen. Setiap tenant memiliki konfigurasi AI, token Fonnte, nomor WA, dan knowledge base yang sepenuhnya terisolasi satu sama lain.

### Alur Utama
```
[WhatsApp Customer]
       ↓
  [Fonnte API]
       ↓ webhook POST ke /webhook/{wa_channel_uuid}
[Verra - CI4 App]
   ↓          ↓
[KB Tenant] [History Tenant]
       ↓
[AI Provider - Gemini / Grok]
       ↓
[Verra - CI4 App]
       ↓ Fonnte Send API
  [Fonnte API]
       ↓
[WhatsApp Customer]
```

---

## 2. Tech Stack

| Komponen | Teknologi |
|---|---|
| Framework | CodeIgniter 4 (CI4) terbaru |
| Auth & RBAC | CI4 Shield |
| Database | MySQL 8+ — shared DB, isolasi via `tenant_id` |
| PHP | 8.1+ |
| Web Server | Apache/Nginx (shared hosting compatible) |
| WA Gateway | Fonnte API |
| AI Provider | Google Gemini API & xAI Grok API (configurable per tenant) |
| Frontend | Bootstrap 5 + vanilla JS |
| Real-time | Server-Sent Events (SSE) — untuk live chat agent panel |

---

## 3. Multi-Tenancy

### Prinsip
- Semua data menggunakan **shared database** dengan kolom `tenant_id` sebagai isolator
- Setiap query yang menyentuh data tenant **wajib** difilter by `tenant_id`
- Tenant tidak bisa mengakses, melihat, atau memodifikasi data tenant lain dalam kondisi apapun

### Tabel `tenants`
```sql
CREATE TABLE tenants (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid         VARCHAR(36) NOT NULL UNIQUE,
    name         VARCHAR(255) NOT NULL,
    slug         VARCHAR(100) NOT NULL UNIQUE,
    is_active    TINYINT(1) DEFAULT 1,
    created_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Resolusi Tenant
- Tenant diidentifikasi dari user yang sedang login (kolom `tenant_id` di tabel `users`)
- Super Admin tidak terikat tenant, bisa akses semua
- Setiap request dari user non-Super Admin harus inject `tenant_id` secara otomatis via BaseController atau Filter

---

## 4. RBAC dengan CI4 Shield

### Daftar Role

| Role | Scope | Deskripsi |
|---|---|---|
| `superadmin` | Global | Owner Verra. Bisa manage semua tenant, semua user, semua konfigurasi sistem |
| `tenant_admin` | Per Tenant | Manage 1 tenant: KB, API keys, nomor WA, user, konfigurasi AI |
| `operator` | Per Tenant | Lihat conversation history & handover list. Tidak bisa edit config |
| `agent` | Per Tenant | Hanya bisa handle (claim & close) handover yang masuk |

### Daftar Permission

```
// Tenant Management (superadmin only)
tenants.create
tenants.read
tenants.update
tenants.delete

// User Management
users.create
users.read
users.update
users.delete

// Knowledge Base
kb.create
kb.read
kb.update
kb.delete

// WA Channels
channels.create
channels.read
channels.update
channels.delete

// Configuration (API keys, AI provider)
config.read
config.update

// Conversations
conversations.read

// Handover
handover.read
handover.handle
handover.close
```

### Mapping Role → Permission

| Permission | superadmin | tenant_admin | operator | agent |
|---|:---:|:---:|:---:|:---:|
| tenants.* | ✅ | ❌ | ❌ | ❌ |
| users.* | ✅ | ✅ (tenant only) | ❌ | ❌ |
| kb.* | ✅ | ✅ | ❌ | ❌ |
| channels.* | ✅ | ✅ | ❌ | ❌ |
| config.read | ✅ | ✅ | ❌ | ❌ |
| config.update | ✅ | ✅ | ❌ | ❌ |
| conversations.read | ✅ | ✅ | ✅ | ❌ |
| handover.read | ✅ | ✅ | ✅ | ✅ |
| handover.handle | ✅ | ✅ | ✅ | ✅ |
| handover.close | ✅ | ✅ | ✅ | ✅ |

---

## 5. Skema Database Lengkap

### 5.1 Users (extend Shield)
Tambahkan kolom berikut ke tabel `users` milik Shield:
```sql
ALTER TABLE users ADD COLUMN tenant_id INT UNSIGNED NULL;
ALTER TABLE users ADD COLUMN full_name VARCHAR(255) NULL;
ALTER TABLE users ADD COLUMN is_active TINYINT(1) DEFAULT 1;
ALTER TABLE users ADD INDEX idx_tenant_id (tenant_id);
```
> `tenant_id = NULL` artinya user adalah Super Admin (tidak terikat tenant)

### 5.2 WA Channels
Satu tenant bisa punya banyak nomor WA Fonnte.
```sql
CREATE TABLE wa_channels (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id      INT UNSIGNED NOT NULL,
    uuid           VARCHAR(36) NOT NULL UNIQUE COMMENT 'Digunakan sebagai webhook path',
    name           VARCHAR(100) NOT NULL COMMENT 'Label channel, misal: CS Utama, CS Properti B',
    wa_number      VARCHAR(20) NOT NULL COMMENT 'Nomor WA terdaftar di Fonnte (dengan kode negara)',
    fonnte_token   VARCHAR(255) NOT NULL,
    is_active      TINYINT(1) DEFAULT 1,
    created_at     DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at     DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_uuid (uuid)
);
```
> Webhook Fonnte diarahkan ke: `POST /webhook/{uuid}`
> UUID unik per channel, sehingga Verra tahu pesan masuk untuk tenant dan channel mana.

### 5.3 Tenant Config (AI Provider)
Konfigurasi AI provider per tenant, dikelola oleh Tenant Admin via UI.
```sql
CREATE TABLE tenant_configs (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id       INT UNSIGNED NOT NULL UNIQUE,
    ai_provider     ENUM('gemini', 'grok') DEFAULT 'gemini',
    gemini_api_key  TEXT NULL,
    gemini_model    VARCHAR(100) DEFAULT 'gemini-1.5-flash',
    grok_api_key    TEXT NULL,
    grok_model      VARCHAR(100) DEFAULT 'grok-beta',
    system_prompt   TEXT NULL COMMENT 'Custom system prompt untuk AI. Akan digabung dengan KB.',
    max_history     TINYINT UNSIGNED DEFAULT 10 COMMENT 'Jumlah turn history yang dikirim ke AI',
    updated_at      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tenant_id (tenant_id)
);
```

### 5.4 Knowledge Base
KB shared per tenant, tidak per channel.
```sql
CREATE TABLE knowledge_base (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id   INT UNSIGNED NOT NULL,
    category    VARCHAR(100) NOT NULL,
    title       VARCHAR(255) NOT NULL,
    content     TEXT NOT NULL,
    is_active   TINYINT(1) DEFAULT 1,
    sort_order  INT DEFAULT 0,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_active (tenant_id, is_active)
);
```

### 5.5 Conversations
History percakapan per nomor WA customer, per channel.
```sql
CREATE TABLE conversations (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id      INT UNSIGNED NOT NULL,
    channel_id     INT UNSIGNED NOT NULL,
    wa_number      VARCHAR(20) NOT NULL COMMENT 'Nomor WA customer',
    role           ENUM('user', 'assistant') NOT NULL,
    message        TEXT NOT NULL,
    created_at     DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_lookup (tenant_id, channel_id, wa_number),
    INDEX idx_created_at (created_at)
);
```

### 5.6 Handover Log
Eskalasi ke CS manusia.
```sql
CREATE TABLE handover_log (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id       INT UNSIGNED NOT NULL,
    channel_id      INT UNSIGNED NOT NULL,
    wa_number       VARCHAR(20) NOT NULL,
    trigger_message TEXT NOT NULL,
    trigger_type    ENUM('keyword', 'ai_unable', 'manual') NOT NULL,
    status          ENUM('pending', 'in_progress', 'handled') DEFAULT 'pending',
    mode            ENUM('ai', 'agent') DEFAULT 'ai' COMMENT 'Mode aktif saat ini: ai = dibalas AI, agent = diambil alih manusia',
    claimed_by      INT UNSIGNED NULL COMMENT 'user_id agent yang claim',
    claimed_at      DATETIME NULL,
    returned_to_ai_at DATETIME NULL COMMENT 'Timestamp terakhir dikembalikan ke AI',
    handled_at      DATETIME NULL,
    notes           TEXT NULL,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tenant_status (tenant_id, status),
    INDEX idx_wa_number (tenant_id, wa_number)
);
```

### 5.7 Handover Keywords
Kata kunci trigger handover, bisa dikonfigurasi per tenant.
```sql
CREATE TABLE handover_keywords (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id   INT UNSIGNED NOT NULL,
    keyword     VARCHAR(100) NOT NULL,
    is_active   TINYINT(1) DEFAULT 1,
    INDEX idx_tenant_id (tenant_id)
);
```
**Default keywords saat tenant dibuat:** `agent`, `cs`, `manusia`, `operator`, `bantuan`, `tolong`

### 5.8 Agent Messages
Pesan yang dikirim langsung oleh agent via UI Verra (bukan AI). Disimpan terpisah dari `conversations` agar bisa dibedakan sumbernya, tapi tetap masuk ke history tampilan.
```sql
CREATE TABLE agent_messages (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id   INT UNSIGNED NOT NULL,
    channel_id  INT UNSIGNED NOT NULL,
    handover_id INT UNSIGNED NOT NULL COMMENT 'FK ke handover_log.id',
    wa_number   VARCHAR(20) NOT NULL COMMENT 'Nomor WA customer tujuan',
    agent_id    INT UNSIGNED NOT NULL COMMENT 'FK ke users.id',
    message     TEXT NOT NULL,
    sent_at     DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_handover (handover_id),
    INDEX idx_lookup (tenant_id, channel_id, wa_number)
);
```

### 5.9 SSE Events Queue
Antrian event untuk Server-Sent Events. Agent panel melakukan SSE subscribe, server push event baru dari tabel ini.
```sql
CREATE TABLE sse_events (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id   INT UNSIGNED NOT NULL,
    channel_id  INT UNSIGNED NOT NULL,
    wa_number   VARCHAR(20) NOT NULL COMMENT 'Percakapan yang di-update',
    event_type  ENUM('new_message', 'handover_created', 'handover_claimed', 'returned_to_ai') NOT NULL,
    payload     JSON NOT NULL COMMENT 'Data event: message, sender, timestamp, dll',
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_lookup (tenant_id, channel_id, created_at)
);
```
> Row di tabel ini dibuat setiap ada event baru. SSE endpoint melakukan `SELECT` berkala dan push ke client. Row lama (> 5 menit) dibersihkan via scheduled cleanup.

---

## 6. Struktur Direktori CI4

```
app/
├── Config/
│   └── AuthGroups.php          # Definisi role & permission Shield
├── Controllers/
│   ├── BaseController.php      # Inject tenant_id, cek permission
│   ├── WebhookController.php   # Public, tanpa auth
│   ├── Auth/                   # Login, logout (Shield)
│   ├── SuperAdmin/
│   │   ├── DashboardController.php
│   │   └── TenantController.php
│   └── Tenant/
│       ├── DashboardController.php
│       ├── ChannelController.php
│       ├── KnowledgeBaseController.php
│       ├── ConfigController.php
│       ├── ConversationController.php
│       ├── HandoverController.php
│       ├── AgentChatController.php  # Ambil alih, balas, kembalikan ke AI
│       └── SseController.php        # SSE endpoint untuk live update agent panel
├── Filters/
│   ├── TenantFilter.php        # Validasi tenant_id pada setiap request
│   └── PermissionFilter.php    # Wrapper Shield permission check
├── Models/
│   ├── TenantModel.php
│   ├── WaChannelModel.php
│   ├── TenantConfigModel.php
│   ├── KnowledgeBaseModel.php
│   ├── ConversationModel.php
│   ├── HandoverLogModel.php
│   ├── HandoverKeywordModel.php
│   ├── AgentMessageModel.php
│   └── SseEventModel.php
├── Services/
│   ├── AiService.php                # Abstraksi ke Gemini / Grok
│   ├── FonnteService.php            # Kirim pesan via Fonnte API
│   ├── WebhookProcessorService.php  # Orkestrasi: terima → proses → balas
│   ├── HandoverService.php          # Logic deteksi & trigger handover
│   └── AgentChatService.php         # Logic ambil alih, balas manual, kembalikan ke AI
└── Views/
    ├── _config/
    │   ├── theme.php           # Global theme config (warna, font, logo, app name)
    │   ├── menu_superadmin.php # Definisi menu sidebar Super Admin
    │   └── menu_tenant.php     # Definisi menu sidebar Tenant (semua role)
    ├── _layouts/
    │   ├── superadmin.php      # Layout utama Super Admin
    │   └── tenant.php          # Layout utama Tenant
    ├── _partials/
    │   ├── header.php          # Tag <head>, meta, CSS includes
    │   ├── navbar.php          # Top navbar
    │   ├── sidebar.php         # Sidebar (render menu dari config)
    │   ├── footer.php          # Footer + JS includes
    │   └── flash_message.php   # Alert success/error/warning
    ├── _components/
    │   ├── stat_card.php       # Card statistik (icon, angka, label)
    │   ├── data_table.php      # Wrapper tabel dengan search & pagination
    │   ├── modal_confirm.php   # Modal konfirmasi delete/action
    │   ├── badge_status.php    # Badge status (active, pending, dll)
    │   ├── empty_state.php     # Tampilan kosong ketika data tidak ada
    │   └── page_header.php     # Judul halaman + breadcrumb + action button
    ├── superadmin/
    │   ├── dashboard/
    │   │   └── index.php
    │   └── tenant/
    │       ├── index.php
    │       ├── create.php
    │       └── edit.php
    └── tenant/
        ├── dashboard/
        │   └── index.php
        ├── channels/
        │   ├── index.php
        │   ├── create.php
        │   └── edit.php
        ├── knowledge_base/
        │   ├── index.php
        │   ├── create.php
        │   └── edit.php
        ├── config/
        │   └── index.php
        ├── keywords/
        │   └── index.php
        ├── users/
        │   ├── index.php
        │   └── create.php
        ├── conversations/
        │   └── index.php
        └── handover/
            ├── index.php
            ├── detail.php
            └── chat.php        # Live chat UI agent (SSE-powered)
```

---

## 7. Arsitektur Frontend

### 7.1 Prinsip

Frontend dibangun dengan sistem **dua lapis**:
- **Layout Partial** — memecah halaman menjadi potongan yang di-include: header, navbar, sidebar, footer
- **Reusable UI Components** — elemen UI yang bisa dipanggil berulang dari view manapun seperti fungsi

Tidak boleh ada duplikasi HTML. Jika suatu elemen muncul di lebih dari 1 halaman, ia **harus** jadi component atau partial.

### 7.2 Cara Render Layout

Setiap view halaman menggunakan layout wrapper. Controller memanggil:
```php
// Di Controller
return view('_layouts/tenant', [
    'title'   => 'Knowledge Base',
    'content' => view('tenant/knowledge_base/index', $data),
]);
```

Layout (`_layouts/tenant.php`) berisi:
```php
<?= view('_partials/header', ['title' => $title]) ?>
<?= view('_partials/navbar') ?>
<?= view('_partials/sidebar', ['menu' => config_menu('tenant', $currentRole)]) ?>

<main class="main-content">
    <?= view('_partials/flash_message') ?>
    <?= $content ?>
</main>

<?= view('_partials/footer') ?>
```

### 7.3 Cara Pakai Component

Component dipanggil seperti include dengan data:
```php
// Stat card
<?= view('_components/stat_card', [
    'icon'  => 'bi-chat-dots',
    'value' => $total_conversations,
    'label' => 'Percakapan Hari Ini',
    'color' => 'primary',
]) ?>

// Page header
<?= view('_components/page_header', [
    'title'      => 'Knowledge Base',
    'breadcrumb' => [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'KB']],
    'action'     => ['label' => '+ Tambah', 'url' => '/kb/create'],
]) ?>

// Badge status
<?= view('_components/badge_status', ['status' => $handover->status]) ?>
```

### 7.4 Global Theme Config (`_config/theme.php`)

File ini adalah **satu-satunya tempat** untuk mengubah tampilan global. Tidak boleh ada nilai warna, font, atau nama app yang hardcode di view lain.

```php
<?php
// app/Views/_config/theme.php
return [
    'app_name'     => 'Verra',
    'app_tagline'  => 'CS AI Platform',
    'logo_path'    => '/assets/img/logo.svg',
    'favicon_path' => '/assets/img/favicon.ico',

    'colors' => [
        'primary'   => '#6366f1',   // Indigo
        'secondary' => '#8b5cf6',   // Violet
        'success'   => '#22c55e',
        'danger'    => '#ef4444',
        'warning'   => '#f59e0b',
        'info'      => '#3b82f6',
        'dark'      => '#1e293b',
        'sidebar_bg'=> '#1e293b',
        'navbar_bg' => '#ffffff',
    ],

    'font' => [
        'family' => 'Inter',
        'url'    => 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap',
    ],

    'layout' => [
        'sidebar_width'          => '260px',
        'sidebar_collapsed_width'=> '70px',
        'navbar_height'          => '60px',
    ],
];
```

Theme config diakses via helper:
```php
// app/Helpers/theme_helper.php
function theme(string $key = null) {
    static $config = null;
    if ($config === null) {
        $config = include APPPATH . 'Views/_config/theme.php';
    }
    if ($key === null) return $config;
    return data_get($config, $key); // dot notation support
}
```

Dipakai di view:
```php
<title><?= theme('app_name') ?> - <?= $title ?></title>
<link href="<?= theme('font.url') ?>" rel="stylesheet">
<style>
    :root {
        --color-primary: <?= theme('colors.primary') ?>;
        --sidebar-width: <?= theme('layout.sidebar_width') ?>;
    }
</style>
```

### 7.5 Menu Config (`_config/menu_tenant.php`)

Menu sidebar didefinisikan sebagai array, bukan hardcode di HTML. Ini memudahkan penambahan menu tanpa menyentuh partial sidebar.

```php
<?php
// app/Views/_config/menu_tenant.php
return [
    'tenant_admin' => [
        ['icon' => 'bi-speedometer2', 'label' => 'Dashboard',      'url' => '/dashboard'],
        ['icon' => 'bi-phone',        'label' => 'WA Channels',     'url' => '/channels'],
        ['icon' => 'bi-book',         'label' => 'Knowledge Base',  'url' => '/kb'],
        ['icon' => 'bi-gear',         'label' => 'Konfigurasi AI',  'url' => '/config'],
        ['icon' => 'bi-key',          'label' => 'Handover Keywords','url' => '/keywords'],
        ['icon' => 'bi-people',       'label' => 'Users',           'url' => '/users'],
        ['icon' => 'bi-chat-left-text','label'=> 'Conversations',   'url' => '/conversations'],
        ['icon' => 'bi-person-lines-fill','label'=>'Handover',      'url' => '/handover'],
    ],
    'operator' => [
        ['icon' => 'bi-speedometer2',  'label' => 'Dashboard',    'url' => '/dashboard'],
        ['icon' => 'bi-chat-left-text','label' => 'Conversations', 'url' => '/conversations'],
        ['icon' => 'bi-person-lines-fill','label'=>'Handover',    'url' => '/handover'],
    ],
    'agent' => [
        ['icon' => 'bi-speedometer2',    'label' => 'Dashboard', 'url' => '/dashboard'],
        ['icon' => 'bi-person-lines-fill','label'=>'Handover',   'url' => '/handover'],
    ],
];
```

Helper untuk ambil menu:
```php
function config_menu(string $layout, string $role): array {
    $file = ($layout === 'superadmin')
        ? include APPPATH . 'Views/_config/menu_superadmin.php'
        : include APPPATH . 'Views/_config/menu_tenant.php';
    return $file[$role] ?? [];
}
```

### 7.6 Aturan Wajib FE

- **Dilarang** hardcode warna, font name, atau app name di view manapun — semua dari `theme()`
- **Dilarang** duplikasi navbar/sidebar/footer HTML — selalu pakai partial
- **Dilarang** copy-paste blok HTML yang sama di lebih dari 1 file — buat jadi component
- Semua halaman wajib melewati layout (`_layouts/tenant.php` atau `_layouts/superadmin.php`)
- Tambah menu baru cukup edit `_config/menu_tenant.php` atau `_config/menu_superadmin.php`
- Ganti warna/font/logo cukup edit `_config/theme.php`

---

## 8. Logika Webhook & AI

### 7.1 Endpoint Webhook
```
POST /webhook/{channel_uuid}
```
- Endpoint ini **publik** (tidak perlu auth)
- CI4 Route: `$routes->post('webhook/(:segment)', 'WebhookController::receive/$1');`
- Validasi: cek `channel_uuid` ada di tabel `wa_channels` dan `is_active = 1`

### 7.2 Alur Pemrosesan Pesan (WebhookProcessorService)

```
1. Terima payload dari Fonnte
2. Identifikasi tenant & channel dari channel_uuid
3. Cek apakah wa_number customer punya handover aktif (status: pending/in_progress)
   DAN mode = 'agent'
   → Jika YA: simpan pesan ke conversations (role: user), insert SSE event
     'new_message', STOP — agent yang akan balas manual via UI
   → Jika TIDAK / mode = 'ai': lanjut ke step 4
4. Simpan pesan customer ke tabel conversations (role: user)
5. Cek trigger keyword handover
   → Jika MATCH: buat record handover_log, kirim pesan "Menghubungkan ke agen kami...", stop
   → Jika TIDAK: lanjut ke step 6
6. Ambil knowledge_base aktif milik tenant (semua record is_active=1)
7. Ambil history percakapan (N turn terakhir sesuai max_history di config)
8. Build prompt:
   - System prompt = tenant config system_prompt + "\n\n## KNOWLEDGE BASE:\n" + seluruh KB
   - Messages = history + pesan baru
9. Kirim ke AI provider sesuai config tenant (Gemini / Grok)
10. Jika AI response mengindikasikan tidak bisa menjawab (deteksi via keyword response AI)
    → Trigger handover otomatis (trigger_type: ai_unable)
11. Simpan response AI ke conversations (role: assistant)
12. Kirim response via Fonnte API ke wa_number customer
```

### 7.3 Build Prompt
```
[SYSTEM]
{tenant_config.system_prompt}

## KNOWLEDGE BASE:
### {category}
**{title}**
{content}

[Jika ada banyak KB, semua di-concat di sini]

[MESSAGES]
- {history N turn terakhir}
- {pesan baru dari customer}
```

### 7.4 Deteksi AI Unable
Cek apakah response AI mengandung frasa berikut (case-insensitive):
- "tidak tahu"
- "tidak memiliki informasi"
- "hubungi kami"
- "silakan hubungi"
- "di luar kemampuan saya"

Jika match → trigger handover dengan `trigger_type = 'ai_unable'`

---

## 8. Agent Chat — Ambil Alih & Kembalikan ke AI

### 8.1 Konsep Mode

Setiap handover aktif memiliki kolom `mode`:
- `mode = 'ai'` → pesan customer diproses AI seperti biasa
- `mode = 'agent'` → pesan customer disimpan tapi tidak diproses AI, agent balas manual via UI

### 8.2 Alur Ambil Alih (Claim)

```
Agent buka halaman Handover Detail
→ Klik tombol "Ambil Alih"
→ POST /agent-chat/{handover_id}/claim
→ AgentChatService::claim():
   - Validasi handover milik tenant yang sama
   - Validasi status masih 'pending' atau 'in_progress'
   - Update handover_log: status='in_progress', mode='agent',
     claimed_by={agent_id}, claimed_at=NOW()
   - Insert SSE event: 'handover_claimed'
→ UI beralih ke mode live chat
→ Input pesan muncul, agent bisa mulai balas
```

### 8.3 Alur Agent Balas Pesan

```
Agent ketik pesan → klik Kirim
→ POST /agent-chat/{handover_id}/send
→ AgentChatService::sendMessage():
   - Validasi agent adalah claimed_by di handover ini
   - Kirim pesan via FonnteService ke wa_number customer
   - Simpan ke agent_messages
   - Simpan juga ke conversations (role: 'assistant') agar history utuh
   - Insert SSE event: 'new_message' (payload: pesan, sender: 'agent', nama agent)
→ Pesan muncul di UI agent secara real-time via SSE
```

### 8.4 Alur Kembalikan ke AI

```
Agent klik tombol "Kembalikan ke AI"
→ POST /agent-chat/{handover_id}/return-to-ai
→ AgentChatService::returnToAi():
   - Update handover_log: mode='ai', returned_to_ai_at=NOW()
   - Status tetap 'in_progress' (handover belum selesai, hanya ganti mode)
   - Insert SSE event: 'returned_to_ai'
→ Input pesan di UI agent di-disable
→ Pesan customer berikutnya kembali diproses AI
→ Agent tetap bisa lihat percakapan (read-only)
```

### 8.5 Alur Close Handover

```
Agent klik "Selesai / Close"
→ POST /agent-chat/{handover_id}/close
→ Update handover_log: status='handled', mode='ai', handled_at=NOW()
→ Percakapan berikutnya dari customer ini akan diproses AI dari awal
```

### 8.6 SSE Endpoint

```
GET /sse/{channel_id}/{wa_number}
Header: Accept: text/event-stream
```

- Endpoint ini di-subscribe oleh browser agent saat membuka halaman chat
- Server melakukan long-polling query ke `sse_events` setiap 1 detik
- Push event baru ke client dalam format SSE standard:
```
id: {event_id}
event: new_message
data: {"message":"halo kak","sender":"customer","timestamp":"..."}

id: {event_id}
event: returned_to_ai
data: {"agent_name":"Budi","timestamp":"..."}
```
- Koneksi SSE otomatis di-reconnect oleh browser jika terputus (native SSE behavior)
- Filter event hanya untuk `tenant_id` + `channel_id` + `wa_number` yang relevan

### 8.7 UI Agent Chat (`tenant/handover/chat.php`)

Halaman ini adalah **live chat interface** mirip WhatsApp Web:

- Kiri: daftar handover aktif milik tenant (sidebar percakapan)
- Kanan: bubble chat history percakapan yang dipilih
  - Bubble biru = customer
  - Bubble hijau = AI
  - Bubble ungu = agent (dengan nama agent)
- Header chat menampilkan: nomor WA customer, channel, status mode (badge "AI" / "Agent: [nama]")
- Tombol aksi di header:
  - Jika mode AI + status pending → **"Ambil Alih"** (primary)
  - Jika mode agent + claimed_by = saya → **"Kembalikan ke AI"** (warning) + **"Selesai"** (success)
  - Jika mode agent + claimed_by ≠ saya → tampilkan "Diambil oleh [nama]" (disabled)
- Input area muncul hanya jika agent adalah claimed_by yang aktif
- SSE subscribe otomatis saat halaman dibuka, disconnect saat halaman ditutup

AiService harus mengabstraksi perbedaan Gemini dan Grok sehingga WebhookProcessorService tidak perlu tahu provider yang aktif.

### Interface Logika
```php
// AiService::chat(array $config, string $systemPrompt, array $messages): string
// $config = row dari tenant_configs
// $messages = array of ['role' => 'user'|'assistant', 'content' => '...']
// return: string response teks dari AI
```

### Gemini API
- Endpoint: `https://generativelanguage.googleapis.com/v1beta/models/{model}:generateContent?key={api_key}`
- Method: POST
- Format messages: konversi `role: assistant` → `role: model` untuk Gemini
- System prompt: gunakan field `system_instruction`

### Grok API
- Endpoint: `https://api.x.ai/v1/chat/completions`
- Method: POST
- Format: OpenAI-compatible (role: system, user, assistant)
- System prompt: masukkan sebagai message pertama dengan role `system`

---

## 11. FonnteService

### Kirim Pesan
```
POST https://api.fonnte.com/send
Header: Authorization: {fonnte_token}
Body (form-data):
  target: {wa_number customer}
  message: {teks balasan}
  countryCode: 62
```

---

## 12. Admin Panel — Halaman & Fitur

### Super Admin
| Halaman | Fitur |
|---|---|
| Dashboard | Statistik: total tenant, total percakapan hari ini, total handover pending |
| Tenant List | CRUD tenant, activate/deactivate |
| Tenant Detail | Lihat semua user, channel, dan aktivitas tenant |

### Tenant Admin
| Halaman | Fitur |
|---|---|
| Dashboard | Statistik: total channel, percakapan hari ini, handover pending |
| WA Channels | CRUD channel (nama, nomor WA, token Fonnte). Tampilkan webhook URL per channel |
| Knowledge Base | CRUD KB dengan kategori, judul, konten, sort order, toggle aktif |
| Konfigurasi AI | Pilih provider (Gemini/Grok), input API key, pilih model, edit system prompt, set max history |
| Handover Keywords | CRUD kata kunci trigger handover |
| User Management | Invite/CRUD user dalam tenant, assign role (operator/agent) |

### Operator
| Halaman | Fitur |
|---|---|
| Conversation List | Lihat semua percakapan per channel, filter by nomor WA, cari |
| Handover List | Lihat semua handover (read-only dari sisi operator) |

### Agent
| Halaman | Fitur |
|---|---|
| Handover List | Lihat handover berstatus pending/in_progress, filter by channel |
| Live Chat (`/handover/chat`) | Interface chat real-time: lihat percakapan, ambil alih, balas manual, kembalikan ke AI, close handover |

---

## 13. Keamanan

- **API keys tersimpan terenkripsi** di database menggunakan `encrypt()`/`decrypt()` bawaan CI4 (Encryption service)
- **Webhook endpoint tidak bisa di-brute force** karena menggunakan UUID v4 acak per channel
- **Tenant isolation wajib** — setiap Model yang berhubungan dengan data tenant harus override method query untuk selalu append `WHERE tenant_id = {current_tenant_id}`
- **CSRF protection** aktif untuk semua form admin panel
- **Shield** menangani session, hashing password, dan rate limiting login
- **Input sanitization** wajib pada semua input yang masuk ke database

---

## 14. Urutan Development (Sprint Order)

Kerjakan secara berurutan, jangan loncat-loncat:

1. **Setup CI4 + Shield** — instalasi, konfigurasi database, migrasi tabel Shield
2. **Migrasi Database** — buat semua migration file untuk tabel custom
3. **Seeder** — Super Admin default, role & permission Shield
4. **Auth** — Login/logout via Shield, redirect by role
5. **FE Foundation** — buat `_config/theme.php`, `_config/menu_superadmin.php`, `_config/menu_tenant.php`, `theme_helper.php`, semua `_partials/`, semua `_components/`, dan kedua `_layouts/`
6. **TenantFilter** — middleware inject `tenant_id` ke semua request
7. **Super Admin Panel** — CRUD Tenant
8. **Tenant Admin: WA Channels** — CRUD + generate webhook URL
9. **Tenant Admin: Konfigurasi AI** — form simpan config, enkripsi API key
10. **Tenant Admin: Knowledge Base** — CRUD KB
11. **Tenant Admin: Handover Keywords** — CRUD keywords
12. **AiService** — implementasi Gemini & Grok
13. **FonnteService** — implementasi send message
14. **WebhookController + WebhookProcessorService** — alur utama, cek mode agent
15. **Handover flow** — deteksi keyword, buat log, status & mode management
16. **AgentChatService** — claim, send, return-to-ai, close
17. **SseController** — SSE endpoint + SseEventModel
18. **Agent Panel: Live Chat UI** — bubble chat, SSE subscribe, tombol aksi
19. **Operator Panel** — conversation list & handover read-only
20. **Tenant Admin: User Management** — invite & assign role
21. **Super Admin: Dashboard** — statistik global
22. **Tenant Dashboard** — statistik per tenant
23. **SSE Cleanup** — scheduled task hapus sse_events > 5 menit
24. **Testing end-to-end** — simulasi webhook, ambil alih, balas, kembalikan ke AI, cek isolasi tenant

---

## 15. Catatan Penting untuk Agent

- **Jangan** buat endpoint atau fitur di luar yang terdefinisi di dokumen ini tanpa konfirmasi
- **Jangan** hardcode warna, font, nama app, atau menu item di dalam file view/partial/component — semua wajib dari `_config/theme.php` atau `_config/menu_*.php`
- **Jangan** duplikasi HTML navbar/sidebar/footer — selalu `view('_partials/...')`
- Setiap elemen UI yang muncul di lebih dari 1 halaman **wajib** dijadikan component di `_components/`
- **Selalu** gunakan Migration untuk perubahan database, jangan edit manual
- **Semua Model** dengan data tenant wajib ada proteksi `tenant_id` — tidak boleh ada query lintas tenant
- **Enkripsi API key** wajib, jangan simpan plain text
- **Webhook URL** format: `https://{domain}/webhook/{channel_uuid}` — tampilkan ini di UI channel management agar tenant bisa copy-paste ke Fonnte
- Gunakan **UUID v4** untuk `tenants.uuid` dan `wa_channels.uuid`, generate saat insert
- Response AI ke customer harus dalam **bahasa yang sama** dengan pesan customer — instruksikan ini di default system prompt
- Jika AI provider return error (API key salah, quota habis, dll) → **jangan crash**, kirim pesan fallback ke customer: *"Maaf, kami sedang mengalami gangguan. Silakan coba beberapa saat lagi."* dan log error-nya
- **Media/attachment tidak didukung** — Verra hanya mengirim dan menerima pesan teks. Jika perlu berbagi gambar/dokumen/video, agent cukup paste link (Google Drive, dll) sebagai teks biasa. Jangan implementasikan fitur upload atau pengiriman media apapun di v1. `Content-Type: text/event-stream`, `Cache-Control: no-cache`, `X-Accel-Buffering: no` — tanpa header ini SSE tidak akan berfungsi di Nginx/Apache
- **Jangan** proses pesan AI jika `handover_log.mode = 'agent'` — cek ini adalah gate pertama di WebhookProcessorService
- Saat agent balas via UI, pesan **wajib** disimpan di dua tempat: `agent_messages` (untuk audit trail agent) dan `conversations` (untuk history AI jika nanti dikembalikan ke AI)

---

*Dokumen ini adalah v1.0 — fitur billing, subscription plan, dan analytics lanjutan akan masuk PRD v2.0*
