# Matchbot

Matchbot is an application that can be used together with Slack, to choose random players for table football matches. Matchbot provides functionality to manage results and to view statistics et cetera.

## Requirements

- Docker
- Incoming Slack webhook and Slack API token (Slack app is required for match initiation functionality)

## Getting started

1. Configure the Slack custom integrations (Incoming WebHooks and Slash Commands (optional)). You will need a token for the Slack API.
2. Configure environment variables
3. Run the following command to start the development environment: `docker-compose -f docker-compose.dev.yml up`. It will now listen to changes in the angular app. The app is available at `localhost:4002`.

## Credits

- Ramon Bakker <ramonbakker@rambit.com>
- Sander van Ooijen <sandervo@protonmail.com>
- Richard Hansma
- Roy Freij
