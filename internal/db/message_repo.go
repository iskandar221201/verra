package db

import (
	"database/sql"
	"fmt"
	"time"
	"verra/internal/dto"

	"github.com/google/uuid"
)

// MessageRepo handles CRUD for the messages table.
type MessageRepo struct {
	db *sql.DB
}

// NewMessageRepo creates a new MessageRepo.
func NewMessageRepo(db *sql.DB) *MessageRepo {
	return &MessageRepo{db: db}
}

// Insert saves a new message and returns the created message.
func (r *MessageRepo) Insert(conversationID, role, content string) (dto.Message, error) {
	msg := dto.Message{
		ID:             uuid.New().String(),
		ConversationID: conversationID,
		Role:           role,
		Content:        content,
		CreatedAt:      time.Now().UTC().Format(time.RFC3339),
	}

	_, err := r.db.Exec(`
		INSERT INTO messages (id, conversation_id, role, content, created_at)
		VALUES (?, ?, ?, ?, ?)
	`, msg.ID, msg.ConversationID, msg.Role, msg.Content, msg.CreatedAt)
	if err != nil {
		return msg, fmt.Errorf("message_repo.Insert: %w", err)
	}

	return msg, nil
}

// InsertWithID saves a message with a specific ID (e.g., WA message ID).
func (r *MessageRepo) InsertWithID(id, conversationID, role, content string) (dto.Message, error) {
	msg := dto.Message{
		ID:             id,
		ConversationID: conversationID,
		Role:           role,
		Content:        content,
		CreatedAt:      time.Now().UTC().Format(time.RFC3339),
	}

	_, err := r.db.Exec(`
		INSERT OR IGNORE INTO messages (id, conversation_id, role, content, created_at)
		VALUES (?, ?, ?, ?, ?)
	`, msg.ID, msg.ConversationID, msg.Role, msg.Content, msg.CreatedAt)
	if err != nil {
		return msg, fmt.Errorf("message_repo.InsertWithID: %w", err)
	}

	return msg, nil
}

// GetByConversation retrieves messages for a conversation, ordered by creation time.
func (r *MessageRepo) GetByConversation(conversationID string, limit int) ([]dto.Message, error) {
	rows, err := r.db.Query(`
		SELECT id, conversation_id, role, content, created_at
		FROM messages
		WHERE conversation_id = ?
		ORDER BY created_at ASC
		LIMIT ?
	`, conversationID, limit)
	if err != nil {
		return nil, fmt.Errorf("message_repo.GetByConversation: %w", err)
	}
	defer rows.Close()

	var messages []dto.Message
	for rows.Next() {
		var m dto.Message
		if err := rows.Scan(&m.ID, &m.ConversationID, &m.Role, &m.Content, &m.CreatedAt); err != nil {
			return nil, fmt.Errorf("message_repo.GetByConversation: scan: %w", err)
		}
		messages = append(messages, m)
	}

	return messages, nil
}

// GetLastN retrieves the last N messages for a conversation (for AI context).
func (r *MessageRepo) GetLastN(conversationID string, n int) ([]dto.Message, error) {
	rows, err := r.db.Query(`
		SELECT id, conversation_id, role, content, created_at
		FROM (
			SELECT * FROM messages
			WHERE conversation_id = ?
			ORDER BY created_at DESC
			LIMIT ?
		) sub
		ORDER BY created_at ASC
	`, conversationID, n)
	if err != nil {
		return nil, fmt.Errorf("message_repo.GetLastN: %w", err)
	}
	defer rows.Close()

	var messages []dto.Message
	for rows.Next() {
		var m dto.Message
		if err := rows.Scan(&m.ID, &m.ConversationID, &m.Role, &m.Content, &m.CreatedAt); err != nil {
			return nil, fmt.Errorf("message_repo.GetLastN: scan: %w", err)
		}
		messages = append(messages, m)
	}

	return messages, nil
}

// GetLastNCustomerMessages retrieves the last N customer-only messages.
func (r *MessageRepo) GetLastNCustomerMessages(conversationID string, n int) ([]dto.Message, error) {
	rows, err := r.db.Query(`
		SELECT id, conversation_id, role, content, created_at
		FROM messages
		WHERE conversation_id = ? AND role = 'customer'
		ORDER BY created_at DESC
		LIMIT ?
	`, conversationID, n)
	if err != nil {
		return nil, fmt.Errorf("message_repo.GetLastNCustomerMessages: %w", err)
	}
	defer rows.Close()

	var messages []dto.Message
	for rows.Next() {
		var m dto.Message
		if err := rows.Scan(&m.ID, &m.ConversationID, &m.Role, &m.Content, &m.CreatedAt); err != nil {
			return nil, fmt.Errorf("message_repo.GetLastNCustomerMessages: scan: %w", err)
		}
		messages = append(messages, m)
	}

	return messages, nil
}
