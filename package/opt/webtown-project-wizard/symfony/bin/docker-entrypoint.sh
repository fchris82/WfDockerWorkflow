#!/bin/bash

# XDEBUG
mkdir -p /tmp/php.conf.d
echo "xdebug.remote_host = $(/sbin/ip route|awk '/default/ { print $3 }')" > /tmp/php.conf.d/xdebug.ini
export PHP_INI_SCAN_DIR=$PHP_INI_DIR/conf.d:/tmp/php.conf.d
export XDEBUG_CONFIG="idekey=PHPSTORM"

# USER
USER_ID=${LOCAL_USER_ID:-9001}
adduser -D -H -u $USER_ID user
export HOME=/home/user

su-exec user "$@"
