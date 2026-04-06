package db

import (
	"database/sql"
	"encoding/json"
	"fmt"
	"verra/internal/dto"
)

// ConfigRepo handles read/write of the business_config singleton.
type ConfigRepo struct {
	db *sql.DB
}

// NewConfigRepo creates a new ConfigRepo.
func NewConfigRepo(db *sql.DB) *ConfigRepo {
	return &ConfigRepo{db: db}
}

// Get retrieves the business configuration.
func (r *ConfigRepo) Get() (dto.BusinessConfig, error) {
	var cfg dto.BusinessConfig
	var keywordsJSON string

	err := r.db.QueryRow(`
		SELECT business_name, ai_persona, language, context_window_n,
			handover_keywords, greeting_message, handover_message, handover_wait_message
		FROM business_config WHERE id = 1
	`).Scan(
		&cfg.BusinessName, &cfg.AIPersona, &cfg.Language, &cfg.ContextWindowN,
		&keywordsJSON, &cfg.GreetingMessage, &cfg.HandoverMessage, &cfg.HandoverWaitMessage,
	)
	if err != nil {
		if err == sql.ErrNoRows {
			// Return defaults
			return dto.BusinessConfig{
				BusinessName:        "Toko Saya",
				AIPersona:           "Ramah, sopan, dan membantu",
				Language:            "Indonesia",
				ContextWindowN:      10,
				HandoverKeywords:    []string{"komplain", "refund", "minta cs", "bicara manusia", "tidak puas"},
				GreetingMessage:     "Halo! Ada yang bisa saya bantu?",
				HandoverMessage:     "Baik kak, saya hubungkan ke tim CS kami ya. Mohon tunggu sebentar 🙏",
				HandoverWaitMessage: "Maaf kak, CS kami sedang memproses. Mohon tunggu sebentar ya 🙏",
			}, nil
		}
		return cfg, fmt.Errorf("config_repo.Get: %w", err)
	}

	// Parse handover keywords from JSON
	if err := json.Unmarshal([]byte(keywordsJSON), &cfg.HandoverKeywords); err != nil {
		cfg.HandoverKeywords = []string{}
	}

	return cfg, nil
}

// Save updates the business configuration.
func (r *ConfigRepo) Save(cfg dto.BusinessConfig) error {
	keywordsJSON, err := json.Marshal(cfg.HandoverKeywords)
	if err != nil {
		return fmt.Errorf("config_repo.Save: marshal keywords: %w", err)
	}

	_, err = r.db.Exec(`
		UPDATE business_config SET
			business_name = ?,
			ai_persona = ?,
			language = ?,
			context_window_n = ?,
			handover_keywords = ?,
			greeting_message = ?,
			handover_message = ?,
			handover_wait_message = ?
		WHERE id = 1
	`, cfg.BusinessName, cfg.AIPersona, cfg.Language, cfg.ContextWindowN,
		string(keywordsJSON), cfg.GreetingMessage, cfg.HandoverMessage, cfg.HandoverWaitMessage)
	if err != nil {
		return fmt.Errorf("config_repo.Save: %w", err)
	}

	return nil
}
