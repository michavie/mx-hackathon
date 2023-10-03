package common

import (
	"os"

	"github.com/joho/godotenv"
)

var (
	AppVersion              = "0.1.0"
	AppName                 string
	ApiUrl                  = "https://api.spawnable.io"
	ApiToken                string
	StorageDir              = "./storage"
	OutputBroadcastPerLines = 20
)

func init() {
	loadEnvVariables()
}

func loadEnvVariables() {
	if err := godotenv.Load(); err != nil {
		panic("No .env file found")
	}

	AppName = os.Getenv("APP_NAME")
	ApiUrl = os.Getenv("API_URL")
	ApiToken = os.Getenv("API_TOKEN")
}
