FROM composer:2 AS api-builder
WORKDIR /build
COPY ./api /build
RUN composer install

FROM alpine:3.16
RUN apk add nginx php8 php8-fpm php8-ldap php8-sqlite3 php8-mbstring php8-ctype php8-json php8-session php8-openssl php8-curl php8-fileinfo php8-xml php8-tokenizer supervisor
COPY ./conf/supervisord.conf /etc/supervisord.conf
COPY ./conf/nginx.conf /etc/nginx/http.d/default.conf
COPY --from=api-builder /build /app/api
# the sqlite database lives inside /app/api, so php-fpm (nginx user) must be able to write it
RUN chown -R nginx:nginx /app/api
WORKDIR /app
EXPOSE 8888
CMD ["sh", "-c", "php8 /app/api/console.php migrate --interactive=0 && exec /usr/bin/supervisord -c /etc/supervisord.conf"]
