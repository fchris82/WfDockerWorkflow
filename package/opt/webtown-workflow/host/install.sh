#!/bin/bash
# docker run -it -u $(id -u) -e USER_SHELL=${SHELL} -v /~:/home/user fchris82/wf install

ALIAS_WF="alias wf='~/.webtown-workflow/bin/wf_runner.sh wf'"
ALIAS_WIZARD="alias wizard='~/.webtown-workflow/bin/wf_runner.sh wizard'"

# CREATE DIRECTORIES
mkdir -p /home/user/.webtown-workflow/bin
mkdir -p /home/user/.webtown-workflow/config
# COPY binary file
cp -f /opt/webtown-workflow/host/wf_runner.sh /home/user/.webtown_workflow/bin/wf_runner.sh
# COPY all *.orig file overwrite exists
cp -f /etc/webtown-workflow/*.orig /home/user/.webtown-workflow/config/
# COPY all NOT *.orig and lastupdate file
find /etc/webtown-workflow/ -type f ! -name *.orig ! -name lastupdate -exec cp --backup -t /home/user/.webtown-workflow/config/ {} +

RC_FILE=/home/user/.${USER_SHELL:-bash}rc
if [ ! -f ${RC_FILE} ]; then
    RC_FILE=/home/user/.bashrc
    if [ ! -f ${RC_FILE} ]; then
        echo "We don't find your rc file! You have to register your aliases by hand!\n\n"
        echo "${ALIAS_WF}"
        echo "${ALIAS_WIZARD}"
        exit
    fi
fi

if [[ ! -z $(grep -w ${RC_FILE} -e 'alias wf=') ]]; then
    echo "${ALIAS_WF}" >> ${RC_FILE}
fi
if [[ ! -z $(grep -w ${RC_FILE} -e 'alias wizard=') ]]; then
    echo "${ALIAS_WF}" >> ${RC_FILE}
fi

# @todo ZSH AUTOCOMPLETE install