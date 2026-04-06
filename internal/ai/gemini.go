package ai

import (
	"bytes"
	"encoding/json"
	"fmt"
	"io"
	"net/http"
	"time"
	"verra/internal/config"
	"verra/internal/dto"
)

// GeminiRequest represents the Gemini API request body.
type GeminiRequest struct {
	Contents          []GeminiContent         `json:"contents"`
	SystemInstruction *GeminiContent          `json:"systemInstruction,omitempty"`
	GenerationConfig  *GeminiGenerationConfig `json:"generationConfig,omitempty"`
}

// GeminiContent represents a content block (role + parts).
type GeminiContent struct {
	Role  string       `json:"role,omitempty"`
	Parts []GeminiPart `json:"parts"`
}

// GeminiPart represents a single part (text).
type GeminiPart struct {
	Text string `json:"text"`
}

// GeminiGenerationConfig holds generation parameters.
type GeminiGenerationConfig struct {
	MaxOutputTokens int     `json:"maxOutputTokens,omitempty"`
	Temperature     float64 `json:"temperature,omitempty"`
}

// GeminiResponse represents the Gemini API response.
type GeminiResponse struct {
	Candidates []struct {
		Content struct {
			Parts []struct {
				Text string `json:"text"`
			} `json:"parts"`
		} `json:"content"`
	} `json:"candidates"`
	Error *struct {
		Code    int    `json:"code"`
		Message string `json:"message"`
	} `json:"error,omitempty"`
}

// ErrRateLimit indicates a 429 Too Many Requests error.
var ErrRateLimit = fmt.Errorf("rate limited (HTTP 429)")

// Call makes a raw HTTP request to the Gemini API.
func Call(apiKey, systemPrompt string, history []dto.Message, userMsg string) (string, error) {
	// Build contents from history
	var contents []GeminiContent
	for _, msg := range history {
		role := "user"
		if msg.Role == "ai" {
			role = "model"
		}
		contents = append(contents, GeminiContent{
			Role:  role,
			Parts: []GeminiPart{{Text: msg.Content}},
		})
	}

	// Add current user message
	contents = append(contents, GeminiContent{
		Role:  "user",
		Parts: []GeminiPart{{Text: userMsg}},
	})

	req := GeminiRequest{
		Contents: contents,
		SystemInstruction: &GeminiContent{
			Parts: []GeminiPart{{Text: systemPrompt}},
		},
		GenerationConfig: &GeminiGenerationConfig{
			MaxOutputTokens: 1024,
			Temperature:     0.7,
		},
	}

	body, err := json.Marshal(req)
	if err != nil {
		return "", fmt.Errorf("ai.Call: marshal request: %w", err)
	}

	url := config.GeminiAPIEndpoint + "?key=" + apiKey
	httpReq, err := http.NewRequest("POST", url, bytes.NewReader(body))
	if err != nil {
		return "", fmt.Errorf("ai.Call: create request: %w", err)
	}
	httpReq.Header.Set("Content-Type", "application/json")

	client := &http.Client{Timeout: 30 * time.Second}
	resp, err := client.Do(httpReq)
	if err != nil {
		return "", fmt.Errorf("ai.Call: HTTP request failed: %w", err)
	}
	defer resp.Body.Close()

	respBody, err := io.ReadAll(resp.Body)
	if err != nil {
		return "", fmt.Errorf("ai.Call: read response: %w", err)
	}

	if resp.StatusCode != 200 {
		fmt.Printf("ai.Call: API error %d: %s\n", resp.StatusCode, string(respBody))
		if resp.StatusCode == 429 {
			return "", ErrRateLimit
		}
		return "", fmt.Errorf("ai.Call: API returned status %d: %s", resp.StatusCode, string(respBody))
	}

	var geminiResp GeminiResponse
	if err := json.Unmarshal(respBody, &geminiResp); err != nil {
		return "", fmt.Errorf("ai.Call: parse response: %w", err)
	}

	if geminiResp.Error != nil {
		return "", fmt.Errorf("ai.Call: API error %d: %s", geminiResp.Error.Code, geminiResp.Error.Message)
	}

	if len(geminiResp.Candidates) == 0 || len(geminiResp.Candidates[0].Content.Parts) == 0 {
		return "", fmt.Errorf("ai.Call: empty response from API")
	}

	return geminiResp.Candidates[0].Content.Parts[0].Text, nil
}
