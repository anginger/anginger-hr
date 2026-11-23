FROM node:16-alpine AS web-builder
WORKDIR /build
COPY ./web/package.json ./web/package-lock.json ./
RUN npm ci
COPY ./web ./
COPY ./web/.env.docker ./.env
RUN npm run build

FROM composer:2 AS api-builder
WORKDIR /build
COPY ./api/composer.json ./api/composer.lock ./
RUN composer install --no-interaction --no-progress --no-dev
COPY ./api ./
RUN composer install --no-interaction --no-progress --no-dev

FROM alpine:3.16
RUN apk add --no-cache nginx php8-fpm php8-cli php8-pdo php8-sqlite3 php8-mbstring \
    php8-ctype php8-json php8-session php8-openssl php8-curl php8-fileinfo php8-xml \
    php8-tokenizer supervisor
COPY ./conf/supervisord.conf /etc/supervisord.conf
COPY ./conf/nginx.conf /etc/nginx/http.d/default.conf
COPY ./conf/docker-entrypoint.sh /docker-entrypoint.sh
COPY --from=web-builder /build/dist /app/web
COPY --from=api-builder /build /app/api
RUN chmod +x /docker-entrypoint.sh
WORKDIR /app
EXPOSE 8888
ENTRYPOINT ["/docker-entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
