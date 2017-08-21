VERSION				:=	1.0
PROJECT_DIRECOTRY	:=	project
DOCKER_BASENAME		:=	$${USER}p$${PWD\#\#*/}
DOCKER_CONFIG_FILES	:=	./docker-compose.yml
DOCKER_ENVIRONMENTS	:=
DOCKER_CLI_NAME		:=	engine
DOCKER_DB_NAME		:=	mysql
DOCKER_USER			:=	user
DOCKER_SHELL		:=	/bin/bash
DOCKER_HOST			:=	localhost
DOCKER_PORT_PREFIX	:=	42
DEPLOYER_DIRECTORY	:=	./deploy
SHARED_DIRS			:=	var
DIST_FILES			:=	./.env \
						./docker-compose.local.yml
GITFLOW_DEVELOP     :=  develop
GITFLOW_FEATURE     :=  feature
GITFLOW_HOTFIX      :=  hotfix

include .make.env

CMD_DOCKER_BASE	:= docker-compose -p $(DOCKER_BASENAME)
CMD_PHP			:= $(CMD_DOCKER_BASE) exec --user $(DOCKER_USER) $(DOCKER_CLI_NAME) php
THIS_FILE		:= $(firstword $(MAKEFILE_LIST))

# List help
.PHONY: help
help:
	@echo "help"
	@echo "${THIS_FILE}"

# List all targets
.PHONY: list
list:
	@sh -c "$(MAKE) -p | awk -F':' '/^[a-zA-Z0-9][^\$$#\/\\t=]*:([^=]|$$)/ {split(\$$1,A,/ /);for(i in A)print A[i]}' | grep -v '__\$$' | sort"

.PHONY: init
init:
	@echo "create dist files"
	@echo "update .env and other"
	@echo "setfacl ${SHARED_DIRS}"
#	# We have to create these files from them `.dist` version
#	TARGETS=(".env" "docker-compose.local.yml" ".docker/local_build.sh")
#	for TARGET in "${TARGETS[@]}"
#	do
#	    # the `.dist` file
#	    DIST="${TARGET}.dist"
#	    # if the `.dist` file exists...
#	    if [ -f $DIST ]; then
#	        # ...and the original file exists...
#	        if [ -f $TARGET ]; then
#	            # print alert with red characters
#	            printf "\e[31m✘ The \e[0m${TARGET}\e[31m file is exist. If you want to reset it, delete it before run script!\e[0m\n"
#	        # ...and the original file doesn't exist
#	        else
#	            cp $DIST $TARGET
#	            # print success message in green
#	            printf "\e[32m✔ Edit the new \e[0m${TARGET}\e[32m file!\e[0m\n"
#	        fi
#	    # if the `.dist` file doesn't exist
#	    else
#	        # print alert with red characters
#	        printf "\e[31m✘ Something is wrong. The \e[0m${DIST}\e[31m dist file doesn't exist.\e[0m\n"
#	    fi
#	done
#
#	# Set the local user ID
#	sed -e "s/LOCAL_USER_ID=1000/LOCAL_USER_ID=$UID/" -i .env

.PHONY: install
install: init
	@echo "composer install"
	@echo "create database"
	@echo "load migrations"
	@echo "load fixtures"
	@$(MAKE) -f $(THIS_FILE) up
	@$(MAKE) -f $(THIS_FILE) ps

.PHONY: up
up:
	@echo "$(CMD_DOCKER_BASE) $(foreach env,$(DOCKER_ENVIRONMENTS),-e $(env) $(foreach file,$(DOCKER_CONFIG_FILES),-f $(file)) up -d"

.PHONY: down
down:
	@echo '$(CMD_DOCKER_BASE) down'

.PHONY: enter
enter:
	@echo '$(CMD_DOCKER_BASE) exec --user $(DOCKER_USER) $(DOCKER_CLI_NAME) $(DOCKER_SHELL)'

.PHONY: rebuild
rebuild:
	@echo '$(CMD_DOCKER_BASE) $(foreach env,$(DOCKER_ENVIRONMENTS),-e $(env) $(foreach file,$(DOCKER_CONFIG_FILES),-f $(file)) build --no-cache $(DOCKER_CLI_NAME)'

.PHONY: ps
ps:
	@echo '$(CMD_DOCKER_BASE) ps'

.PHONY: dbdump
dbdump:
	@echo '$(CMD_DOCKER_BASE) exec [...]'

.PHONY: dbimport
dbimport:
	@echo '$(CMD_DOCKER_BASE) exec [...]'

.PHONY: php
php:
	@echo '$(CMD_DOCKER_BASE) exec --user $(DOCKER_USER) $(DOCKER_CLI_NAME) php $(ARGS)'

# make sf ARGS="doctrine:migrations:migrate -n" --> docker-compose -p chrisptest -e ablak=1 exec --user user engine php bin/console doctrine:migrations:migrate -n
.PHONY: sf
sf:
	@echo '$(CMD_PHP) bin/console $(ARGS)'

.PHONY: composer
composer:
	@echo '$(CMD_DOCKER_BASE) exec --user $(DOCKER_USER) $(DOCKER_CLI_NAME) composer $(ARGS)'

.PHONY: deploy
deploy:
	@echo '$(DEPLOYER_DIRECTORY)/vendor/bin/dep $(ARGS)'
