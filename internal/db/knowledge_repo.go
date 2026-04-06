package db

import (
	"database/sql"
	"encoding/json"
	"fmt"
	"verra/internal/dto"
)

// KnowledgeRepo handles CRUD for all knowledge base tables.
type KnowledgeRepo struct {
	db *sql.DB
}

// NewKnowledgeRepo creates a new KnowledgeRepo.
func NewKnowledgeRepo(db *sql.DB) *KnowledgeRepo {
	return &KnowledgeRepo{db: db}
}

// --- FAQ ---

// GetFAQs returns all FAQs ordered by sort_order.
func (r *KnowledgeRepo) GetFAQs() ([]dto.FAQ, error) {
	rows, err := r.db.Query(`
		SELECT id, question, answer, category, sort_order, is_active
		FROM kb_faqs ORDER BY sort_order ASC, id ASC
	`)
	if err != nil {
		return nil, fmt.Errorf("knowledge_repo.GetFAQs: %w", err)
	}
	defer rows.Close()

	var faqs []dto.FAQ
	for rows.Next() {
		var f dto.FAQ
		if err := rows.Scan(&f.ID, &f.Question, &f.Answer, &f.Category, &f.SortOrder, &f.IsActive); err != nil {
			return nil, fmt.Errorf("knowledge_repo.GetFAQs: scan: %w", err)
		}
		faqs = append(faqs, f)
	}
	return faqs, nil
}

// GetActiveFAQs returns only active FAQs.
func (r *KnowledgeRepo) GetActiveFAQs() ([]dto.FAQ, error) {
	rows, err := r.db.Query(`
		SELECT id, question, answer, category, sort_order, is_active
		FROM kb_faqs WHERE is_active = 1 ORDER BY sort_order ASC, id ASC
	`)
	if err != nil {
		return nil, fmt.Errorf("knowledge_repo.GetActiveFAQs: %w", err)
	}
	defer rows.Close()

	var faqs []dto.FAQ
	for rows.Next() {
		var f dto.FAQ
		if err := rows.Scan(&f.ID, &f.Question, &f.Answer, &f.Category, &f.SortOrder, &f.IsActive); err != nil {
			return nil, fmt.Errorf("knowledge_repo.GetActiveFAQs: scan: %w", err)
		}
		faqs = append(faqs, f)
	}
	return faqs, nil
}

// SaveFAQ inserts or updates a FAQ.
func (r *KnowledgeRepo) SaveFAQ(f dto.FAQ) error {
	if f.ID == 0 {
		_, err := r.db.Exec(`
			INSERT INTO kb_faqs (question, answer, category, sort_order, is_active)
			VALUES (?, ?, ?, ?, ?)
		`, f.Question, f.Answer, f.Category, f.SortOrder, boolToInt(f.IsActive))
		if err != nil {
			return fmt.Errorf("knowledge_repo.SaveFAQ insert: %w", err)
		}
	} else {
		_, err := r.db.Exec(`
			UPDATE kb_faqs SET question=?, answer=?, category=?, sort_order=?, is_active=? WHERE id=?
		`, f.Question, f.Answer, f.Category, f.SortOrder, boolToInt(f.IsActive), f.ID)
		if err != nil {
			return fmt.Errorf("knowledge_repo.SaveFAQ update: %w", err)
		}
	}
	return nil
}

// DeleteFAQ removes a FAQ by ID.
func (r *KnowledgeRepo) DeleteFAQ(id int) error {
	_, err := r.db.Exec(`DELETE FROM kb_faqs WHERE id = ?`, id)
	if err != nil {
		return fmt.Errorf("knowledge_repo.DeleteFAQ: %w", err)
	}
	return nil
}

// ReorderFAQs updates sort_order based on the order of IDs.
func (r *KnowledgeRepo) ReorderFAQs(ids []int) error {
	tx, err := r.db.Begin()
	if err != nil {
		return fmt.Errorf("knowledge_repo.ReorderFAQs: begin tx: %w", err)
	}
	for i, id := range ids {
		if _, err := tx.Exec(`UPDATE kb_faqs SET sort_order = ? WHERE id = ?`, i, id); err != nil {
			tx.Rollback()
			return fmt.Errorf("knowledge_repo.ReorderFAQs: %w", err)
		}
	}
	return tx.Commit()
}

// --- Products ---

// GetProducts returns all products.
func (r *KnowledgeRepo) GetProducts() ([]dto.Product, error) {
	rows, err := r.db.Query(`
		SELECT id, name, price, description, stock_status, category, is_active
		FROM kb_products ORDER BY id ASC
	`)
	if err != nil {
		return nil, fmt.Errorf("knowledge_repo.GetProducts: %w", err)
	}
	defer rows.Close()

	var products []dto.Product
	for rows.Next() {
		var p dto.Product
		if err := rows.Scan(&p.ID, &p.Name, &p.Price, &p.Description, &p.StockStatus, &p.Category, &p.IsActive); err != nil {
			return nil, fmt.Errorf("knowledge_repo.GetProducts: scan: %w", err)
		}
		products = append(products, p)
	}
	return products, nil
}

// GetActiveProducts returns only active products.
func (r *KnowledgeRepo) GetActiveProducts() ([]dto.Product, error) {
	rows, err := r.db.Query(`
		SELECT id, name, price, description, stock_status, category, is_active
		FROM kb_products WHERE is_active = 1 ORDER BY id ASC
	`)
	if err != nil {
		return nil, fmt.Errorf("knowledge_repo.GetActiveProducts: %w", err)
	}
	defer rows.Close()

	var products []dto.Product
	for rows.Next() {
		var p dto.Product
		if err := rows.Scan(&p.ID, &p.Name, &p.Price, &p.Description, &p.StockStatus, &p.Category, &p.IsActive); err != nil {
			return nil, fmt.Errorf("knowledge_repo.GetActiveProducts: scan: %w", err)
		}
		products = append(products, p)
	}
	return products, nil
}

// SaveProduct inserts or updates a product.
func (r *KnowledgeRepo) SaveProduct(p dto.Product) error {
	if p.ID == 0 {
		_, err := r.db.Exec(`
			INSERT INTO kb_products (name, price, description, stock_status, category, is_active)
			VALUES (?, ?, ?, ?, ?, ?)
		`, p.Name, p.Price, p.Description, p.StockStatus, p.Category, boolToInt(p.IsActive))
		if err != nil {
			return fmt.Errorf("knowledge_repo.SaveProduct insert: %w", err)
		}
	} else {
		_, err := r.db.Exec(`
			UPDATE kb_products SET name=?, price=?, description=?, stock_status=?, category=?, is_active=? WHERE id=?
		`, p.Name, p.Price, p.Description, p.StockStatus, p.Category, boolToInt(p.IsActive), p.ID)
		if err != nil {
			return fmt.Errorf("knowledge_repo.SaveProduct update: %w", err)
		}
	}
	return nil
}

// DeleteProduct removes a product by ID.
func (r *KnowledgeRepo) DeleteProduct(id int) error {
	_, err := r.db.Exec(`DELETE FROM kb_products WHERE id = ?`, id)
	if err != nil {
		return fmt.Errorf("knowledge_repo.DeleteProduct: %w", err)
	}
	return nil
}

// --- SOPs ---

// GetSOPs returns all SOPs.
func (r *KnowledgeRepo) GetSOPs() ([]dto.SOP, error) {
	rows, err := r.db.Query(`
		SELECT id, title, trigger_keywords, steps, escalate_to_human, is_active
		FROM kb_sops ORDER BY id ASC
	`)
	if err != nil {
		return nil, fmt.Errorf("knowledge_repo.GetSOPs: %w", err)
	}
	defer rows.Close()

	var sops []dto.SOP
	for rows.Next() {
		var s dto.SOP
		var keywordsJSON, stepsJSON string
		if err := rows.Scan(&s.ID, &s.Title, &keywordsJSON, &stepsJSON, &s.EscalateToHuman, &s.IsActive); err != nil {
			return nil, fmt.Errorf("knowledge_repo.GetSOPs: scan: %w", err)
		}
		json.Unmarshal([]byte(keywordsJSON), &s.TriggerKeywords)
		json.Unmarshal([]byte(stepsJSON), &s.Steps)
		sops = append(sops, s)
	}
	return sops, nil
}

// GetActiveSOPs returns only active SOPs.
func (r *KnowledgeRepo) GetActiveSOPs() ([]dto.SOP, error) {
	rows, err := r.db.Query(`
		SELECT id, title, trigger_keywords, steps, escalate_to_human, is_active
		FROM kb_sops WHERE is_active = 1 ORDER BY id ASC
	`)
	if err != nil {
		return nil, fmt.Errorf("knowledge_repo.GetActiveSOPs: %w", err)
	}
	defer rows.Close()

	var sops []dto.SOP
	for rows.Next() {
		var s dto.SOP
		var keywordsJSON, stepsJSON string
		if err := rows.Scan(&s.ID, &s.Title, &keywordsJSON, &stepsJSON, &s.EscalateToHuman, &s.IsActive); err != nil {
			return nil, fmt.Errorf("knowledge_repo.GetActiveSOPs: scan: %w", err)
		}
		json.Unmarshal([]byte(keywordsJSON), &s.TriggerKeywords)
		json.Unmarshal([]byte(stepsJSON), &s.Steps)
		sops = append(sops, s)
	}
	return sops, nil
}

// SaveSOP inserts or updates a SOP.
func (r *KnowledgeRepo) SaveSOP(s dto.SOP) error {
	keywordsJSON, _ := json.Marshal(s.TriggerKeywords)
	stepsJSON, _ := json.Marshal(s.Steps)
	escalate := boolToInt(s.EscalateToHuman)

	if s.ID == 0 {
		_, err := r.db.Exec(`
			INSERT INTO kb_sops (title, trigger_keywords, steps, escalate_to_human, is_active)
			VALUES (?, ?, ?, ?, ?)
		`, s.Title, string(keywordsJSON), string(stepsJSON), escalate, boolToInt(s.IsActive))
		if err != nil {
			return fmt.Errorf("knowledge_repo.SaveSOP insert: %w", err)
		}
	} else {
		_, err := r.db.Exec(`
			UPDATE kb_sops SET title=?, trigger_keywords=?, steps=?, escalate_to_human=?, is_active=? WHERE id=?
		`, s.Title, string(keywordsJSON), string(stepsJSON), escalate, boolToInt(s.IsActive), s.ID)
		if err != nil {
			return fmt.Errorf("knowledge_repo.SaveSOP update: %w", err)
		}
	}
	return nil
}

// DeleteSOP removes a SOP by ID.
func (r *KnowledgeRepo) DeleteSOP(id int) error {
	_, err := r.db.Exec(`DELETE FROM kb_sops WHERE id = ?`, id)
	if err != nil {
		return fmt.Errorf("knowledge_repo.DeleteSOP: %w", err)
	}
	return nil
}

// --- Notes ---

// GetNotes returns all notes.
func (r *KnowledgeRepo) GetNotes() ([]dto.Note, error) {
	rows, err := r.db.Query(`
		SELECT id, title, content, category, COALESCE(source_file,''), is_active, updated_at
		FROM kb_notes ORDER BY updated_at DESC
	`)
	if err != nil {
		return nil, fmt.Errorf("knowledge_repo.GetNotes: %w", err)
	}
	defer rows.Close()

	var notes []dto.Note
	for rows.Next() {
		var n dto.Note
		if err := rows.Scan(&n.ID, &n.Title, &n.Content, &n.Category, &n.SourceFile, &n.IsActive, &n.UpdatedAt); err != nil {
			return nil, fmt.Errorf("knowledge_repo.GetNotes: scan: %w", err)
		}
		notes = append(notes, n)
	}
	return notes, nil
}

// GetActiveNotes returns only active notes.
func (r *KnowledgeRepo) GetActiveNotes() ([]dto.Note, error) {
	rows, err := r.db.Query(`
		SELECT id, title, content, category, COALESCE(source_file,''), is_active, updated_at
		FROM kb_notes WHERE is_active = 1 ORDER BY updated_at DESC
	`)
	if err != nil {
		return nil, fmt.Errorf("knowledge_repo.GetActiveNotes: %w", err)
	}
	defer rows.Close()

	var notes []dto.Note
	for rows.Next() {
		var n dto.Note
		if err := rows.Scan(&n.ID, &n.Title, &n.Content, &n.Category, &n.SourceFile, &n.IsActive, &n.UpdatedAt); err != nil {
			return nil, fmt.Errorf("knowledge_repo.GetActiveNotes: scan: %w", err)
		}
		notes = append(notes, n)
	}
	return notes, nil
}

// SaveNote inserts or updates a note.
func (r *KnowledgeRepo) SaveNote(n dto.Note) error {
	if n.ID == 0 {
		_, err := r.db.Exec(`
			INSERT INTO kb_notes (title, content, category, source_file, is_active)
			VALUES (?, ?, ?, NULLIF(?, ''), ?)
		`, n.Title, n.Content, n.Category, n.SourceFile, boolToInt(n.IsActive))
		if err != nil {
			return fmt.Errorf("knowledge_repo.SaveNote insert: %w", err)
		}
	} else {
		_, err := r.db.Exec(`
			UPDATE kb_notes SET title=?, content=?, category=?, source_file=NULLIF(?,''), is_active=?, updated_at=CURRENT_TIMESTAMP WHERE id=?
		`, n.Title, n.Content, n.Category, n.SourceFile, boolToInt(n.IsActive), n.ID)
		if err != nil {
			return fmt.Errorf("knowledge_repo.SaveNote update: %w", err)
		}
	}
	return nil
}

// DeleteNote removes a note by ID.
func (r *KnowledgeRepo) DeleteNote(id int) error {
	_, err := r.db.Exec(`DELETE FROM kb_notes WHERE id = ?`, id)
	if err != nil {
		return fmt.Errorf("knowledge_repo.DeleteNote: %w", err)
	}
	return nil
}

// --- Helper ---

func boolToInt(b bool) int {
	if b {
		return 1
	}
	return 0
}
