#!/bin/bash

if [ ${WF_DEBUG:-0} -ge 1 ]; then
    [[ -f /.dockerenv ]] && echo -e "\033[1mDocker: \033[33m${WF_DOCKER_HOST_CHAIN}\033[0m"
    echo -e "\033[1mDEBUG\033[33m $(realpath "$0")\033[0m"
    SYMFONY_COMMAND_DEBUG="-vvv"
    DOCKER_DEBUG="-e WF_DEBUG=${WF_DEBUG}"
fi
[[ ${WF_DEBUG:-0} -ge 2 ]] && set -x

SOURCE="${BASH_SOURCE[0]}"
while [ -h "$SOURCE" ]; do # resolve $SOURCE until the file is no longer a symlink
  DIR="$( cd -P "$( dirname "$SOURCE" )" && pwd )"
  SOURCE="$(readlink "$SOURCE")"
  [[ $SOURCE != /* ]] && SOURCE="$DIR/$SOURCE" # if $SOURCE was a relative symlink, we need to resolve it relative to the path where the symlink file was located
done
DIR="$( cd -P "$( dirname "$SOURCE" )" && pwd )"

# CREATE DIRECTORIES
echo "Install binaries..."
mkdir -p ${HOME}/.webtown-workflow/bin
mkdir -p ${HOME}/.webtown-workflow/config

# COPY files, except config
find ${DIR} -mindepth 1 -maxdepth 1 -type d ! -name config -exec cp -f -R {} ${HOME}/.webtown-workflow/ \;
# COPY config directory
#   1. Create dist files
for f in ${DIR}/config/*; do cp -f "$f" "${HOME}/.webtown-workflow/config/$(basename $f).dist"; done
#   2. Copy if doesn't exist
cp -an ${DIR}/config/. ${HOME}/.webtown-workflow/config/

# Symfony cache directory
mkdir -p ${HOME}/.webtown-workflow/cache
rm -rf ${HOME}/.webtown-workflow/cache/*
chmod 777 ${HOME}/.webtown-workflow/cache
