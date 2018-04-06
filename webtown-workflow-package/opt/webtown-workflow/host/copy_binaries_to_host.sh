#!/usr/bin/env bash

# CREATE DIRECTORIES
mkdir -p ${HOME}/.webtown-workflow/bin
mkdir -p ${HOME}/.webtown-workflow/config

# COPY binary file
cp -f -R /opt/webtown-workflow/host/bin/* ${HOME}/.webtown-workflow/bin/

# COPY config file
cp --backup /etc/webtown-workflow/* ${HOME}/.webtown-workflow/config/
