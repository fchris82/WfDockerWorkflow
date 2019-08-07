include packages/nginx-reverse-proxy/Makefile packages/wf-docker-workflow/Makefile

.PHONY: list
list:
	@make -pRrq : 2>/dev/null | egrep "^.PHONY" | awk '{ for (i=2; i<=NF; ++i) print $$i }' | grep "^[^_]" | sort

# @todo
.PHONY: tests
tests:
	$(MAKE) -f test/tests.mk
