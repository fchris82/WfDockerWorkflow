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

# Build command
function build {
    docker build --no-cache --pull -t ${USER}/wf-user ~/.webtown-workflow
}

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

BASE_IMAGE=${1:-"fchris82/wf"}
# Refresh
if [ -f ~/.webtown-workflow/Dockerfile ]; then
    build
    IMAGE=${USER}/wf-user
else
    docker pull ${BASE_IMAGE}
    IMAGE=${BASE_IMAGE}
fi

# If we want to use the local and fresh files
if [ -f ${DIR}/webtown-workflow-package/opt/webtown-workflow/host/copy_binaries_to_host.sh ]; then
    ${DIR}/webtown-workflow-package/opt/webtown-workflow/host/copy_binaries_to_host.sh
# If the docker is available
elif [ -S /var/run/docker.sock ]; then
    # Copy files from image to host. YOU CAN'T USE docker cp COMMAND, because it doesn't work with image name, it works with containers!
    docker run -i \
     -v ~/:${HOME} \
     -e LOCAL_USER_ID=$(id -u) -e LOCAL_USER_NAME=${USER} -e LOCAL_USER_HOME=${HOME} -e USER_GROUP=$(stat -c '%g' /var/run/docker.sock) \
     -e BASE_IMAGE=${BASE_IMAGE} \
     ${IMAGE} \
     /opt/webtown-workflow/host/copy_binaries_to_host.sh
fi

# Add commands to path!
COMMAND_PATH=~/.webtown-workflow/bin/commands
mkdir -p ~/bin
ln -sf $COMMAND_PATH/* ~/bin

# Build if we haven't done it yet.
if [ "${BASE_IMAGE}" == "${IMAGE}" ]; then
    build
fi

# Install BASH init script
# @todo On mac: dtruss instead of strace
BASH_FILE_TRACES=$(echo exit | strace bash -li |& less | grep "^open.*\"$HOME" | cut -d'"' -f2);
OLDIFS=$IFS
IFS=$'\n'
for file in $BASH_FILE_TRACES; do
    if [ -f "$file" ]; then
        BASH_PROFILE_FILE="$file"
        break
    fi
done
IFS=$OLDIFS
if [ -f "$BASH_PROFILE_FILE" ] && [ "$(basename "$BASH_PROFILE_FILE")" != ".bash_history" ] \
    && [ $(cat $BASH_PROFILE_FILE | egrep "^[^#]*source[^#]/.webtown-workflow/bin/bash/bash.extension.sh" | wc -l) == 0 ]; then
        echo -e "\n# WF extension\nsource ~/.webtown-workflow/bin/bash/bash.extension.sh\n" >> $BASH_PROFILE_FILE
        # Reload the shell if it needs
        [[ "$(basename "$SHELL")" == "bash" ]] && source $BASH_PROFILE_FILE
        echo -e "${GREEN}We register the the BASH autoload extension in the ${YELLOW}${BASH_PROFILE_FILE}${GREEN} file!${RESTORE}"
fi

# Install ZSH init script and autocomplete
if [ -f ~/.zshrc ]; then
    mkdir -p ~/.zsh/completion
    ln -sf ~/.webtown-workflow/bin/zsh/zsh_autocomplete.sh ~/.zsh/completion/_wf
    if [ $(echo $fpath | egrep ~/.zsh/completion | wc -l) == 0 ] \
        && [ $(cat ~/.zshrc | egrep "^[^#]*source[^#]/.webtown-workflow/bin/zsh/zsh.extension.sh" | wc -l) == 0 ]; then
            echo -e "\n# WF extension\nsource ~/.webtown-workflow/bin/zsh/zsh.extension.sh\n" >> ~/.zshrc
            # Reload the shell if it needs
            [[ "$(basename "$SHELL")" == "zsh" ]] && source ~/.zshrc
            echo -e "${GREEN}We register the the ZSH autoload extension in the ${YELLOW}~/.zshrc${GREEN} file!${RESTORE}"
    fi
else
    echo -e "You don't have installed the zsh! Nothing changed."
fi

GLOBAL_IGNORE=(/.wf /.wf.yml /.docker.env)
# Install gitignore
git --version 2>&1 >/dev/null # improvement by tripleee
GIT_IS_AVAILABLE=$?
if [ $GIT_IS_AVAILABLE -eq 0 ]; then
    GITIGNORE_FILE=$(bash -c "echo $(git config --get core.excludesfile)")
    # if it doesn't exist, create global gitignore file
    if [ -z $GITIGNORE_FILE ]; then
        git config --global core.excludesfile '~/.gitignore'
        GITIGNORE_FILE=$(bash -c "echo $(git config --get core.excludesfile)")
    fi
    if [ ! -z $GITIGNORE_FILE ] && [ -f $GITIGNORE_FILE ]; then
        for ignore in "${GLOBAL_IGNORE[@]}"
        do
            if ! grep -q ^${ignore}$ $GITIGNORE_FILE; then
                echo $ignore >> $GITIGNORE_FILE
                echo -e "${GREEN}We added the ${YELLOW}${ignore}${GREEN} path to ${YELLOW}${GITIGNORE_FILE}${GREEN} file${RESTORE}"
            fi
        done
    else
        echo -e "${YELLOW}You don't have global ${GREEN}.gitignore${YELLOW} file! Nothing changed.${RESTORE}"
    fi
else
    echo -e "INFO: You don't have installed the git."
fi

# Clean / Old version upgrade
[[ -f ~/.webtown-workflow/config/config ]] && rm -f ~/.webtown-workflow/config/config*
[[ -d ~/.webtown-workflow/recipes ]] \
    && rsync --remove-source-files -a -v ~/.webtown-workflow/recipes/* ~/.webtown-workflow/extensions/recipes \
    && rm -rf ~/.webtown-workflow/recipes
[[ -d ~/.webtown-workflow/wizards ]] \
    && rsync --remove-source-files -a -v ~/.webtown-workflow/wizards/* ~/.webtown-workflow/extensions/wizards \
    && rm -rf ~/.webtown-workflow/wizards
[[ -f ~/.webtown-workflow/bin/zsh_autocomplete.sh ]] && rm -f ~/.webtown-workflow/bin/zsh_autocomplete.sh
# todo Remove autocomplete from ~/.zshrc file

echo -e "${GREEN}Install success${RESTORE}"
