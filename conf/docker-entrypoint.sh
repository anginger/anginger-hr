#!/bin/sh
# Copyright 2026 Anginger HR
#
# Licensed under the MIT License (https://opensource.org/licenses/MIT)
#
# Applies database migrations (creating/seeding the SQLite database on
# first boot) before handing over to supervisord.
set -e

cd /app/api
php8 yii migrate --interactive=0

exec "$@"
