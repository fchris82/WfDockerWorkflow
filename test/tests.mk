DOCKER_COMPOSE_FILE := $$(dirname $(abspath $(firstword $(MAKEFILE_LIST))))/docker-compose.yml
TEST_PROJECT_PATH := /tmp/www

define run
docker-compose -f $(DOCKER_COMPOSE_FILE) run -v $(TEST_PROJECT_PATH):$(TEST_PROJECT_PATH) --rm test make -f /tmp/tests/$(1) TEST_PROJECT_PATH=$(TEST_PROJECT_PATH) $(2)
endef

all: functions base test.1.0.0 clean

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

# @todo ez nem mindig fut le, jogosultság hiba miatt
clean:
	rm -rf $(TEST_PROJECT_PATH)
