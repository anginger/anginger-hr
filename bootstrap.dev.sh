#!/bin/sh
# Copyright 2026 Anginger HR
#
# Licensed under the MIT License (https://opensource.org/licenses/MIT)
#
# Local development bootstrap: Yii2 API on http://127.0.0.1:1270 and the
# Vue dev server (defaults to http://localhost:8080, see web/.env).
set -e

ROOT="$(cd "$(dirname "$0")" && pwd)"

sh -c "cd '$ROOT/api' && composer install && php yii migrate --interactive=0 && php -S 127.0.0.1:1270 -t web web/index.php" &
sh -c "cd '$ROOT/web' && npm install && npm run serve" &

wait
