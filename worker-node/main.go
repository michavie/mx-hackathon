package main

import (
	"fmt"
	"runner/common"
	"runner/manager"
	"runner/worker"
	"time"
)

func main() {
	fmt.Println("Starting runner ...")

	manager := manager.NewManager(common.ApiUrl, common.ApiToken)

	for {
		instruction, err := manager.FetchInstructions()
		if err != nil {
			fmt.Println("Error fetching instructions:", err)
			continue
		}
		if instruction == nil {
			fmt.Println("No instruction found, sleeping for 30 seconds ...")
			time.Sleep(30 * time.Second)
			continue
		}

		worker, err := worker.NewWorker()
		if err != nil {
			fmt.Println("Error creating worker:", err)
			continue
		}

		err = worker.Start(instruction, func(result common.RunResult) {
			manager.SendResult(result)
		})
		if err != nil {
			fmt.Println("Error running repo:", err)
			continue
		}

		time.Sleep(1 * time.Second)
	}
}
