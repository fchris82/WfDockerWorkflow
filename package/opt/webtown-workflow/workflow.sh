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
CONFIG_PATH="${DIR}/../../etc/webtown-workflow"
CONFIG="$CONFIG_PATH/config"
SYMFONY_SKELETON_PATH="$CONFIG_PATH/skeletons"

# defaults
CLONE_REPOSITORY=$(awk '/^repository/{print $3}' "${CONFIG}")
# update parameters
PROGRAM_REPOSITORY=$(awk '/^program_repository/{print $3}' "${CONFIG}")

source ${DIR}/lib/_css.sh
source ${DIR}/lib/_workflow_help.sh
source ${DIR}/lib/_functions.sh

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
            CURRENT_VERSION=$(git archive --remote=${PROGRAM_REPOSITORY} HEAD package/DEBIAN/control | tar -xO | grep ^Version: | cut -d\  -f2)
            if [ "${CURRENT_VERSION}" != "${LAST_VERSION}" ]; then
                echo -e "There is a newer version from \033[1;34mwebtown-workflow\033[0m! \033[33m${CURRENT_VERSION}\033[0m vs \033[32m${LAST_VERSION}\033[0m Run the \033[1mwf -u\033[0m command for upgrade."
            fi
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
    --init-reverse-proxy)
        NETWORK_EXISTS=$(docker network ls | grep 'reverse-proxy')
        REVERSE_PROXY_PORT=$(awk '/^reverse_proxy_port/{print $3}' "${CONFIG}")
        if [[ -z "$NETWORK_EXISTS" ]]; then
            docker network create --driver bridge reverse-proxy
        fi
        docker stop nginx-reverse-proxy
        docker rm nginx-reverse-proxy
        docker run -d -p ${REVERSE_PROXY_PORT}:${REVERSE_PROXY_PORT} \
            --name nginx-reverse-proxy \
            --net reverse-proxy \
            -v /var/run/docker.sock:/tmp/docker.sock:ro \
            -v /etc/webtown-workflow/nginx.tmpl:/app/nginx.tmpl:ro \
            -v /etc/webtown-workflow/nginx-proxy-503.tmpl:/app/nginx-proxy-503.tmpl:ro \
            -v /etc/webtown-workflow/docker-gen.cfg:/app/docker-gen.cfg:ro \
            -v /etc/webtown-workflow/Procfile:/app/Procfile:ro \
            -e LISTENED_PORT=${REVERSE_PROXY_PORT} \
            --restart always \
            jwilder/nginx-proxy
    ;;
    -erp|--enter-reverse-proxy)
        docker exec -i -t nginx-reverse-proxy /bin/bash
    ;;
    -scrp|--show-config-reverse-proxy)
        docker exec -i -t nginx-reverse-proxy cat /etc/nginx/conf.d/default.conf
    ;;
    -ps|--docker-ps)
        docker inspect -f "{{printf \"%-30s\" .Name}} {{printf \"%.12s\t\" .Id}}{{index .Config.Labels \"com.wf.basedirectory\"}}" $(docker ps -a -q)
    ;;
    --reconfigure)
        PROJECT_ROOT_DIR=$(git rev-parse --show-toplevel || echo ".")
        WF_WORKING_DIRECTORY=$(awk '/^working_directory/{print $3}' "${CONFIG}")
        WF_CONFIGURATION_FILE=$(awk '/^configuration_file/{print $3}' "${CONFIG}")
        PROJECT_CONFIG_FILE="${PROJECT_ROOT_DIR}/${WF_CONFIGURATION_FILE}"
        if [ -f "${PROJECT_CONFIG_FILE}" ]; then
            CONFIG_HASH=$(crc32 ${PROJECT_CONFIG_FILE})
            ${DIR}/../webtown-project-wizard/wizard.sh --reconfigure \
                --file ${WF_CONFIGURATION_FILE} \
                --target-directory ${WF_WORKING_DIRECTORY} \
                --config-hash ${CONFIG_HASH}
        else
            echo "The ${PROJECT_CONFIG_FILE} doesn't exist."
        fi
    ;;
    # Project makefile
    *)
        COMMAND="$1"
        shift

        PROJECT_ROOT_DIR=$(git rev-parse --show-toplevel || echo ".")
        WF_WORKING_DIRECTORY=$(awk '/^working_directory/{print $3}' "${CONFIG}")
        WF_CONFIGURATION_FILE=$(awk '/^configuration_file/{print $3}' "${CONFIG}")
        # Deploy esetén nem biztos, hogy van .git könyvtár, ellenben ettől még a projekt fájl létezhet
        if [ "${PROJECT_ROOT_DIR}" == "." ] && [ ! -f "${PROJECT_MAKEFILE}" ]; then
            echo_fail "You are not in project directory! Git top level is missing!"
            quit
        fi
        PROJECT_CONFIG_FILE="${PROJECT_ROOT_DIR}/${WF_CONFIGURATION_FILE}"
        if [ -f "${PROJECT_CONFIG_FILE}" ]; then
            CONFIG_HASH=$(crc32 ${PROJECT_CONFIG_FILE})
            PROJECT_MAKEFILE="${PROJECT_ROOT_DIR}/${WF_WORKING_DIRECTORY}/${CONFIG_HASH}.mk"
            if [ ! -f "${PROJECT_MAKEFILE}" ]; then
                ${DIR}/../webtown-project-wizard/wizard.sh --reconfigure --file ${WF_CONFIGURATION_FILE} --target-directory ${WF_WORKING_DIRECTORY} --config-hash ${CONFIG_HASH}
            fi
        else
            # If we are using "hidden" docker environment...
            DOCKER_ENVIRONEMNT_MAKEFIILE="${PROJECT_ROOT_DIR}/.docker.env.makefile"
            # If we are using old version
            OLD_PROJECT_MAKEFILE="${PROJECT_ROOT_DIR}/.project.makefile"
            if [ -f "${DOCKER_ENVIRONEMNT_MAKEFIILE}" ]; then
                PROJECT_MAKEFILE="${DOCKER_ENVIRONEMNT_MAKEFIILE}";
            elif [ -f "${OLD_PROJECT_MAKEFILE}" ]; then
                PROJECT_MAKEFILE="${OLD_PROJECT_MAKEFILE}";
            else
                echo_fail "The project makefile doesn't exist in this path: ${PROJECT_MAKEFILE}"
                quit
            fi
        fi

        ARGS=$(escape "$@")

        make $(make_params) -f ${PROJECT_MAKEFILE} -C ${PROJECT_ROOT_DIR} ${COMMAND} \
            ARGS="${ARGS}" \
            WORKFLOW_BINARY_DIRECTORY="${DIR}/bin" \
            WORKFLOW_MAKEFILE_PATH="${DIR}/versions/Makefile" || quit
    ;;
esac
