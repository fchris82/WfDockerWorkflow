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

# CONFIG
# @todo Ez majd a /etc/webtown-workflow könyvtárra mutasson!
#CONFIG_PATH="/etc/webtown-kunstmaan-installer"
CONFIG_PATH="${DIR}"
CONFIG="$CONFIG_PATH/config"
SYMFONY_SKELETON_PATH="$CONFIG_PATH/skeletons"

# defaults
CLONE_REPOSITORY=$(awk '/^repository/{print $3}' "${CONFIG}")
# update parameters
PROGRAM_REPOSITORY=$(awk '/^program_repository/{print $3}' "${CONFIG}")

source ${DIR}/_css.sh
source ${DIR}/_help.sh
source ${DIR}/_functions.sh

case $1 in
    ""|-h|--help)
        showHelp
    ;;
    # UPDATE the software
    -u|--update)
        cd /tmp && git archive --remote=${PROGRAM_REPOSITORY} HEAD webtown-workflow-installer.deb | tar -x || quit
        dpkg -i webtown-kunstmaan-installer.deb || quit
        cleanup
    ;;
# @todo Egyelőre nem itt van megvalósítva. Jobb lenne, ha itt lenne, csak utána kellene nézni, hogy felül lehet-e írni egy makefile-ban a tartgetet azzal, hogy include-dal behúzunk alá egy másik makefile-t, máshonnan.
#    # Local makefile
#    feature|hotfix|publish|push)
#        COMMAND="$1"
#        shift
#        # @todo Ez még nem jó, meg kell oldani az escape-et!
#        ARGS=$(escape "$@")
#        make -f ${DIR}/makefile ${COMMAND} ARGS="${ARGS}" || quit
#    ;;
    # Project makefile
    *)
        COMMAND="$1"
        shift
        # @todo Ez még nem jó, meg kell oldani az escape-et!
        ARGS=$(escape "$@")
        make -f ${WORKDIR}/makefile ${COMMAND} ARGS="${ARGS}" || quit
    ;;
esac
