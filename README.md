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
`LDAP_STARTTLS`、`LDAP_TIMEOUT`。需要 PHP **ext-ldap**（Docker image 已內建 `php8-ldap`）。

## License

[Apache 2.0 license](LICENSE)

> (c) 2021 [Star Inc.](https://starinc.xyz)
