# BulkImport contacts and Profiles to Trengo

## Up and Running

Requirements:
- Git
- Docker

Steps:
- Clone the repository `git@github.com:rasadeghnasab/trengo-import.git`
- Cd to `trengo-import` directory
- copy `.env.example` to `.env` and set the required variables
- run the `./vendor/bin/sail up` command and WAIT
- open a new terminal
- run the `./vendor/bin/sail artisan queue:work`
- Now you can run each one of endpoint you wish at `localhost/api/*`


## Available Endpoints

- You can check [this](https://documenter.getpostman.com/view/844555/UzBtoQ3c) URL to see documentation for all available endpoints: https://documenter.getpostman.com/view/844555/UzBtoQ3c

