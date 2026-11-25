# Anginger HR

Anginger HR 是一個專注於為臺灣中小企業所設計的人力資源管理系統。

Anginger HR is a human resource management system designed for Taiwanese SMEs.

架構與 [anginger-erp](https://github.com/anginger/anginger-erp) 一致：
Yii 2 後端 + server-rendered PHP 頁面 + Alpine.js（無 build 步驟），
每個業務領域一個 module，各自擁有頁面路由與 `/api/*` JSON API。

## Stack

| Layer  | Technology                                              |
| ------ | ------------------------------------------------------- |
| App    | PHP 8 + [Yii 2](https://www.yiiframework.com/) + SQLite |
| UI     | Server-rendered PHP views + Alpine.js（本地化，不依賴 CDN）+純 CSS |
| Deploy | Docker（nginx + php-fpm + supervisord，單一容器，port 8888） |

## 結構

```
api/
├── config.php / console.php   web/console 設定（console.php 可直接跑 migrate）
├── controllers/               BaseController（CORS + 操作紀錄）、ResourceController（通用 CRUD）、
│                              PageBaseController、AuthenticController（登入）、SiteController（總覽/登入頁）
├── models/                    User（IdentityInterface）、Employee、Department、History
├── modules/
│   ├── employee/              員工：page（Alpine 頁面）+ api（/api/employee*）
│   ├── department/            部門：page + api（/api/department*）
│   └── account/               登入帳號：page + api（/api/account*）
├── views/                     layouts、site（login/index/error）
├── web/                       index.php 入口 + assets（alpine.min.js、app.css、module-*.js）
└── migrations/                資料庫版控與種子資料（admin/admin）
```

## Default User

| Username | Password |
| -------- | -------- |
| admin    | admin    |

## LDAP / Active Directory 登入

設定 `LDAP_ENABLED=true`（環境變數或 `api/config.local.php`，見 `config.local.sample.php`）後：

- 登入會先以本機密碼驗證**內建帳號**（內建帳號不受影響，仍可登入使用），失敗再向 LDAP/AD 目錄驗證
- 目錄驗證成功的使用者會**自動擴充進內建 `users` 表**（`auth_source=ldap`，同步 `ldap_dn`、顯示名稱、email）
- 啟用期間**停用內建註冊**（無法手動新增帳號），帳號統一由目錄同步
- 帳號管理頁可**封存/解除封存**：封存後該帳號無法以密碼或 LDAP 登入，現有 session 立即失效

環境變數一覽（AD 範例）：`LDAP_HOST`、`LDAP_PORT`（636=LDAPS）、`LDAP_BASE_DN`、
`LDAP_BIND_DN`/`LDAP_BIND_PASSWORD`（服務帳號搜尋）或 `LDAP_USER_DN_PATTERN`（直接 bind）、
`LDAP_USER_FILTER`（AD: `(sAMAccountName=%s)`）、`LDAP_USERNAME_ATTRIBUTE`（AD: `sAMAccountName`）、
`LDAP_STARTTLS`、`LDAP_TIMEOUT`、`LDAP_DISPLAY_NAME_ATTRIBUTE`（預設 `cn`）、`LDAP_EMAIL_ATTRIBUTE`（預設 `mail`）。需要 PHP **ext-ldap**（Docker image 已內建 `php8-ldap`）。

## Deploy / Render

需要 **PHP >= 8.2**（`api/composer.json` 有寫，brick/math 依賴要求 ^8.2）。Render 上請用 **Docker service**（吃 repo 的 `render.yaml` + `Dockerfile`，PHP 8.4），或 native PHP service 時在 Dashboard 把 PHP version 選 8.2 以上。

## Development

```sh
./bootstrap.dev.sh
```

然後開啟 <http://127.0.0.1:1270/login>（預設帳號 admin/admin）。

手動步驟：

```sh
cd api
composer install
php console.php migrate --interactive=0
cd web && php -S 127.0.0.1:1270 index.php
```

資料庫為 `api/database.sqlite`（gitignore），新 ID 使用 ramsey/uuid 的 **UUIDv7**。

## API

| Method | Path                          | 說明                     |
| ------ | ----------------------------- | ------------------------ |
| GET    | `/api/authentic/session`      | 目前登入者（200/401）     |
| POST   | `/api/authentic/session`      | 登入（201/401）           |
| DELETE | `/api/authentic/session`      | 登出（204）               |
| GET    | `/api/employee?emp_id=`       | 查詢單一員工              |
| POST   | `/api/employee`               | 新增員工（201）           |
| PUT    | `/api/employee`               | 更新員工（204）           |
| DELETE | `/api/employee?emp_id=`       | 刪除員工（204；有帳號者 400） |
| GET    | `/api/employee/all`           | 所有員工                  |
|        | `/api/department*`            | 部門 CRUD（同上結構）      |
|        | `/api/account*`               | 帳號 CRUD（同上結構）      |

未登入呼叫 API 回 403 JSON；頁面則導向 `/login`。每個請求會寫入 `histories` 表（操作紀錄，顯示於總覽頁）。

## Docker

```sh
docker build -t anginger-hr .
docker run -p 8888:8888 anginger-hr
```

開啟 <http://localhost:8888/login>。容器啟動時自動執行 `php console.php migrate`。

## Security

- Session-based auth; all management pages/APIs are admin-only (403 otherwise). `User::fields()` never exposes the password hash or `ldap_dn`.
- CSRF is enabled globally: the layout emits a meta token and `app-ui.js` injects `X-CSRF-Token` into every same-origin mutating fetch (missing token -> 400).
- Passwords are bcrypt via `Yii::$app->security`; legacy sha256 hashes still verify and are transparently re-hashed on successful login.
- Login rate limit: 5 failed attempts per IP+username per 15 minutes -> 429. Archived accounts get 423 and their existing sessions are invalidated immediately.
- All view-embedded JSON goes through `Json::htmlEncode` (no `</script>` breakout); Alpine templates use `x-text`, never `x-html`.
- SQLite runs in WAL mode with `foreign_keys=ON`; `GET /health` verifies the DB connection.

## Operational notes

- Error codes: `401` 未登入、`403` 已登入但無權限（管理功能限 admin）、`423` 帳號已封存、`429` 登入嘗試過於頻繁（5 次 / 15 分鐘，鎖定後回 429）。
- CSRF：所有會異動狀態的請求（POST/PUT/DELETE）需帶 `X-CSRF-Token` header（layout 已輸出 meta tag，`app-ui.js` 會自動為 fetch 注入），缺少時回 `400`。
- `GET /health` 不需登入，回 `{"status":"ok"}`，供 Docker `HEALTHCHECK` 與 Render `healthCheckPath` 使用。
- SQLite 為 WAL mode：備份請用 `sqlite3 api/database.sqlite ".backup backup.sqlite"`，或複製時一併複製 `database.sqlite-wal` / `-shm`（線上請先 `PRAGMA wal_checkpoint(TRUNCATE);`）。
- 測試：`cd api && vendor/bin/phpunit`（unit tests，自動用暫存 SQLite 跑 migrate）；E2E smoke：`BASE=http://127.0.0.1:1270 api/tests/e2e.sh`（需先啟動 dev server）。
- 正式環境請設定環境變數：`YII_ENV=prod`、`YII_DEBUG=false`、`COOKIE_VALIDATION_KEY`（隨機字串）、`DB_DSN` / `SQLITE_PATH`（預設 `api/database.sqlite`）。
## License

[MIT license](LICENSE)

> (c) 2022 [Star Inc.](https://starinc.xyz)
