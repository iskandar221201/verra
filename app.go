package main

import (
	"context"
	"verra/internal/dto"
	"verra/internal/whatsapp"
)

// App struct
type App struct {
	ctx      context.Context
	waClient *whatsapp.WhatsAppClient
}

// NewApp creates a new App application struct
func NewApp() *App {
	return &App{}
}

// startup is called when the app starts. The context is saved
// so we can call the runtime methods
func (a *App) startup(ctx context.Context) {
	a.ctx = ctx
	a.waClient = whatsapp.NewWhatsAppClient(ctx)
	// Auto-init if possible in background
	go a.waClient.Init()
}

// --- Conversation ---

func (a *App) GetInbox() []dto.ConversationSummary {
	return []dto.ConversationSummary{}
}

func (a *App) GetMessages(convID string, limit int) []dto.Message {
	return []dto.Message{}
}

func (a *App) AgentClaimHandover(convID string) error {
	return nil
}

func (a *App) AgentSendMessage(convID string, text string) error {
	return a.waClient.SendText(convID, text)
}

func (a *App) AgentResolveConversation(convID string) error {
	return nil
}

// --- WhatsApp ---

func (a *App) GetWAStatus() dto.WAStatus {
	return a.waClient.GetStatus()
}

func (a *App) GetQRCode() string {
	// QR is handled via events in Init()
	return ""
}

func (a *App) DisconnectWA() error {
	a.waClient.Disconnect()
	return nil
}

// --- Knowledge Base ---

func (a *App) GetFAQs() []dto.FAQ {
	return []dto.FAQ{}
}

func (a *App) SaveFAQ(faq dto.FAQ) error {
	return nil
}

func (a *App) DeleteFAQ(id int) error {
	return nil
}

func (a *App) ReorderFAQs(ids []int) error {
	return nil
}

func (a *App) GetProducts() []dto.Product {
	return []dto.Product{}
}

func (a *App) SaveProduct(p dto.Product) error {
	return nil
}

func (a *App) DeleteProduct(id int) error {
	return nil
}

func (a *App) GetSOPs() []dto.SOP {
	return []dto.SOP{}
}

func (a *App) SaveSOP(s dto.SOP) error {
	return nil
}

func (a *App) DeleteSOP(id int) error {
	return nil
}

func (a *App) GetNotes() []dto.Note {
	return []dto.Note{}
}

func (a *App) SaveNote(n dto.Note) error {
	return nil
}

func (a *App) DeleteNote(id int) error {
	return nil
}

func (a *App) ImportFAQFromCSV(csvContent string) (int, error) {
	return 0, nil
}

func (a *App) ImportProductFromCSV(csvContent string) (int, error) {
	return 0, nil
}

func (a *App) ImportNoteFromFile(filename string, fileBytes []byte) error {
	return nil
}

// --- Config ---

func (a *App) GetBusinessConfig() dto.BusinessConfig {
	return dto.BusinessConfig{
		BusinessName: "Toko Saya",
		Language:     "Indonesia",
	}
}

func (a *App) SaveBusinessConfig(cfg dto.BusinessConfig) error {
	return nil
}

// --- API Keys ---

func (a *App) GetAPIKeys() []dto.APIKeySafe {
	return []dto.APIKeySafe{}
}

func (a *App) AddAPIKey(label string, apiKey string) error {
	return nil
}

func (a *App) ToggleAPIKey(id int, active bool) error {
	return nil
}

func (a *App) DeleteAPIKey(id int) error {
	return nil
}
