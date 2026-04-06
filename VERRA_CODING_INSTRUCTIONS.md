# Verra — AI Coding Agent Instructions
> Desktop AI Customer Service tool berbasis WhatsApp
> Stack: Wails v2 + Go + React + SQLite

---

## Identitas Proyek

**Nama aplikasi:** Verra
**Tagline:** AI Customer Service yang jujur dan terpercaya
**Window default:** 1280×800px, resizable minimum 960×600px
**Target OS:** Windows (primary), macOS (secondary)

---

## Prinsip Utama yang Wajib Diikuti

1. **Maintainability first** — setiap file maksimal 300 baris. Kalau lebih, pecah jadi file terpisah.
2. **Separation of concerns** — Go handle semua business logic, React hanya handle UI state dan display.
3. **No magic numbers** — semua konstanta di `internal/config/constants.go`.
4. **Error selalu di-handle** — tidak ada `_` untuk error kecuali benar-benar intentional dan diberi komentar.
5. **Komentar bahasa Inggris** — semua komentar kode dalam bahasa Inggris agar konsisten.
6. **Commit kecil dan atomic** — satu fitur atau satu bugfix per commit.

---

## Struktur Folder Lengkap

```
verra/
├── main.go                          # Wails entry point — minimal, hanya init
├── app.go                           # Struct App, semua Wails binding method
├── wails.json
├── go.mod
├── go.sum
│
├── internal/
│   ├── config/
│   │   ├── config.go                # Load/save app config dari SQLite business_config
│   │   └── constants.go            # Semua konstanta: cooldown duration, default N-turn, dll
│   │
│   ├── db/
│   │   ├── sqlite.go                # Init DB, run migrations, return *sql.DB
│   │   ├── migrations/
│   │   │   ├── 001_init.sql
│   │   │   ├── 002_knowledge.sql
│   │   │   └── 003_handover_logs.sql
│   │   ├── conversation_repo.go     # CRUD conversations
│   │   ├── message_repo.go          # CRUD messages
│   │   ├── key_repo.go              # CRUD gemini_keys + LRU query
│   │   ├── knowledge_repo.go        # CRUD kb_faqs, kb_products, kb_sops, kb_notes
│   │   └── config_repo.go          # Read/write business_config singleton
│   │
│   ├── whatsapp/
│   │   ├── client.go                # Init whatsmeow client, QR handler, reconnect loop
│   │   ├── handler.go               # Event listener — on message received
│   │   └── sender.go                # SendText(jid, text) — single responsibility
│   │
│   ├── ai/
│   │   ├── dispatcher.go            # Dispatch(systemPrompt, history, userMsg) → response
│   │   ├── key_pool.go              # GetLRUKey(), SetCooldown(), UpdateLastUsed()
│   │   ├── context_builder.go       # BuildSystemPrompt(db, config, matchedSOP) → string
│   │   └── gemini.go                # Raw HTTP call ke Gemini API, parse response
│   │
│   ├── conversation/
│   │   ├── service.go               # Orkestrasi utama — HandleIncoming, ProcessWithAI
│   │   └── router.go                # Route pesan: ai mode vs human mode
│   │
│   ├── handover/
│   │   ├── engine.go                # TriggerHandover, AgentClaim, AgentResolve
│   │   └── trigger.go               # MatchKeywords, IsRepeatedMessage (Levenshtein)
│   │
│   ├── knowledge/
│   │   ├── service.go               # Business logic CRUD knowledge
│   │   ├── importer.go              # CSV parser — FAQ dan produk
│   │   └── extractor.go             # Ekstrak teks dari PDF dan DOCX
│   │
│   └── dto/
│       └── types.go                 # Semua struct yang di-share antar layer dan ke frontend
│
└── frontend/
    ├── index.html
    ├── package.json
    └── src/
        ├── main.jsx
        ├── App.jsx                  # Router utama antar halaman
        │
        ├── pages/
        │   ├── InboxPage.jsx        # Layout 3 kolom utama
        │   ├── KnowledgePage.jsx    # Knowledge base management
        │   ├── SettingsPage.jsx     # Business config
        │   └── APIKeysPage.jsx      # Gemini key management
        │
        ├── components/
        │   ├── layout/
        │   │   ├── Sidebar.jsx      # Nav + inbox list
        │   │   └── DetailPanel.jsx  # Customer info panel kanan
        │   │
        │   ├── chat/
        │   │   ├── ChatView.jsx     # Container chat
        │   │   ├── MessageBubble.jsx
        │   │   ├── ChatHeader.jsx   # Nama, status badge, tombol aksi
        │   │   ├── AIActiveBar.jsx  # Bottom bar saat AI mode
        │   │   ├── HandoverPendingBar.jsx
        │   │   └── AgentInputBar.jsx
        │   │
        │   ├── inbox/
        │   │   └── InboxItem.jsx    # Satu item di list inbox
        │   │
        │   ├── knowledge/
        │   │   ├── FAQTab.jsx
        │   │   ├── ProductTab.jsx
        │   │   ├── SOPTab.jsx
        │   │   └── NotesTab.jsx
        │   │
        │   └── ui/                  # Reusable primitives
        │       ├── StatusBadge.jsx
        │       ├── TagInput.jsx
        │       ├── Modal.jsx
        │       ├── ConfirmDialog.jsx
        │       ├── Toggle.jsx
        │       └── EmptyState.jsx
        │
        ├── hooks/
        │   ├── useConversation.js   # State & logic untuk active conversation
        │   ├── useInbox.js          # Inbox list state + WA event subscription
        │   └── useWailsEvent.js     # Wrapper untuk EventsOn/EventsOff lifecycle
        │
        ├── store/
        │   └── appStore.js          # Zustand store — selectedConvId, status map
        │
        └── lib/
            ├── wailsjs/             # Auto-generated Go bindings (jangan edit manual)
            ├── format.js            # formatRupiah, formatTimestamp, truncate
            └── constants.js         # Status string constants yang mirror Go constants
```

---

## Database Schema Lengkap

Buat file `internal/db/migrations/001_init.sql`:

```sql
CREATE TABLE IF NOT EXISTS conversations (
    id               TEXT PRIMARY KEY,  -- WA JID (e.g. 628xxx@s.whatsapp.net)
    customer_name    TEXT NOT NULL DEFAULT '',
    status           TEXT NOT NULL DEFAULT 'ai'
                         CHECK(status IN ('ai','handover_pending','human','resolved')),
    last_message_at  DATETIME,
    handover_reason  TEXT,
    created_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS messages (
    id               TEXT PRIMARY KEY,  -- WA message ID
    conversation_id  TEXT NOT NULL REFERENCES conversations(id),
    role             TEXT NOT NULL CHECK(role IN ('customer','ai','agent')),
    content          TEXT NOT NULL,
    created_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_messages_conv_created
    ON messages(conversation_id, created_at DESC);

CREATE TABLE IF NOT EXISTS gemini_keys (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    label           TEXT NOT NULL,
    api_key         TEXT NOT NULL,  -- AES-GCM encrypted
    last_used_at    DATETIME,
    cooldown_until  DATETIME,
    total_requests  INTEGER NOT NULL DEFAULT 0,
    is_active       INTEGER NOT NULL DEFAULT 1
);

CREATE TABLE IF NOT EXISTS handover_logs (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    conversation_id TEXT NOT NULL REFERENCES conversations(id),
    trigger_type    TEXT NOT NULL CHECK(trigger_type IN ('keyword','sop_keyword','repeated','manual')),
    trigger_detail  TEXT,
    handed_over_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    resolved_at     DATETIME
);

CREATE TABLE IF NOT EXISTS business_config (
    id               INTEGER PRIMARY KEY DEFAULT 1,
    business_name    TEXT NOT NULL DEFAULT 'Toko Saya',
    ai_persona       TEXT NOT NULL DEFAULT 'Ramah, sopan, dan membantu',
    language         TEXT NOT NULL DEFAULT 'Indonesia',
    context_window_n INTEGER NOT NULL DEFAULT 10,
    handover_keywords TEXT NOT NULL DEFAULT '["komplain","refund","minta cs","bicara manusia","tidak puas"]',
    greeting_message TEXT NOT NULL DEFAULT 'Halo! Ada yang bisa saya bantu?',
    handover_message TEXT NOT NULL DEFAULT 'Baik kak, saya hubungkan ke tim CS kami ya. Mohon tunggu sebentar 🙏',
    handover_wait_message TEXT NOT NULL DEFAULT 'Maaf kak, CS kami sedang memproses. Mohon tunggu sebentar ya 🙏'
);

INSERT OR IGNORE INTO business_config (id) VALUES (1);
```

Buat file `internal/db/migrations/002_knowledge.sql`:

```sql
CREATE TABLE IF NOT EXISTS kb_faqs (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    question    TEXT NOT NULL,
    answer      TEXT NOT NULL,
    category    TEXT NOT NULL DEFAULT 'umum',
    sort_order  INTEGER NOT NULL DEFAULT 0,
    is_active   INTEGER NOT NULL DEFAULT 1,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS kb_products (
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    name         TEXT NOT NULL,
    price        INTEGER NOT NULL DEFAULT 0,
    description  TEXT NOT NULL DEFAULT '',
    stock_status TEXT NOT NULL DEFAULT 'available'
                     CHECK(stock_status IN ('available','out_of_stock','pre_order')),
    category     TEXT NOT NULL DEFAULT 'umum',
    is_active    INTEGER NOT NULL DEFAULT 1,
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS kb_sops (
    id               INTEGER PRIMARY KEY AUTOINCREMENT,
    title            TEXT NOT NULL,
    trigger_keywords TEXT NOT NULL DEFAULT '[]',  -- JSON array
    steps            TEXT NOT NULL DEFAULT '[]',  -- JSON array of strings
    escalate_to_human INTEGER NOT NULL DEFAULT 0,
    is_active        INTEGER NOT NULL DEFAULT 1,
    created_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS kb_notes (
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    title        TEXT NOT NULL,
    content      TEXT NOT NULL,
    category     TEXT NOT NULL DEFAULT 'umum',
    source_file  TEXT,  -- NULL jika input manual
    is_active    INTEGER NOT NULL DEFAULT 1,
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

---

## Go Dependencies

```
go.mod dependencies yang dibutuhkan:

github.com/wailsapp/wails/v2
go.mau.fi/whatsmeow
github.com/mattn/go-sqlite3
github.com/denisbrodbeck/machineid   -- untuk enkripsi API key per mesin
github.com/google/uuid
github.com/ledongthuc/pdf            -- ekstrak teks dari PDF
golang.org/x/text                    -- text normalization untuk Levenshtein
```

Frontend dependencies:
```
zustand          -- state management, ringan dan simple
react-hot-toast  -- notifikasi/toast
lucide-react     -- icon library
date-fns         -- formatting tanggal
papaparse        -- CSV parsing di sisi frontend (preview sebelum import)
```

---

## Wails Binding Methods (app.go)

Semua method di bawah wajib diexpose ke frontend via Wails. Naming convention: PascalCase.

```go
// --- Conversation ---
GetInbox() []dto.ConversationSummary
GetMessages(convID string, limit int) []dto.Message
AgentClaimHandover(convID string) error
AgentSendMessage(convID string, text string) error
AgentResolveConversation(convID string) error

// --- WhatsApp ---
GetWAStatus() dto.WAStatus          // connected | disconnected | connecting
GetQRCode() string                  // base64 QR image, kosong jika sudah connected
DisconnectWA() error

// --- Knowledge Base ---
GetFAQs() []dto.FAQ
SaveFAQ(faq dto.FAQ) error          // insert jika id=0, update jika id>0
DeleteFAQ(id int) error
ReorderFAQs(ids []int) error        // update sort_order batch

GetProducts() []dto.Product
SaveProduct(p dto.Product) error
DeleteProduct(id int) error

GetSOPs() []dto.SOP
SaveSOP(s dto.SOP) error
DeleteSOP(id int) error

GetNotes() []dto.Note
SaveNote(n dto.Note) error
DeleteNote(id int) error

ImportFAQFromCSV(csvContent string) (int, error)    // return jumlah row berhasil
ImportProductFromCSV(csvContent string) (int, error)
ImportNoteFromFile(filename string, fileBytes []byte) error  -- handle PDF/DOCX/TXT

// --- Config ---
GetBusinessConfig() dto.BusinessConfig
SaveBusinessConfig(cfg dto.BusinessConfig) error

// --- API Keys ---
GetAPIKeys() []dto.APIKeySafe       -- JANGAN return key asli, return masked version
AddAPIKey(label string, apiKey string) error
ToggleAPIKey(id int, active bool) error
DeleteAPIKey(id int) error
```

---

## Wails Events (Go → Frontend)

Gunakan `runtime.EventsEmit` dari Go ke frontend. Frontend subscribe dengan `EventsOn`.

```
"verra:inbox_update"      -- payload: ConversationSummary — update item di sidebar
"verra:new_message"       -- payload: Message — append ke chat view
"verra:status_change"     -- payload: {convID, status} — update badge + bottom bar
"verra:handover_alert"    -- payload: {convID, customerName, triggerType} — notif merah
"verra:wa_status"         -- payload: WAStatus — update koneksi indicator
"verra:qr_code"           -- payload: base64 string — tampilkan QR di settings
```

---

## DTO Types (internal/dto/types.go)

```go
package dto

type ConversationSummary struct {
    ID             string `json:"id"`
    CustomerName   string `json:"customer_name"`
    LastMessage    string `json:"last_message"`
    LastMessageAt  string `json:"last_message_at"`  // ISO 8601
    Status         string `json:"status"`
    UnreadCount    int    `json:"unread_count"`
}

type Message struct {
    ID             string `json:"id"`
    ConversationID string `json:"conversation_id"`
    Role           string `json:"role"`   // customer | ai | agent
    Content        string `json:"content"`
    CreatedAt      string `json:"created_at"`
}

type WAStatus struct {
    State   string `json:"state"`   // connected | disconnected | connecting
    Phone   string `json:"phone"`   // nomor WA yang terkoneksi
}

type BusinessConfig struct {
    BusinessName        string   `json:"business_name"`
    AIPersona           string   `json:"ai_persona"`
    Language            string   `json:"language"`
    ContextWindowN      int      `json:"context_window_n"`
    HandoverKeywords    []string `json:"handover_keywords"`
    GreetingMessage     string   `json:"greeting_message"`
    HandoverMessage     string   `json:"handover_message"`
    HandoverWaitMessage string   `json:"handover_wait_message"`
}

type APIKeySafe struct {
    ID            int    `json:"id"`
    Label         string `json:"label"`
    MaskedKey     string `json:"masked_key"`   // "••••••••••••AbCdEf"
    IsActive      bool   `json:"is_active"`
    LastUsedAt    string `json:"last_used_at"`
    TotalRequests int    `json:"total_requests"`
    InCooldown    bool   `json:"in_cooldown"`
}

type FAQ struct {
    ID        int    `json:"id"`
    Question  string `json:"question"`
    Answer    string `json:"answer"`
    Category  string `json:"category"`
    SortOrder int    `json:"sort_order"`
    IsActive  bool   `json:"is_active"`
}

type Product struct {
    ID          int    `json:"id"`
    Name        string `json:"name"`
    Price       int    `json:"price"`
    Description string `json:"description"`
    StockStatus string `json:"stock_status"`
    Category    string `json:"category"`
    IsActive    bool   `json:"is_active"`
}

type SOP struct {
    ID               int      `json:"id"`
    Title            string   `json:"title"`
    TriggerKeywords  []string `json:"trigger_keywords"`
    Steps            []string `json:"steps"`
    EscalateToHuman  bool     `json:"escalate_to_human"`
    IsActive         bool     `json:"is_active"`
}

type Note struct {
    ID         int    `json:"id"`
    Title      string `json:"title"`
    Content    string `json:"content"`
    Category   string `json:"category"`
    SourceFile string `json:"source_file"`
    IsActive   bool   `json:"is_active"`
    UpdatedAt  string `json:"updated_at"`
}
```

---

## Core Business Logic

### conversation/service.go

```
FUNCTION HandleIncomingMessage(waMsg):

  // 1. Persist & upsert
  SaveMessage(role=customer, content=waMsg.Text)
  conv = UpsertConversation(waMsg.JID, waMsg.PushName)
  Emit("verra:inbox_update", conv)
  Emit("verra:new_message", msg)

  // 2. Route
  IF conv.Status == "human":
    RETURN  // AI diam, agent handle

  IF conv.Status == "handover_pending":
    WA.Send(conv.ID, config.HandoverWaitMessage)
    RETURN

  // 3. AI flow
  ProcessWithAI(conv, msg)

FUNCTION ProcessWithAI(conv, msg):

  // Check SOP first — no API call if escalate
  matchedSOP = SOP.MatchKeywords(msg.Content)
  IF matchedSOP != nil AND matchedSOP.EscalateToHuman:
    TriggerHandover(conv, "sop_keyword", matchedSOP.Title)
    RETURN

  // Check handover keywords
  IF trigger.MatchesHandoverKeyword(msg.Content, config.HandoverKeywords):
    TriggerHandover(conv, "keyword", msg.Content)
    RETURN

  // Check repeated message (frustration signal)
  IF trigger.IsRepeatedMessage(conv.ID, msg.Content):
    TriggerHandover(conv, "repeated", msg.Content)
    RETURN

  // Build context and call Gemini
  systemPrompt = contextBuilder.Build(matchedSOP)
  history      = db.GetLastNMessages(conv.ID, config.ContextWindowN)

  aiResp, err = ai.Dispatch(systemPrompt, history, msg.Content)
  IF err == ErrAllKeysExhausted:
    WA.Send(conv.ID, "Maaf, sedang sibuk. Mohon tunggu sebentar.")
    RETURN
  IF err != nil:
    log.Error(err)
    RETURN

  // Save and send
  SaveMessage(role=ai, content=aiResp.Text)
  WA.Send(conv.ID, aiResp.Text)
  Emit("verra:new_message", aiMsg)
```

### ai/dispatcher.go

```
FUNCTION Dispatch(systemPrompt, history, userMsg):
  availableKeys = db.GetActiveKeys()
  maxRetry      = len(availableKeys)

  FOR attempt = 0 TO maxRetry:
    key = db.GetLRUKey()
    // Query: WHERE is_active=1 AND (cooldown_until IS NULL OR cooldown_until < NOW())
    // ORDER BY last_used_at ASC LIMIT 1

    IF key == nil:
      RETURN nil, ErrAllKeysExhausted

    db.UpdateKeyLastUsed(key.ID)       // set sebelum request
    db.IncrementKeyRequestCount(key.ID)

    resp, err = gemini.Call(key.APIKey, systemPrompt, history, userMsg)

    IF err == nil:
      RETURN resp, nil

    IF err == ErrRateLimit (HTTP 429):
      db.SetKeyCooldown(key.ID, now + 60s)
      CONTINUE  // coba key berikutnya

    RETURN nil, err  // error lain, stop

  RETURN nil, ErrAllKeysExhausted
```

### handover/trigger.go — IsRepeatedMessage

```
FUNCTION IsRepeatedMessage(convID, newMsg):
  // Ambil 5 pesan terakhir dari customer di conversation ini
  recentMsgs = db.GetLastNCustomerMessages(convID, 5)

  matchCount = 0
  FOR each msg IN recentMsgs:
    similarity = LevenshteinSimilarity(newMsg, msg.Content)
    IF similarity >= 0.80:   // 80% mirip dianggap sama
      matchCount++

  RETURN matchCount >= 2   // muncul 2+ kali = repeated
```

### ai/context_builder.go

```
FUNCTION BuildSystemPrompt(matchedSOP):
  parts = []

  // 1. Identity — always
  parts.append("""
    Kamu adalah customer service dari {config.BusinessName}.
    {config.AIPersona}.
    Balas dalam bahasa {config.Language}.
    Jangan pernah menyebut bahwa kamu adalah AI kecuali ditanya langsung.
  """)

  // 2. Products — always
  products = db.GetActiveProducts()
  IF len(products) > 0:
    parts.append(FormatProducts(products))

  // 3. FAQ — always, ordered by sort_order
  faqs = db.GetActiveFAQs()
  IF len(faqs) > 0:
    parts.append(FormatFAQs(faqs))

  // 4. SOP — conditional, only if keyword matched and NOT escalate
  IF matchedSOP != nil AND NOT matchedSOP.EscalateToHuman:
    parts.append(FormatSOP(matchedSOP))

  // 5. Notes — always, all active
  notes = db.GetActiveNotes()
  IF len(notes) > 0:
    parts.append(FormatNotes(notes))

  // 6. Closing rules — always
  parts.append("""
    Aturan penting:
    - Jika tidak tahu jawabannya, katakan 'saya cek dulu ya kak' — JANGAN mengarang.
    - Jawab singkat dan natural, bukan seperti robot.
    - Gunakan 'kak' sebagai sapaan.
    - Jika customer meminta bicara dengan manusia, balas: 'baik kak, saya hubungkan ke tim kami ya 🙏'
  """)

  RETURN join(parts, "\n\n")
```

---

## Enkripsi API Key

Gunakan `machineid` + AES-GCM:

```go
// internal/config/crypto.go

// GetEncryptionKey derives 32-byte key from machine ID
// This ensures encrypted keys are only valid on the same machine
func GetEncryptionKey() ([]byte, error) {
    id, err := machineid.ProtectedID("verra")
    // hash machine ID to get consistent 32-byte key
    // use sha256.Sum256([]byte(id))
}

func EncryptAPIKey(plaintext string) (string, error)  // return base64 ciphertext
func DecryptAPIKey(ciphertext string) (string, error) // return plaintext
```

Enkripsi terjadi saat `AddAPIKey`, dekripsi terjadi saat `gemini.Call` — tidak pernah expose plaintext ke frontend.

---

## Frontend Architecture

### State Management (Zustand)

```js
// store/appStore.js
const useAppStore = create((set) => ({
  // Navigation
  activePage: 'inbox',           // inbox | knowledge | settings | apikeys
  setActivePage: (page) => set({ activePage: page }),

  // Inbox
  conversations: [],
  selectedConvId: null,
  setConversations: (convs) => set({ conversations: convs }),
  setSelectedConvId: (id) => set({ selectedConvId: id }),
  updateConversation: (conv) => set((state) => ({
    conversations: state.conversations.map(c => c.id === conv.id ? conv : c)
  })),

  // WA Status
  waStatus: 'disconnected',
  setWAStatus: (status) => set({ waStatus: status }),
}))
```

### Custom Hook: useWailsEvent

```js
// hooks/useWailsEvent.js
// Wrapper untuk cleanup otomatis EventsOn saat component unmount
export function useWailsEvent(eventName, callback) {
  useEffect(() => {
    const unlisten = EventsOn(eventName, callback)
    return () => unlisten()   // cleanup on unmount
  }, [eventName, callback])
}
```

### Custom Hook: useConversation

```js
// hooks/useConversation.js
export function useConversation(convId) {
  const [messages, setMessages] = useState([])
  const [loading, setLoading] = useState(false)

  // Load initial messages
  useEffect(() => {
    if (!convId) return
    setLoading(true)
    GetMessages(convId, 50).then(setMessages).finally(() => setLoading(false))
  }, [convId])

  // Subscribe to new messages
  useWailsEvent('verra:new_message', (msg) => {
    if (msg.conversation_id !== convId) return
    setMessages(prev => [...prev, msg])
    // Auto-scroll to bottom
    scrollToBottom()
  })

  return { messages, loading }
}
```

---

## UI/UX Guidelines

### Layout
- Tiga kolom: Sidebar 240px fixed | Chat flex | Detail panel 300px fixed (collapsible)
- Detail panel collapsed by default pada window < 1100px
- Sidebar tidak bisa di-resize (fixed)

### Status Color System
```
AI mode       → green  (#22C55E) — "sistem berjalan normal"
Pending       → amber  (#F59E0B) — "butuh perhatian"
Human mode    → blue   (#3B82F6) — "agent sedang handle"
Resolved      → gray   (#9CA3AF) — "selesai"
```

### Bottom Bar Logic (ChatView)
```
conv.status == "ai"               → AIActiveBar (pulsing green dot + "AI aktif")
conv.status == "handover_pending" → HandoverPendingBar (amber bg + tombol "Ambil Alih")
conv.status == "human"            → AgentInputBar (text input + send + tombol "Selesai")
```
**Penting:** Bottom bar adalah single conditional render — bukan tiga komponen sekaligus.

### Handover Alert
- Gunakan `react-hot-toast` dengan custom style untuk handover alert
- Toast persistent (tidak auto-dismiss) hingga agent klik "Ambil Alih" atau dismiss manual
- Tampil di pojok kanan atas
- Sidebar item yang bersangkutan: tambah pulsing amber dot indicator

### Empty States
Setiap halaman wajib punya empty state yang informatif:
- Inbox kosong → "Belum ada percakapan. Hubungkan WhatsApp untuk mulai."
- Knowledge kosong → "Belum ada [FAQ/Produk/SOP]. Tambahkan sekarang."

### Form Validation
- Semua form validate di frontend sebelum memanggil Go binding
- Error inline di bawah field, bukan alert popup
- Tombol submit disabled saat loading

### Tabel Knowledge Base
Semua tab di Knowledge Base pakai komponen `KnowledgeTable` yang reusable:
- Kolom: content preview | category | status toggle | actions (edit, delete)
- Row click → buka modal edit
- Bulk delete dengan checkbox (opsional, bisa dikerjakan belakangan)
- Drag-and-drop reorder hanya untuk FAQ tab (sort_order)

---

## Error Handling Pattern

### Go side
```go
// Selalu wrap error dengan context
if err != nil {
    return fmt.Errorf("conversation.ProcessWithAI: dispatch failed: %w", err)
}
```

### Frontend side
```js
// Semua Wails call dibungkus try-catch
async function handleSend() {
  try {
    await AgentSendMessage(convId, text)
  } catch (err) {
    toast.error('Gagal mengirim pesan. Coba lagi.')
    console.error('AgentSendMessage:', err)
  }
}
```

---

## WhatsApp Connection Flow

```
App startup:
  1. Cek apakah whatsmeow session sudah ada di DB
  2. Jika ada → auto connect, emit "verra:wa_status" = "connecting" lalu "connected"
  3. Jika tidak ada → emit "verra:wa_status" = "disconnected"
     → Frontend tampilkan prompt "Hubungkan WhatsApp" di Settings
     → User klik → GetQRCode() → tampilkan QR
     → whatsmeow emit QR via "verra:qr_code" setiap QR refresh
     → Setelah scan → emit "verra:wa_status" = "connected"

Reconnect:
  - whatsmeow handle reconnect otomatis dengan backoff
  - Setiap perubahan state koneksi → emit "verra:wa_status"
```

---

## CSV Import Format

Sediakan template CSV yang bisa didownload dari UI.

**FAQ template:**
```csv
question,answer,category
Bagaimana cara order?,Klik tombol beli di halaman produk kemudian...,order
Berapa lama pengiriman?,Pengiriman memakan waktu 2-3 hari kerja...,pengiriman
```

**Produk template:**
```csv
name,price,description,stock_status,category
Produk A,150000,Deskripsi produk A,available,kategori1
Produk B,200000,Deskripsi produk B,out_of_stock,kategori2
```

Saat import: parse di Go menggunakan `encoding/csv`, validasi setiap row, return jumlah sukses dan list error per baris ke frontend. Frontend tampilkan preview tabel sebelum konfirmasi import.

---

## File Extraction untuk kb_notes

```go
// internal/knowledge/extractor.go

func ExtractText(filename string, data []byte) (string, error) {
    ext = strings.ToLower(filepath.Ext(filename))
    switch ext {
    case ".txt":
        return string(data), nil
    case ".pdf":
        return extractPDF(data)
    case ".docx":
        return extractDOCX(data)
    default:
        return "", ErrUnsupportedFormat
    }
}
```

Hasil ekstraksi langsung masuk ke `kb_notes` dengan `source_file` = nama file asli. Admin bisa edit konten hasil ekstraksi dari UI kalau ada yang tidak bersih.

---

## Constants (internal/config/constants.go)

```go
package config

const (
    // AI dispatcher
    KeyCooldownDuration  = 60 * time.Second
    DefaultContextWindow = 10
    MaxRetryPerDispatch  = 50  // maksimal retry = jumlah key aktif, tapi cap di 50

    // Repeated message detection
    RepeatedMsgLookback    = 5     // cek N pesan terakhir
    RepeatedMsgThreshold   = 2     // minimal N match untuk trigger handover
    LevenshteinSimilarity  = 0.80  // 80% similarity = dianggap sama

    // Gemini
    GeminiAPIEndpoint = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent"

    // Wails events
    EventInboxUpdate   = "verra:inbox_update"
    EventNewMessage    = "verra:new_message"
    EventStatusChange  = "verra:status_change"
    EventHandoverAlert = "verra:handover_alert"
    EventWAStatus      = "verra:wa_status"
    EventQRCode        = "verra:qr_code"
)
```

---

## Build & Development

```bash
# Development
wails dev

# Build production
wails build

# Generate Wails bindings setelah ubah app.go
wails generate module
```

### wails.json minimal config
```json
{
  "name": "Verra",
  "outputfilename": "Verra",
  "frontend": {
    "dir": "./frontend",
    "install": "npm install",
    "build": "npm run build",
    "dev:watcher": "npm run dev"
  }
}
```

---

## Urutan Implementasi yang Disarankan

Ikuti urutan ini agar setiap tahap bisa di-test sebelum lanjut:

```
Phase 1 — Fondasi
  [ ] Setup Wails project
  [ ] SQLite init + migrations
  [ ] DTO types
  [ ] db repos (conversation, message, key, knowledge, config)

Phase 2 — WhatsApp
  [ ] whatsapp/client.go — connect + QR
  [ ] whatsapp/handler.go — receive message (log ke console dulu)
  [ ] whatsapp/sender.go — send text
  [ ] Test: scan QR, terima pesan, kirim pesan manual

Phase 3 — AI Core
  [ ] gemini.go — raw HTTP call
  [ ] key_pool.go — LRU + cooldown
  [ ] dispatcher.go — retry loop
  [ ] context_builder.go — system prompt builder
  [ ] Test: input manual → dapat response Gemini

Phase 4 — Conversation Orchestration
  [ ] handover/trigger.go — keyword + repeated
  [ ] handover/engine.go — state transitions
  [ ] conversation/service.go — full flow
  [ ] Test: kirim pesan dari WA nyata → AI reply

Phase 5 — Knowledge Base
  [ ] knowledge/service.go — CRUD
  [ ] knowledge/importer.go — CSV
  [ ] knowledge/extractor.go — PDF/DOCX
  [ ] Test: tambah FAQ → muncul di prompt → AI pakai

Phase 6 — Frontend
  [ ] Sidebar + InboxPage layout
  [ ] ChatView + MessageBubble
  [ ] Bottom bar 3 state (AI/Pending/Human)
  [ ] HandoverAlert toast
  [ ] Knowledge Base pages
  [ ] Settings + API Keys pages
  [ ] WA connection flow di Settings

Phase 7 — Polish
  [ ] Empty states semua halaman
  [ ] Error handling semua form
  [ ] Loading states
  [ ] Enkripsi API key
  [ ] Test end-to-end lengkap
```

---

## Catatan Keamanan

1. **API key tidak pernah keluar ke frontend** — `GetAPIKeys()` hanya return `APIKeySafe` dengan masked key.
2. **Enkripsi berbasis machine ID** — database tidak bisa dipindah mentah ke mesin lain.
3. **whatsmeow session disimpan lokal** — tidak ada data WA yang keluar ke server eksternal selain Gemini API.
4. **Input sanitasi** — semua input dari frontend di-trim dan di-validate di Go sebelum masuk DB.

---

*Dokumen ini adalah single source of truth untuk pembangunan Verra. Ikuti urutan fase implementasi dan jangan skip phase testing di setiap tahap.*
