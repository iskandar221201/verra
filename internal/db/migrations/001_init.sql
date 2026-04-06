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
