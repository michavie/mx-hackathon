package worker

import (
	"fmt"
	"io/ioutil"
	"os"
	"path/filepath"
	"runner/common"
)

type Storage struct {
	Path string
}

func NewStorage() *Storage {
	return &Storage{Path: common.StorageDir}
}

func (s *Storage) SaveFile(filename string, content []byte) error {
	fullPath := filepath.Join(s.Path, filename)
	return ioutil.WriteFile(fullPath, content, 0644)
}

func (s *Storage) CreateDirectory(path string) error {
	return os.MkdirAll(path, 0755)
}

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

func (s *Storage) Delete(path string) error {
	fmt.Println("Deleting:", path)

	err := os.Remove(path)
	if err != nil {
		// If it's a directory or something else, try to remove everything under that path.
		return os.RemoveAll(path)
	}
	return nil
}
