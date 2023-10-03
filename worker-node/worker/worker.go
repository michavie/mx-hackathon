package worker

import (
	"archive/tar"
	"bufio"
	"bytes"
	"context"
	"errors"
	"fmt"
	"io"
	"os"
	"path/filepath"
	"runner/common"
	"strings"
	"time"

	"github.com/docker/docker/api/types"
	"github.com/docker/docker/api/types/container"
	"github.com/docker/docker/client"
)

type Worker struct {
	Container  *client.Client
	ZipManager *ZipManager
	Storage    *Storage
	Image      string
}

func NewWorker() (*Worker, error) {
	cli, err := client.NewClientWithOpts(client.FromEnv, client.WithAPIVersionNegotiation())
	if err != nil {
		return nil, err
	}

	return &Worker{Container: cli, ZipManager: NewZipManager(), Storage: NewStorage()}, nil
}

func (w *Worker) BuildImage(dockerfile string) error {
	ctx := context.Background()

	// Create a tarball of the Dockerfile to send to Docker.
	buf := new(bytes.Buffer)
	tw := tar.NewWriter(buf)
	defer tw.Close()

	dockerFileReader, err := os.Open("./worker/" + dockerfile)
	if err != nil {
		return err
	}
	defer dockerFileReader.Close()

	stat, err := dockerFileReader.Stat()
	if err != nil {
		return err
	}

	hdr := &tar.Header{
		Name: dockerfile,
		Size: stat.Size(),
	}
	if err := tw.WriteHeader(hdr); err != nil {
		return err
	}
	if _, err := io.Copy(tw, dockerFileReader); err != nil {
		return err
	}

	dockerFileTarReader := bytes.NewReader(buf.Bytes())
	options := types.ImageBuildOptions{
		Tags:       []string{w.Image},
		Dockerfile: dockerfile,
		Remove:     true,
	}

	response, err := w.Container.ImageBuild(ctx, dockerFileTarReader, options)
	if err != nil {
		return err
	}
	defer response.Body.Close()

	_, err = io.Copy(os.Stdout, response.Body)
	return err
}

func (w *Worker) Start(instruction *common.RunInstruction, output func(result common.RunResult)) error {
	fmt.Println("Starting new worker for run Id:", instruction.RunId)

	commands := []string{}

	if instruction.GithubRepoName != "" {
		commands = append(commands, "git clone https://x-access-token:"+instruction.GithubOAuthToken+"@github.com/"+instruction.GithubRepoName+".git .")
	} else {
		commands = append(commands, "git clone https://github.com/"+instruction.GithubRepoName+" .")
	}

	commands = append(commands, instruction.Steps...)

	var dockerfilePath string
	switch instruction.RunType {
	case common.RunTypeMml:
		w.Image = "node"
		dockerfilePath = "Dockerfile.node"
	case common.RunTypeContractMx:
		w.Image = "contract"
		dockerfilePath = "Dockerfile.contract-mx"
	default:
		return errors.New("Unsupported build type" + string(instruction.RunType))
	}

	if err := w.BuildImage(dockerfilePath); err != nil {
		return err
	}

	ctx := context.Background()
	containerId, err := w.createContainer(ctx)
	if err != nil {
		return err
	}

	for _, cmd := range commands {
		err := w.runCommand(instruction, ctx, containerId, cmd, output)
		if err != nil {
			fmt.Println("Stopping command execution because of error", err)

			w.terminateContainer(ctx, containerId)
			break
		}
	}

	artifactZipPath, err := w.saveContainerArtifacts(instruction.RunId, containerId)
	if err != nil {
		return err
	}

	if err := w.terminateContainer(ctx, containerId); err != nil {
		return err
	}

	output(common.RunResult{
		RunId:           instruction.RunId,
		Status:          common.RunStatusSuccess,
		ArtifactZipPath: artifactZipPath,
	})

	w.Storage.Delete(artifactZipPath)

	return nil
}

func (w *Worker) createContainer(ctx context.Context) (string, error) {
	fmt.Println("Spin up container ...")

	resp, err := w.Container.ContainerCreate(ctx, &container.Config{
		Image:      w.Image,
		Cmd:        []string{"tail", "-f", "/dev/null"},
		Tty:        false,
		WorkingDir: "/app",
		Entrypoint: []string{},
	}, &container.HostConfig{
		Resources: container.Resources{
			Memory: 1024 * 1024 * 1024 * 4, // 8 GB
		},
	}, nil, nil, "")
	if err != nil {
		return "", err
	}

	if err := w.Container.ContainerStart(ctx, resp.ID, types.ContainerStartOptions{}); err != nil {
		return "", err
	}

	return resp.ID, nil
}

func (w *Worker) runCommand(instruction *common.RunInstruction, ctx context.Context, containerID string, cmd string, output func(result common.RunResult)) error {
	execConfig := types.ExecConfig{
		Tty:          false,
		AttachStdout: true,
		AttachStderr: true,
		Cmd:          []string{"sh", "-c", cmd},
	}

	execID, err := w.Container.ContainerExecCreate(ctx, containerID, execConfig)
	if err != nil {
		fmt.Println("Error creating container:", err)
		return err
	}

	attachResp, err := w.Container.ContainerExecAttach(ctx, execID.ID, types.ExecStartCheck{})
	if err != nil {
		fmt.Println("Error attaching to container:", err)
		return err
	}

	defer attachResp.Close()

	// Create a channel to send command output
	outputCh := make(chan string)

	// Start a goroutine to read the command output
	go func() {
		scanner := bufio.NewScanner(attachResp.Reader)
		for scanner.Scan() {
			cleanedLine := sanitizeString(scanner.Text())
			if cleanedLine != "" {
				outputCh <- cleanedLine
			}
		}
		if err := scanner.Err(); err != nil {
			fmt.Println("Error reading command output:", err)
		}
		close(outputCh)
	}()

	var lines []string

	for line := range outputCh {
		if line != "" {
			lines = append(lines, line)
		}
		if len(lines) == common.OutputBroadcastPerLines {
			output(common.RunResult{
				RunId:  instruction.RunId,
				Status: common.RunStatusActive,
				Output: strings.Join(lines, "\n"),
			})
			lines = []string{}
		}
	}

	// Output remaining lines if there are fewer than 10 left
	if len(lines) > 0 {
		output(common.RunResult{
			RunId:  instruction.RunId,
			Status: common.RunStatusActive,
			Output: strings.Join(lines, "\n"),
		})
	}

	// Wait for command to finish
	for {
		execInspect, err := w.Container.ContainerExecInspect(ctx, execID.ID)
		if err != nil {
			fmt.Println("Error inspecting container:", err)
			return err
		}
		if !execInspect.Running {
			if execInspect.ExitCode != 0 {
				return fmt.Errorf("Command finished with error: %v", execInspect.ExitCode)
			}
			break
		}
		time.Sleep(time.Second)
	}

	return nil
}

func (w *Worker) terminateContainer(ctx context.Context, respId string) error {
	fmt.Println("Terminating container:", respId)

	if err := w.Container.ContainerStop(ctx, respId, container.StopOptions{}); err != nil {
		return err
	}

	if err := w.Container.ContainerRemove(ctx, respId, types.ContainerRemoveOptions{}); err != nil {
		return err
	}

	return nil
}

func (w *Worker) saveContainerArtifacts(runId string, containerId string) (string, error) {
	ctx := context.Background()

	// 1. Copying files out of the Docker container
	reader, _, err := w.Container.CopyFromContainer(ctx, containerId, "/runner-artifacts")
	if err != nil {
		return "", err
	}
	defer reader.Close()

	// Create directories for raw and zip files
	rawPath, zipPath, err := w.Storage.CreateArtifactDirs(runId)
	if err != nil {
		return "", err
	}

	tr := tar.NewReader(reader)
	for {
		header, err := tr.Next()
		if err == io.EOF {
			break
		}
		if err != nil {
			return "", err
		}

		// Remove the "runner-artifacts" prefix
		relativePath := strings.TrimPrefix(header.Name, "runner-artifacts")
		if strings.HasPrefix(relativePath, "/") {
			relativePath = strings.TrimPrefix(relativePath, "/")
		}
		target := filepath.Join(rawPath, relativePath)

		if header.Typeflag == tar.TypeDir {
			if _, err := os.Stat(target); err != nil {
				if err := w.Storage.CreateDirectory(target); err != nil {
					return "", err
				}
			}
		} else if header.Typeflag == tar.TypeReg {
			file, err := os.Create(target)
			if err != nil {
				return "", err
			}
			defer file.Close()
			if _, err := io.Copy(file, tr); err != nil {
				return "", err
			}
		}
	}

	// 2. Zipping the files
	files := []string{}
	err = filepath.Walk(rawPath, func(path string, info os.FileInfo, err error) error {
		if err != nil {
			return err
		}
		if !info.IsDir() {
			relPath, err := filepath.Rel(rawPath, path)
			if err != nil {
				return err
			}
			files = append(files, filepath.Join(rawPath, relPath))
		}
		return nil
	})
	if err != nil {
		return "", err
	}
	zipFilename := filepath.Join(zipPath, "artifacts.zip")

	err = w.ZipManager.ZipFiles(zipFilename, files)
	if err != nil {
		return "", err
	}

	return zipFilename, nil
}

func sanitizeString(s string) string {
	var result []rune
	for _, r := range s {
		if (r >= 32 && r <= 126) || r == '\n' || r == '\r' { // ASCII printable characters range + newline and carriage return
			result = append(result, r)
		}
	}
	return string(result)
}
