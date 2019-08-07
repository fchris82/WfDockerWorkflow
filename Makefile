include packages/nginx-reverse-proxy/Makefile packages/wf-docker-workflow/Makefile

.PHONY: list
list:
	@make -pRrq : 2>/dev/null | egrep "^.PHONY" | awk '{ for (i=2; i<=NF; ++i) print $$i }' | grep "^[^_]" | sort

# @todo
.PHONY: tests
tests:
	$(MAKE) -f test/tests.mk

.PHONY: shell
shell:
	PWD=$(CURDIR) CURRENT_UID=$$(id -u):$$(id -g) docker-compose run --rm develop /bin/bash

.PHONY: rootshell
rootshell:
	PWD=$(CURDIR) CURRENT_UID=$$(id -u root):$$(id -g root) docker-compose run --rm develop /bin/bash
