package config

import "time"

const (
	// Conversation Status
	StatusAI              = "ai"
	StatusHandoverPending = "handover_pending"
	StatusHuman           = "human"
	StatusResolved        = "resolved"

	// Message Roles
	RoleCustomer = "customer"
	RoleAI       = "ai"
	RoleAgent    = "agent"

	// Handover Trigger Types
	TriggerKeyword    = "keyword"
	TriggerSOPKeyword = "sop_keyword"
	TriggerRepeated   = "repeated"
	TriggerManual     = "manual"

	// AI Configuration Defaults
	DefaultContextWindowN = 10
	KeyCooldownDuration   = 60 * time.Second
	MaxRetryPerDispatch   = 50

	// Repeated message detection
	RepeatedMsgLookback   = 5
	RepeatedMsgThreshold  = 4
	LevenshteinSimilarity = 0.80

	// Gemini
	GeminiAPIEndpoint = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent"

	// Product Stock Status
	StockAvailable  = "available"
	StockOutOfStock = "out_of_stock"
	StockPreOrder   = "pre_order"

	// Wails events
	EventInboxUpdate   = "verra:inbox_update"
	EventNewMessage    = "verra:new_message"
	EventStatusChange  = "verra:status_change"
	EventHandoverAlert = "verra:handover_alert"
	EventWAStatus      = "verra:wa_status"
	EventQRCode        = "verra:qr_code"
)
