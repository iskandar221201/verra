package handover

import (
	"context"
	"database/sql"
	"fmt"
	"time"
	"verra/internal/config"
	"verra/internal/db"

	wailsRuntime "github.com/wailsapp/wails/v2/pkg/runtime"
)

// Engine manages handover state transitions.
type Engine struct {
	convRepo *db.ConversationRepo
	db       *sql.DB
}

// NewEngine creates a new handover Engine.
func NewEngine(convRepo *db.ConversationRepo, database *sql.DB, ctx context.Context) *Engine {
	return &Engine{
		convRepo: convRepo,
		db:       database,
	}
}

// TriggerHandover transitions a conversation to handover_pending and logs it.
func (e *Engine) TriggerHandover(convID, triggerType, detail string, ctx interface{}) error {
	// Update status
	if err := e.convRepo.UpdateStatus(convID, config.StatusHandoverPending); err != nil {
		return fmt.Errorf("handover.TriggerHandover: update status: %w", err)
	}

	// Log the handover
	_, err := e.db.Exec(`
		INSERT INTO handover_logs (conversation_id, trigger_type, trigger_detail)
		VALUES (?, ?, ?)
	`, convID, triggerType, detail)
	if err != nil {
		return fmt.Errorf("handover.TriggerHandover: insert log: %w", err)
	}

	// Emit handover alert event
	if wailsCtx, ok := ctx.(context.Context); ok {
		conv, _ := e.convRepo.GetByID(convID)
		wailsRuntime.EventsEmit(wailsCtx, config.EventHandoverAlert, map[string]interface{}{
			"convID":       convID,
			"customerName": conv.CustomerName,
			"triggerType":  triggerType,
		})
		wailsRuntime.EventsEmit(wailsCtx, config.EventStatusChange, map[string]interface{}{
			"convID": convID,
			"status": config.StatusHandoverPending,
		})
	}

	return nil
}

// AgentClaim transitions a conversation from handover_pending to human.
func (e *Engine) AgentClaim(convID string, ctx interface{}) error {
	if err := e.convRepo.UpdateStatus(convID, config.StatusHuman); err != nil {
		return fmt.Errorf("handover.AgentClaim: %w", err)
	}

	if wailsCtx, ok := ctx.(context.Context); ok {
		wailsRuntime.EventsEmit(wailsCtx, config.EventStatusChange, map[string]interface{}{
			"convID": convID,
			"status": config.StatusHuman,
		})
	}

	return nil
}

// AgentResolve transitions a conversation to resolved and logs the resolved time.
func (e *Engine) AgentResolve(convID string, ctx interface{}) error {
	if err := e.convRepo.UpdateStatus(convID, config.StatusResolved); err != nil {
		return fmt.Errorf("handover.AgentResolve: %w", err)
	}

	// Update the handover log with resolved_at
	now := time.Now().UTC().Format(time.RFC3339)
	_, err := e.db.Exec(`
		UPDATE handover_logs SET resolved_at = ?
		WHERE conversation_id = ? AND resolved_at IS NULL
	`, now, convID)
	if err != nil {
		fmt.Printf("handover.AgentResolve: warning: failed to update log: %v\n", err)
	}

	if wailsCtx, ok := ctx.(context.Context); ok {
		wailsRuntime.EventsEmit(wailsCtx, config.EventStatusChange, map[string]interface{}{
			"convID": convID,
			"status": config.StatusResolved,
		})
	}

	return nil
}
