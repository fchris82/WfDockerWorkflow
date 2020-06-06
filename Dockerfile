FROM php:7.4-cli-alpine

RUN set -x && apk update && \
    apk --no-cache add --update bash git git-subtree openssh-client && \
    curl -sS https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer && \
    composer global require hirak/prestissimo
