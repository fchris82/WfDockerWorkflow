#!/bin/bash

if [ "${XDEBUG_ENABLED}" == "1" ]; then
    mkdir -p /tmp/php.conf.d
    echo "xdebug.remote_host = $(/sbin/ip route|awk '/default/ { print $3 }')" > /tmp/php.conf.d/xdebug.ini
    export PHP_INI_SCAN_DIR=$PHP_INI_DIR/conf.d:/tmp/php.conf.d
    export XDEBUG_CONFIG="idekey=Docker"
    export PHP_IDE_CONFIG="serverName=Docker"
else
    # Disable xdebug
    XDEBUG_INI_BASE=`php --ini | grep -oh "\S*xdebug.ini"`
    XDEBUG_INI=$([ -h ${XDEBUG_INI_BASE} ] && readlink ${XDEBUG_INI_BASE} || echo ${XDEBUG_INI_BASE})
    mv ${XDEBUG_INI} ${XDEBUG_INI}.disable
fi

# USER
USER_ID=${LOCAL_USER_ID:-9001}
useradd -u $USER_ID ${LOCAL_USER_NAME} -G docker
export HOME=${LOCAL_USER_HOME}

[[ -f /opt/webtown-workflow/symfony4/.env ]] && chown -R ${USER_ID} /opt/webtown-workflow/symfony4/.env
[[ -f /opt/webtown-workflow/symfony4/var ]] && chown -R ${USER_ID} /opt/webtown-workflow/symfony4/var

gosu ${LOCAL_USER_NAME} "$@"
