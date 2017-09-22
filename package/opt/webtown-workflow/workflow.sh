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
CONFIG_PATH="/etc/webtown-workflow"
CONFIG="$CONFIG_PATH/config"
SYMFONY_SKELETON_PATH="$CONFIG_PATH/skeletons"

# defaults
CLONE_REPOSITORY=$(awk '/^repository/{print $3}' "${CONFIG}")
# update parameters
PROGRAM_REPOSITORY=$(awk '/^program_repository/{print $3}' "${CONFIG}")

source ${DIR}/lib/_css.sh
source ${DIR}/lib/_help.sh
source ${DIR}/lib/_functions.sh

case $1 in
    ""|-h|--help)
        showHelp
    ;;
    # UPDATE the software
    --check-update)
        LAST_VERSION=$(dpkg-query --showformat='${Version}' --show webtown-workflow)
        PROGRAM_REPOSITORY=$(cat /etc/webtown-workflow/repository.txt)
        CURRENT_VERSION=$(git archive --remote=${PROGRAM_REPOSITORY} HEAD package/DEBIAN/control | tar -xO | grep ^Version: | cut -d\  -f2)
        if [ "${CURRENT_VERSION}" != "${LAST_VERSION}" ]; then
            echo "There is a newer version from \033[1;34mwebtown-workflow\033[0m! \033[33m${CURRENT_VERSION}\033[0m vs \033[32m${LAST_VERSION}\033[0m Run the \033[1mwf -u\033[0m command for upgrade."
        fi
    ;;
    -u|--update)
        PACKAGE_NAME=webtown-workflow.deb
        echo -e "\033[32mStarting upgrade from: \033[33m${PROGRAM_REPOSITORY}\033[0m"
        echo -e "\033[32mPackage:               \033[33m${PACKAGE_NAME}\033[0m"
        cd /tmp && git archive --remote=${PROGRAM_REPOSITORY} HEAD ${PACKAGE_NAME} | tar -x || quit
        sudo dpkg -i ${PACKAGE_NAME} || quit
        rm -rf ${PACKAGE_NAME}
    ;;
    --install-autocomplete)
        if [ -d ~/.zsh ]; then
            mkdir -p ~/.zsh/completion
            ln -sf ${DIR}/var/zsh/autocomplete.sh ~/.zsh/completion/_wf
            if [ $(echo "$fpath" | grep ${HOME}/.zsh/completion | wc -l) == 0 ]; then
                echo -e "${YELLOW}You have to edit the ${GREEN}~/.zshrc${YELLOW} file and add this row:${RESTORE}"
                echo -e "fpath=(~/.zsh/completion \$fpath)"
            fi
            if [ $(cat ~/.zshrc| egrep "^[^#]*compinit" | wc -l) == 0 ]; then
                echo -e "${YELLOW}You have to edit the ${GREEN}~/.zshrc${YELLOW} file and add this row AFTER the fpath!${RESTORE}"
                echo -e "autoload -Uz compinit && compinit -i"
            fi
        else
            echo -e "You don't have installed the zsh! Nothing changed."
        fi
    ;;
    # Project makefile
    *)
        COMMAND="$1"
        shift

        PROJECT_ROOT_DIR=$(git rev-parse --show-toplevel || echo "0")
        if [ "${PROJECT_ROOT_DIR}" == "0" ]; then
            echo_fail "You are not in project directory! Git top level is missing!"
            quit
        fi

        PROJECT_MAKEFILE="${PROJECT_ROOT_DIR}/.project.makefile"
        if [ ! -f "${PROJECT_MAKEFILE}" ]; then
            echo_fail "The project makefile doesn't exist in this path: ${PROJECT_MAKEFILE}"
            quit
        fi

        ARGS=$(escape "$@")

        make $(make_params) -f ${PROJECT_MAKEFILE} -C ${PROJECT_ROOT_DIR} ${COMMAND} \
            ARGS="${ARGS}" \
            WORKFLOW_BINARY_DIRECTORY="${DIR}/bin" \
            WORKFLOW_MAKEFILE_PATH="${DIR}/versions/Makefile" || quit
    ;;
esac
