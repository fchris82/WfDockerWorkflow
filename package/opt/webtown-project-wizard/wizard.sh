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
source ${DIR}/lib/_wizard_help.sh
source ${DIR}/../webtown-workflow/lib/_functions.sh

BASE_RUN="docker-compose -f ${DIR}/symfony/docker-compose.yml run --rm \
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
        $BASE_RUN -w /usr/src/script/symfony \
            -e SYMFONY_ENV=${SYMFONY_ENV:-dev} \
            cli composer install ${@}
    ;;
    # REBUILD the docker container
    -r|--rebuild)
        rm -rf ${DIR}/symfony/var/cache/*
        docker-compose -f ${DIR}/symfony/docker-compose.yml build --no-cache
    ;;
    -t|--test)
        $BASE_RUN cli php /usr/src/script/symfony/vendor/bin/phpunit -c /usr/src/script/symfony
        $BASE_RUN cli php /usr/src/script/symfony/vendor/bin/php-cs-fixer fix --config=/usr/src/script/symfony/.php_cs.dist
    ;;
    # RUN wizard
    *)
        $BASE_RUN cli php /usr/src/script/symfony/bin/console app:wizard -e ${SYMFONY_ENV:-prod} ${@}
    ;;
esac
