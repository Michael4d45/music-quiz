#!/bin/bash

# Exit immediately if a command exits with a non-zero status
set -e

# Running fresh migrations with seeding
php artisan migrate:fresh --seed

# Running tests in parallel with database recreation
php artisan test --parallel --recreate-databases
