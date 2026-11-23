FROM node:16-alpine AS web-builder
WORKDIR /build
COPY ./web /build
RUN npm ci && npm run build

FROM composer:2 AS api-builder
WORKDIR /build
COPY ./api /build
RUN composer install

FROM alpine:3.16
RUN apk add nginx php8 php8-fpm supervisor
COPY ./conf/supervisord.conf /etc/supervisord.conf
COPY ./conf/nginx.conf /etc/nginx/http.d/default.conf
COPY --from=web-builder /build/dist /app/web
COPY --from=api-builder /build /app/api
# the sqlite database lives inside /app/api, so php-fpm (nginx user) must be able to write it
RUN chown -R nginx:nginx /app/api
WORKDIR /app
EXPOSE 8888
CMD ["sh", "-c", "php8 /app/api/console.php migrate --interactive=0 && exec /usr/bin/supervisord -c /etc/supervisord.conf"]
