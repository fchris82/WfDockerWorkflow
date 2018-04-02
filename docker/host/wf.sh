#!/bin/bash
# Debug mode:
#set -x

# DIRECTORIES
WORKDIR=$(pwd)
SOURCE="${BASH_SOURCE[0]}"
while [ -h "$SOURCE" ]; do # resolve $SOURCE until the file is no longer a symlink
  DIR="$( cd -P "$( dirname "$SOURCE" )" && pwd )"
  SOURCE="$(readlink "$SOURCE")"
  [[ $SOURCE != /* ]] && SOURCE="$DIR/$SOURCE" # if $SOURCE was a relative symlink, we need to resolve it relative to the path where the symlink file was located
done
DIR="$( cd -P "$( dirname "$SOURCE" )" && pwd )"

# You can use the `--dev` to enable it without edit config
APP_ENV=$(get_config 'symfony_env')
XDEBUG_ENABLED=$(get_config 'xdebug_enabled')
if [ "$1" == "--dev" ]; then
    shift
    APP_ENV="dev"
    XDEBUG_ENABLED="1"
fi

DOCKER_COMPOSE_ENV="LOCAL_USER_ID=$(id -u) USER_GROUP=$(getent group docker | cut -d: -f3) APP_ENV=${APP_ENV:-prod} XDEBUG_ENABLED=${XDEBUG_ENABLED:-0}"
BASE_RUN="${DOCKER_COMPOSE_ENV} docker-compose \
            -f ${DIR}/symfony4/docker-compose.yml \
            run --rm"
BASE_PROJECT_RUN="${DOCKER_COMPOSE_ENV} docker-compose \
            -f ${DIR}/symfony4/docker-compose.yml \
            -f ${DIR}/symfony4/docker-compose.project.yml \
            run --rm"

LOCAL_USER_ID=$(id -u) USER_GROUP=$(getent group docker | cut -d: -f3) APP_ENV=${APP_ENV:-prod} XDEBUG_ENABLED=${XDEBUG_ENABLED:-0} \
 docker-compose -f /etc/webtown-workflow/docker/docker-compose.yml -f /etc/webtown-workflow/docker/docker-compose.project.yml \
 run --rm cli wf ${@}
