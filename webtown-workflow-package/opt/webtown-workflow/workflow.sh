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

# Switch on debug modes:
#   wf -v sf docker:create:database -vvv
#      ^^                           ^^^^
#      Makefile debug mode          Symfony debug mode
if [ "$1" == "-v" ]; then
    MAKE_DISABLE_SILENC=1
    shift
elif [ "$1" == "-vvv" ]; then
    MAKE_DISABLE_SILENC=1
    MAKE_DEBUG_MODE=1
    shift
fi

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
    -u|--update)
        PACKAGE_NAME=webtown-workflow.deb
        echo -e "\033[32mStarting upgrade from: \033[33m${WF_PROGRAM_REPOSITORY}\033[0m"
        echo -e "\033[32mPackage:               \033[33m${PACKAGE_NAME}\033[0m"
        cd /tmp && git archive --remote=${WF_PROGRAM_REPOSITORY} HEAD ${PACKAGE_NAME} | tar -x || quit
        sudo dpkg -i ${PACKAGE_NAME} || quit
        rm -rf ${PACKAGE_NAME}
    ;;
    -ps|--docker-ps)
        docker inspect -f "{{printf \"%-30s\" .Name}} {{printf \"%.12s\t\" .Id}}{{index .Config.Labels \"com.wf.basedirectory\"}}" $(docker ps -a -q)
    ;;
    # You can call with symfony command verbose, like: wf --reconfigure -v
    # @todo (Chris) Ezt inkább -- nélkül kellene, autocomplete-tel
    --reconfigure)
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

        ARGS=$(escape "$@")
        MAKE_EXTRA_PARAMS=$(make_params)

        make ${MAKE_EXTRA_PARAMS} -f ${PROJECT_MAKEFILE} -C ${PROJECT_ROOT_DIR} ${COMMAND} \
            ARGS="${ARGS}" \
            WORKFLOW_BINARY_DIRECTORY="${DIR}/bin" \
            WORKFLOW_MAKEFILE_PATH="${DIR}/versions/Makefile" \
            MAKE_EXTRA_PARAMS="${MAKE_EXTRA_PARAMS}" \
            DEBUG="${DEBUG}" || quit
    ;;
esac
