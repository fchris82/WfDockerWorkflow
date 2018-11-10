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

source ${DIR}/lib/_debug.sh
source ${DIR}/lib/_css.sh
source ${DIR}/lib/_wizard_help.sh
source ${DIR}/lib/_functions.sh

# You can use the `--dev` to enable it without edit config
WF_SYMFONY_ENV=${WF_SYMFONY_ENV:-prod}
WF_XDEBUG_ENABLED=${WF_XDEBUG_ENABLED:-0}
if [ "$1" == "--dev" ]; then
    shift
    WF_SYMFONY_ENV="dev"
    WF_XDEBUG_ENABLED="1"
fi

DOCKER_COMPOSE_ENV=" \
    LOCAL_USER_ID=$(id -u) \
    LOCAL_USER_NAME=${USER} \
    LOCAL_USER_HOME=${HOME} \
    USER_GROUP=$(getent group docker | cut -d: -f3) \
    APP_ENV=${WF_SYMFONY_ENV} \
    XDEBUG_ENABLED=${WF_XDEBUG_ENABLED} \
    SYMFONY_DEPRECATIONS_HELPER=${SYMFONY_DEPRECATIONS_HELPER} \
    SYMFONY_TRUSTED_PROXIES=${SYMFONY_TRUSTED_PROXIES:-127.0.0.1,172.16.0.0/12,192.168.0.0/16}"
BASE_RUN="${DOCKER_COMPOSE_ENV} docker-compose \
            -f ${DIR}/symfony4/docker-compose.yml \
            run --rm"
BASE_PROJECT_RUN="${DOCKER_COMPOSE_ENV} docker-compose \
            -f ${DIR}/symfony4/docker-compose.yml \
            -f ${DIR}/symfony4/docker-compose.project.yml \
            run --rm"

case $1 in
    -h|--help)
        showHelp
    ;;
    # For debugging
    -e|--enter)
        eval "$BASE_RUN cli /bin/bash"
    ;;
    -i|--install)
        shift
        if [ -x "$(which composer)" ]; then
            cd /opt/webtown-workflow/symfony4 && composer install ${@}
        else
            eval "$BASE_RUN -w /opt/webtown-workflow/symfony4 cli composer install ${@} ${SYMFONY_DISABLE_TTY} ${SYMFONY_COMMAND_DEBUG}"
        fi
    ;;
    # REBUILD the docker container
    -r|--rebuild)
        rm -rf ${DIR}/symfony4/var/cache/*
        eval "$DOCKER_COMPOSE_ENV docker-compose -f ${DIR}/symfony4/docker-compose.yml build --no-cache"
    ;;
    -t|--test)
#        eval "$BASE_RUN cli php /opt/webtown-workflow/symfony4/bin/phpunit -c /opt/webtown-workflow/symfony4"
#        $BASE_RUN cli php /opt/webtown-workflow/symfony4/vendor/bin/php-cs-fixer fix --config=/opt/webtown-workflow/symfony4/.php_cs.dist
    ;;
    --debug)
        shift
        eval "$BASE_PROJECT_RUN cli ${@}"
    ;;
    # RUN wizard
    *)
        #eval "$BASE_PROJECT_RUN cli php /opt/webtown-workflow/symfony4/bin/console app:wizard ${@}"
        php /opt/webtown-workflow/symfony4/bin/console app:wizard \
            --wf-version $(dpkg-query --showformat='${Version}' --show webtown-workflow) \
            ${@} ${SYMFONY_DISABLE_TTY} ${SYMFONY_COMMAND_DEBUG}
    ;;
esac
