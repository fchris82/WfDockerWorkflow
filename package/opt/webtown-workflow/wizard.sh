#!/bin/bash
# Debug mode:
# set -x

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

BASE_RUN="docker-compose \
            -f ${DIR}/symfony4/docker-compose.yml \
            run --rm \
            -e LOCAL_USER_ID=$(id -u) -e USER_GROUP=$(getent group docker | cut -d: -f3)"
BASE_PROJECT_RUN="docker-compose \
            -f ${DIR}/symfony4/docker-compose.yml \
            -f ${DIR}/symfony4/docker-compose.project.yml \
            run --rm \
            -e LOCAL_USER_ID=$(id -u) -e USER_GROUP=$(getent group docker | cut -d: -f3)"

case $1 in
    -h|--help)
        showHelp
    ;;
    # For debugging
    -e|--enter)
        $BASE_RUN cli /bin/bash
    ;;
    -i|--install)
        shift
        $BASE_RUN -w /usr/src/script/symfony4 \
            -e SYMFONY_ENV=${SYMFONY_ENV:-prod} \
            cli composer install ${@}
    ;;
    # REBUILD the docker container
    -r|--rebuild)
        rm -rf ${DIR}/symfony4/var/cache/*
        docker-compose -f ${DIR}/symfony4/docker-compose.yml build --no-cache
    ;;
    -t|--test)
        $BASE_RUN cli php /usr/src/script/symfony4/vendor/bin/phpunit -c /usr/src/script/symfony4
        $BASE_RUN cli php /usr/src/script/symfony4/vendor/bin/php-cs-fixer fix --config=/usr/src/script/symfony4/.php_cs.dist
    ;;
    # Rebuild config from the yml. See: workflow.sh .
    # @todo (Chris) Ennek az egésznek tulajdonképpen inkább a workflow-ban van a helye, nem itt, csak itt volt már SF ezért ide építettem be.
    # @todo (Chris) Ez így nem jó, mert hívható közvetlenül, de nem dob hibát, ha nincs elég információja!
    --reconfigure)
        shift
        $BASE_PROJECT_RUN cli php /usr/src/script/symfony4/bin/console app:config -e ${SYMFONY_ENV:-prod} ${@}
    ;;
    --config-dump)
        shift
        $BASE_PROJECT_RUN cli php /usr/src/script/symfony4/bin/console app:config-dump -e ${SYMFONY_ENV:-prod} ${@}
    ;;
    # RUN wizard
    *)
        $BASE_PROJECT_RUN cli php /usr/src/script/symfony4/bin/console app:wizard -e ${SYMFONY_ENV:-prod} ${@}
    ;;
esac
