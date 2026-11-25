#!/bin/sh
# Copyright 2026 Anginger HR
#
# Licensed under the MIT License (https://opensource.org/licenses/MIT)
#
# Local development bootstrap: the whole app (server-rendered pages +
# Alpine.js frontend + JSON API) is served by the PHP built-in server.
set -e
export YII_ENV=dev YII_DEBUG=true

ROOT="$(cd "$(dirname "$0")" && pwd)"

sh -c "cd '$ROOT/api' && composer install && php console.php migrate --interactive=0 && cd web && php -S 127.0.0.1:1270 index.php" &

wait
