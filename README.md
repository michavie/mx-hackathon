# Spawnable: A Smart Contract Automation Platform

## Summary

## Repository Structure

- [backend](./backend/): Faciliates the build queue based on business requirements.
- [bridge](./bridge/): Interacts with the blockchain via commands received from the backend.
- [contract](./contract/): Smart contract to manage deployments.
- [ui-elements](./ui-elements/): Selected code snippets from the UI.
- [worker-node](./worker-node/): Work off the queue and informs the backend of build results.

## Highlighted Features

- Fully automated & deterministic smart contract builds in the cloud
- Fully automated deployments & upgrades on Devnet and Testnet
- One-click deployments & upgrades via [xPortal](https://xportal.com) & other wallets
- Automatic ABI processing to support SC `init()` arguments
- App with Push Notifications for e.g. finished Contract builds

## Future Roadmap

- Improve UI visualization, especially for live output
- Smart contract version management (deploy historic version)
- Collaboration tools for teams
- Parallelize worker-node processes
- Self-update mechanism for worker nodes
- Remote logging for worker nodes
- Automatic smart contract verification on the [Explorer](https://explorer.multiversx.com)
- Utilize AI for code analysis and auditing services

## Screenshots

## Hosted At

The code found in this repository, including additional business logic and elements to faciliate a superb user experience, is hosted on [spawnable.io](https://spawnable.io).

## Author

This project was developed by [Micha Vie](https://github.com/michavie) as part of the MultiversX hackathon.
