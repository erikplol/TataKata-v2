#!/usr/bin/env sh
# Entrypoint script for running the Laravel queue worker.
# Usage: set QUEUE_WORKER_OPTS env var to override default options.

set -e

: ${QUEUE_WORKER_OPTS:=--sleep=3 --tries=3 --timeout=300 --memory=512}

echo "Starting Laravel queue worker with options: ${QUEUE_WORKER_OPTS}"

# Ensure storage link exists (idempotent)
php artisan storage:link || true

# Run the worker
php artisan queue:work ${QUEUE_WORKER_OPTS}
