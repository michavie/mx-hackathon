package common

type RunStatus string

var (
	RunStatusActive   RunStatus = "active"
	RunStatusSuccess  RunStatus = "success"
	RunStatusFailed   RunStatus = "failed"
	RunStatusCanceled RunStatus = "canceled"
)

type RunType string

var (
	RunTypeContractMx RunType = "contract-mx"
	RunTypeMml        RunType = "mml"
)

type RunInstruction struct {
	RunId               string      `json:"runId"`
	RunType             RunType     `json:"runType"`
	GithubRepoName      string      `json:"githubRepoName"`
	GithubOAuthToken    string      `json:"githubOAuthToken"`
	Steps               []string    `json:"steps"`
	Settings            RunSettings `json:"settings"`
	RunnerVersionLatest int         `json:"runnerVersionLatest"`
}

type RunSettings struct {
	RootDir string `json:"rootDir"`
}

type RunResult struct {
	RunId           string
	Status          RunStatus
	Output          string
	ArtifactZipPath string
}
