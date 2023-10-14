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
- Live Build output
- Automatic ABI processing to support SC `init()` arguments
- App with Push Notifications for e.g. finished Contract builds

## In-depth Description

TODO

## Screenshots

TODO

## Future Roadmap

- Improve UI visualization, especially for live output
- Smart contract version management (deploy historic version)
- Collaboration tools for teams
- Parallelize worker-node processes
- Self-update mechanism for worker nodes
- Remote logging for worker nodes
- Automatic smart contract verification on the [Explorer](https://explorer.multiversx.com)
- Utilize AI for code analysis and auditing services

## Business Case

### The Problem

Blockchain developers face complexities in smart contract management and deployment. Traditional methods are tedious, error-prone, and inefficient.

### Target Market

Spawnable targets both experienced blockchain developers and newcomers. From startups to established firms and independent developers, Spawnable is designed for anyone looking to simplify their blockchain workflow.

### Market Landscape and Spawnable's Position

While many tools cater to specific aspects of smart contract development, an all-encompassing solution is missing. Spawnable stands out by offering end-to-end automation, from GitHub integration to automatic deployments and intuitive UI generation. In a fragmented market, Spawnable emerges as a comprehensive, forward-looking solution.

## Hosted At

The code found in this repository, including additional business logic and elements to faciliate a superb user experience, is hosted on [spawnable.io](https://spawnable.io).

## Author

This project was developed by [Micha Vie](https://github.com/michavie) as part of the MultiversX hackathon.
