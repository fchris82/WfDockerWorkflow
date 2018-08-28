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

# CONFIG
CONFIG_PATH="${DIR}/../../etc/webtown-workflow"
CONFIG="$CONFIG_PATH/config"
SYMFONY_SKELETON_PATH="$CONFIG_PATH/skeletons"

source ${DIR}/../webtown-workflow/lib/_debug.sh
source ${DIR}/lib/_css.sh
source ${DIR}/lib/_workflow_help.sh
source ${DIR}/lib/_functions.sh

# WF options
# ----------
# First parameters are used by WF!
#
#   wf [OPTIONS] [COMMAND] [COMMAND_OPTIONS]
#
# Example, switch on debug modes:
#   wf -v sf docker:create:database -vvv
#      ^^                           ^^^^
#      Makefile debug mode          Symfony debug mode
#
# Example, add environment:
#   wf -e EXTENSION_DIRS=/var/extensions info
while [[ $# -gt 0 ]]
do
key="$1"

case $key in
    # Add or replace parameter from command line
    -e|--env)
        COMMAND_ENVS="${COMMAND_ENVS:-""} $2"
        shift
        shift
    ;;
    # Debug modes
    -v)
        MAKE_DISABLE_SILENC=1
        shift
    ;;
    -vvv)
        MAKE_DISABLE_SILENC=1
        MAKE_DEBUG_MODE=1
        shift
    ;;
    *)
        break
    ;;
esac
done

# WF command
# ----------
case $1 in
    ""|-h|--help)
        showHelp
    ;;
    # UPDATE the software
    --check-update)
        LAST_UPDATED_FOR=$((`date +%s` - `cat /etc/webtown-workflow/lastupdate`))
        # 10 óránként kérdezzük le
        if [ "$LAST_UPDATED_FOR" -gt "36000" ]; then
            date +%s > /etc/webtown-workflow/lastupdate
            echo "Check new Workflow version..."
            LAST_VERSION=$(dpkg-query --showformat='${Version}' --show webtown-workflow)
            CURRENT_VERSION=$(git archive --remote=${WF_PROGRAM_REPOSITORY} HEAD package/DEBIAN/control | tar -xO | grep ^Version: | cut -d\  -f2)
            if [ "${CURRENT_VERSION}" != "${LAST_VERSION}" ]; then
                echo -e "There is a newer version from \033[1;34mwebtown-workflow\033[0m! \033[33m${CURRENT_VERSION}\033[0m vs \033[32m${LAST_VERSION}\033[0m Run the \033[1mwf -u\033[0m command for upgrade."
            fi
        fi
    ;;
    --version)
        dpkg -l | grep webtown-workflow | awk '{ print "Webtown Workflow " $3 }'
    ;;
    -ps|--docker-ps)
        docker inspect -f "{{printf \"%-30s\" .Name}} {{printf \"%.12s\t\" .Id}}{{index .Config.Labels \"com.wf.basedirectory\"}}" $(docker ps -a -q)
    ;;
    # Clean cache directory. You have to use after put a custom recipe!
    --reload|--clean-cache)
        rm -rf ${DIR}/symfony4/var/cache/*
    ;;
    --config-dump)
        shift
        #eval "$BASE_PROJECT_RUN cli php /opt/webtown-workflow/symfony4/bin/console app:config-dump ${@}"
        php /opt/webtown-workflow/symfony4/bin/console app:config-dump ${@} ${DISABLE_TTY} ${SYMFONY_COMMAND_DEBUG}
    ;;
    # You can call with symfony command verbose, like: wf reconfigure -v
    reconfigure)
        shift
        PROJECT_ROOT_DIR=$(get_project_root_dir)
        PROJECT_CONFIG_FILE=$(get_project_configuration_file "${PROJECT_ROOT_DIR}/${WF_CONFIGURATION_FILE_NAME}")

        if [ -f "${PROJECT_CONFIG_FILE}" ]; then
            FORCE_OVERRIDE=1
            create_makefile_from_config ${@}
        else
            echo "The ${PROJECT_ROOT_DIR}/${WF_CONFIGURATION_FILE_NAME} doesn't exist."
        fi
    ;;
    # Project makefile
    *)
        COMMAND="$1"
        shift

        PROJECT_ROOT_DIR=$(get_project_root_dir)
        find_project_makefile || quit

        ARGS=$(escape $@)
        MAKE_EXTRA_PARAMS=$(make_params)

        make ${MAKE_EXTRA_PARAMS} -f ${PROJECT_MAKEFILE} -C ${PROJECT_ROOT_DIR} ${COMMAND} \
            ARGS="${ARGS}" \
            WORKFLOW_BINARY_DIRECTORY="${DIR}/bin" \
            WORKFLOW_MAKEFILE_PATH="${DIR}/versions/Makefile" \
            MAKE_EXTRA_PARAMS="${MAKE_EXTRA_PARAMS}" \
            COMMAND_ENVS="${COMMAND_ENVS:-""}" \
            WF_DEBUG="${WF_DEBUG}" || quit
    ;;
esac
