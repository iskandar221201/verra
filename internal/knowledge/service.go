package knowledge

import (
	"verra/internal/db"
	"verra/internal/dto"
)

// Service provides business logic for knowledge base operations.
type Service struct {
	repo *db.KnowledgeRepo
}

// NewService creates a new knowledge Service.
func NewService(repo *db.KnowledgeRepo) *Service {
	return &Service{repo: repo}
}

// --- FAQ ---

func (s *Service) GetFAQs() ([]dto.FAQ, error) { return s.repo.GetFAQs() }
func (s *Service) SaveFAQ(f dto.FAQ) error     { return s.repo.SaveFAQ(f) }
func (s *Service) DeleteFAQ(id int) error      { return s.repo.DeleteFAQ(id) }
func (s *Service) ReorderFAQs(ids []int) error { return s.repo.ReorderFAQs(ids) }

// --- Products ---

func (s *Service) GetProducts() ([]dto.Product, error) { return s.repo.GetProducts() }
func (s *Service) SaveProduct(p dto.Product) error     { return s.repo.SaveProduct(p) }
func (s *Service) DeleteProduct(id int) error          { return s.repo.DeleteProduct(id) }

// --- SOPs ---

func (s *Service) GetSOPs() ([]dto.SOP, error) { return s.repo.GetSOPs() }
func (s *Service) SaveSOP(sop dto.SOP) error   { return s.repo.SaveSOP(sop) }
func (s *Service) DeleteSOP(id int) error      { return s.repo.DeleteSOP(id) }

// --- Notes ---

func (s *Service) GetNotes() ([]dto.Note, error) { return s.repo.GetNotes() }
func (s *Service) SaveNote(n dto.Note) error     { return s.repo.SaveNote(n) }
func (s *Service) DeleteNote(id int) error       { return s.repo.DeleteNote(id) }
