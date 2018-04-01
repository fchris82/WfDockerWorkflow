#!/bin/bash

LOCKFILE="/var/lock/`basename $0`"

function escape {
    C=();
    whitespace="[[:space:]]"
    for i in "$@"
    do
        if [[ $i =~ $whitespace ]]
        then
            i=\"$i\"
        fi
        C+=("$i")
    done
    echo ${C[*]}
#
#  echo "${C//\"/\\\"}"
}

function lock {
    if [ -f $LOCKFILE ]; then
        CLASS=$'\x1B[31;107m'
        echo -e "\n${CLASS}${CLREOL}"
        echo -e "${CLREOL}"
        echo -e "\e[1m SCRIPT IS LOCKED!${CLREOL}"
        echo -e " =================\e[0m${CLASS}${CLREOL}"
        echo -e "${CLREOL}"
        echo -e " The script is running now by \e[30;42m$(stat -c \"%U\" $LOCKFILE)${CLASS}!${CLREOL}"
        echo -e " If you are sure that the lock file is 'wrong' - don't running a script - delete it by hand: \e[43mrm -f ${LOCKFILE}${CLASS}${CLREOL}"
        echo -e "${CLREOL}${RESTORE}\e[0m\n"
        exit 1
    else
        touch ${LOCKFILE} || quit
    fi
}

function cleanup {
    if [ -d "/tmp/gulp-ruby-sass" ]; then
        rm -rf /tmp/gulp-ruby-sass/
    fi
    if [ -f /tmp/webtown-kunstmaan-installer.deb ]; then
        rm -f /tmp/webtown-kunstmaan-installer.deb
    fi
    if [ -f ${LOCKFILE} ]; then
        rm -f ${LOCKFILE}
    fi
    return $?
}

function quit {
    exitcode=$?
    cleanup
    echo_block "31;107m" " Something went wrong! The script doesn't run down!"
    echo "${YELLOW}If you need some help call the ${BOLD}${WHITE}wf -h${RESTORE}${YELLOW} command!${RESTORE}"
    exit $exitcode
}

# You can manage some make parameters with these env variables
# Eg: MAKE_DISABLE_SILENC=1 MAKE_DEBUG_MODE=1 MAKE_ONLY_PRINT=1 wf list
function make_params {
    PARAMS="";
    if [ -z "$MAKE_DISABLE_SILENC" ]; then
        PARAMS="${PARAMS} -s --no-print-directory"
    fi
    if [ ! -z "$MAKE_DEBUG_MODE" ]; then
        PARAMS="${PARAMS} -d"
    fi
    if [ ! -z "$MAKE_ONLY_PRINT" ]; then
        PARAMS="${PARAMS} -n"
    fi

    echo $PARAMS
}

# Try to find the project root directory
function get_project_root_dir {
    echo $(git rev-parse --show-toplevel || echo ".")
}

# Find the project makefile:
#  1. .wf.yml --> makefile
#  2. .wf.yml.dist --> makefile
#  3. .docker.env.makefile
#  4. .project.makefile
function find_project_makefile {
    PROJECT_CONFIG_FILE=$(get_project_configuration_file "${PROJECT_ROOT_DIR}/${WF_CONFIGURATION_FILE}")
    if [ "${PROJECT_CONFIG_FILE}" == "null" ]; then
        # If we are using "hidden" docker environment...
        DOCKER_ENVIRONEMNT_MAKEFIILE="${PROJECT_ROOT_DIR}/.docker.env.makefile"
        # If we are using old version
        OLD_PROJECT_MAKEFILE="${PROJECT_ROOT_DIR}/.project.makefile"
        if [ -f "${DOCKER_ENVIRONEMNT_MAKEFIILE}" ]; then
            PROJECT_MAKEFILE="${DOCKER_ENVIRONEMNT_MAKEFIILE}";
        elif [ -f "${OLD_PROJECT_MAKEFILE}" ]; then
            PROJECT_MAKEFILE="${OLD_PROJECT_MAKEFILE}";
        else
            echo_fail "We didn't find any project makefile in this path: ${PROJECT_ROOT_DIR}"
            quit
        fi
    else
        create_makefile_from_config
    fi

    # Deploy esetén nem biztos, hogy van .git könyvtár, ellenben ettől még a projekt fájl létezhet
    if [ "${PROJECT_ROOT_DIR}" == "." ] && [ ! -f "${PROJECT_MAKEFILE}" ]; then
        echo_fail "You are not in project directory! Git top level is missing!"
        quit
    fi
}

# If `.wf.yml` doesn't exist but the `.wf.yml.dist` does
function get_project_configuration_file {
    local _path_="null"
    if [ -f "${1}" ]; then
        _path_="${1}";
    elif [ -f "${1}.dist" ]; then
        _path_="${1}.dist";
    fi

    echo ${_path_};
}

function create_makefile_from_config {
    CONFIG_HASH=$(crc32 ${PROJECT_CONFIG_FILE})
    PROJECT_MAKEFILE="${PROJECT_ROOT_DIR}/${WF_WORKING_DIRECTORY}/${CONFIG_HASH}.mk"
    if [ ! -f "${PROJECT_MAKEFILE}" ] || [ "${FORCE_OVERRIDE}" == "1" ]; then
        ${DIR}/../webtown-workflow/wizard.sh --reconfigure \
            --file ${PROJECT_CONFIG_FILE} \
            --target-directory ${WF_WORKING_DIRECTORY} \
            --config-hash ${CONFIG_HASH} ${@} || quit
    fi
}

# Handle CTRL + C
trap quit SIGINT
