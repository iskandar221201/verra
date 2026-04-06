package conversation

import (
	"context"
	"errors"
	"fmt"
	"verra/internal/ai"
	"verra/internal/config"
	"verra/internal/db"
	"verra/internal/dto"
	"verra/internal/handover"
	"verra/internal/whatsapp"

	wailsRuntime "github.com/wailsapp/wails/v2/pkg/runtime"
)

// Service orchestrates the main conversation flow.
type Service struct {
	convRepo      *db.ConversationRepo
	msgRepo       *db.MessageRepo
	knowledgeRepo *db.KnowledgeRepo
	configRepo    *db.ConfigRepo
	dispatcher    *ai.Dispatcher
	ctxBuilder    *ai.ContextBuilder
	trigger       *handover.Trigger
	engine        *handover.Engine
	waClient      *whatsapp.WhatsAppClient
	ctx           context.Context
}

// NewService creates a new conversation Service.
func NewService(
	convRepo *db.ConversationRepo,
	msgRepo *db.MessageRepo,
	knowledgeRepo *db.KnowledgeRepo,
	configRepo *db.ConfigRepo,
	dispatcher *ai.Dispatcher,
	ctxBuilder *ai.ContextBuilder,
	trigger *handover.Trigger,
	engine *handover.Engine,
	waClient *whatsapp.WhatsAppClient,
	ctx context.Context,
) *Service {
	return &Service{
		convRepo:      convRepo,
		msgRepo:       msgRepo,
		knowledgeRepo: knowledgeRepo,
		configRepo:    configRepo,
		dispatcher:    dispatcher,
		ctxBuilder:    ctxBuilder,
		trigger:       trigger,
		engine:        engine,
		waClient:      waClient,
		ctx:           ctx,
	}
}

// HandleIncomingMessage processes a new incoming WhatsApp message.
func (s *Service) HandleIncomingMessage(senderJID, pushName, msgID, text string) {
	// 1. Upsert conversation
	conv, err := s.convRepo.Upsert(senderJID, pushName)
	if err != nil {
		fmt.Printf("conversation.HandleIncoming: upsert failed: %v\n", err)
		return
	}

	// 2. Save customer message
	msg, err := s.msgRepo.InsertWithID(msgID, senderJID, config.RoleCustomer, text)
	if err != nil {
		fmt.Printf("conversation.HandleIncoming: save message failed: %v\n", err)
		return
	}

	// 3. Emit events to frontend
	wailsRuntime.EventsEmit(s.ctx, config.EventInboxUpdate, conv)
	wailsRuntime.EventsEmit(s.ctx, config.EventNewMessage, msg)

	// 4. Route based on status
	s.route(conv, msg)
}

// route directs the message flow based on conversation status.
func (s *Service) route(conv dto.ConversationSummary, msg dto.Message) {
	cfg, err := s.configRepo.Get()
	if err != nil {
		fmt.Printf("conversation.route: get config failed: %v\n", err)
		return
	}

	switch conv.Status {
	case config.StatusHuman:
		// AI is silent, agent handles
		return

	case config.StatusHandoverPending:
		// Send waiting message
		if err := s.waClient.SendText(conv.ID, cfg.HandoverWaitMessage); err != nil {
			fmt.Printf("conversation.route: send wait message failed: %v\n", err)
		}
		return

	case config.StatusResolved:
		// Re-activate to AI mode for new messages
		if err := s.convRepo.UpdateStatus(conv.ID, config.StatusAI); err != nil {
			fmt.Printf("conversation.route: reactivate failed: %v\n", err)
		}
		s.processWithAI(conv, msg, cfg)

	default: // "ai"
		s.processWithAI(conv, msg, cfg)
	}
}

// processWithAI runs the full AI pipeline.
func (s *Service) processWithAI(conv dto.ConversationSummary, msg dto.Message, cfg dto.BusinessConfig) {
	// Get active SOPs for keyword matching
	sops, _ := s.knowledgeRepo.GetActiveSOPs()

	// Check SOP first — no API call if escalate
	matchedSOP := s.trigger.MatchSOP(msg.Content, sops)
	if matchedSOP != nil && matchedSOP.EscalateToHuman {
		s.engine.TriggerHandover(conv.ID, config.TriggerSOPKeyword, matchedSOP.Title, s.ctx)
		if err := s.waClient.SendText(conv.ID, cfg.HandoverMessage); err != nil {
			fmt.Printf("conversation.processWithAI: send handover msg failed: %v\n", err)
		}
		return
	}

	// Check handover keywords
	if s.trigger.MatchesHandoverKeyword(msg.Content, cfg.HandoverKeywords) {
		s.engine.TriggerHandover(conv.ID, config.TriggerKeyword, msg.Content, s.ctx)
		if err := s.waClient.SendText(conv.ID, cfg.HandoverMessage); err != nil {
			fmt.Printf("conversation.processWithAI: send handover msg failed: %v\n", err)
		}
		return
	}

	// Check repeated message (frustration signal)
	if s.trigger.IsRepeatedMessage(conv.ID, msg.Content) {
		s.engine.TriggerHandover(conv.ID, config.TriggerRepeated, msg.Content, s.ctx)
		if err := s.waClient.SendText(conv.ID, cfg.HandoverMessage); err != nil {
			fmt.Printf("conversation.processWithAI: send handover msg failed: %v\n", err)
		}
		return
	}

	// Build context and call Gemini
	products, _ := s.knowledgeRepo.GetActiveProducts()
	faqs, _ := s.knowledgeRepo.GetActiveFAQs()
	notes, _ := s.knowledgeRepo.GetActiveNotes()

	systemPrompt := s.ctxBuilder.Build(cfg, products, faqs, matchedSOP, notes)
	history, _ := s.msgRepo.GetLastN(conv.ID, cfg.ContextWindowN)

	aiResp, err := s.dispatcher.Dispatch(systemPrompt, history, msg.Content)
	if err != nil {
		if errors.Is(err, ai.ErrAllKeysExhausted) {
			fallbackMsg := "Maaf, sistem sedang sibuk atau kunci API belum dikonfigurasi. Mohon tunggu sebentar."
			s.waClient.SendText(conv.ID, fallbackMsg)
			savedMsg, _ := s.msgRepo.Insert(conv.ID, config.RoleAI, fallbackMsg)
			wailsRuntime.EventsEmit(s.ctx, config.EventNewMessage, savedMsg)
		}
		fmt.Printf("conversation.processWithAI: dispatch failed: %v\n", err)
		return
	}

	// Save and send AI response
	aiMsg, err := s.msgRepo.Insert(conv.ID, config.RoleAI, aiResp)
	if err != nil {
		fmt.Printf("conversation.processWithAI: save AI msg failed: %v\n", err)
		return
	}

	if err := s.waClient.SendText(conv.ID, aiResp); err != nil {
		fmt.Printf("conversation.processWithAI: send AI msg failed: %v\n", err)
		return
	}

	wailsRuntime.EventsEmit(s.ctx, config.EventNewMessage, aiMsg)
}
