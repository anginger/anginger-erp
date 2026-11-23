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

## License

[Apache 2.0 license](LICENSE)

> (c) 2021 [Star Inc.](https://starinc.xyz)
