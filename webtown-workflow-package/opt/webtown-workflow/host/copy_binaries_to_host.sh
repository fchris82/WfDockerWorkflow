#!/bin/bash

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

# COPY binary file
cp -f -R ${DIR}/bin/* ${HOME}/.webtown-workflow/bin/
# COPY recipes directory
cp -f -R ${DIR}/recipes ${HOME}/.webtown-workflow/

# COPY config file
cp --backup ${DIR}/../../../etc/webtown-workflow/* ${HOME}/.webtown-workflow/config/

# Symfony cache directory
mkdir -p ${HOME}/.webtown-workflow/cache
chmod 777 ${HOME}/.webtown-workflow/cache
