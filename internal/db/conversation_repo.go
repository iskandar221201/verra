package db

import (
	"database/sql"
	"fmt"
	"time"
	"verra/internal/dto"
)

// ConversationRepo handles CRUD for the conversations table.
type ConversationRepo struct {
	db *sql.DB
}

// NewConversationRepo creates a new ConversationRepo.
func NewConversationRepo(db *sql.DB) *ConversationRepo {
	return &ConversationRepo{db: db}
}

// Upsert creates or updates a conversation. Returns the conversation summary.
func (r *ConversationRepo) Upsert(jid string, customerName string) (dto.ConversationSummary, error) {
	now := time.Now().UTC().Format(time.RFC3339)

	_, err := r.db.Exec(`
		INSERT INTO conversations (id, customer_name, last_message_at)
		VALUES (?, ?, ?)
		ON CONFLICT(id) DO UPDATE SET
			customer_name = CASE WHEN excluded.customer_name != '' THEN excluded.customer_name ELSE conversations.customer_name END,
			last_message_at = excluded.last_message_at
	`, jid, customerName, now)
	if err != nil {
		return dto.ConversationSummary{}, fmt.Errorf("conversation_repo.Upsert: %w", err)
	}

	return r.GetByID(jid)
}

// GetByID returns a single conversation by its JID.
func (r *ConversationRepo) GetByID(id string) (dto.ConversationSummary, error) {
	var conv dto.ConversationSummary
	var lastMsgAt sql.NullString

	err := r.db.QueryRow(`
		SELECT c.id, c.customer_name, c.status,
			COALESCE(c.last_message_at, '') as last_message_at,
			COALESCE((SELECT content FROM messages WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1), '') as last_message,
			0 as unread_count
		FROM conversations c
		WHERE c.id = ?
	`, id).Scan(&conv.ID, &conv.CustomerName, &conv.Status, &lastMsgAt, &conv.LastMessage, &conv.UnreadCount)
	if err != nil {
		return conv, fmt.Errorf("conversation_repo.GetByID: %w", err)
	}

	if lastMsgAt.Valid {
		conv.LastMessageAt = lastMsgAt.String
	}

	return conv, nil
}

// GetAll returns all conversations ordered by last_message_at desc (inbox).
func (r *ConversationRepo) GetAll() ([]dto.ConversationSummary, error) {
	rows, err := r.db.Query(`
		SELECT c.id, c.customer_name, c.status,
			COALESCE(c.last_message_at, '') as last_message_at,
			COALESCE((SELECT content FROM messages WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1), '') as last_message,
			0 as unread_count
		FROM conversations c
		ORDER BY c.last_message_at DESC
	`)
	if err != nil {
		return nil, fmt.Errorf("conversation_repo.GetAll: %w", err)
	}
	defer rows.Close()

	var convs []dto.ConversationSummary
	for rows.Next() {
		var conv dto.ConversationSummary
		var lastMsgAt sql.NullString
		if err := rows.Scan(&conv.ID, &conv.CustomerName, &conv.Status, &lastMsgAt, &conv.LastMessage, &conv.UnreadCount); err != nil {
			return nil, fmt.Errorf("conversation_repo.GetAll: scan: %w", err)
		}
		if lastMsgAt.Valid {
			conv.LastMessageAt = lastMsgAt.String
		}
		convs = append(convs, conv)
	}

	return convs, nil
}

// UpdateStatus changes the conversation status.
func (r *ConversationRepo) UpdateStatus(id string, status string) error {
	_, err := r.db.Exec(`UPDATE conversations SET status = ? WHERE id = ?`, status, id)
	if err != nil {
		return fmt.Errorf("conversation_repo.UpdateStatus: %w", err)
	}
	return nil
}

// GetStatus returns the current status of a conversation.
func (r *ConversationRepo) GetStatus(id string) (string, error) {
	var status string
	err := r.db.QueryRow(`SELECT status FROM conversations WHERE id = ?`, id).Scan(&status)
	if err != nil {
		return "", fmt.Errorf("conversation_repo.GetStatus: %w", err)
	}
	return status, nil
}
