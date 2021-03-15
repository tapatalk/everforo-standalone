FROM webdevops/php-nginx:7.3-alpine

RUN apk add gmp-dev

RUN docker-php-ext-install gmp

ENV WEB_DOCUMENT_ROOT="/app/public"

COPY nginx.conf/1-echo.conf /opt/docker/etc/nginx/vhost.common.d/
