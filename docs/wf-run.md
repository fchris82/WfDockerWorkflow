<!-- TODO -->
```
docker run -it -u ${LOCAL_USER_ID}:${USER_GROUP} -w /home/chris/www/ez -v ${COMPOSER_HOME}:${COMPOSER_HOME} -v /home/chris/www/ez:/home/chris/www/ez -e LOCAL_USER_ID=${LOCAL_USER_ID} -e LOCAL_USER_NAME=${LOCAL_USER_NAME} -e LOCAL_USER_HOME=${LOCAL_USER_HOME} -e WF_HOST_TIMEZONE=${WF_HOST_TIMEZONE} -e WF_HOST_LOCALE=${WF_HOST_LOCALE} -e WF_DOCKER_HOST_CHAIN="${WF_DOCKER_HOST_CHAIN}$(hostname) " -e COMPOSER_HOME=${COMPOSER_HOME} -e COMPOSER_MEMORY_LIMIT=-1 -e USER_GROUP=${USER_GROUP} -e APP_ENV=dev -e XDEBUG_ENABLED=0 -e WF_DEBUG=0 -e CI=0 -e DOCKER_RUN=1 -e WF_TTY=1  fchris82/symfony:ez2 composer create-project ezsystems/ezplatform .

docker run -it -u ${LOCAL_USER_ID}:${USER_GROUP} -w /home/chris/www/ez -v ${COMPOSER_HOME}:${COMPOSER_HOME} -v /home/chris/www/ez:/home/chris/www/ez -e LOCAL_USER_ID=${LOCAL_USER_ID} -e LOCAL_USER_NAME=${LOCAL_USER_NAME} -e LOCAL_USER_HOME=${LOCAL_USER_HOME} -e WF_HOST_TIMEZONE=${WF_HOST_TIMEZONE} -e WF_HOST_LOCALE=${WF_HOST_LOCALE} -e WF_DOCKER_HOST_CHAIN="${WF_DOCKER_HOST_CHAIN}$(hostname) " -e COMPOSER_HOME=${COMPOSER_HOME} -e COMPOSER_MEMORY_LIMIT=-1 -e USER_GROUP=${USER_GROUP} -e APP_ENV=dev -e XDEBUG_ENABLED=0 -e WF_DEBUG=0 -e CI=0 -e DOCKER_RUN=1 -e WF_TTY=1  fchris82/symfony:ez2 composer require kaliop/ezmigrationbundle

```
