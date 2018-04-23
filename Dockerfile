FROM php:7.2-cli-alpine3.7

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

## Az acl csak azért kell, hogy a parancsot megtalálja, a setfacl NEM MŰKÖDIK docker image-ben!
#RUN apt-get update && apt-get install -y jq make ca-certificates curl git acl libarchive-zip-perl locales vim \
#    libmcrypt-dev openssh-client libxml2-dev libpng-dev g++ autoconf gettext wget \
#    apt-transport-https software-properties-common && \
#    curl -fsSL https://download.docker.com/linux/ubuntu/gpg | apt-key add - && \
#    add-apt-repository \
#          "deb [arch=amd64] https://download.docker.com/linux/debian \
#          $(lsb_release -cs) \
#          stable" && \
#    apt-get update && apt-get install -y docker-ce && \
#    COMPOSE_VER=$(curl -s -o /dev/null -I -w "%{redirect_url}\n" https://github.com/docker/compose/releases/latest | grep -oP "[0-9]+(\.[0-9]+)+$") && \
#    curl -o /usr/local/bin/docker-compose -L https://github.com/docker/compose/releases/download/$COMPOSE_VER/docker-compose-$(uname -s)-$(uname -m) && \
#    chmod +x /usr/local/bin/docker-compose && \
#    apt-get clean && \
#    rm -rf /var/lib/apt/lists/* && \
#    echo "${LOCALE}.UTF-8 UTF-8" >> /etc/locale.gen && \
#    locale-gen ${LOCALE}.UTF-8 && \
#    /usr/sbin/update-locale LANG=${LOCALE}.UTF-8
#
#ENV GOSU_VERSION 1.10
#RUN set -ex; \
#    \
#    dpkgArch="$(dpkg --print-architecture | awk -F- '{ print $NF }')"; \
#    wget -O /usr/local/bin/gosu "https://github.com/tianon/gosu/releases/download/$GOSU_VERSION/gosu-$dpkgArch"; \
#    wget -O /usr/local/bin/gosu.asc "https://github.com/tianon/gosu/releases/download/$GOSU_VERSION/gosu-$dpkgArch.asc"; \
#    \
## verify the signature
#    export GNUPGHOME="$(mktemp -d)"; \
#    gpg --keyserver ha.pool.sks-keyservers.net --recv-keys B42F6819007F00F88E364FD4036A9C25BF357DD4; \
#    gpg --batch --verify /usr/local/bin/gosu.asc /usr/local/bin/gosu; \
#    rm -r "$GNUPGHOME" /usr/local/bin/gosu.asc; \
#    \
#    chmod +x /usr/local/bin/gosu; \
## verify that the binary works
#    gosu nobody true

COPY docker/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
