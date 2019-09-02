# BSBIP: Matchbot


- [BSBIP: Matchbot](#bsbip-matchbot)
  - [Installation](#installation)
    - [Requirements:](#requirements)
    - [Laravel](#laravel)
    - [Queue (when you want to use initialization features)](#queue-when-you-want-to-use-initialization-features)
  - [Client](#client)
  - [Slack](#slack)
    - [Create web app](#create-web-app)
    - [Setup interactive components](#setup-interactive-components)
    - [Create bot user](#create-bot-user)
    - [Setup slash command](#setup-slash-command)
    - [Setup permission scopes](#setup-permission-scopes)
    - [Setup incoming webhook](#setup-incoming-webhook)
    - [Setup laravel env variables](#setup-laravel-env-variables)

## Installation

Clone the repository by using the convential methods and follow the following steps

### Requirements:
Server that can run Laravel, for server requirements: <a href="https://laravel.com/docs/5.8/installation#server-requirements">Server requirements</a>

### Laravel

Setup laravel and your database following the conventional laravel installment guide:
<a href="https://laravel.com/docs/5.8/installation">Laravel installation guide</a>

### Queue (when you want to use initialization features)

Setup queue folowing guide following the <a href="https://laravel.com/docs/5.8/queues#running-the-queue-worker">Guidelines</a>

Run the following commands

``` composer install ```

``` php artisan migrate ```


## Client

- cd into client folder
- Run `npm run build`

## Slack

You will need a slack app for this application to work. Creating one and setting up everything you need will be explained here.

### Create web app

Go to <a href="https://api.slack.com/apps?new_app=1">apps</a> in slack and login with your login details that have acces to the slack workspace where the app has to be installed.

Press on the "Create New App" button and setup your app name and workspace

![alt text](./documentation/assets/images/create-slack-app.png "Create slack app")

### Setup interactive components

When you have created your app and selected it, go to "Interactive Components" and switch it on. 

The Request URL you need to provide will need the following structure:

``` http(s)://{your_url}/api/slack/interaction ```

![alt text](./documentation/assets/images/interactive-components.png "Interactive components")

### Create bot user

Now go to "Bot users" and create a bot user, setup it's display name and default username. 

### Setup slash command

Select "Slash commands" and create a new command. Enter the following details:

- Command: `/initiate`
- Request url: `http(s)://{your_url}/api/slack/match/initiate`
- Short description: `Initiates match`

![alt text](./documentation/assets/images/slash-command.png "Slash command")

### Setup permission scopes

Now go to "Oauth & Permissions", you will notice there are two oAuth access tokens. You will need those in the "Setup laravel env variables" step. For now make sure your app has the following permissions:

![alt text](./documentation/assets/images/permissions.png "Permissions")

### Setup incoming webhook

Go to "Incoming Webhooks" and activate the feature. You will need this webhook in the following step

### Setup laravel env variables

Setup the following .env variables:

- SLACK_TOKEN='`Bot User OAuth Access Token`' at "OAuth & Permissions"
- SLACK_SIGNING_SECRET='`Signing Secret`' at "Basic information"
- SLACK_WEBHOOK_URL='`Webhook url`' at "Incoming Webhooks"
