#!/bin/bash

usermod -u ${UID} ${CONTAINER_USER}
groupmod -g ${GID} ${CONTAINER_GROUP}

${CONTAINER_ENTRYPOINT} ${@}
