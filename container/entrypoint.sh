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
    php artisan migrate:fresh
    php artisan db:seed --class=CitiesSeeder
}

wait_for_db() {
    local -r db_host="${DB_HOST:-mariadb}"
    local -r db_port="${DB_PORT:-3306}"
    local db_address
    db_address=$(getent hosts "$db_host" | awk '{ print $1 }')
    local counter=0
    log "Connecting to mysql at $db_address"
    while ! nc -z "$db_address" "$db_port" >/dev/null; do
        counter=$((counter + 1))
        if [ $counter == 30 ]; then
            log "Error: Couldn't connect to mariadb."
            exit 1
        fi
        log "Trying to connect to mysql at $db_address. Attempt $counter."
        sleep 5
    done
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

wait_for_db
setup_db
npm run prod

php artisan serve --host=0.0.0.0 --port=3000