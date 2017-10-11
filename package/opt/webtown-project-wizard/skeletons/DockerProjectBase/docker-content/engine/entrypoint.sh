#!/bin/bash

CHECKFILE="/home/.system.ready"

read -r -d '' HELP <<-EOM

Initialize script:

  -w --wait-for-init [arg]   Waiting for initialize

EOM


function init {
    rm -rf $CHECKFILE

    # CREATE USER
    USER_ID=${WWW_DATA_UID:-9001}

    echo "Starting with UID : $USER_ID"
    usermod -u $USER_ID www-data
    groupmod -g $WWW_DATA_GID www-data

    if [[ $XDEBUG_ENABLED != 1 ]]; then
        # Disable xdebug
        XDEBUG_INI_BASE=`php --ini | grep -oh ".*\-xdebug.ini"`
        XDEBUG_INI=$([ -h ${XDEBUG_INI_BASE} ] && readlink ${XDEBUG_INI_BASE} || echo ${XDEBUG_INI_BASE})
        sed -i "s/\([^;]*zend_extension=.*xdebug.so\)/;\\1/" $XDEBUG_INI
    else
        # Set remote IP
        HOST_IP=`/sbin/ip route|awk '/default/ { print $3 }'`
        for file in $(egrep -lir --include=xdebug.ini "remote" /etc/php); do
            sed -i "s/\(xdebug.remote_host *= *\).*/\\1${HOST_IP}/" $file
        done
    fi

    # PHP-FPM start
    if [[ $CI != 1 && $CI != 'true' ]]; then
        # Symfony envs. Some PHP-FPM doesn't support the empty value (like 5.6), so this grep find only not empty values!
        env | grep ^SYMFONY.*[^=]$ | awk '{split($0,a,"="); print "env[" a[1] "]=" a[2]}' >> /etc/php/${PHP_VERSION}/fpm/pool.d/www.conf
        service php${PHP_VERSION}-fpm start
        echo "PHP-FPM started: service php${PHP_VERSION}-fpm start"
    fi

    touch $CHECKFILE

    # START BASH
    gosu www-data ${@:-/bin/bash}
}

function waitingForStart {
    c=0
    while [ ! -f $CHECKFILE ]; do
        c++ && $c==10 && exit 1;
        echo "Waiting for engine ready"
        sleep 1
    done;
}

case $1 in
    -h|--help)
    echo -e "${HELP}"
    exit 1
    ;;
    # Wait-for init
    -w|--wait-for-init)
    waitingForStart
    ;;
    # Init
    *)
    init ${@}
    ;;
esac
