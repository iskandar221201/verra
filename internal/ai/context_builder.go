package ai

import (
	"fmt"
	"strings"
	"verra/internal/dto"
)

// ContextBuilder constructs the system prompt for the AI.
type ContextBuilder struct{}

// NewContextBuilder creates a new ContextBuilder.
func NewContextBuilder() *ContextBuilder {
	return &ContextBuilder{}
}

// Build creates the full system prompt from config and knowledge base data.
func (cb *ContextBuilder) Build(
	cfg dto.BusinessConfig,
	products []dto.Product,
	faqs []dto.FAQ,
	matchedSOP *dto.SOP,
	notes []dto.Note,
) string {
	var parts []string

	// 1. Identity — always
	parts = append(parts, fmt.Sprintf(`Kamu adalah customer service dari %s.
%s.
Balas dalam bahasa %s.
Jangan pernah menyebut bahwa kamu adalah AI kecuali ditanya langsung.`,
		cfg.BusinessName, cfg.AIPersona, cfg.Language))

	// 2. Products — always
	if len(products) > 0 {
		parts = append(parts, cb.formatProducts(products))
	}

	// 3. FAQ — always, ordered by sort_order
	if len(faqs) > 0 {
		parts = append(parts, cb.formatFAQs(faqs))
	}

	// 4. SOP — conditional, only if keyword matched and NOT escalate
	if matchedSOP != nil && !matchedSOP.EscalateToHuman {
		parts = append(parts, cb.formatSOP(matchedSOP))
	}

	// 5. Notes — always, all active
	if len(notes) > 0 {
		parts = append(parts, cb.formatNotes(notes))
	}

	// 6. Closing rules — always
	parts = append(parts, `Aturan penting:
- Jika tidak tahu jawabannya, katakan 'saya cek dulu ya kak' — JANGAN mengarang.
- Jawab singkat dan natural, bukan seperti robot.
- Gunakan 'kak' sebagai sapaan.
- Jika customer meminta bicara dengan manusia, balas: 'baik kak, saya hubungkan ke tim kami ya 🙏'`)

	return strings.Join(parts, "\n\n")
}

func (cb *ContextBuilder) formatProducts(products []dto.Product) string {
	var sb strings.Builder
	sb.WriteString("Daftar Produk:\n")
	for _, p := range products {
		status := "Tersedia"
		switch p.StockStatus {
		case "out_of_stock":
			status = "Habis"
		case "pre_order":
			status = "Pre-order"
		}
		sb.WriteString(fmt.Sprintf("- %s: Rp%d | %s | %s\n", p.Name, p.Price, status, p.Description))
	}
	return sb.String()
}

func (cb *ContextBuilder) formatFAQs(faqs []dto.FAQ) string {
	var sb strings.Builder
	sb.WriteString("FAQ:\n")
	for _, f := range faqs {
		sb.WriteString(fmt.Sprintf("Q: %s\nA: %s\n\n", f.Question, f.Answer))
	}
	return sb.String()
}

func (cb *ContextBuilder) formatSOP(sop *dto.SOP) string {
	var sb strings.Builder
	sb.WriteString(fmt.Sprintf("SOP yang harus diikuti — %s:\n", sop.Title))
	for i, step := range sop.Steps {
		sb.WriteString(fmt.Sprintf("%d. %s\n", i+1, step))
	}
	return sb.String()
}

func (cb *ContextBuilder) formatNotes(notes []dto.Note) string {
	var sb strings.Builder
	sb.WriteString("Catatan Tambahan:\n")
	for _, n := range notes {
		sb.WriteString(fmt.Sprintf("--- %s ---\n%s\n\n", n.Title, n.Content))
	}
	return sb.String()
}
