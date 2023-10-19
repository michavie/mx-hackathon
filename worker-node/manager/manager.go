package manager

import (
	"bytes"
	"encoding/json"
	"fmt"
	"io"
	"io/ioutil"
	"mime/multipart"
	"net/http"
	"os"
	"path/filepath"
	"runner/common"
)

// Manager facilitates communication with the server by fetching instructions
// and sending results.
type Manager struct {
	http *HttpClient
}

// Initializes a new Manager with the given server URL and authentication token.
func NewManager(url string, token string) *Manager {
	return &Manager{
		http: NewHttpClient(url, token),
	}
}

type ResponseWrapper struct {
	Data []common.RunInstruction `json:"data"`
}

// Retrieves the next set of run instructions from the server.
// If no instructions are available, it returns nil without error.
func (m *Manager) FetchInstructions() (*common.RunInstruction, error) {
	resp, err := m.http.doPost("/runs/runners/next", nil)
	if err != nil {
		return nil, err
	}
	defer resp.Body.Close()

	body, err := ioutil.ReadAll(resp.Body)
	if err != nil {
		return nil, err
	}

	var wrapper ResponseWrapper
	err = json.Unmarshal(body, &wrapper)
	if err != nil {
		return nil, err
	}

	if len(wrapper.Data) == 0 {
		return nil, nil
	}

	return &wrapper.Data[0], nil
}

// Sends a given run result to the server.
func (m *Manager) SendResult(result common.RunResult) error {
	fmt.Println("Sending result for run Id:", result.RunId, result.Status, result.Output, result.ArtifactZipPath)

	body := &bytes.Buffer{}
	writer := multipart.NewWriter(body)

	writer.WriteField("version", common.AppVersion)
	writer.WriteField("runId", result.RunId)
	writer.WriteField("status", string(result.Status))
	writer.WriteField("output", result.Output)

	if err := attachArtifactToRequest(writer, result); err != nil {
		return err
	}

	return m.postUpdateRequest(writer, body)
}

func attachArtifactToRequest(writer *multipart.Writer, result common.RunResult) error {
	if result.ArtifactZipPath == "" {
		return nil
	}

	file, err := os.Open(result.ArtifactZipPath)
	if err != nil {
		return err
	}
	defer file.Close()

	part, err := writer.CreateFormFile("artifacts", filepath.Base(result.ArtifactZipPath))
	if err != nil {
		return err
	}
	_, err = io.Copy(part, file)
	if err != nil {
		return err
	}

	return writer.Close()
}

func (m *Manager) postUpdateRequest(writer *multipart.Writer, body *bytes.Buffer) error {
	req, err := http.NewRequest("POST", m.http.baseUrl+"/runs/runners/update", body)
	if err != nil {
		return err
	}

	req.Header.Set("Content-Type", writer.FormDataContentType())
	req.Header.Add("Authorization", m.http.authtoken)

	resp, err := m.http.client.Do(req)
	if err != nil {
		return err
	}
	defer resp.Body.Close()

	if resp.StatusCode != http.StatusOK {
		fmt.Println("Error sending result:", resp.Status, resp.Body)
	}

	return nil
}
