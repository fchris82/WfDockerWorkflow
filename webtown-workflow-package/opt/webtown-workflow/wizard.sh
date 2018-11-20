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

case $1 in
    -h|--help)
        showHelp
        echo "${WHITE}SYMFONY COMMAND HELP${RESTORE}"
        echo ""
        php /opt/webtown-workflow/symfony4/bin/console app:wizard \
            --wf-version $(dpkg-query --showformat='${Version}' --show webtown-workflow) \
            ${@} ${SYMFONY_DISABLE_TTY} ${SYMFONY_COMMAND_DEBUG}
    ;;
    --config)
        shift
        php /opt/webtown-workflow/symfony4/bin/console app:wizard:config \
            ${@} ${SYMFONY_DISABLE_TTY} ${SYMFONY_COMMAND_DEBUG}
    ;;
    # RUN wizard
    *)
        #eval "$BASE_PROJECT_RUN cli php /opt/webtown-workflow/symfony4/bin/console app:wizard ${@}"
        php /opt/webtown-workflow/symfony4/bin/console app:wizard \
            --wf-version $(dpkg-query --showformat='${Version}' --show webtown-workflow) \
            ${@} ${SYMFONY_DISABLE_TTY} ${SYMFONY_COMMAND_DEBUG}
    ;;
esac
