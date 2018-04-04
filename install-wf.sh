#!/bin/bash

#-- Vars
RESTORE=$'\x1B[0m'
# Clear to end of line: http://www.isthe.com/chongo/tech/comp/ansi_escapes.html
CLREOL=$'\x1B[K'
# Colors
RED=$'\x1B[00;31m'
GREEN=$'\x1B[00;32m'
YELLOW=$'\x1B[00;33m'
WHITE=$'\x1B[01;37m'
BOLD=$'\x1B[1m'

# Refresh
if [ "${1}" != "--no-pull" ]; then
    docker pull fchris82/wf
else
    shift
fi

# If the docker is available
if [ -S /var/run/docker.sock ]; then
    # Copy files from image to host. YOU CAN'T USE docker cp COMMAND, because it doesn't work with image name, it works with containers!
    docker run -it \
     -v ~/:/home/user \
     -e LOCAL_USER_ID=$(id -u) -e USER_GROUP=$(getent group docker | cut -d: -f3) \
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

echo -e "${GREEN}Install success${RESTORE}"
