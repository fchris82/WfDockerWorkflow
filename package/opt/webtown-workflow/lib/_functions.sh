#!/bin/bash

LOCKFILE="/var/lock/`basename $0`"

function escape {
  echo "${@//\"/\\\"}"
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

# Handle CTRL + C
trap quit SIGINT
