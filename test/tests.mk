DOCKER_COMPOSE_FILE := $$(dirname $(abspath $(firstword $(MAKEFILE_LIST))))/docker-compose.yml
TEST_PROJECT_PATH := /tmp/www

define run
docker-compose -f $(DOCKER_COMPOSE_FILE) run -v $(TEST_PROJECT_PATH):$(TEST_PROJECT_PATH) --rm test make -f /tmp/tests/$(1) TEST_PROJECT_PATH=$(TEST_PROJECT_PATH) $(2)
endef

all: functions base test.1.0.0 test.1.1.0 clean

# @todo Ebben a repo-ban el kellene helyezni egy DEPLOY SSH kulcsot a workflow-test projekthez, mert a gitlab-nál nem vagy nehezen tudjuk megoldani a kulcs átadását a többszörösöen beágyazott docker container-eken keresztül. Addig csak így külön tesztelhető.
gitlab: functions clean

rebuild:
	docker-compose -f $(DOCKER_COMPOSE_FILE) build --no-cache test

init:
	mkdir -p $(TEST_PROJECT_PATH)

functions:
	./test/tests/functions.sh

base:
	$(call run,base.mk all)

test.1.0.0: init
	$(call run,test.1.0.0.mk all clean)

test.1.1.0: init
	$(call run,test.1.1.0.mk all clean)

# @todo ez nem mindig fut le, jogosultság hiba miatt
clean:
	rm -rf $(TEST_PROJECT_PATH)
