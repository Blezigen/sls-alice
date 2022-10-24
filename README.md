# Документация

Требуется установить на сервер:
- node.js 14+
- php 8.1 +
- ext-bcmath
- ext-pdo_pgsql


## Установка

Что-бы работала авторизация необходимо создать сертификаты:

```bash
cd ./backend
openssl genrsa -out private.pem 2048
openssl rsa -in private.pem -pubout -out public.pem
```

API

```sh
cd ./backend
composer install
chmod +x ./bin/start.sh
./bin/start.sh
php yii serve --docroot="api/web"
```

Frontend

```sh
cd ./frontend
npm install
npm run dev
```
