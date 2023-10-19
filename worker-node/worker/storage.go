package worker

import (
	"fmt"
	"io/ioutil"
	"os"
	"path/filepath"
	"runner/common"
)

// Manages file and directory operations within a specified path.
type Storage struct {
	Path string
}

// Initializes a new Storage instance with a default directory.
func NewStorage() *Storage {
	return &Storage{Path: common.StorageDir}
}

// Writes the provided content to a file specified by the given filename.
func (s *Storage) SaveFile(filename string, content []byte) error {
	fullPath := filepath.Join(s.Path, filename)
	return ioutil.WriteFile(fullPath, content, 0644)
}

// Ensures the existence of the provided path, creating any required directories along the way.
func (s *Storage) CreateDirectory(path string) error {
	return os.MkdirAll(path, 0755)
}

// Sets up directories to store raw and zipped artifacts for a given run ID.
// Returns the paths for the raw and zip directories.
func (s *Storage) CreateArtifactDirs(runId string) (string, string, error) {
	runDir := "run" + runId
	rawPath := filepath.Join(s.Path, "artifacts", runDir, "raw")
	zipPath := filepath.Join(s.Path, "artifacts", runDir, "zip")

	if err := s.CreateDirectory(rawPath); err != nil {
		return "", "", err
	}

	if err := s.CreateDirectory(zipPath); err != nil {
		return "", "", err
	}

	return rawPath, zipPath, nil
}

// Removes the specified file or directory from the storage.
// If it's a directory, all contents inside it will be removed.
func (s *Storage) Delete(path string) error {
	fmt.Println("Deleting:", path)

	err := os.Remove(path)
	if err != nil {
		// If the path can't be removed as a file, assume it's a directory and attempt recursive deletion.
		return os.RemoveAll(path)
	}
	return nil
}
