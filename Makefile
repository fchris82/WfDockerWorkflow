.PHONY: rebuild
rebuild: build cleanup

.PHONY: build
build: versionupgrade rsync
	dpkg -b tmp webtown-workflow.deb

.PHONY: versionupgrade
versionupgrade:
    ifeq (,$(KEEPVERSION))
        ifeq (,$(VERSION))
            # Original Version + New Version
			ov=$$(grep Version package/DEBIAN/control | egrep -o '[0-9\.]*'); \
				nv=$$(echo "$${ov%.*}.$$(($${ov##*.}+1))"); \
				sed -i -e "s/Version: *$${ov}/Version: $${nv}/" package/DEBIAN/control; \
				echo "Version: $${nv}"
        else
			sed -i -e "s/Version: *[0-9\.]*/Version: $(VERSION)/" package/DEBIAN/control; \
				echo "Version: $(VERSION)"
        endif
    endif

.PHONY: rsync
rsync:
	mkdir -p tmp
	rsync -r --delete --force --filter=":- package/opt/webtown-workflow/symfony/.gitignore" package/* tmp

.PHONY: cleanup
cleanup:
	rm -rf tmp

# DEV!
.PHONY: enter
enter:
	package/opt/webtown-workflow/host/wf_runner.sh /bin/bash

.PHONY: build_docker
build_docker:
	docker-compose -f docker/docker-compose.yml build --no-cache

.PHONY: push_docker
push_docker:
	docker login
	docker-compose -f docker/docker-compose.yml push

.PHONY: tests
tests:
	$(MAKE) -f test/tests.mk
