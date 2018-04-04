#!/bin/bash

# Refresh
if [ "${1}" != "--no-pull" ]; then
    docker pull fchris82/wf
fi
# Copy files from image to host. YOU CAN'T USE docker cp COMMAND, because it doesn't work with image name, it works with containers!
docker run -it \
 -v ~/:/home/user \
 -e LOCAL_USER_ID=$(id -u) -e USER_GROUP=$(getent group docker | cut -d: -f3) \
 fchris82/wf \
 /opt/webtown-workflow/host/copy_binaries_to_host.sh

ALIAS_WF="alias wf='~/.webtown-workflow/bin/wf_runner.sh wf'"
ALIAS_WIZARD="alias wizard='~/.webtown-workflow/bin/wf_runner.sh wizard'"

# Register aliases
RC_FILE=~/.$(basename ${SHELL:-bash})rc
if [ ! -f ${RC_FILE} ]; then
    RC_FILE=~/.bashrc
    if [ ! -f ${RC_FILE} ]; then
        echo "We don't find your rc file! You have to register your aliases by hand!"
        echo ""
        echo "${ALIAS_WF}"
        echo "${ALIAS_WIZARD}"
    fi
fi
if [ -f ${RC_FILE} ]; then
    if [[ -z $(grep -w ${RC_FILE} -e 'alias wf=') ]]; then
        echo "${ALIAS_WF}" >> ${RC_FILE}
    fi
    if [[ -z $(grep -w ${RC_FILE} -e 'alias wizard=') ]]; then
        echo "${ALIAS_WIZARD}" >> ${RC_FILE}
    fi
    echo "We registered the wf and wizard aliases in ${RC_FILE}"
fi

# Install autocomplete
if [ -d ~/.zsh ]; then
    mkdir -p ~/.zsh/completion
    ln -sf ~/.webtown-workflow/bin/zsh_autocomplete.sh ~/.zsh/completion/_wf
    if [ $(echo "$fpath" | grep /.zsh/completion | wc -l) == 0 ]; then
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
