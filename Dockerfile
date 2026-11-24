FROM composer:2 AS api-builder
WORKDIR /build
COPY ./api /build
RUN composer install

FROM alpine:3.21
RUN apk add --no-cache nginx php84 php84-fpm php84-pdo php84-ldap php84-pdo_sqlite php84-sqlite3 php84-mbstring php84-ctype php84-json php84-session php84-openssl php84-curl php84-fileinfo php84-xml php84-tokenizer supervisor
# php-fpm pool must run as nginx (owner of /app/api) so sqlite writes work
RUN sed -i -e "s/^user = nobody/user = nginx/" -e "s/^group = nobody/group = nginx/" /etc/php84/php-fpm.d/www.conf
COPY ./conf/supervisord.conf /etc/supervisord.conf
COPY ./conf/nginx.conf /etc/nginx/http.d/default.conf
COPY --from=api-builder /build /app/api
# the sqlite database lives inside /app/api, so php-fpm (nginx user) must be able to write it;
# source files may be 0600 and runtime/ is gitignored, so fix perms and create it
RUN chown -R nginx:nginx /app/api && chmod -R a+rX /app/api && mkdir -p /app/api/runtime && chown -R nginx:nginx /app/api/runtime
WORKDIR /app
EXPOSE 8888
CMD ["sh", "-c", "su -s /bin/sh nginx -c \"php84 /app/api/console.php migrate --interactive=0\" && exec /usr/bin/supervisord -c /etc/supervisord.conf"]
