#!/bin/bash
# Debug mode:
#set -x

# COMMAND
CMD=${1}
shift

# DIRECTORIES
WORKDIR=$(pwd)
if [[ ! ${WORKDIR} =~ ^/(home|etc|var|tmp)/ ]] && [ "${1}" != "-h" ] && [ "${1}" != "--help" ]; then
    echo -e "\033[1;37mYou can only work in \033[33m/home\033[37m, \033[33m/etc\033[37m, \033[33m/var\033[37m and \033[33m/tmp\033[37m directories! The \033[31m${WORKDIR}\033[37m is out of this space!\033[0m"
    exit 1
fi

SOURCE="${BASH_SOURCE[0]}"
while [ -h "$SOURCE" ]; do # resolve $SOURCE until the file is no longer a symlink
  DIR="$( cd -P "$( dirname "$SOURCE" )" && pwd )"
  SOURCE="$(readlink "$SOURCE")"
  [[ $SOURCE != /* ]] && SOURCE="$DIR/$SOURCE" # if $SOURCE was a relative symlink, we need to resolve it relative to the path where the symlink file was located
done
DIR="$( cd -P "$( dirname "$SOURCE" )" && pwd )"

# You can use the `--dev` to enable it without edit config
if [ "$1" == "--dev" ]; then
    shift
    APP_ENV="dev"
    XDEBUG_ENABLED="1"
fi

DOCKER_COMPOSE_ENV="-e LOCAL_USER_ID=$(id -u) -e USER_GROUP=$(getent group docker | cut -d: -f3) -e APP_ENV=${APP_ENV:-prod} -e XDEBUG_ENABLED=${XDEBUG_ENABLED:-0}"
docker run -it \
            ${DOCKER_COMPOSE_ENV} \
            -w ${WORKDIR} \
            -v ${WORKDIR}:${WORKDIR} \
            -v ~/:/home/user \
            fchris82/wf ${CMD} ${@}
