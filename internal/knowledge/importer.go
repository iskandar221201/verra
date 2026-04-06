package knowledge

import (
	"encoding/csv"
	"fmt"
	"strconv"
	"strings"
	"verra/internal/db"
	"verra/internal/dto"
)

// Importer handles CSV import for FAQ and Product data.
type Importer struct {
	repo *db.KnowledgeRepo
}

// NewImporter creates a new Importer.
func NewImporter(repo *db.KnowledgeRepo) *Importer {
	return &Importer{repo: repo}
}

// ImportFAQFromCSV parses CSV content and inserts FAQ entries.
// Expected columns: question, answer, category
// Returns the number of successfully imported rows.
func (imp *Importer) ImportFAQFromCSV(csvContent string) (int, error) {
	reader := csv.NewReader(strings.NewReader(csvContent))
	records, err := reader.ReadAll()
	if err != nil {
		return 0, fmt.Errorf("knowledge.ImportFAQ: parse CSV: %w", err)
	}

	if len(records) < 2 {
		return 0, fmt.Errorf("knowledge.ImportFAQ: CSV must have header + at least 1 row")
	}

	successCount := 0
	for i, row := range records[1:] { // Skip header
		if len(row) < 2 {
			fmt.Printf("knowledge.ImportFAQ: skip row %d — insufficient columns\n", i+2)
			continue
		}

		question := strings.TrimSpace(row[0])
		answer := strings.TrimSpace(row[1])
		category := "umum"
		if len(row) >= 3 && strings.TrimSpace(row[2]) != "" {
			category = strings.TrimSpace(row[2])
		}

		if question == "" || answer == "" {
			fmt.Printf("knowledge.ImportFAQ: skip row %d — empty question or answer\n", i+2)
			continue
		}

		faq := dto.FAQ{
			Question: question,
			Answer:   answer,
			Category: category,
			IsActive: true,
		}

		if err := imp.repo.SaveFAQ(faq); err != nil {
			fmt.Printf("knowledge.ImportFAQ: error row %d: %v\n", i+2, err)
			continue
		}
		successCount++
	}

	return successCount, nil
}

// ImportProductFromCSV parses CSV content and inserts Product entries.
// Expected columns: name, price, description, stock_status, category
// Returns the number of successfully imported rows.
func (imp *Importer) ImportProductFromCSV(csvContent string) (int, error) {
	reader := csv.NewReader(strings.NewReader(csvContent))
	records, err := reader.ReadAll()
	if err != nil {
		return 0, fmt.Errorf("knowledge.ImportProduct: parse CSV: %w", err)
	}

	if len(records) < 2 {
		return 0, fmt.Errorf("knowledge.ImportProduct: CSV must have header + at least 1 row")
	}

	successCount := 0
	for i, row := range records[1:] {
		if len(row) < 2 {
			fmt.Printf("knowledge.ImportProduct: skip row %d — insufficient columns\n", i+2)
			continue
		}

		name := strings.TrimSpace(row[0])
		if name == "" {
			continue
		}

		price := 0
		if len(row) >= 2 {
			p, err := strconv.Atoi(strings.TrimSpace(row[1]))
			if err == nil {
				price = p
			}
		}

		description := ""
		if len(row) >= 3 {
			description = strings.TrimSpace(row[2])
		}

		stockStatus := "available"
		if len(row) >= 4 && strings.TrimSpace(row[3]) != "" {
			stockStatus = strings.TrimSpace(row[3])
		}

		category := "umum"
		if len(row) >= 5 && strings.TrimSpace(row[4]) != "" {
			category = strings.TrimSpace(row[4])
		}

		product := dto.Product{
			Name:        name,
			Price:       price,
			Description: description,
			StockStatus: stockStatus,
			Category:    category,
			IsActive:    true,
		}

		if err := imp.repo.SaveProduct(product); err != nil {
			fmt.Printf("knowledge.ImportProduct: error row %d: %v\n", i+2, err)
			continue
		}
		successCount++
	}

	return successCount, nil
}
