package worker

import (
	"archive/zip"
	"fmt"
	"io"
	"os"
	"path/filepath"
)

// ZipManager is a type responsible for handling the
// creation of zip archives.
type ZipManager struct{}

// NewZipManager returns a new instance of ZipManager.
func NewZipManager() *ZipManager {
	return &ZipManager{}
}

// ZipFiles creates a zip archive with the provided filename
// and adds the given files to it.
func (zm *ZipManager) ZipFiles(zipFilename string, files []string) error {
	fmt.Println("Files to be zipped:", files)

	newZipFile, err := os.Create(zipFilename)
	if err != nil {
		return err
	}
	defer newZipFile.Close()

	fmt.Println("Creating zip at:", zipFilename)

	writer := zip.NewWriter(newZipFile)
	defer writer.Close()

	for _, file := range files {
		relPath, err := filepath.Rel(filepath.Dir(filepath.Dir(zipFilename)), file)
		if err != nil {
			return err
		}
		err = zm.addToZip(writer, file, relPath)
		if err != nil {
			return err
		}
	}
	return nil
}

// A helper method to add a file or a directory
// to the provided zip writer. If a directory is provided,
// it will recursively add its contents to the zip archive.
func (zm *ZipManager) addToZip(writer *zip.Writer, filename string, zipPath string) error {
	fmt.Println("Adding to zip:", filename)

	fileInfo, err := os.Stat(filename)
	if err != nil {
		return err
	}

	if fileInfo.IsDir() {
		files, err := os.ReadDir(filename)
		if err != nil {
			return err
		}

		for _, file := range files {
			err = zm.addToZip(writer, filepath.Join(filename, file.Name()), filepath.Join(zipPath, file.Name()))
			if err != nil {
				return err
			}
		}
	} else {
		zipFile, err := writer.Create(zipPath)
		if err != nil {
			return err
		}

		fsFile, err := os.Open(filename)
		if err != nil {
			return err
		}
		defer fsFile.Close()

		_, err = io.Copy(zipFile, fsFile)
		if err != nil {
			return err
		}
	}

	return nil
}
