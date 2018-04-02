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

source ${DIR}/../webtown-workflow/lib/_css.sh
source ${DIR}/../webtown-workflow/lib/_wizard_help.sh
source ${DIR}/../webtown-workflow/lib/_functions.sh

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
            eval "$BASE_RUN -w /opt/webtown-workflow/symfony4 cli composer install ${@}"
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
    # Rebuild config from the yml. See: workflow.sh .
    # @todo (Chris) Ennek az egésznek tulajdonképpen inkább a workflow-ban van a helye, nem itt, csak itt volt már SF ezért ide építettem be.
    # @todo (Chris) Ez így nem jó, mert hívható közvetlenül, de nem dob hibát, ha nincs elég információja!
    --reconfigure)
        shift
        #eval "$BASE_PROJECT_RUN cli php /opt/webtown-workflow/symfony4/bin/console app:config ${@}"
        php /opt/webtown-workflow/symfony4/bin/console app:config ${@}
    ;;
    --config-dump)
        shift
        #eval "$BASE_PROJECT_RUN cli php /opt/webtown-workflow/symfony4/bin/console app:config-dump ${@}"
        php /opt/webtown-workflow/symfony4/bin/console app:config-dump ${@}
    ;;
    --debug)
        shift
        eval "$BASE_PROJECT_RUN cli ${@}"
    ;;
    # RUN wizard
    *)
        #eval "$BASE_PROJECT_RUN cli php /opt/webtown-workflow/symfony4/bin/console app:wizard ${@}"
        php /opt/webtown-workflow/symfony4/bin/console app:wizard ${@}
    ;;
esac
