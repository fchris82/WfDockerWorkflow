# Fixen felülírjuk a komplett parameters.yml-t ezekkel az adatokkal
define EZ_PARAMETERS
# This file is auto-generated during the composer install
parameters:
    secret: ThisEzPlatformTokenIsNotSoSecretChangeIt
    database_driver: pdo_mysql
    database_host: mysql
    database_port: null
    database_name: symfony
    database_user: root
    database_password: root
endef
export EZ_PARAMETERS

# Amennyiben az Enterprise Edition-t használjuk, akkor szükségünk lesz az auth.json-re az alábbi formában
define AUTHJSON
{
    "http-basic": {
        "updates.ez.no": {
            "username": "{{ auth_username }}",
            "password": "{{ auth_password }}"
        }
    }
}
endef
export AUTHJSON

# Ha van auth.json, akkor azt át kell adnunk az első futásnál a Docker container-nek, mert más módon nem megoldható,
# hogy ne kérje be újra az információkat minden egyes futásnál.
define REGISTER_AUTHJSON
services:
    engine:
        volumes:
            - "$${PWD}/auth.json:/home/user/.composer/auth.json"
endef
export REGISTER_AUTHJSON

# Ezeket hozzá csapjuk a .gitignore fájlhoz
define GITIGNORE
/.project.env
docker-compose.local.yml
/.docker/.data
!/.docker/.data/.gitkeep
endef
export GITIGNORE

# Ha szeretnél debugolni, akkor az alábbi rész kikommentezésével látni fogod a wf és wizard parancsok részleteit is.
#MAKE_DISABLE_SILENC=1
#export MAKE_DISABLE_SILENC

# Fixen beállítjuk a shell-t. Ez sajnos ZSH alatt szükséges, mert máshogy működik.
ez-install: SHELL:=/bin/bash
ez-install:
	wizard
	git init && git add . && git commit -m "Docker Init"
	wf init
	@read -p "Enter local host (reverse proxy), eg: 'project.loc' : " host; \
	echo "DOCKER_HTTP_HOST := $$host" >> .project.env
	@read -p "Enter port prefix (without reverse proxy): " port; \
	echo "DOCKER_PORT_PREFIX := $$port" >> .project.env
{% if package != 'ezsystems/ezplatform' %}
	echo "$$AUTHJSON" > auth.json; \
	echo "$$REGISTER_AUTHJSON" >> .docker/docker-compose.local.yml;
{% endif %}
	wf composer create-project {{ package }} ez;
	mv ez/* . && mv ez/.[!.]* . && rm -rf ez
    # Docker compose fix (Enterprise Edition)
	cp -f .docker/docker-compose.local.yml.dist .docker/docker-compose.local.yml
	@echo "$$GITIGNORE" >> .gitignore
    # Bugfix:
	wf composer update
	git add . && git commit -m "eZ init"
	wf down
	wf up
	@echo "$$EZ_PARAMETERS" > app/config/parameters.yml
	wf composer require "nikic/php-parser:2.*" "kaliop/ezmigrationbundle" "doctrine/orm"
	wf sf doctrine:database:create --if-not-exists
	wf sf ezplatform:install {{ ez_install_type }};
	echo "Jegyezd be: new \Kaliop\eZMigrationBundle\EzMigrationBundle()"
	echo "Törölheted a makefile-t"
