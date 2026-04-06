package ai

import (
	"fmt"
	"time"
	"verra/internal/config"
	"verra/internal/db"
)

// KeyPool manages API key selection with LRU and cooldown.
type KeyPool struct {
	keyRepo *db.KeyRepo
}

// NewKeyPool creates a new KeyPool.
func NewKeyPool(keyRepo *db.KeyRepo) *KeyPool {
	return &KeyPool{keyRepo: keyRepo}
}

// GetNextKey returns the next available API key using LRU strategy.
// Returns keyID, decrypted API key, or error if none available.
func (kp *KeyPool) GetNextKey() (int, string, error) {
	id, encryptedKey, err := kp.keyRepo.GetLRUKey()
	if err != nil {
		return 0, "", fmt.Errorf("ai.KeyPool.GetNextKey: %w", err)
	}

	// Decrypt the key
	apiKey, err := config.DecryptAPIKey(encryptedKey)
	if err != nil {
		return 0, "", fmt.Errorf("ai.KeyPool.GetNextKey: decrypt failed: %w", err)
	}

	return id, apiKey, nil
}

// MarkUsed updates the last_used_at and increments request count.
func (kp *KeyPool) MarkUsed(keyID int) error {
	if err := kp.keyRepo.UpdateLastUsed(keyID); err != nil {
		return fmt.Errorf("ai.KeyPool.MarkUsed: update last used: %w", err)
	}
	if err := kp.keyRepo.IncrementRequests(keyID); err != nil {
		return fmt.Errorf("ai.KeyPool.MarkUsed: increment requests: %w", err)
	}
	return nil
}

// SetCooldown puts a key into cooldown for the configured duration.
func (kp *KeyPool) SetCooldown(keyID int) error {
	until := time.Now().Add(config.KeyCooldownDuration)
	return kp.keyRepo.SetCooldown(keyID, until)
}

// GetActiveCount returns the number of active keys.
func (kp *KeyPool) GetActiveCount() (int, error) {
	return kp.keyRepo.GetActiveKeyCount()
}
