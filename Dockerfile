FROM php:7.1

ENV LANG en_US.UTF-8
ENV LANGUAGE en_US.UTF-8
ENV LC_ALL en_US.UTF-8
ENV APP_ENV dev

ARG LOCALE=en_US

# Az acl csak azért kell, hogy a parancsot megtalálja, a setfacl NEM MŰKÖDIK docker image-ben!
RUN apt-get update && apt-get install -y jq make ca-certificates curl git acl libarchive-zip-perl locales vim \
    libmcrypt-dev openssh-client libxml2-dev libpng-dev g++ make autoconf gettext ca-certificates wget && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* && \
    echo "${LOCALE}.UTF-8 UTF-8" >> /etc/locale.gen && \
    locale-gen ${LOCALE}.UTF-8 && \
    /usr/sbin/update-locale LANG=${LOCALE}.UTF-8

ENV GOSU_VERSION 1.10
RUN set -ex; \
    \
    dpkgArch="$(dpkg --print-architecture | awk -F- '{ print $NF }')"; \
    wget -O /usr/local/bin/gosu "https://github.com/tianon/gosu/releases/download/$GOSU_VERSION/gosu-$dpkgArch"; \
    wget -O /usr/local/bin/gosu.asc "https://github.com/tianon/gosu/releases/download/$GOSU_VERSION/gosu-$dpkgArch.asc"; \
    \
# verify the signature
    export GNUPGHOME="$(mktemp -d)"; \
    gpg --keyserver ha.pool.sks-keyservers.net --recv-keys B42F6819007F00F88E364FD4036A9C25BF357DD4; \
    gpg --batch --verify /usr/local/bin/gosu.asc /usr/local/bin/gosu; \
    rm -r "$GNUPGHOME" /usr/local/bin/gosu.asc; \
    \
    chmod +x /usr/local/bin/gosu; \
# verify that the binary works
    gosu nobody true

# Ez direkt a legelejére kerül, hogy már itt hibát dobjon, ha hiányzik a deb!
COPY webtown-workflow.deb /root/webtown-workflow.deb
# @todo (Chris) Ha erre lesz jobb ötlet, hogy itt töltsük le a deb-et, akkor azt kellene használni
RUN curl -sS https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer && \
    dpkg -i /root/webtown-workflow.deb || echo "Done" && \
    rm -f /root/webtown-workflow.deb

# Install XDEBUG
RUN docker-php-source extract \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && docker-php-source delete \
    && echo "xdebug.remote_enable=on" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.remote_autostart=off" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.remote_port=9000" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.remote_handler=dbgp" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.remote_connect_back=0" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && rm -rf /tmp/*

COPY docker/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
