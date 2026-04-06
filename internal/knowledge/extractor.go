package knowledge

import (
	"fmt"
	"path/filepath"
	"strings"
)

// ErrUnsupportedFormat indicates an unsupported file format.
var ErrUnsupportedFormat = fmt.Errorf("unsupported file format")

// ExtractText extracts text content from various file formats.
func ExtractText(filename string, data []byte) (string, error) {
	ext := strings.ToLower(filepath.Ext(filename))
	switch ext {
	case ".txt":
		return string(data), nil
	case ".pdf":
		return extractPDF(data)
	case ".docx":
		return extractDOCX(data)
	default:
		return "", ErrUnsupportedFormat
	}
}

// extractPDF extracts text from PDF data.
// Note: Full PDF extraction requires github.com/ledongthuc/pdf
// For now, this is a basic implementation.
func extractPDF(data []byte) (string, error) {
	// Simplified: PDF extraction is complex and would need
	// the ledongthuc/pdf library. For now return a placeholder
	// that indicates PDF support needs the dependency.
	// TODO: Implement with github.com/ledongthuc/pdf when added to go.mod
	return "", fmt.Errorf("knowledge.extractPDF: PDF extraction not yet implemented — add github.com/ledongthuc/pdf to go.mod")
}

// extractDOCX extracts text from DOCX data.
// DOCX files are ZIP archives containing XML documents.
func extractDOCX(data []byte) (string, error) {
	// Simplified: DOCX is a ZIP with XML inside.
	// For production, use a proper DOCX parsing library.
	// TODO: Implement full DOCX parsing
	return "", fmt.Errorf("knowledge.extractDOCX: DOCX extraction not yet implemented")
}
