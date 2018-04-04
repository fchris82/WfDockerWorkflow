#!/usr/bin/env bash

# CREATE DIRECTORIES
mkdir -p /home/user/.webtown-workflow/bin
mkdir -p /home/user/.webtown-workflow/config

# COPY binary file
cp -f /opt/webtown-workflow/host/bin/* /home/user/.webtown-workflow/bin/

# COPY config file
cp --backup /etc/webtown-workflow/* /home/user/.webtown-workflow/config/
