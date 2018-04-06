#!/bin/bash

if [ ${DEBUG:-0} -ge 1 ]; then
    [[ -f /.dockerenv ]] && echo -e "\033[1mDocker: \033[33m${HOSTNAME}\033[0m"
    echo -e "\033[1mDEBUG\033[33m $(realpath "$0")\033[0m"
    SYMFONY_COMMAND_DEBUG="-vvv"
    DOCKER_DEBUG="-e DEBUG=${DEBUG}"
fi

[[ ${DEBUG:-0} -ge 2 ]] && set -x
