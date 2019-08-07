FROM php:7.3-cli-alpine

RUN set -x && apk update && \
    apk --no-cache add --update bash git && \
    curl -sS https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer && \
    composer global require hirak/prestissimo
