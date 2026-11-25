# Anginger ERP

Anginger ERP 是一款輕量級 ERP 系統，面向臺灣客戶設計。

Anginger ERP is a lightweight ERP system designed for Taiwanese. 

## Architecture

- **Backend**: Yii2, organized as **modules** under `api/modules/`
  (`product`, `provider`, `user`, `level`, `history`). Each module owns its
  server-rendered page (`/product`, `/user`, ...) and its JSON API
  (`/api/product/all`, ...).
- **Frontend**: server-rendered PHP views + **Alpine.js** (localized in
  `api/web/assets/`, no build step). Pages are rendered with initial data;
  Alpine components in each module's asset file call the JSON API with
  `fetch()` for interactions.
- **Database**: SQLite in dev (`api/database.sqlite`), created by
  `php api/console.php migrate --interactive=0`.

## Deploy / Render

需要 **PHP >= 8.2**（`api/composer.json` 有寫，brick/math 依賴要求 ^8.2）。Render 上請用 **Docker service**（吃 repo 的 `render.yaml` + `Dockerfile`，PHP 8.4），或 native PHP service 時在 Dashboard 把 PHP version 選 8.2 以上。

## Run in development

```sh
sh run_dev.sh   # serves http://127.0.0.1:1270 (migrate + php -S)
```

## Run with Docker

```sh
docker build -t anginger-erp .
docker run -p 8888:8888 anginger-erp   # http://localhost:8888
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
- 使用者管理頁可**封存/解除封存**：封存後該帳號無法以密碼或 LDAP 登入，現有 session 立即失效

環境變數一覽（AD 範例）：`LDAP_HOST`、`LDAP_PORT`（636=LDAPS）、`LDAP_BASE_DN`、
`LDAP_BIND_DN`/`LDAP_BIND_PASSWORD`（服務帳號搜尋）或 `LDAP_USER_DN_PATTERN`（直接 bind）、
`LDAP_USER_FILTER`（AD: `(sAMAccountName=%s)`）、`LDAP_USERNAME_ATTRIBUTE`（AD: `sAMAccountName`）、
`LDAP_STARTTLS`、`LDAP_TIMEOUT`、`LDAP_DISPLAY_NAME_ATTRIBUTE`（預設 `cn`）、`LDAP_EMAIL_ATTRIBUTE`（預設 `mail`）。需要 PHP **ext-ldap**（Docker image 已內建 `php8-ldap`）。

## API

| Method | Path | 說明 |
| ------ | ---- | ---- |
| GET | `/api/authentic/session` | 目前登入者（200/401） |
| POST | `/api/authentic/session` | 登入（201/401/423/429） |
| DELETE | `/api/authentic/session` | 登出（204） |
| GET | `/api/authentic/ldap` | LDAP 是否啟用（`{"enabled":bool}`） |
| GET/POST/PUT/DELETE | `/api/product?uuid=…` | 商品 CRUD（GET 單筆；POST 新增 201；PUT 更新 204；DELETE 204；驗證失敗 400、衝突 409） |
| 同上結構 | `/api/provider?_id=…` | 供應商 CRUD |
| 同上結構 | `/api/level?_id=…` | 層級 CRUD |
| 同上結構 | `/api/user?uuid=…` | 使用者 CRUD |
| POST | `/api/user/archive` | 封存/解除封存（body：`uuid`、`archived`；封存後登入回 423） |
| GET | `/api/history?id=…`、`/api/history/all` | 操作紀錄查詢 |
| GET | `/api/<module>/all` | 各模組列表 |
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

[Apache 2.0 license](LICENSE)

> (c) 2021 [Star Inc.](https://starinc.xyz)
