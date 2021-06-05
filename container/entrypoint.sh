#!/bin/bash

set -o errexit
set -o pipefail
# set -o xtrace

# Load libraries
# shellcheck disable=SC1091
. /opt/bitnami/base/functions

# Constants
INIT_SEM=/tmp/initialized.sem

########################
# Setup the database configuration
# Arguments: none
# Returns: none
#########################
setup_db() {
    log "Configuring the database"
    php artisan migrate --force
    php artisan db:seed --class=CitiesSeeder
}

if [[ ! -d /app/app ]]; then
    log "Creating laravel application"
    cp -a /tmp/app/. /app/
    log "Regenerating APP_KEY"
    php artisan key:generate --ansi
fi

log "Installing/Updating Laravel dependencies (composer)"
if [[ ! -d /app/vendor ]]; then
    composer install
    log "Dependencies installed"
else
    log "Dependencies already install"
fi

log "Installing/Updating Laravel dependencies (node)"
if [[ ! -d /app/node_modules ]]; then
    npm i
    log "Npm dependencies installed"
else
    log "Npm dependencies already install"
fi

setup_db
npm run prod

php artisan serve --host=0.0.0.0 --port=3000