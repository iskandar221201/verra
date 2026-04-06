package handover

import (
	"strings"
	"unicode/utf8"
	"verra/internal/config"
	"verra/internal/db"
	"verra/internal/dto"
)

// Trigger handles handover detection logic.
type Trigger struct {
	msgRepo *db.MessageRepo
}

// NewTrigger creates a new Trigger.
func NewTrigger(msgRepo *db.MessageRepo) *Trigger {
	return &Trigger{msgRepo: msgRepo}
}

// MatchesHandoverKeyword checks if the message contains any handover keyword.
func (t *Trigger) MatchesHandoverKeyword(text string, keywords []string) bool {
	lower := strings.ToLower(text)
	for _, kw := range keywords {
		kw = strings.TrimSpace(kw)
		if kw == "" {
			continue
		}
		if strings.Contains(lower, strings.ToLower(kw)) {
			return true
		}
	}
	return false
}

// MatchSOP finds the first active SOP whose trigger keywords match the message.
func (t *Trigger) MatchSOP(text string, sops []dto.SOP) *dto.SOP {
	lower := strings.ToLower(text)
	for _, sop := range sops {
		for _, kw := range sop.TriggerKeywords {
			kw = strings.TrimSpace(kw)
			if kw == "" {
				continue
			}
			if strings.Contains(lower, strings.ToLower(kw)) {
				return &sop
			}
		}
	}
	return nil
}

// IsRepeatedMessage detects frustration by checking message similarity.
func (t *Trigger) IsRepeatedMessage(convID string, newMsg string) bool {
	// Ignore short messages (like "halo", "ping", "hi", etc) completely
	// to prevent unwarranted handovers from common greetings.
	if len(newMsg) < 10 {
		return false
	}

	recentMsgs, err := t.msgRepo.GetLastNCustomerMessages(convID, config.RepeatedMsgLookback)
	if err != nil {
		return false
	}

	matchCount := 0
	for _, msg := range recentMsgs {
		similarity := levenshteinSimilarity(newMsg, msg.Content)
		if similarity >= config.LevenshteinSimilarity {
			matchCount++
		}
	}

	return matchCount >= config.RepeatedMsgThreshold
}

// levenshteinSimilarity computes the similarity ratio between two strings.
func levenshteinSimilarity(a, b string) float64 {
	la := utf8.RuneCountInString(a)
	lb := utf8.RuneCountInString(b)
	if la == 0 && lb == 0 {
		return 1.0
	}

	dist := levenshteinDistance([]rune(strings.ToLower(a)), []rune(strings.ToLower(b)))
	maxLen := la
	if lb > maxLen {
		maxLen = lb
	}

	return 1.0 - float64(dist)/float64(maxLen)
}

// levenshteinDistance computes the edit distance between two rune slices.
func levenshteinDistance(a, b []rune) int {
	la := len(a)
	lb := len(b)

	if la == 0 {
		return lb
	}
	if lb == 0 {
		return la
	}

	// Use two rows instead of full matrix for space efficiency
	prev := make([]int, lb+1)
	curr := make([]int, lb+1)

	for j := 0; j <= lb; j++ {
		prev[j] = j
	}

	for i := 1; i <= la; i++ {
		curr[0] = i
		for j := 1; j <= lb; j++ {
			cost := 1
			if a[i-1] == b[j-1] {
				cost = 0
			}
			curr[j] = min(curr[j-1]+1, min(prev[j]+1, prev[j-1]+cost))
		}
		prev, curr = curr, prev
	}

	return prev[lb]
}

func min(a, b int) int {
	if a < b {
		return a
	}
	return b
}
