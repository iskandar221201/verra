package db

import (
	"database/sql"
	"embed"
	"fmt"
	"sort"
	"strings"

	_ "modernc.org/sqlite"
)

//go:embed migrations/*.sql
var migrationsFS embed.FS

// InitDB opens the SQLite database and runs all migrations.
func InitDB(dbPath string) (*sql.DB, error) {
	db, err := sql.Open("sqlite", dbPath+"?_pragma=foreign_keys(1)&_pragma=journal_mode(WAL)")
	if err != nil {
		return nil, fmt.Errorf("db.InitDB: failed to open database: %w", err)
	}

	// Test connection
	if err := db.Ping(); err != nil {
		return nil, fmt.Errorf("db.InitDB: failed to ping database: %w", err)
	}

	// Run migrations
	if err := runMigrations(db); err != nil {
		return nil, fmt.Errorf("db.InitDB: failed to run migrations: %w", err)
	}

	return db, nil
}

// runMigrations reads all .sql files from the embedded migrations directory
// and executes them in sorted order.
func runMigrations(db *sql.DB) error {
	entries, err := migrationsFS.ReadDir("migrations")
	if err != nil {
		return fmt.Errorf("db.runMigrations: failed to read migrations dir: %w", err)
	}

	// Sort by filename to ensure correct order
	var filenames []string
	for _, entry := range entries {
		if !entry.IsDir() && strings.HasSuffix(entry.Name(), ".sql") {
			filenames = append(filenames, entry.Name())
		}
	}
	sort.Strings(filenames)

	for _, filename := range filenames {
		content, err := migrationsFS.ReadFile("migrations/" + filename)
		if err != nil {
			return fmt.Errorf("db.runMigrations: failed to read %s: %w", filename, err)
		}

		_, err = db.Exec(string(content))
		if err != nil {
			return fmt.Errorf("db.runMigrations: failed to execute %s: %w", filename, err)
		}

		fmt.Printf("db.runMigrations: executed %s\n", filename)
	}

	return nil
}
