package db

import (
	"database/sql"
	"fmt"
	"time"
	"verra/internal/dto"
)

// KeyRepo handles CRUD for the gemini_keys table.
type KeyRepo struct {
	db *sql.DB
}

// NewKeyRepo creates a new KeyRepo.
func NewKeyRepo(db *sql.DB) *KeyRepo {
	return &KeyRepo{db: db}
}

// Insert adds a new API key (already encrypted).
func (r *KeyRepo) Insert(label, encryptedKey string) error {
	_, err := r.db.Exec(`
		INSERT INTO gemini_keys (label, api_key) VALUES (?, ?)
	`, label, encryptedKey)
	if err != nil {
		return fmt.Errorf("key_repo.Insert: %w", err)
	}
	return nil
}

// GetAll returns all keys with masked values for frontend display.
func (r *KeyRepo) GetAll() ([]dto.APIKeySafe, error) {
	rows, err := r.db.Query(`
		SELECT id, label, api_key, last_used_at, total_requests, is_active, cooldown_until
		FROM gemini_keys
		ORDER BY id ASC
	`)
	if err != nil {
		return nil, fmt.Errorf("key_repo.GetAll: %w", err)
	}
	defer rows.Close()

	now := time.Now().UTC()
	var keys []dto.APIKeySafe
	for rows.Next() {
		var k dto.APIKeySafe
		var apiKey string
		var lastUsedAt, cooldownUntil sql.NullString

		if err := rows.Scan(&k.ID, &k.Label, &apiKey, &lastUsedAt, &k.TotalRequests, &k.IsActive, &cooldownUntil); err != nil {
			return nil, fmt.Errorf("key_repo.GetAll: scan: %w", err)
		}

		// Mask the key
		if len(apiKey) > 6 {
			k.MaskedKey = "••••••••••••" + apiKey[len(apiKey)-6:]
		} else {
			k.MaskedKey = "••••••••••••"
		}

		if lastUsedAt.Valid {
			k.LastUsedAt = lastUsedAt.String
		}

		if cooldownUntil.Valid {
			t, err := time.Parse("2006-01-02 15:04:05", cooldownUntil.String)
			if err == nil && t.After(now) {
				k.InCooldown = true
			}
		}

		keys = append(keys, k)
	}

	return keys, nil
}

// GetLRUKey returns the least-recently-used active key not in cooldown.
func (r *KeyRepo) GetLRUKey() (int, string, error) {
	var id int
	var apiKey string

	err := r.db.QueryRow(`
		SELECT id, api_key FROM gemini_keys
		WHERE is_active = 1
		ORDER BY last_used_at ASC, id ASC
		LIMIT 1
	`).Scan(&id, &apiKey)
	if err != nil {
		if err == sql.ErrNoRows {
			return 0, "", fmt.Errorf("key_repo.GetLRUKey: no available keys")
		}
		return 0, "", fmt.Errorf("key_repo.GetLRUKey: %w", err)
	}

	return id, apiKey, nil
}

// GetActiveKeyCount returns the number of active keys.
func (r *KeyRepo) GetActiveKeyCount() (int, error) {
	var count int
	err := r.db.QueryRow(`SELECT COUNT(*) FROM gemini_keys WHERE is_active = 1`).Scan(&count)
	if err != nil {
		return 0, fmt.Errorf("key_repo.GetActiveKeyCount: %w", err)
	}
	return count, nil
}

// UpdateLastUsed sets the last_used_at to now.
func (r *KeyRepo) UpdateLastUsed(id int) error {
	_, err := r.db.Exec(`UPDATE gemini_keys SET last_used_at = strftime('%Y-%m-%d %H:%M:%f', 'now') WHERE id = ?`, id)
	if err != nil {
		return fmt.Errorf("key_repo.UpdateLastUsed: %w", err)
	}
	return nil
}

// IncrementRequests increments the total_requests counter.
func (r *KeyRepo) IncrementRequests(id int) error {
	_, err := r.db.Exec(`UPDATE gemini_keys SET total_requests = total_requests + 1 WHERE id = ?`, id)
	if err != nil {
		return fmt.Errorf("key_repo.IncrementRequests: %w", err)
	}
	return nil
}

// SetCooldown sets the cooldown_until timestamp.
func (r *KeyRepo) SetCooldown(id int, until time.Time) error {
	_, err := r.db.Exec(`UPDATE gemini_keys SET cooldown_until = ? WHERE id = ?`, until.UTC().Format("2006-01-02 15:04:05"), id)
	if err != nil {
		return fmt.Errorf("key_repo.SetCooldown: %w", err)
	}
	return nil
}

// Toggle enables or disables a key.
func (r *KeyRepo) Toggle(id int, active bool) error {
	val := 0
	if active {
		val = 1
	}
	_, err := r.db.Exec(`UPDATE gemini_keys SET is_active = ? WHERE id = ?`, val, id)
	if err != nil {
		return fmt.Errorf("key_repo.Toggle: %w", err)
	}
	return nil
}

// Delete removes a key by ID.
func (r *KeyRepo) Delete(id int) error {
	_, err := r.db.Exec(`DELETE FROM gemini_keys WHERE id = ?`, id)
	if err != nil {
		return fmt.Errorf("key_repo.Delete: %w", err)
	}
	return nil
}
