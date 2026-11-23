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

## License

[MIT license](LICENSE)

> (c) 2022 [Star Inc.](https://starinc.xyz)
