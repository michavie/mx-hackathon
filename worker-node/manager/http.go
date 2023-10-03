package manager

import (
	"bytes"
	"encoding/json"
	"fmt"
	"net/http"
)

type HttpClient struct {
	client    *http.Client
	baseUrl   string
	authtoken string
}

func NewHttpClient(url, token string) *HttpClient {
	return &HttpClient{
		client:    &http.Client{},
		baseUrl:   url,
		authtoken: token,
	}
}

func (client *HttpClient) doPost(endpoint string, body interface{}) (*http.Response, error) {
	fmt.Println("POST", client.baseUrl+endpoint)

	jsonData, err := json.Marshal(body)
	if err != nil {
		return nil, err
	}

	req, err := http.NewRequest("POST", client.baseUrl+endpoint, bytes.NewBuffer(jsonData))
	if err != nil {
		return nil, err
	}

	req.Header.Set("Content-Type", "application/json")
	req.Header.Add("Authorization", client.authtoken)

	return client.client.Do(req)
}
