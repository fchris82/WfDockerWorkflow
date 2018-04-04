.PHONY: rebuild_wf
rebuild_wf: build_wf cleanup

.PHONY: build_wf
build_wf: PACKAGE := webtown-workflow-package
build_wf: versionupgrade rsync
	dpkg -b tmp webtown-workflow.deb

.PHONY: versionupgrade
versionupgrade:
    ifeq (,$(KEEPVERSION))
        ifeq (,$(VERSION))
            # Original Version + New Version
			ov=$$(grep Version $(PACKAGE)/DEBIAN/control | egrep -o '[0-9\.]*'); \
				nv=$$(echo "$${ov%.*}.$$(($${ov##*.}+1))"); \
				sed -i -e "s/Version: *$${ov}/Version: $${nv}/" $(PACKAGE)/DEBIAN/control; \
				echo "Version: $${nv}"
        else
			sed -i -e "s/Version: *[0-9\.]*/Version: $(VERSION)/" $(PACKAGE)/DEBIAN/control; \
				echo "Version: $(VERSION)"
        endif
    endif

.PHONY: rsync
rsync:
	mkdir -p tmp
	rsync -r --delete --force --filter=":- webtown-workflow-package/opt/webtown-workflow/symfony/.gitignore" webtown-workflow-package/* tmp

.PHONY: cleanup
cleanup:
	rm -rf tmp

# nginx reverse proxy
.PHONY: rebuild_proxy
rebuild_proxy: build_proxy

.PHONY: build_proxy
build_proxy: PACKAGE := nginx-reverse-proxy-package
build_proxy: versionupgrade
	dpkg -b $(PACKAGE) nginx-reverse-proxy.deb

# DEV!
.PHONY: enter
enter:
	webtown-workflow-package/opt/webtown-workflow/host/workflow_runner.sh /bin/bash

.PHONY: build_docker
build_docker:
	docker-compose -f docker/docker-compose.yml build --no-cache

.PHONY: push_docker
push_docker:
	docker login
	docker-compose -f docker/docker-compose.yml push

# @todo
.PHONY: tests
tests:
	$(MAKE) -f test/tests.mk
