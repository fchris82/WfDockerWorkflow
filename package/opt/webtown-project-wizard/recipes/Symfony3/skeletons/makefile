DOCKER_CLI_NAME		:= engine
DOCKER_DOC_ROOT		:= /var/www
SF_CONSOLE_CMD		:= bin/console
SF_BIN_DIR			:= vendor/bin
SHARED_DIRS			:= var

XDEBUG_IDE_SERVER_NAME := {{ environment.XDEBUG_IDE_SERVER_NAME | default(xdebug_ide_server_name) }}
PHP_IDE_CONFIG := "serverName=${XDEBUG_IDE_SERVER_NAME}"
XDEBUG_ENABLED := {{ xdebug ? '1' : '0' }}

# HTTP AUTH : http://www.htaccesstools.com/htpasswd-generator/
# Don't forget to escape the $ sign: $ --> \$$
HTTP_AUTH_PASS :=

ORIGINAL_CMD_DOCKER_ENV := $(CMD_DOCKER_ENV)
define CMD_DOCKER_ENV
    $(ORIGINAL_CMD_DOCKER_ENV) \
    XDEBUG_ENABLED=$(XDEBUG_ENABLED) \
    PHP_IDE_CONFIG=$(PHP_IDE_CONFIG)
endef

.PHONY: php
php:
	$(CMD_DOCKER_RUN_CLI) php $(ARGS)

.PHONY: sf
sf: up
	$(CMD_DOCKER_EXEC_CLI) php $(SF_CONSOLE_CMD) $(ARGS)

.PHONY: composer
composer:
	$(CMD_DOCKER_RUN_CLI) composer $(ARGS)
