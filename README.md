# Spawnable: A Smart Contract Automation Platform

## Summary

Developing smart contracts is often a daunting task for the average developer. Beyond merely crafting the core business logic, they must also manage a myriad of other responsibilities, typically through local scripts. This introduces unnecessary complexity, making the development process tedious and error-prone.

Enter **Spawnable**, designed with a mission: to provide the most seamless developer experience for blockchain environments the world has ever seen on [MultiversX](https://multiversx.com). All a developer needs to do is push their code to GitHub. With each commit, Spawnable takes the reins — it builds the smart contract in the cloud and automatically deploys it to both devnet and testnet. Want to launch on mainnet later? It's as simple as a one-click deployment using your [xPortal](https://xportal.com) mobile wallet or any compatible wallet.

The essence of an outstanding developer experience is allowing focus on the app's business logic without distractions. Spawnable achieves this, revolutionizing the way smart contracts are developed, making it straightforward, efficient, and frictionless.

> "All user input is error." - Elon Musk

## Repository Structure

- [backend](./backend/): Faciliates the build queue based on business requirements.
- [bridge](./bridge/): Interacts with the blockchain via commands received from the backend.
- [contract](./contract/): Smart contract to manage deployments.
- [ui-elements](./ui-elements/): Selected code snippets from the UI.
- [worker-node](./worker-node/): Work off the queue and informs the backend of build results.

## Highlighted Features

- Fully automated & deterministic smart contract builds in the cloud
- Fully automated deployments & upgrades on Devnet and Testnet
- One-click **Mainnet** deployments & upgrades via [xPortal](https://xportal.com) & other wallets
- Automatic ABI processing to support SC `init()` arguments
- App with Push Notifications for e.g. finished Contract builds

## Screenshots

## Future Roadmap

- Improve UI visualization, especially for live output
- Smart contract version management (deploy historic version)
- Collaboration tools for teams
- Parallelize worker-node processes
- Self-update mechanism for worker nodes
- Remote logging for worker nodes
- Automatic smart contract verification on the [Explorer](https://explorer.multiversx.com)
- Utilize AI for code analysis and auditing services

## Hosted At

The code found in this repository, including additional business logic and elements to faciliate a superb user experience, is hosted on [spawnable.io](https://spawnable.io).

## Author

This project was developed by [Micha Vie](https://github.com/michavie) as part of the MultiversX hackathon.
