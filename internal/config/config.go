package config

import (
	"verra/internal/db"
	"verra/internal/dto"
)

// ConfigService provides business config access backed by the database.
type ConfigService struct {
	repo *db.ConfigRepo
}

// NewConfigService creates a new ConfigService.
func NewConfigService(repo *db.ConfigRepo) *ConfigService {
	return &ConfigService{repo: repo}
}

// Get retrieves the current business configuration.
func (s *ConfigService) Get() (dto.BusinessConfig, error) {
	return s.repo.Get()
}

// Save updates the business configuration.
func (s *ConfigService) Save(cfg dto.BusinessConfig) error {
	return s.repo.Save(cfg)
}
