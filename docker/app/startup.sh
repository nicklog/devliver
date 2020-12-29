#!/bin/sh
set -e

composer install --no-interaction --no-progress --classmap-authoritative
yarn install
yarn prod

bin/console cache:clear
bin/console doctrine:migrations:migrate --no-interaction
