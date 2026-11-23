# Anginger HR

Anginger HR 是一個專注於為臺灣中小企業所設計的人力資源管理系統。

Anginger HR is a human resource management system designed for Taiwanese SMEs.

## Stack

| Layer    | Technology                                                        |
| -------- | ----------------------------------------------------------------- |
| API      | PHP 8 + [Yii 2](https://www.yiiframework.com/) (SQLite，可改 MySQL) |
| Web      | Vue 2 + Vuetify + Vuex + Vue Router + Chart.js                     |
| Deploy   | Docker (nginx + php-fpm + supervisord，單一容器，port 8888)        |

後端結構與隔壁的 `anginger_erp` 一致：`api/config.php`、`api/web/index.php`、
`api/controllers/`、`api/models/`、`api/migrations/`（資料庫版控與種子資料）。

## Default User

| Username | Password |
| -------- | -------- |
| admin    | admin    |

## Development

```sh
./bootstrap.dev.sh
```

- API：<http://127.0.0.1:1270>（`web/.env` 內 `VUE_APP_API_HOST` 指向此位址）
- Web：<http://localhost:8080>

資料庫為 `api/database.sqlite`，以 Yii migration 建置與播種：

```sh
cd api
composer install
php yii migrate --interactive=0
php -S 127.0.0.1:1270 -t web web/index.php
```

## Docker

```sh
docker build -t anginger-hr .
docker run -p 8888:8888 anginger-hr
```

開啟 <http://localhost:8888>，API 走 `/api/` 前綴（見 `conf/nginx.conf`）。
容器啟動時會自動執行 `php yii migrate`（見 `conf/docker-entrypoint.sh`）。

## License

[MIT license](LICENSE)

> (c) 2022 [Star Inc.](https://starinc.xyz)
