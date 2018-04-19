DOCKER_CLI_NAME		:= engine
DOCKER_DOC_ROOT		:= /var/www
SF_CONSOLE_CMD		:= {{ sf_console_command }}
SF_BIN_DIR			:= {{ sf_bin_dir }}
SHARED_DIRS			:= var

XDEBUG_IDE_SERVER_NAME := {{ environment.XDEBUG_IDE_SERVER_NAME | default(server.xdebug_ide_server_name) }}
PHP_IDE_CONFIG := "serverName=${XDEBUG_IDE_SERVER_NAME}"
WF_XDEBUG_ENABLED := {{ server.xdebug ? '1' : '0' }}

# HTTP AUTH : http://www.htaccesstools.com/htpasswd-generator/
# Don't forget to escape the $ sign: $ --> \$$
HTTP_AUTH_PASS :=

ORIGINAL_CMD_DOCKER_ENV := $(CMD_DOCKER_ENV)
define CMD_DOCKER_ENV
    $(ORIGINAL_CMD_DOCKER_ENV) \
    WF_XDEBUG_ENABLED=$(WF_XDEBUG_ENABLED) \
    PHP_IDE_CONFIG=$(PHP_IDE_CONFIG) \
    SYMFONY_DEBUG=$(SYMFONY_DEBUG) \
    SYMFONY_CLASSLOADER_FILE=$(SYMFONY_CLASSLOADER_FILE) \
    SYMFONY_HTTP_CACHE=$(SYMFONY_HTTP_CACHE) \
    SYMFONY_HTTP_CACHE_CLASS=$(SYMFONY_HTTP_CACHE_CLASS) \
    SYMFONY_TRUSTED_PROXIES=$(SYMFONY_TRUSTED_PROXIES) \
    SYMFONY_DEPRECATIONS_HELPER=$(SYMFONY_DEPRECATIONS_HELPER)
endef

# @todo (Chris) A php és a composer parancs esetén itt korábban `$(CMD_DOCKER_RUN_CLI) php $(ARGS)` szerepelt, azaz nem az EXEC volt hívva. Viszont ha nem az exec van hívva, akkor az ENTRYPOINT vagy nem fut le, vagy vmi más problémája van, a lényeg, hogy nem működik a `dep` parancs, mert nem találja a ~/.ssh könyvtárat.
.PHONY: php
php: DOCKER_USER="www-data"
php:
	$(CMD_DOCKER_RUN_CLI) php $(ARGS)

php-exec: DOCKER_USER="www-data"
php-exec: up
	$(CMD_DOCKER_EXEC_CLI) php $(ARGS)

.PHONY: sf
sf: DOCKER_USER="www-data"
sf: up
	$(CMD_DOCKER_EXEC_CLI) php $(SF_CONSOLE_CMD) $(ARGS)

.PHONY: composer
composer: DOCKER_USER="www-data"
composer:
	$(CMD_DOCKER_RUN_CLI) composer $(ARGS)
