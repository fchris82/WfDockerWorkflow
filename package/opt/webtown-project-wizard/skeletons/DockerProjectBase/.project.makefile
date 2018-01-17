VERSION						:=	1.1.0
PROJECT_DIRECOTRY			:=	{{ project_directory }}
DOCKER_BASENAME				:=	$${USER}p$${PWD\#\#*/}
DOCKER_DATA_DIR				:=	{{ docker_data_dir }}
DOCKER_PROVISIONING			:=	{{ docker_provisioning }}
DOCKER_CONFIG_FILES			:=	$(DOCKER_PROVISIONING)/docker-compose.yml \
								$(DOCKER_PROVISIONING)/docker-compose.local.yml
DOCKER_USER_CONFIG_FILES	:=
DOCKER_ENVIRONMENTS	:=
DOCKER_CLI_NAME		:=	engine
DOCKER_DB_NAME		:=	mysql
# Az aktuális projekt gazdája
WWW_DATA_UID		:=	$$(stat -c '%u' .)
# Az Docker group
WWW_DATA_GID		:=	$$(getent group docker | cut -d: -f3)
# Docker user
SSH_PATH			:= ~/.ssh
DOCKER_USER			:=	$(WWW_DATA_UID):$(WWW_DATA_GID)
DOCKER_SHELL		:=	/bin/bash
DOCKER_HTTP_HOST	:=	localhost
DOCKER_PORT_PREFIX	:=	42
DOCKER_PROXY_NETWORK:=	reverse-proxy
DOCKER_DOC_ROOT		:=	/var/www
DEPLOYER_DIRECTORY	:=	{{ deploy_directory }}
SF_CONSOLE_CMD		:=	{{ sf_console_cmd }}
SHARED_DIRS			:=	{{ shared_dirs }}
DIST_FILES			:=	.project.env \
						$(DOCKER_PROVISIONING)/docker-compose.local.yml \
						$(DOCKER_PROVISIONING)/$(DOCKER_CLI_NAME)/Dockerfile \
						$(DOCKER_PROVISIONING)/$(DOCKER_CLI_NAME)/config/custom.php.ini \
						$(DOCKER_PROVISIONING)/$(DOCKER_CLI_NAME)/config/xdebug.ini
GITFLOW_DEVELOP :=  develop
GITFLOW_FEATURE :=  feature
GITFLOW_HOTFIX  :=  hotfix

DB_PASSWORD := root
DB_NAME     := symfony
DB_URL      := mysql://root:$(DB_PASSWORD)@$(DOCKER_DB_NAME)/$(DB_NAME)
PHP_VERSION := {{ php_version }}

# PHP environments
TIMEZONE               := Europe/Budapest
PHP_MAX_EXECUTION_TIME := 30
PHP_MEMORY_LIMIT       := 128M
PHP_MAX_UPLOAD         := 50M
PHP_MAX_FILE_UPLOADS   := 20
PHP_MAX_POST           := 100M

{% if sf_version < 4 %}
# Symfony environments. If you add new then you have to register it in the `docker-compose.yml` file (`environment` block) and add to CMD_DOCKER_ENV too!
SYMFONY_ENV := prod
SYMFONY_DEBUG :=
SYMFONY_CLASSLOADER_FILE :=
SYMFONY_HTTP_CACHE :=
SYMFONY_HTTP_CACHE_CLASS :=
SYMFONY_TRUSTED_PROXIES :=
{% endif %}

# HTTP AUTH : http://www.htaccesstools.com/htpasswd-generator/
# Don't forget to escape the $ sign: $ --> \$$
HTTP_AUTH_PASS :=

# A Gitlab CI miatt kell definiálni.
SUDO              := sudo
DOCKER_PSEUDO_TTY :=

# Check if file exists
-include .project.env

# Ennek muszáj elől lennie, mert szeretnénk, ha ez lenne az első és alapértelmezett "target"
define PROJECT_HELP
\033[1;97m
This is a Webtown Workflow project!
===================================
\033[0m
    \033[33mlist\033[0m          \033[32mShow all workflow commands of the project\033[0m
    \033[33mhelp\033[0m          \033[32mShow this help\033[0m
    \033[33minfo\033[0m          \033[32mShow some important project info\033[0m
\033[1;97m
Install the project
\033[0m
  1. \033[94m> wf init\033[0m
  2. \033[97mEdit the new files!\033[0m
  3. \033[94m> wf install\033[0m

  You can use the \033[33mdbreload\033[0m, \033[33mdbdump\033[0m and \033[33mdbimport\033[0m commands.
\033[1;97m
Working
\033[0m
    \033[33mfeature         \033[32mStart a new feature branch from $(GITFLOW_DEVELOP). Set the "suffix" only which is required.
                    Eg: \033[94m> wf feature [--from-this] [--disable-db] [--reload-db] PR-12\033[0m --> \033[97mfeature/PR-12\033[0m
                    Enabled parameters:
                        \033[33m--from-this\033[0m   Ha meg van adva, akkor nem a `develop` branch-ből indít, hanem az aktuálisból
                        \033[33m--disable-db\033[0m  Ha meg van adva, akkor az adatbázishoz nem nyúl hozzá.
                        \033[33m--reload-db\033[0m   Ha meg van adva, akkor újratölti a teljes adatbázist (drop -> create -> migrations -> fixtures)
    \033[33mhotfix          \033[32mStart a new hotfix branch from master branch. Set the "suffix" only which is required.
                    Eg: \033[94m> wf hotfix [--from-this] [--disable-db] [--reload-db] PR-12\033[0m --> \033[97mhotfix/PR-12\033[0m
                    Enabled parameters:
                        \033[33m--from-this\033[0m   Ha meg van adva, akkor nem a `develop` branch-ből indít, hanem az aktuálisból
                        \033[33m--disable-db\033[0m  Ha meg van adva, akkor az adatbázishoz nem nyúl hozzá.
                        \033[33m--reload-db\033[0m   Ha meg van adva, akkor újratölti a teljes adatbázist (drop -> create -> migrations -> fixtures)
    \033[33mpush            \033[32mPush the current branch.\033[0m
    \033[33mpublish         \033[32mPUSH and create merge requiest in Gitlab. You don't have to
                    call push before it.\033[0m
\033[1;97m
Docker commands
\033[0m
    \033[33menter\033[0m                \033[32mOpen the project $(DOCKER_CLI_NAME) shell or you can connect other container\033[0m
    \033[33mexec\033[0m                 \033[32mExecute a command in running container. You can connect eg:\033[0m
                           wf exec <container> <shell>
                           \033[32mEg: \033[94m> wf exec mysql /bin/bash\033[0m
    \033[33mrun\033[0m                  \033[32mRun a command in container. Use the --rm!\033[0m
                           wf run --rm <container> <shell>
                           \033[32mEg: \033[94m> wf run --rm engine php -v\033[0m
    \033[33mdebug-docker-config\033[0m  \033[32mShow the current docker config settings\033[0m
    \033[33mps\033[0m                   \033[32mShow runing project containers\033[0m
    \033[33mup\033[0m                   \033[32mStart project containers\033[0m
    \033[33mdown\033[0m                 \033[32mStop project containers\033[0m
    \033[33mrebuild\033[0m              \033[32mRebuild project \033[0m$(DOCKER_CLI_NAME)\033[32m container\033[0m
    \033[33mlogs\033[0m                 \033[32mYou can show the container logs\033[0m
                           wf logs <container> [...]
                           \033[32mEg: \033[94m> wf logs web\033[32m --> you can show the web logs.\033[0m
    \033[33mdocker-compose\033[0m       \033[32mYou can run custom docker-compose command.\033[0m
                           wf docker-compose <command> [...]
                           \033[32mEg: \033[94m> wf docker-compose up web\033[32m --> you can debug the start\033[0m
\033[1;97m
Helper commands
\033[0m
    \033[33mphp\033[0m             \033[32mRUN php command. You have to use the docker container file structure\033[0m
    \033[33mcomposer\033[0m        \033[32mEXEC a composer command\033[0m
    \033[33msf\033[0m              \033[32mEXEC a symfony console command\033[0m
    \033[33mmysql\033[0m           \033[32mConnect to the database\033[0m
    \033[33mdbreload\033[0m        \033[32mReload the project database with fixtures\033[0m
    \033[33mdbdump\033[0m          \033[32mMake a database dump\033[0m
    \033[33mdbimport\033[0m        \033[32mImport database\033[0m

    \033[33mdeploy\033[0m          \033[32mDeploy...\033[0m

    \033[33mdebug-*\033[0m         \033[32mDebug commands\033[0m

endef
export PROJECT_HELP
.PHONY: help
help:
	@echo "$$PROJECT_HELP"

BASE_MAKEFILE_PATH	:=	$(WORKFLOW_MAKEFILE_PATH).$(VERSION)
include $(BASE_MAKEFILE_PATH)

BASE_DIRECTORY	:= $$(git rev-parse --show-toplevel)
THIS_FILE		:= $(firstword $(MAKEFILE_LIST))
# @todo Ez nem kapja meg a debug paramétereket!
CMD_MAKE              := $(MAKE) -C $(BASE_DIRECTORY) -f $(THIS_FILE) $(MAKEOVERRIDES)

# Docker commands
# Ha szeretnéd az nginx-et debug módban futtatni, akkor írd át:
#    [...]
#    DOCKER_CONFIG_NGINX_DEBUG=debug \
#    DOCKER_NGINX_COMMAND=nginx-debug
CMD_DOCKER_ENV        := $(DOCKER_ENVIRONMENTS) \
                            BASE_DIRECTORY=$(BASE_DIRECTORY) \
                            PROJECT_DIR_NAME=$(PROJECT_DIRECOTRY) \
                            PROJECT_COMPOSE_DIR=$(BASE_DIRECTORY)/$(DOCKER_PROVISIONING) \
                            DOCKER_DATA_DIR=$(BASE_DIRECTORY)/$(DOCKER_DATA_DIR) \
                            DOCKER_PORT_PREFIX=$(DOCKER_PORT_PREFIX) \
                            DOCKER_HTTP_HOST=$(DOCKER_HTTP_HOST) \
                            DOCKER_PROXY_NETWORK=$(DOCKER_PROXY_NETWORK) \
                            DOCKER_DOC_ROOT=$(DOCKER_DOC_ROOT) \
                            MYSQL_ROOT_PASSWORD=$(DB_PASSWORD) \
                            DB_NAME=$(DB_NAME) \
                            DATABASE_URL=$(DB_URL) \
                            TIMEZONE=$(TIMEZONE) \
                            PHP_MAX_EXECUTION_TIME=$(PHP_MAX_EXECUTION_TIME) \
                            PHP_MEMORY_LIMIT=$(PHP_MEMORY_LIMIT) \
                            PHP_MAX_UPLOAD=$(PHP_MAX_UPLOAD) \
                            PHP_MAX_FILE_UPLOADS=$(PHP_MAX_FILE_UPLOADS) \
                            PHP_MAX_POST=$(PHP_MAX_POST) \
{% if sf_version < 4 %}
                            SYMFONY_ENV=$(SYMFONY_ENV) \
                            SYMFONY_DEBUG=$(SYMFONY_DEBUG) \
                            SYMFONY_CLASSLOADER_FILE=$(SYMFONY_CLASSLOADER_FILE) \
                            SYMFONY_HTTP_CACHE=$(SYMFONY_HTTP_CACHE) \
                            SYMFONY_HTTP_CACHE_CLASS=$(SYMFONY_HTTP_CACHE_CLASS) \
                            SYMFONY_TRUSTED_PROXIES=$(SYMFONY_TRUSTED_PROXIES) \
{% endif %}
                            DOCKER_USER=$(DOCKER_USER) \
                            SSH_PATH=$(SSH_PATH) \
                            WWW_DATA_UID=$(WWW_DATA_UID) \
                            WWW_DATA_GID=$(WWW_DATA_GID) \
                            PHP_VERSION=$(PHP_VERSION) \
                            CI=$${CI} \
                            HTTP_AUTH_PASS=$(HTTP_AUTH_PASS) \
                            DOCKER_CONFIG_NGINX_DEBUG= \
                            DOCKER_NGINX_COMMAND=nginx
CMD_DOCKER_BASE       := $(CMD_DOCKER_ENV) docker-compose \
                            -p $(DOCKER_BASENAME) \
                            $(foreach file,$(DOCKER_CONFIG_FILES),-f $(file)) \
                            $(foreach file,$(DOCKER_USER_CONFIG_FILES),-f $(file)) \
                            --project-directory $(CURDIR)
CMD_DOCKER_RUN        := $(CMD_DOCKER_BASE) run --rm
CMD_DOCKER_RUN_CLI    := $(CMD_DOCKER_RUN) $(DOCKER_CLI_NAME)
CMD_DOCKER_EXEC       := $(CMD_DOCKER_BASE) exec
CMD_DOCKER_EXEC_CLI   := $(CMD_DOCKER_EXEC) --user $(DOCKER_USER) $(DOCKER_PSEUDO_TTY) $(DOCKER_CLI_NAME)

# @@@ Edit
.PHONY: init
init:
	$(foreach file,$(DIST_FILES), cp -i $(file).dist $(file);)
	@echo "\033[32m✔ Edit the new files before run the \033[94minstall\033[32m command:\033[0m"
	@$(foreach file,$(DIST_FILES),echo "   - \033[33m$(file)\033[0m";)

# @@@ Edit
.PHONY: install
install: up
	$(CMD_MAKE) composer ARGS="install"
	$(CMD_MAKE) sf ARGS="doctrine:database:create --if-not-exists"
#	$(CMD_MAKE) sf ARGS="ezplatform:install clean"
#	$(CMD_MAKE) sf ARGS="kaliop:migration:migrate -n"
	$(CMD_MAKE) sf ARGS="doctrine:migrations:migrate -n --allow-no-migration"
#    # If you wish use it on dev and test mode
#    ifneq ($(SYMFONY_ENV),"prod")
#		$(CMD_MAKE) sf ARGS="doctrine:fixtures:load -n"
#    endif
#	$(CMD_DOCKER_EXEC_CLI) bundle install
#	$(CMD_DOCKER_EXEC_CLI) npm install
#	$(CMD_DOCKER_EXEC_CLI) bower install
#	$(CMD_DOCKER_EXEC_CLI) gulp build
	$(CMD_MAKE) sf ARGS="assets:install --symlink"
	$(CMD_MAKE) sf ARGS="assetic:dump"
	@echo "\033[32m✔ Now you can use the project!\033[0m"

# @@@ Edit
.PHONY: reinstall
reinstall:
	$(CMD_MAKE) sf ARGS="doctrine:database:drop --if-exists --force"
	$(CMD_MAKE) install

# @@@ Edit
.PHONY: debug-servicecheck
debug-servicecheck: up
	$(CMD_DOCKER_EXEC) $(DOCKER_CLI_NAME) service php$(PHP_VERSION)-fpm status
	$(CMD_DOCKER_EXEC) $(DOCKER_DB_NAME) service mysql status

.PHONY: logs
logs: __docker_logs

.PHONY: mysql
mysql: __mysql_connect

# Újra tölti az adatbázist
# Eg:
# make -f .project.makefile dbreload FULL="1"
# make -f .project.makefile dbreload ARGS="--full"
.PHONY: dbreload
dbreload: up
    ifneq (,$(FULL)$(findstring --full,$(ARGS)))
		$(CMD_MAKE) sf ARGS="doctrine:database:drop --if-exists --force"
    endif
	$(CMD_MAKE) sf ARGS="doctrine:database:create --if-not-exists"
	$(CMD_MAKE) sf ARGS="doctrine:migrations:migrate -n"
    ifneq (,$(FULL)$(findstring --full,$(ARGS)))
		$(CMD_MAKE) sf ARGS="doctrine:fixtures:load -n"
    endif

.PHONY: up
up: __docker_up

.PHONY: down
down: __docker_down

.PHONY: enter
enter: __docker_enter

.PHONY: debug-enter
debug-enter: __docker_debug_enter

.PHONY: exec
exec: __docker_exec

.PHONY: run
run: __docker_run

.PHONY: rebuild
rebuild: __docker_rebuild

.PHONY: ps
ps: __docker_ps

.PHONY: debug-docker-config
debug-docker-config: __docker_docker-config

.PHONY: php
php: __container_php

.PHONY: sf
sf: __container_sf

.PHONY: composer
composer: __container_composer

# @todo
.PHONY: deploy
deploy:
	echo '$(DEPLOYER_DIRECTORY)/bin/dep $(ARGS)'

.PHONY: feature
feature: __feature

.PHONY: hotfix
hotfix: __hotfix

.PHONY: push
push: __push

.PHONY: publish
publish: __publish
