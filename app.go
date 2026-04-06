package main

import (
	"context"
	"fmt"
	"strings"
	"verra/internal/ai"
	"verra/internal/config"
	"verra/internal/conversation"
	"verra/internal/db"
	"verra/internal/dto"
	"verra/internal/handover"
	"verra/internal/knowledge"
	"verra/internal/whatsapp"

	wailsRuntime "github.com/wailsapp/wails/v2/pkg/runtime"
)

// App struct holds all application dependencies.
type App struct {
	ctx           context.Context
	waClient      *whatsapp.WhatsAppClient
	convRepo      *db.ConversationRepo
	msgRepo       *db.MessageRepo
	keyRepo       *db.KeyRepo
	knowledgeRepo *db.KnowledgeRepo
	configRepo    *db.ConfigRepo
	convService   *conversation.Service
	kbService     *knowledge.Service
	kbImporter    *knowledge.Importer
	handoverEng   *handover.Engine
}

// NewApp creates a new App application struct.
func NewApp() *App {
	return &App{}
}

// startup is called when the app starts.
func (a *App) startup(ctx context.Context) {
	a.ctx = ctx

	// Initialize database
	database, err := db.InitDB("verra.db")
	if err != nil {
		fmt.Printf("app.startup: failed to init DB: %v\n", err)
		return
	}

	// Initialize repos
	a.convRepo = db.NewConversationRepo(database)
	a.msgRepo = db.NewMessageRepo(database)
	a.keyRepo = db.NewKeyRepo(database)
	a.knowledgeRepo = db.NewKnowledgeRepo(database)
	a.configRepo = db.NewConfigRepo(database)

	// Initialize services
	a.kbService = knowledge.NewService(a.knowledgeRepo)
	a.kbImporter = knowledge.NewImporter(a.knowledgeRepo)

	// Initialize AI
	keyPool := ai.NewKeyPool(a.keyRepo)
	dispatcher := ai.NewDispatcher(keyPool)
	ctxBuilder := ai.NewContextBuilder()

	// Initialize handover
	trigger := handover.NewTrigger(a.msgRepo)
	a.handoverEng = handover.NewEngine(a.convRepo, database, ctx)

	// Initialize WhatsApp
	a.waClient = whatsapp.NewWhatsAppClient(ctx)

	// Initialize conversation service
	a.convService = conversation.NewService(
		a.convRepo, a.msgRepo, a.knowledgeRepo, a.configRepo,
		dispatcher, ctxBuilder, trigger, a.handoverEng,
		a.waClient, ctx,
	)

	// Set message handler on WhatsApp client
	a.waClient.SetMessageHandler(a.convService.HandleIncomingMessage)

	// Auto-init WhatsApp in background
	go a.waClient.Init()
}

// --- Conversation ---

func (a *App) GetInbox() []dto.ConversationSummary {
	convs, err := a.convRepo.GetAll()
	if err != nil {
		fmt.Printf("app.GetInbox: %v\n", err)
		return []dto.ConversationSummary{}
	}
	return convs
}

func (a *App) GetMessages(convID string, limit int) []dto.Message {
	msgs, err := a.msgRepo.GetByConversation(convID, limit)
	if err != nil {
		fmt.Printf("app.GetMessages: %v\n", err)
		return []dto.Message{}
	}
	return msgs
}

func (a *App) AgentClaimHandover(convID string) error {
	return a.handoverEng.AgentClaim(convID, a.ctx)
}

func (a *App) AgentSendMessage(convID string, text string) error {
	text = strings.TrimSpace(text)
	if text == "" {
		return fmt.Errorf("message cannot be empty")
	}

	// Save agent message
	msg, err := a.msgRepo.Insert(convID, config.RoleAgent, text)
	if err != nil {
		return fmt.Errorf("app.AgentSendMessage: save: %w", err)
	}

	// Send via WhatsApp
	if err := a.waClient.SendText(convID, text); err != nil {
		return fmt.Errorf("app.AgentSendMessage: send: %w", err)
	}

	// Emit event
	wailsRuntime.EventsEmit(a.ctx, config.EventNewMessage, msg)

	return nil
}

func (a *App) AgentResolveConversation(convID string) error {
	return a.handoverEng.AgentResolve(convID, a.ctx)
}

func (a *App) AgentSwitchToAI(convID string) error {
	if err := a.convRepo.UpdateStatus(convID, config.StatusAI); err != nil {
		return fmt.Errorf("app.AgentSwitchToAI: %w", err)
	}

	wailsRuntime.EventsEmit(a.ctx, config.EventStatusChange, map[string]interface{}{
		"convID": convID,
		"status": config.StatusAI,
	})

	return nil
}

// --- WhatsApp ---

func (a *App) GetWAStatus() dto.WAStatus {
	if a.waClient == nil {
		return dto.WAStatus{State: "disconnected"}
	}
	return a.waClient.GetStatus()
}

func (a *App) GetQRCode() string {
	if a.waClient == nil {
		return ""
	}
	return a.waClient.GetQRCode()
}

func (a *App) InitWA() error {
	if a.waClient != nil {
		go a.waClient.Init()
	}
	return nil
}

func (a *App) DisconnectWA() error {
	if a.waClient != nil {
		a.waClient.Disconnect()
	}
	return nil
}

// --- Knowledge Base ---

func (a *App) GetFAQs() []dto.FAQ {
	faqs, err := a.kbService.GetFAQs()
	if err != nil {
		fmt.Printf("app.GetFAQs: %v\n", err)
		return []dto.FAQ{}
	}
	return faqs
}

func (a *App) SaveFAQ(faq dto.FAQ) error   { return a.kbService.SaveFAQ(faq) }
func (a *App) DeleteFAQ(id int) error      { return a.kbService.DeleteFAQ(id) }
func (a *App) ReorderFAQs(ids []int) error { return a.kbService.ReorderFAQs(ids) }

func (a *App) GetProducts() []dto.Product {
	products, err := a.kbService.GetProducts()
	if err != nil {
		fmt.Printf("app.GetProducts: %v\n", err)
		return []dto.Product{}
	}
	return products
}

func (a *App) SaveProduct(p dto.Product) error { return a.kbService.SaveProduct(p) }
func (a *App) DeleteProduct(id int) error      { return a.kbService.DeleteProduct(id) }

func (a *App) GetSOPs() []dto.SOP {
	sops, err := a.kbService.GetSOPs()
	if err != nil {
		fmt.Printf("app.GetSOPs: %v\n", err)
		return []dto.SOP{}
	}
	return sops
}

func (a *App) SaveSOP(s dto.SOP) error { return a.kbService.SaveSOP(s) }
func (a *App) DeleteSOP(id int) error  { return a.kbService.DeleteSOP(id) }

func (a *App) GetNotes() []dto.Note {
	notes, err := a.kbService.GetNotes()
	if err != nil {
		fmt.Printf("app.GetNotes: %v\n", err)
		return []dto.Note{}
	}
	return notes
}

func (a *App) SaveNote(n dto.Note) error { return a.kbService.SaveNote(n) }
func (a *App) DeleteNote(id int) error   { return a.kbService.DeleteNote(id) }

func (a *App) ImportFAQFromCSV(csvContent string) (int, error) {
	return a.kbImporter.ImportFAQFromCSV(csvContent)
}

func (a *App) ImportProductFromCSV(csvContent string) (int, error) {
	return a.kbImporter.ImportProductFromCSV(csvContent)
}

func (a *App) ImportNoteFromFile(filename string, fileBytes []byte) error {
	text, err := knowledge.ExtractText(filename, fileBytes)
	if err != nil {
		return fmt.Errorf("app.ImportNoteFromFile: %w", err)
	}

	note := dto.Note{
		Title:      filename,
		Content:    text,
		Category:   "umum",
		SourceFile: filename,
		IsActive:   true,
	}

	return a.kbService.SaveNote(note)
}

// --- Config ---

func (a *App) GetBusinessConfig() dto.BusinessConfig {
	cfg, err := a.configRepo.Get()
	if err != nil {
		fmt.Printf("app.GetBusinessConfig: %v\n", err)
		return dto.BusinessConfig{BusinessName: "Toko Saya", Language: "Indonesia"}
	}
	return cfg
}

func (a *App) SaveBusinessConfig(cfg dto.BusinessConfig) error {
	return a.configRepo.Save(cfg)
}

// --- API Keys ---

func (a *App) GetAPIKeys() []dto.APIKeySafe {
	keys, err := a.keyRepo.GetAll()
	if err != nil {
		fmt.Printf("app.GetAPIKeys: %v\n", err)
		return []dto.APIKeySafe{}
	}
	return keys
}

func (a *App) AddAPIKey(label string, apiKey string) error {
	label = strings.TrimSpace(label)
	apiKey = strings.TrimSpace(apiKey)
	if label == "" || apiKey == "" {
		return fmt.Errorf("label and API key cannot be empty")
	}

	encrypted, err := config.EncryptAPIKey(apiKey)
	if err != nil {
		return fmt.Errorf("app.AddAPIKey: encrypt: %w", err)
	}

	return a.keyRepo.Insert(label, encrypted)
}

func (a *App) ToggleAPIKey(id int, active bool) error {
	return a.keyRepo.Toggle(id, active)
}

func (a *App) DeleteAPIKey(id int) error {
	return a.keyRepo.Delete(id)
}
