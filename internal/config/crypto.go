package config

import (
	"crypto/aes"
	"crypto/cipher"
	"crypto/rand"
	"crypto/sha256"
	"encoding/base64"
	"fmt"
	"io"

	"github.com/denisbrodbeck/machineid"
)

// GetEncryptionKey derives a 32-byte key from the machine ID.
// This ensures encrypted keys are only valid on the same machine.
func GetEncryptionKey() ([]byte, error) {
	id, err := machineid.ProtectedID("verra")
	if err != nil {
		return nil, fmt.Errorf("config.GetEncryptionKey: failed to get machine ID: %w", err)
	}
	hash := sha256.Sum256([]byte(id))
	return hash[:], nil
}

// EncryptAPIKey encrypts a plaintext API key using AES-GCM.
// Returns a base64-encoded ciphertext.
func EncryptAPIKey(plaintext string) (string, error) {
	key, err := GetEncryptionKey()
	if err != nil {
		return "", err
	}

	block, err := aes.NewCipher(key)
	if err != nil {
		return "", fmt.Errorf("config.EncryptAPIKey: new cipher: %w", err)
	}

	aesGCM, err := cipher.NewGCM(block)
	if err != nil {
		return "", fmt.Errorf("config.EncryptAPIKey: new GCM: %w", err)
	}

	nonce := make([]byte, aesGCM.NonceSize())
	if _, err := io.ReadFull(rand.Reader, nonce); err != nil {
		return "", fmt.Errorf("config.EncryptAPIKey: generate nonce: %w", err)
	}

	ciphertext := aesGCM.Seal(nonce, nonce, []byte(plaintext), nil)
	return base64.StdEncoding.EncodeToString(ciphertext), nil
}

// DecryptAPIKey decrypts a base64-encoded AES-GCM ciphertext.
// Returns the plaintext API key.
func DecryptAPIKey(ciphertextB64 string) (string, error) {
	key, err := GetEncryptionKey()
	if err != nil {
		return "", err
	}

	ciphertext, err := base64.StdEncoding.DecodeString(ciphertextB64)
	if err != nil {
		return "", fmt.Errorf("config.DecryptAPIKey: base64 decode: %w", err)
	}

	block, err := aes.NewCipher(key)
	if err != nil {
		return "", fmt.Errorf("config.DecryptAPIKey: new cipher: %w", err)
	}

	aesGCM, err := cipher.NewGCM(block)
	if err != nil {
		return "", fmt.Errorf("config.DecryptAPIKey: new GCM: %w", err)
	}

	nonceSize := aesGCM.NonceSize()
	if len(ciphertext) < nonceSize {
		return "", fmt.Errorf("config.DecryptAPIKey: ciphertext too short")
	}

	nonce, ciphertext := ciphertext[:nonceSize], ciphertext[nonceSize:]
	plaintext, err := aesGCM.Open(nil, nonce, ciphertext, nil)
	if err != nil {
		return "", fmt.Errorf("config.DecryptAPIKey: decrypt failed: %w", err)
	}

	return string(plaintext), nil
}
