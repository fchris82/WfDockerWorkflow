# You can test the build with `docker run --rm -it php:7.3-alpine /bin/sh` command
FROM php:7.3-cli-alpine

LABEL workflow-base=true
ENV WF_VERSION=2.323

ENV LANG en_US.UTF-8
ENV LANGUAGE en_US.UTF-8
ENV LC_ALL en_US.UTF-8
ENV APP_ENV dev

ARG LOCALE=en_US
ENV XDEBUG_CONFIG_FILE=/usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini.disable
# Useful paths
ENV SYMFONY_PATH=/opt/wf-docker-workflow/symfony4
ENV SYMFONY_CONSOLE=$SYMFONY_PATH/bin/console
ENV WIZARDS_PATH=$SYMFONY_PATH/src/Wizards
ENV RECIPES_PATH=$SYMFONY_PATH/src/Recipes

# We want to get an error if it is missing!
COPY tmp/opt/wf-docker-workflow /opt/wf-docker-workflow

# Docker compose needs: https://docs.docker.com/compose/install/
RUN set -x && apk update && \
    apk --no-cache --virtual .build-deps add --update dpkg $PHPIZE_DEPS && \
    apk --no-cache add --update bash jq ca-certificates curl git su-exec \
        docker-cli make shadow openssh coreutils grep && \
    pecl install xdebug && docker-php-ext-enable xdebug && \
    echo "zend_extension=/usr/lib/php7/modules/xdebug.so" > $XDEBUG_CONFIG_FILE && \
    echo "xdebug.remote_enable=on" >> $XDEBUG_CONFIG_FILE && \
    echo "xdebug.remote_autostart=off" >> $XDEBUG_CONFIG_FILE && \
    echo "xdebug.remote_port=9000" >> $XDEBUG_CONFIG_FILE && \
    echo "xdebug.remote_handler=dbgp" >> $XDEBUG_CONFIG_FILE && \
    echo "xdebug.remote_connect_back=0" >> $XDEBUG_CONFIG_FILE && \
    # @todo Ez a megoldás +200 MB, de elméletileg kihozható ennél kisebbre is.
    apk --no-cache --virtual .dc-deps add python3-dev libffi-dev openssl-dev gcc libc-dev && \
    pip3 install --upgrade pip && \
    pip3 install docker-compose && \
    # Install yq.
    # http://mikefarah.github.io/yq/
    YQ_URL=https://github.com$(wget -q -O- https://github.com/mikefarah/yq/releases/latest \
        | grep -Eo 'href="[^"]+yq_linux_amd64' \
        | sed 's/^href="//' \
        | head -n1) && \
    wget -q -O /usr/local/bin/yq $YQ_URL && \
    chmod a+rx /usr/local/bin/yq && \
    chmod +s $(which su-exec) && \
    curl -sS https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer && \
    touch /var/lib/dpkg/status && \
    apk del --purge .build-deps && \
    pip3 uninstall -y pip setuptools

RUN APP_ENV=prod WF_SYMFONY_ENV=prod /opt/wf-docker-workflow/workflow.sh --composer-install --no-dev && \
    chmod -R 777 /opt/wf-docker-workflow/symfony4/var/cache && \
    chmod -R 777 /opt/wf-docker-workflow/symfony4/var/log && \
    ln -sf /opt/wf-docker-workflow/workflow.sh /usr/local/bin/wf && \
    ln -sf /opt/wf-docker-workflow/wizard.sh /usr/local/bin/wizard && \
    ln -sf /opt/wf-docker-workflow/lib/wf-composer-require.sh /usr/local/bin/wf-composer-require

COPY docker/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
