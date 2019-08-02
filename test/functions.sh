#!/bin/bash

SOURCE="${BASH_SOURCE[0]}"
while [ -h "$SOURCE" ]; do # resolve $SOURCE until the file is no longer a symlink
  DIR="$( cd -P "$( dirname "$SOURCE" )" && pwd )"
  SOURCE="$(readlink "$SOURCE")"
  [[ $SOURCE != /* ]] && SOURCE="$DIR/$SOURCE" # if $SOURCE was a relative symlink, we need to resolve it relative to the path where the symlink file was located
done
DIR="$( cd -P "$( dirname "$SOURCE" )" && pwd )"

source ${DIR}/../docker-workflow-package/opt/wf-docker-workflow/lib/_functions.sh

# Teszting escape
TESTS=('teszt1' 'teszt2="ertek"' '--env="prod" --password="ez egy szóközös jelszó"')
RESULTS=('teszt1' 'teszt2=\"ertek\"' '--env=\"prod\" --password=\"ez egy szóközös jelszó\"')

for i in "${!TESTS[@]}"
do
    ESCAPED=$(escape ${TESTS[$i]})
    if [ "$ESCAPED" != "${RESULTS[$i]}" ]; then
        echo "$ESCAPED != ${RESULTS[$i]}"
        exit 1
    fi
done
