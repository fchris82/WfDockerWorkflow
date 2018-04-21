#!/bin/bash

if [ ${DEBUG:-0} -ge 1 ]; then
    [[ -f /.dockerenv ]] && echo -e "\033[1mDocker: \033[33m${WF_DOCKER_HOST_CHAIN}\033[0m"
    echo -e "\033[1mDEBUG\033[33m $(realpath "$0")\033[0m"
    SYMFONY_COMMAND_DEBUG="-vvv"
    DOCKER_DEBUG="-e DEBUG=${DEBUG}"
fi
[[ ${DEBUG:-0} -ge 2 ]] && set -x

SOURCE="${BASH_SOURCE[0]}"
while [ -h "$SOURCE" ]; do # resolve $SOURCE until the file is no longer a symlink
  DIR="$( cd -P "$( dirname "$SOURCE" )" && pwd )"
  SOURCE="$(readlink "$SOURCE")"
  [[ $SOURCE != /* ]] && SOURCE="$DIR/$SOURCE" # if $SOURCE was a relative symlink, we need to resolve it relative to the path where the symlink file was located
done
DIR="$( cd -P "$( dirname "$SOURCE" )" && pwd )"

# Colors
RED=$'\x1B[00;31m'
GREEN=$'\x1B[00;32m'
YELLOW=$'\x1B[00;33m'
WHITE=$'\x1B[01;37m'
BOLD=$'\x1B[1m'
# Clear to end of line: http://www.isthe.com/chongo/tech/comp/ansi_escapes.html
CLREOL=$'\x1B[K'
#-- Vars
RESTORE=$'\x1B[0m'

# Refresh
if [ "${1}" != "--no-pull" ]; then
    docker pull fchris82/wf
else
    shift
fi

# If we want to use the local and fresh files
if [ -f ${DIR}/webtown-workflow-package/opt/webtown-workflow/host/copy_binaries_to_host.sh ]; then
    ${DIR}/webtown-workflow-package/opt/webtown-workflow/host/copy_binaries_to_host.sh
# If the docker is available
elif [ -S /var/run/docker.sock ]; then
    # Copy files from image to host. YOU CAN'T USE docker cp COMMAND, because it doesn't work with image name, it works with containers!
    docker run -i \
     -v ~/:${HOME} \
     -e LOCAL_USER_ID=$(id -u) -e LOCAL_USER_NAME=${USER} -e LOCAL_USER_HOME=${HOME} -e USER_GROUP=$(getent group docker | cut -d: -f3) \
     fchris82/wf \
     /opt/webtown-workflow/host/copy_binaries_to_host.sh
fi

# Add commands to path!
COMMAND_PATH=~/.webtown-workflow/bin/commands
mkdir -p ~/bin
ln -sf $COMMAND_PATH/* ~/bin

# Install autocomplete
if [ -d ~/.zsh ]; then
    mkdir -p ~/.zsh/completion
    ln -sf ~/.webtown-workflow/bin/zsh_autocomplete.sh ~/.zsh/completion/_wf
    if [ $(cat ~/.zshrc| egrep "^[^#]*fpath[^#]*/.zsh/completion" | wc -l) == 0 ]; then
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

# Install gitignore
GITIGNORE_FILE=$(bash -c "echo $(git config --get core.excludesfile)")
if [ ! -z $GITIGNORE_FILE ] && [ -f $GITIGNORE_FILE ]; then
    GITIGNORE=(/.wf /.wf.yml /.docker.env)
    for ignore in "${GITIGNORE[@]}"
    do
        if ! grep -q ^${ignore}$ $GITIGNORE_FILE; then
            echo $ignore >> $GITIGNORE_FILE
            echo ${GREEN}Add the ${YELLOW}${ignore}${GREEN} path to ${YELLOW}${GITIGNORE_FILE}${GREEN} file${RESTORE}
        fi
    done
else
    echo -e "${YELLOW}You don't have installed the git or you don't have global ${GREEN}.gitignore${YELLOW} file! Nothing changed.${RESTORE}"
fi

echo -e "${GREEN}Install success${RESTORE}"
