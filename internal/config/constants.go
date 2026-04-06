package config

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
	CooldownDuration      = 60 // seconds

	// Product Stock Status
	StockAvailable  = "available"
	StockOutOfStock = "out_of_stock"
	StockPreOrder   = "pre_order"
)
