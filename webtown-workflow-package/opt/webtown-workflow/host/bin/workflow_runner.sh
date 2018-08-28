#!/bin/bash

# Debug! Host target, so you can't use the `source` solution, you have to copy the _debug.sh file content directly.
# << webtown-workflow-package/opt/webtown-workflow/lib/_debug.sh !!!
if [ ${WF_DEBUG:-0} -ge 1 ]; then
    [[ -f /.dockerenv ]] && echo -e "\033[1mDocker: \033[33m${WF_DOCKER_HOST_CHAIN} $(hostname)\033[0m"
    echo -e "\033[1mDEBUG\033[33m $(realpath "$0")\033[0m"
    SYMFONY_COMMAND_DEBUG="-vvv"
    DOCKER_DEBUG="-e WF_DEBUG=${WF_DEBUG}"
fi
[[ ${WF_DEBUG:-0} -ge 2 ]] && set -x

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
GLOBAL_COMMANDS=("-h" "--help" "--version" "--clean-cache" "--reload")
if [[ ${WORKDIR} =~ ^/($|bin|boot|lib|mnt|proc|sbin|sys) ]] && [[ ! " ${GLOBAL_COMMANDS[@]} " =~ " ${1} " ]]; then
    echo -e "\033[1;37mYou can try to work in a protected directory! The \033[31m${WORKDIR}\033[37m is in a protected space!\033[0m"
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
USER=${USER:-${LOCAL_USER_NAME:-$(id -u -n)}}
HOME=${HOME:-${LOCAL_USER_HOME}}
WEBTOWN_WORKFLOW_BASE_PATH=${WEBTOWN_WORKFLOW_BASE_PATH:-~/.webtown-workflow}
CI=${CI:-0}
# WF
# We look at the TTY existing. If we are in docker then the "-t 1" doesn't work well
if [ -z "${WF_TTY}" ] && [ -t 1 ]; then WF_TTY=1; else WF_TTY=0; fi
WF_PROGRAM_REPOSITORY=${WF_PROGRAM_REPOSITORY:-git@gitlab.webtown.hu:webtown/webtown-workflow.git}
WF_SYMFONY_ENV=${WF_SYMFONY_ENV:-prod}
WF_WORKING_DIRECTORY_NAME=${WF_WORKING_DIRECTORY_NAME:-.wf}
WF_CONFIGURATION_FILE_NAME=${WF_CONFIGURATION_FILE_NAME:-.wf.yml}
WF_ENV_FILE_NAME=${WF_ENV_FILE_NAME:-.wf.env}
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
    'DOCKER_RUN'
    'WEBTOWN_WORKFLOW_BASE_PATH'
    'WF_SYMFONY_ENV'
    'WF_WORKING_DIRECTORY_NAME'
    'WF_CONFIGURATION_FILE_NAME'
    'WF_ENV_FILE_NAME'
    'WF_DEBUG'
    'WF_DOCKER_HOST_CHAIN'
    'WF_TTY'
)

DOCKER_COMPOSE_ENV=" \
    -e LOCAL_USER_ID=$(id -u) \
    -e LOCAL_USER_NAME=${USER} \
    -e LOCAL_USER_HOME=${HOME} \
    -e COMPOSER_HOME=${COMPOSER_HOME:-${HOME}/.composer} \
    -e USER_GROUP=$(getent group docker | cut -d: -f3) \
    -e APP_ENV=${WF_SYMFONY_ENV} \
    -e XDEBUG_ENABLED=${WF_XDEBUG_ENABLED} \
    -e WF_DOCKER_HOST_CHAIN=${WF_DOCKER_HOST_CHAIN} \
    -e WF_DEBUG=${WF_DEBUG} \
    -e CI=${CI} \
    -e DOCKER_RUN=${DOCKER_RUN:-0} \
    -e WF_TTY=${WF_TTY}"
# If the $WORKDIR is outside the user's home directory, we have to put in the docker
if [[ ! ${WORKDIR} =~ ^${HOME:-${LOCAL_USER_HOME}}(/|$) ]] && [[ ! " ${GLOBAL_COMMANDS[@]} " =~ " ${1} " ]]; then
    WORKDIR_SHARE="-v ${WORKDIR}:${WORKDIR}"
fi

# Default:
WORKFLOW_CONFIG=" \
    -e WF_PROGRAM_REPOSITORY=${WF_PROGRAM_REPOSITORY} \
    -e WF_WORKING_DIRECTORY_NAME=${WF_WORKING_DIRECTORY_NAME} \
    -e WF_CONFIGURATION_FILE_NAME=${WF_CONFIGURATION_FILE_NAME} \
    -e WF_ENV_FILE_NAME=${WF_ENV_FILE_NAME} \
    -e WF_SYMFONY_ENV=${WF_SYMFONY_ENV} \
    -e WF_XDEBUG_ENABLED=${WF_XDEBUG_ENABLED} \
    -e WF_DEFAULT_LOCAL_TLD=${WF_DEFAULT_LOCAL_TLD}"
# Change defaults if config file exists
if [ -f ${WEBTOWN_WORKFLOW_BASE_PATH}/config/env ]; then
    WORKFLOW_CONFIG="--env-file ${WEBTOWN_WORKFLOW_BASE_PATH}/config/env"
fi
if [ "${CI}" == "0" ] && [ "${WF_TTY}" == "1" ]; then
    TTY="-it"
fi
if [ "${CI}" == "0" ]; then
    # We use the shared cache only out of cache
    SHARED_SF_CACHE="-v ${WEBTOWN_WORKFLOW_BASE_PATH}/cache:/opt/webtown-workflow/symfony4/var/cache"
fi

# If the .wf.yml is a symlink, we put it into directly. It happens forexample if you are using deployer on a server, and
# share this file among different versions.
if [ -L ${WORKDIR}/${WF_CONFIGURATION_FILE_NAME} ]; then
    CONFIG_FILE_SHARE="-v $(readlink -f ${WORKDIR}/${WF_CONFIGURATION_FILE_NAME}):${WORKDIR}/${WF_CONFIGURATION_FILE_NAME}"
fi
if [ -L ${WORKDIR}/${WF_ENV_FILE_NAME} ]; then
    ENV_FILE_SHARE="-v $(readlink -f ${WORKDIR}/${WF_ENV_FILE_NAME}):${WORKDIR}/${WF_ENV_FILE_NAME}"
fi

# Insert custom recipes
if [ -d ${WEBTOWN_WORKFLOW_BASE_PATH}/recipes ]; then
    RECIPES_PATH=/opt/webtown-workflow/recipes
    RECIPES_SHARE=$(find -L ${WEBTOWN_WORKFLOW_BASE_PATH}/recipes -mindepth 1 -maxdepth 1 -type d -print0 |
        while IFS= read -r -d $'\0' line; do
            RECIPES_SOURCE=$line
            if [ -L $RECIPES_SOURCE ]; then
                RECIPES_SOURCE=$(readlink -f ${RECIPES_SOURCE})
            fi
            echo "-v ${RECIPES_SOURCE}:${RECIPES_PATH}/$(basename $line) "
        done
    )
fi

docker run ${TTY} \
            ${DOCKER_COMPOSE_ENV} \
            -w ${WORKDIR} \
            ${WORKDIR_SHARE} \
            ${CONFIG_FILE_SHARE} \
            ${ENV_FILE_SHARE} \
            -v ${RUNNER_HOME:-$HOME}:${HOME} \
            ${RECIPES_SHARE} \
            ${SHARED_SF_CACHE} \
            -v /var/run/docker.sock:/var/run/docker.sock \
            ${DOCKER_DEVELOP_PATH_VOLUME} \
            ${WORKFLOW_CONFIG} \
            fchris82/wf ${CMD} ${@}
