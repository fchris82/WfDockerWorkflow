#!/bin/bash

LOCKFILE="/var/lock/`basename $0`"

function escape {
  echo "${@//\"/\\\"}"
}

function lock {
    if [ -f $LOCKFILE ]; then
        echo "$(tput setaf 1)$(tput setab 7) "
        echo "$(tput setaf 1)$(tput setab 7)SCRIPT IS LOCKED!"
        echo "$(tput setaf 1)$(tput setab 7)The script is running now by $(tput setab 2)$(tput setaf 0)$(stat -c \"%U\" $LOCKFILE)$(tput setab 7)$(tput setaf 1)!"
        echo "$(tput setaf 1)$(tput setab 7)If you are sure that the lock file is 'wrong' - don't running a script - delete it by hand: $(tput setab 3)rm -f ${LOCKFILE}$(tput setab 7)"
        echo "$(tput setaf 1)$(tput setab 7) "
        echo "$(tput sgr0) "
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
    cleanup
    echo_block "31;107m" " Something went wrong! The script doesn't run down!"
    showHelp
    exit $?
}

# Handle CTRL + C
trap quit SIGINT
