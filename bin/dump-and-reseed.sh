#!/bin/bash

# Exit immediately if a command exits with a non-zero status
set -e

php artisan db:wipe

php artisan migrate:sql-dump

php artisan migrate:fresh --seed
