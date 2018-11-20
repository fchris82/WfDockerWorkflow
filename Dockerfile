FROM php:alpine

ENV LANG en_US.UTF-8
ENV LANGUAGE en_US.UTF-8
ENV LC_ALL en_US.UTF-8
ENV APP_ENV dev

ARG LOCALE=en_US
ENV XDEBUG_CONFIG_FILE=/usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini.disable

# Ez direkt a legelejére kerül, hogy már itt hibát dobjon, ha hiányzik a deb!
# @todo (Chris) Ha erre lesz jobb ötlet, hogy itt töltsük le a deb-et, akkor azt kellene használni
COPY webtown-workflow.deb /root/webtown-workflow.deb

RUN apk update && \
    apk add bash dpkg jq make ca-certificates curl git su-exec docker py-pip php7-xdebug shadow openssh && \
    apk add --upgrade coreutils grep && \
    echo "zend_extension=/usr/lib/php7/modules/xdebug.so" > $XDEBUG_CONFIG_FILE && \
    echo "xdebug.remote_enable=on" >> $XDEBUG_CONFIG_FILE && \
    echo "xdebug.remote_autostart=off" >> $XDEBUG_CONFIG_FILE && \
    echo "xdebug.remote_port=9000" >> $XDEBUG_CONFIG_FILE && \
    echo "xdebug.remote_handler=dbgp" >> $XDEBUG_CONFIG_FILE && \
    echo "xdebug.remote_connect_back=0" >> $XDEBUG_CONFIG_FILE && \
    pip install --upgrade pip && \
    pip install docker-compose && \
    curl -sS https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer && \
    touch /var/lib/dpkg/status && \
    dpkg -i --ignore-depends=git --ignore-depends=jq --ignore-depends=curl --ignore-depends=make /root/webtown-workflow.deb || echo "Done" && \
    rm -f /root/webtown-workflow.deb

COPY docker/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
