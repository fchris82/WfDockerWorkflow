#!/bin/bash
# Debug mode:
#set -x

cd ${SYMFONY_PATH}
composer require ${@}
