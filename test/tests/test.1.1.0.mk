TEST_PROJECT_GIT_URL := git@gitlab.webtown.hu:webtown/workflow-test.git
TEST_PROJECT_BRANCH := v1.1.0
TEST_DIR := test
DB_TEST_NAME := symfony_test
DB_TEST_PASSWORD := root_test

all: \
    test_list \
    test_info \
    test_docker_config \
    test_sf \
    test_db_export_import

# Installáljuk a programot, hogy elérhető legyen a `wf` parancs
.PHONY: install
install:
    ifeq ("$(wildcard /usr/local/bin/wf)","")
		cp -r /home/user/.ssh /root/.ssh && chown -R root:root /root/.ssh
		cd /tmp && git archive --remote=git@gitlab.webtown.hu:webtown/webtown-workflow.git HEAD webtown-workflow.deb | tar -x
		dpkg -i /tmp/webtown-workflow.deb
		which wf | grep "/usr/local/bin/wf"
		wf
    endif

# Letöltjük a test projektet, amivel tesztelni fogunk. Ügyelj arra, hogy ez csak egyszer fut le, ha vmit módosítasz
# a fájlokon, akkor az hatással lehet az utána következő scriptre, úgyhogy mindenképpen gondoskodj az állapot
# visszaállításról.
.PHONY: build_project
build_project: install
    ifeq ("$(wildcard $(TEST_PROJECT_PATH)/$(TEST_DIR))","")
		cd $(TEST_PROJECT_PATH); \
		git clone -b $(TEST_PROJECT_BRANCH) $(TEST_PROJECT_GIT_URL) $(TEST_DIR); \
		cd $(TEST_DIR); \
		wf init; \
		cp project/app/config/parameters.yml.dist project/app/config/parameters.yml; \
		echo "HTTP_PORT=8008\nMYSQL_PUBLIC_PORT=3308\n\n# Don't change this:\nMYSQL_ROOT_PASSWORD=$(DB_TEST_PASSWORD)\nDATABASE_URL=mysql://root:$(DB_TEST_PASSWORD)@mysql/$(DB_TEST_NAME)" > .env; \
		echo "DB_PASSWORD := $(DB_TEST_PASSWORD)\nDB_NAME     := $(DB_TEST_NAME)\nDOCKER_PORT_PREFIX := 1" > .project.env; \
		wf install \
		cat .env
    endif

# Leállítjuk a container-eket.
.PHONY: down
down:
	cd $(TEST_PROJECT_PATH)/$(TEST_DIR); \
	wf down;

# Töröljük a teszt projektet
.PHONY: clean
clean: down
	rm -rf $(TEST_PROJECT_PATH)/$(TEST_DIR)

########################################################################################################################
#                                                                                                                      #
#                                                    T E S T S                                                         #
#                                                                                                                      #
########################################################################################################################


# Lista tesztelése
.PHONY: test_list
test_list: build_project
	cd $(TEST_PROJECT_PATH)/$(TEST_DIR); \
	wf list | grep info; \
	wf list | grep help; \
	wf list | grep hotfix; \
	wf list | grep feature; \
	wf list | grep push; \
	wf list | grep publish; \
	wf list | grep php; \
	wf list | grep composer; \
	wf list | grep sf; \
	wf list | grep exec; \
	wf list | grep run; \
	wf list

# Info tesztelése
.PHONY: test_info
test_info: build_project
	cd $(TEST_PROJECT_PATH)/$(TEST_DIR); \
	wf info | grep 1.0.0; \
	wf info | grep equipment/dev/docker-compose.yml; \
	wf info | grep bin/bash; \
	wf info | grep localhost; \
	\
	cp .project.env .project.env~; \
	echo "DOCKER_SHELL := /bin/zsh\nDOCKER_HTTP_HOST := test.loc\nDOCKER_PORT_PREFIX := 10" > .project.env; \
	wf info | grep bin/zsh; \
	wf info | grep test.loc; \
	wf info | grep 10; \
	wf info; \
	cp .project.env~ .project.env; \
	rm .project.env~

# Docker config test
.PHONY: test_docker_config
test_docker_config: build_project
	cd $(TEST_PROJECT_PATH)/$(TEST_DIR); \
	wf docker-config; \
	wf docker-config | grep "mysql://root:root_test@mysql/symfony_test"; \
	wf docker-config | grep "8008"; \
	wf docker-config | grep "3308"

.PHONY: test_sf
test_sf: build_project
	cd $(TEST_PROJECT_PATH)/$(TEST_DIR); \
	wf sf

# Adatbázis import-export teszt:
#  - DUMP
#  - Fájl módosítás: symfony_demo_user --> symfony_demo_test_user
#  - Az új fájl visszatöltése
#  - DUMP --> ellenőrzés, hogy van-e `symfony_demo_test_user`
#  - Az eredeti adatbázis visszatöltése, hogy a későbbi teszteknél is jó legyen!
.PHONY: test_db_export_import
test_db_export_import: build_project
	cd $(TEST_PROJECT_PATH)/$(TEST_DIR); \
	MAKE_DISABLE_SILENCE=1 wf dbdump; \
	cat dbdump.sql; \
	cp dbdump.sql dbdump.sql~; \
	sed -i "s/symfony_demo_user/symfony_demo_test_user/" dbdump.sql; \
	MAKE_DISABLE_SILENCE=1 wf dbimport; \
	rm dbdump.sql; \
	wf dbdump dbdump2.sql; \
	cat dbdump2.sql | grep symfony_demo_test_user; \
	cp dbdump.sql~ dbdump.sql; \
	rm dbdump.sql~; \
	wf dbimport
