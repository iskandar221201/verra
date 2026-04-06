package ai

import (
	"errors"
	"fmt"
	"time"
	"verra/internal/config"
	"verra/internal/dto"
)

// ErrAllKeysExhausted indicates all API keys are in cooldown or unavailable.
var ErrAllKeysExhausted = errors.New("all API keys exhausted")

// Dispatcher orchestrates AI calls with key rotation and retry.
type Dispatcher struct {
	keyPool *KeyPool
}

// NewDispatcher creates a new Dispatcher.
func NewDispatcher(keyPool *KeyPool) *Dispatcher {
	return &Dispatcher{keyPool: keyPool}
}

// Dispatch sends a message to Gemini with automatic key rotation on rate limits.
func (d *Dispatcher) Dispatch(systemPrompt string, history []dto.Message, userMsg string) (string, error) {
	activeCount, err := d.keyPool.GetActiveCount()
	if err != nil {
		return "", fmt.Errorf("ai.Dispatch: get active count: %w", err)
	}

	maxRetry := activeCount
	if maxRetry > config.MaxRetryPerDispatch {
		maxRetry = config.MaxRetryPerDispatch
	}

	for attempt := 0; attempt < maxRetry; attempt++ {
		keyID, apiKey, err := d.keyPool.GetNextKey()
		if err != nil {
			break
		}

		// Mark as used to advance LRU rotation
		_ = d.keyPool.MarkUsed(keyID)

		resp, err := Call(apiKey, systemPrompt, history, userMsg)
		if err == nil {
			return resp, nil
		}

		// If rate limited, try next key after a small delay
		if errors.Is(err, ErrRateLimit) {
			time.Sleep(500 * time.Millisecond)
			continue
		}

		// Other errors stop retrying
		return "", fmt.Errorf("ai.Dispatch: %w", err)
	}

	return "", ErrAllKeysExhausted
}
