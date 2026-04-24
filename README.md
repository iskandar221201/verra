# Verra

> WhatsApp AI Customer Service platform — multi-tenant, self-hosted, built on CodeIgniter 4.

Verra lets businesses run their own AI-powered WhatsApp CS independently. Each tenant gets isolated AI configuration, WhatsApp channels, knowledge base, and conversation history — all on a single deployment.

---

## Features

- **AI Auto-Reply** — Responds to incoming WhatsApp messages automatically using Gemini or Grok, grounded by a per-tenant knowledge base and custom system prompt
- **Human Handover** — Keyword-triggered escalation to a live agent with real-time chat via SSE
- **Lead Assignment** — Auto-detects new leads and assigns them to sales via WhatsApp group notification (round-robin, persistent)
- **Multi-Tenant** — Full data isolation per tenant via shared DB with `tenant_id` scoping
- **Multi-Channel** — Each tenant can operate multiple WhatsApp numbers via Fonnte
- **API Key Rotation** — Multiple AI API keys per provider with automatic priority-based failover
- **RBAC** — Four roles: `superadmin`, `tenant_admin`, `operator`, `agent`
- **Knowledge Base** — Structured KB with categories, sort order, and per-entry toggle

---

## Tech Stack

| Layer | Technology |
|---|---|
| Framework | CodeIgniter 4 |
| Auth & RBAC | CI4 Shield |
| Database | MySQL 8+ |
| WA Gateway | Fonnte API |
| AI Providers | Google Gemini, xAI Grok |
| Frontend | Bootstrap 5 + Vanilla JS |
| Real-time | Server-Sent Events (SSE) |

---

## How It Works

```
[WhatsApp Customer]
       ↓
  [Fonnte API]
       ↓  webhook POST → /webhook/{channel_uuid}
  [Verra CI4]
   ↓         ↓
[KB Tenant] [Conversation History]
       ↓
  [AI Provider]
       ↓
  [Verra CI4]
       ↓  Fonnte Send API
[WhatsApp Customer]
```

Each WA channel has a unique UUID-based webhook URL. Fonnte routes incoming messages to that URL, and Verra handles the rest — context building, AI call, response delivery.

If a message contains a handover keyword, the conversation switches to `agent` mode and a live agent can take over from the agent panel.

---

## Roles

| Role | Scope | Access |
|---|---|---|
| `superadmin` | Global | Manage all tenants, users, system config |
| `tenant_admin` | Per Tenant | Manage KB, channels, AI config, API keys, users |
| `operator` | Per Tenant | View conversations and handover list (read-only) |
| `agent` | Per Tenant | Claim and handle handover conversations |

---

## Requirements

- PHP 8.1+
- MySQL 8+
- Apache or Nginx (shared hosting compatible)
- Fonnte account with active WA number
- Google Gemini or xAI Grok API key

---

## Installation

```bash
git clone https://github.com/your-username/verra.git
cd verra
composer install
cp env .env
```

Edit `.env`:
```
database.default.hostname = localhost
database.default.database = verra
database.default.username = root
database.default.password = yourpassword
database.default.DBDriver = MySQLi

encryption.key = your-32-char-key-here
```

Run migrations and seeder:
```bash
php spark migrate
php spark db:seed InitialSeeder
```

Point your web server document root to `/public`.

---

## Webhook Setup

For each WA channel, Verra generates a unique webhook URL:
```
https://yourdomain.com/webhook/{channel_uuid}
```

Paste this URL into your Fonnte dashboard as the webhook endpoint for the corresponding WA number.

---

## Lead Assignment

Verra can automatically detect new leads (first-time contacts with no conversation history) and notify a WhatsApp sales group via Fonnte with round-robin assignment.

Configure from **Settings → Lead Assignment**:
1. Toggle system on
2. Enter the WA Group ID (from your Fonnte dashboard)
3. Add your sales team members (name + WA number)
4. Verra assigns leads in order, cycling persistently

Manual assignment is also available from the conversation list.

---

## Security

- API keys stored encrypted via CI4's built-in Encryption service
- Webhook endpoints use UUID v4 — not guessable, not enumerable
- Strict `tenant_id` scoping on every data query — no cross-tenant leakage
- CSRF protection active on all forms
- Shield handles session, password hashing, and login rate limiting

---

## Limitations (v1)

- Text messages only — media/attachments not supported
- Single AI provider active per tenant at a time
- No billing or subscription management (planned for v2)

---

## License

MIT
