#!/bin/bash

# Debug! Host target, so you can't use the `source` solution, you have to copy the _debug.sh file content directly.
# << webtown-workflow-package/opt/webtown-workflow/lib/_debug.sh !!!
if [ ${DEBUG:-0} -ge 1 ]; then
    [[ -f /.dockerenv ]] && echo -e "\033[1mDocker: \033[33m${WF_DOCKER_HOST_CHAIN}\033[0m"
    echo -e "\033[1mDEBUG\033[33m $(realpath "$0")\033[0m"
    SYMFONY_COMMAND_DEBUG="-vvv"
    DOCKER_DEBUG="-e DEBUG=${DEBUG}"
fi
[[ ${DEBUG:-0} -ge 2 ]] && set -x

# You can use the `--dev` to enable it without edit config
if [ "$1" == "--develop" ]; then
    shift
    SOURCE="${BASH_SOURCE[0]}"
    while [ -h "$SOURCE" ]; do # resolve $SOURCE until the file is no longer a symlink
      DIR="$( cd -P "$( dirname "$SOURCE" )" && pwd )"
      SOURCE="$(readlink "$SOURCE")"
      [[ $SOURCE != /* ]] && SOURCE="$DIR/$SOURCE" # if $SOURCE was a relative symlink, we need to resolve it relative to the path where the symlink file was located
    done
    DIR="$( cd -P "$( dirname "$SOURCE" )" && pwd )"
    WF_DEVELOP_PATH="$(realpath ${DIR}/../../../../..)"
    DOCKER_DEVELOP_PATH_VOLUME="-v ${WF_DEVELOP_PATH}/webtown-workflow-package/opt/webtown-workflow:/opt/webtown-workflow"
fi

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
    WF_SYMFONY_ENV="dev"
    WF_XDEBUG_ENABLED="1"
fi

# set defaults
USER=${USER:-${LOCAL_USER_NAME}}
HOME=${HOME:-${LOCAL_USER_HOME}}
WEBTOWN_WORKFLOW_BASE_PATH=${WEBTOWN_WORKFLOW_BASE_PATH:-~/.webtown-workflow}
CI=${CI:-0}
# WF
WF_PROGRAM_REPOSITORY=${WF_PROGRAM_REPOSITORY:-git@gitlab.webtown.hu:webtown/webtown-workflow.git}
WF_SYMFONY_ENV=${WF_SYMFONY_ENV:-prod}
WF_WORKING_DIRECTORY_NAME=${WF_WORKING_DIRECTORY_NAME:-.wf}
WF_CONFIGURATION_FILE_NAME=${WF_CONFIGURATION_FILE_NAME:-.wf.yml}
WF_XDEBUG_ENABLED=${WF_XDEBUG_ENABLED:-0}
WF_DEFAULT_LOCAL_TLD=${WF_DEFAULT_LOCAL_TLD:-.loc}
[[ -z $WF_DOCKER_HOST_CHAIN ]] && WF_DOCKER_HOST_CHAIN=""
WF_DOCKER_HOST_CHAIN+="$(hostname) "
# Chain variables
# @todo Ezt még nem használjuk, de lehet, hogy inkább vmi ilyesmi kellene.
CHAIN_VARIABLE_NAMES=(
    'LOCAL_USER_ID'
    'LOCAL_USER_NAME'
    'LOCAL_USER_HOME'
    'COMPOSER_HOME'
    'USER_GROUP'
    'CI'
    'WEBTOWN_WORKFLOW_BASE_PATH'
    'WF_SYMFONY_ENV'
    'WF_WORKING_DIRECTORY_NAME'
    'WF_CONFIGURATION_FILE_NAME'
    'DEBUG'
    'WF_DOCKER_HOST_CHAIN'
)

DOCKER_COMPOSE_ENV=" \
    -e LOCAL_USER_ID=$(id -u) \
    -e LOCAL_USER_NAME=${USER} \
    -e LOCAL_USER_HOME=${HOME} \
    -e COMPOSER_HOME=${HOME} \
    -e USER_GROUP=$(getent group docker | cut -d: -f3) \
    -e APP_ENV=${WF_SYMFONY_ENV} \
    -e XDEBUG_ENABLED=${WF_XDEBUG_ENABLED} \
    -e WF_DOCKER_HOST_CHAIN=${WF_DOCKER_HOST_CHAIN} \
    -e DEBUG=${DEBUG} \
    -e CI=${CI}"
# If the $WORKDIR is outside the user's home directory, we have to put in the docker
if [[ ! ${WORKDIR} =~ ^${HOME:-${LOCAL_USER_HOME}}(/|$) ]]; then
    WORKDIR_SHARE="-v ${WORKDIR}:${WORKDIR}"
fi

# Default:
WORKFLOW_CONFIG=" \
    -e WF_PROGRAM_REPOSITORY=${WF_PROGRAM_REPOSITORY} \
    -e WF_WORKING_DIRECTORY_NAME=${WF_WORKING_DIRECTORY_NAME} \
    -e WF_CONFIGURATION_FILE_NAME=${WF_CONFIGURATION_FILE_NAME} \
    -e WF_SYMFONY_ENV=${WF_SYMFONY_ENV} \
    -e WF_XDEBUG_ENABLED=${WF_XDEBUG_ENABLED} \
    -e WF_DEFAULT_LOCAL_TLD=${WF_DEFAULT_LOCAL_TLD}"
# Change defaults if config file exists
if [ -f ${WEBTOWN_WORKFLOW_BASE_PATH}/config/env ]; then
    WORKFLOW_CONFIG="--env-file ${WEBTOWN_WORKFLOW_BASE_PATH}/config/env"
fi
if [ "${CI}" == "0" ] && [ -t 1 ]; then
    TTY="-it"
fi

docker run ${TTY} \
            ${DOCKER_COMPOSE_ENV} \
            -w ${WORKDIR} \
            ${WORKDIR_SHARE} \
            -v ${HOME}:${HOME} \
            -v /var/run/docker.sock:/var/run/docker.sock \
            ${DOCKER_DEVELOP_PATH_VOLUME} \
            ${WORKFLOW_CONFIG} \
            fchris82/wf ${CMD} ${@}
