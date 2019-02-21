#!/bin/bash
# Debug mode
#set -x

# Debug! Host target, so you can't use the `source` solution, you have to copy the _debug.sh file content directly.
# << webtown-workflow-package/opt/webtown-workflow/lib/_debug.sh !!!
if [ ${WF_DEBUG:-0} -ge 1 ]; then
    [[ -f /.dockerenv ]] && echo -e "\033[1mDocker: \033[33m${WF_DOCKER_HOST_CHAIN}$(hostname)\033[0m"
    echo -e "\033[1mDEBUG\033[33m $(realpath "$0")\033[0m"
    SYMFONY_COMMAND_DEBUG="-vvv"
    DOCKER_DEBUG="-e WF_DEBUG=${WF_DEBUG}"
fi
[[ ${WF_DEBUG:-0} -ge 2 ]] && set -x

# If user defined docker image doesn't exist, we have to build it first of all. It can miss after a docker prune command.
[ -z "$(docker images -q ${USER}/wf-user)" ] && docker build --no-cache --pull -t ${USER}/wf-user ~/.webtown-workflow

# You can use the `--develop` to enable it without edit config
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

    # Change docker image, if it needs. Maybe you should build first with make command:
    #
    #  $ make -s rebuild_wf build_docker
    #
    GIT_BRANCH=$(cd ${WF_DEVELOP_PATH}/webtown-workflow-package/opt/webtown-workflow && git rev-parse --abbrev-ref HEAD)
    case $GIT_BRANCH in
        master|HEAD)
            # Do nothing
        ;;
        *)
             # @todo Ez egyelőre nem működik helyesen
#            WF_IMAGE="fchris82/wf:`basename ${GIT_BRANCH}`"
        ;;
    esac
fi

# COMMAND
CMD=${1}
shift

# DIRECTORIES
WORKDIR=$(pwd)
GLOBAL_COMMANDS=("-h" "--help" "--version" "--clean-cache" "--reload" "--composer-install")
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
    # Don't use `shift` here!
    SYMFONY_ENV="dev"
    XDEBUG_ENABLED="1"
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
    'WF_HOST_TIMEZONE'
    'WF_HOST_LOCALE'
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
    -e WF_HOST_TIMEZONE=${WF_HOST_TIMEZONE:-$([ -f /etc/timezone ] && cat /etc/timezone)} \
    -e WF_HOST_LOCALE=${WF_HOST_LOCALE:-${LOCALE:-${LANG:-''}}} \
    -e COMPOSER_HOME=${COMPOSER_HOME:-${HOME}/.composer} \
    -e USER_GROUP=$(getent group docker | cut -d: -f3) \
    -e APP_ENV=${SYMFONY_ENV:-'prod'} \
    -e XDEBUG_ENABLED=${XDEBUG_ENABLED:-0} \
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
    SHARED_SF_CACHE="-v ${WEBTOWN_WORKFLOW_BASE_PATH}/cache:${SYMFONY_PATH}/var/cache"
    SHARED_WIZARD_CONFIGURATION="-v ${WEBTOWN_WORKFLOW_BASE_PATH}/config/wizards.yml:/opt/webtown-workflow/host/config/wizards.yml"
fi

# If the .wf.yml is a symlink, we put it into directly. It happens forexample if you are using deployer on a server, and
# share this file among different versions.
if [ -L ${WORKDIR}/${WF_CONFIGURATION_FILE_NAME} ]; then
    CONFIG_FILE_SHARE="-v $(readlink -f ${WORKDIR}/${WF_CONFIGURATION_FILE_NAME}):${WORKDIR}/${WF_CONFIGURATION_FILE_NAME}"
fi
if [ -L ${WORKDIR}/${WF_ENV_FILE_NAME} ]; then
    ENV_FILE_SHARE="-v $(readlink -f ${WORKDIR}/${WF_ENV_FILE_NAME}):${WORKDIR}/${WF_ENV_FILE_NAME}"
fi

# @todo (Chris) !!! Az új megoldást adoptálni: /extensions/recipes + /extensions/wizards
if [ -f ${WEBTOWN_WORKFLOW_BASE_PATH}/cache/extensions.volumes ]; then
    EXTENSIONS_SHARE=$(cat ${WEBTOWN_WORKFLOW_BASE_PATH}/cache/extensions.volumes)
else
    if [ -d ${WEBTOWN_WORKFLOW_BASE_PATH}/extensions ]; then
        RECIPES_SHARE=$(find -L ${WEBTOWN_WORKFLOW_BASE_PATH}/extensions -mindepth 3 -maxdepth 3 -path "${WEBTOWN_WORKFLOW_BASE_PATH}/extensions/*/Recipes/*" -type d -print0 |
            while IFS= read -r -d $'\0' line; do
                RECIPES_SOURCE=$line
                if [ -L $RECIPES_SOURCE ]; then
                    RECIPES_SOURCE=$(readlink -f ${RECIPES_SOURCE})
                fi
                echo -e "-v ${RECIPES_SOURCE}:${RECIPES_PATH}/$(basename $line) \c"
            done
        )

        WIZARDS_SHARE=$(find -L ${WEBTOWN_WORKFLOW_BASE_PATH}/extensions -mindepth 3 -maxdepth 3 -path "${WEBTOWN_WORKFLOW_BASE_PATH}/extensions/*/Wizards/*" -type d -print0 |
            while IFS= read -r -d $'\0' line; do
                WIZARDS_SOURCE=$line
                if [ -L $WIZARDS_SOURCE ]; then
                    WIZARDS_SOURCE=$(readlink -f ${WIZARDS_SOURCE})
                fi
                echo -e "-v ${WIZARDS_SOURCE}:${WIZARDS_PATH}/$(basename $line) \c"
            done
        )
        EXTENSIONS_SHARE="${RECIPES_SHARE}${WIZARDS_SHARE}"
        echo -e "${EXTENSIONS_SHARE}\c" > ${WEBTOWN_WORKFLOW_BASE_PATH}/cache/extensions.volumes
    fi
fi

# You should handle the `WF_DOCKER_HOST_CHAIN` as unique, because the quotes cause some problem if you want to use in an other variable!
docker run ${TTY} \
            ${DOCKER_COMPOSE_ENV} \
            -e WF_DOCKER_HOST_CHAIN="${WF_DOCKER_HOST_CHAIN}" \
            -w ${WORKDIR} \
            ${WORKDIR_SHARE} \
            ${CONFIG_FILE_SHARE} \
            ${ENV_FILE_SHARE} \
            -v ${RUNNER_HOME:-$HOME}:${HOME} \
            ${EXTENSIONS_SHARE} \
            ${SHARED_SF_CACHE} \
            ${SHARED_WIZARD_CONFIGURATION} \
            -v /var/run/docker.sock:/var/run/docker.sock \
            ${DOCKER_DEVELOP_PATH_VOLUME} \
            ${WORKFLOW_CONFIG} \
            ${WF_IMAGE:-${USER}/wf-user} ${CMD} ${@}
