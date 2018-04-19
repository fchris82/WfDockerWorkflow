#!/bin/bash

if [ ${DEBUG:-0} -ge 1 ]; then
    [[ -f /.dockerenv ]] && echo -e "\033[1mDocker: \033[33m${WF_DOCKER_HOST_CHAIN}\033[0m"
    echo -e "\033[1mDEBUG\033[33m $(realpath "$0")\033[0m"
    SYMFONY_COMMAND_DEBUG="-vvv"
    DOCKER_DEBUG="-e DEBUG=${DEBUG}"
fi
[[ ${DEBUG:-0} -ge 2 ]] && set -x

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
