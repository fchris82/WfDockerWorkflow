VERSION						:=	1.0.0
PROJECT_DIRECOTRY			:=	project
DOCKER_BASENAME				:=	$${USER}p$${PWD\#\#*/}
DOCKER_DATA_DIR				:=	equipment/.data
DOCKER_PROVISIONING			:=	equipment/dev
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
DOCKER_USER			:=	$(WWW_DATA_UID):$(WWW_DATA_GID)
DOCKER_SHELL		:=	/bin/bash
DOCKER_HTTP_HOST	:=	localhost
DOCKER_PORT_PREFIX	:=	42
DOCKER_DOC_ROOT		:= /var/www/symfony
DEPLOYER_DIRECTORY	:=	deploy
SHARED_DIRS			:=	var
DIST_FILES			:=	.project.env \
						$(DOCKER_PROVISIONING)/docker-compose.local.yml \
						$(DOCKER_PROVISIONING)/$(DOCKER_CLI_NAME)/Dockerfile
GITFLOW_DEVELOP :=  develop
GITFLOW_FEATURE :=  feature
GITFLOW_HOTFIX  :=  hotfix

DB_PASSWORD := root
DB_NAME     := symfony
DB_URL      := mysql://root:$(DB_PASSWORD)@$(DOCKER_DB_NAME)/$(DB_NAME)

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
    \033[33menter\033[0m                \033[32mOpen the project $(DOCKER_CLI_NAME) shell\033[0m
    \033[33mconnect\033[0m              \033[32mOpen the project custom container\033[0m
                           wf connect <container> <shell>
                           \033[32mEg: \033[94m> wf connect mysql /bin/bash\033[0m
    \033[33mdocker-config\033[0m        \033[32mShow the current docker config settings\033[0m
    \033[33mps\033[0m                   \033[32mShow runing project containers\033[0m
    \033[33mup\033[0m                   \033[32mStart project containers\033[0m
    \033[33mdown\033[0m                 \033[32mStop project containers\033[0m
    \033[33mrebuild\033[0m              \033[32mRebuild project \033[0m$(DOCKER_CLI_NAME)\033[32m container\033[0m
    \033[33mdocker-compose-cmd\033[0m   \033[32mYou can run custom docker-compose command.\033[0m
                           wf docker-compose-cmd <command> [...]
                           \033[32mEg: \033[94m> wf docker-compose-cmd up web\033[32m --> you can debug the start\033[0m
\033[1;97m
Helper commands
\033[0m
    \033[33mphp\033[0m             \033[32mRun php command. You have to use the docker container file structure\033[0m
    \033[33mcomposer\033[0m        \033[32mRun composer command\033[0m
    \033[33msf\033[0m              \033[32mRun symfony console command\033[0m
    \033[33mdbreload\033[0m        \033[32mReload the project database with fixtures\033[0m
    \033[33mdbdump\033[0m          \033[32mMake a database dump\033[0m
    \033[33mdbimport\033[0m        \033[32mImport database\033[0m

    \033[33mdeploy\033[0m          \033[32mDeploy...\033[0m

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
                            DOCKER_DOC_ROOT=$(DOCKER_DOC_ROOT) \
                            MYSQL_ROOT_PASSWORD=$(DB_PASSWORD) \
                            DATABASE_URL=$(DB_URL) \
                            DOCKER_USER=$(DOCKER_USER) \
                            WWW_DATA_UID=$(WWW_DATA_UID) \
                            WWW_DATA_GID=$(WWW_DATA_GID) \
                            DOCKER_CONFIG_NGINX_DEBUG= \
                            DOCKER_NGINX_COMMAND=nginx
CMD_DOCKER_BASE       := $(CMD_DOCKER_ENV) docker-compose \
                            -p $(DOCKER_BASENAME) \
                            $(foreach file,$(DOCKER_CONFIG_FILES),-f $(file)) \
                            $(foreach file,$(DOCKER_USER_CONFIG_FILES),-f $(file)) \
                            --project-directory $(CURDIR)
CMD_DOCKER_RUN        := $(CMD_DOCKER_BASE) run --rm --user $(DOCKER_USER)
CMD_DOCKER_RUN_CLI    := $(CMD_DOCKER_RUN) $(DOCKER_CLI_NAME)

.PHONY: init
init:
	$(foreach file,$(DIST_FILES), cp -i $(file).dist $(file);)
    # @todo Ez nem biztos, hogy így jó még tesztelni kell!
#	$(CMD_DOCKER_RUN_CLI) setfacl -dR -m u:"$$USER":rwX -m u:$$(whoami):rwX $(SHARED_DIRS)
#	$(CMD_DOCKER_RUN_CLI) setfacl -R -m u:"$$USER":rwX -m u:$$(whoami):rwX $(SHARED_DIRS)
	@echo "\033[32m✔ Edit the new files before run the \033[94minstall\033[32m command:\033[0m"
	@$(foreach file,$(DIST_FILES),echo "   - \033[33m$(file)\033[0m";)

.PHONY: install
install: rebuild up
	$(CMD_MAKE) composer ARGS="install"
	$(CMD_MAKE) sf ARGS="doctrine:database:create --if-not-exists"
	$(CMD_MAKE) sf ARGS="doctrine:migrations:migrate -n"
	$(CMD_MAKE) sf ARGS="doctrine:fixtures:load -n"
	@echo "\033[32m✔ Now you can use the project!\033[0m"

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

.PHONY: connect
connect: __docker_connect

.PHONY: rebuild
rebuild: __docker_rebuild

.PHONY: ps
ps: __docker_ps

.PHONY: docker-config
docker-config: __docker_docker-config

.PHONY: php
php: __container_php

.PHONY: sf
sf: __container_sf

.PHONY: composer
composer: __container_composer

# @todo
.PHONY: deploy
deploy:
	echo '$(DEPLOYER_DIRECTORY)/vendor/bin/dep $(ARGS)'

.PHONY: feature
feature: __feature

.PHONY: hotfix
hotfix: __hotfix

.PHONY: push
push: __push

.PHONY: publish
publish: __publish
