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
