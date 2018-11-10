# Create a symlink file to user ~/bin directory.
.PHONY: init-developing
init-developing:
	mkdir -p ~/bin
	ln -s $(patsubst %/,%,$(dir $(abspath $(lastword $(MAKEFILE_LIST)))))/webtown-workflow-package/opt/webtown-workflow/host/bin/workflow_runner.sh ~/bin/workflow_runner_test
	$(patsubst %/,%,$(dir $(abspath $(lastword $(MAKEFILE_LIST)))))/webtown-workflow-package/opt/webtown-workflow/host/bin/workflow_runner.sh --develop wf --composer-install

.PHONY: rebuild_wf
rebuild_wf: __build_wf __build_cleanup

# It works in the tmp directory!
.PHONY: __build_wf
__build_wf: PACKAGE := webtown-workflow-package
__build_wf: __versionupgrade __build_rsync
	dpkg -b tmp webtown-workflow.deb

# Upgrade the version number. It needs a PACKAGE version!!!
.PHONY: __versionupgrade
__versionupgrade:
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

# We skip the ".gitignore" files. We copy everything to a tmp directory, and we will delete it in the `__build_cleanup` command
.PHONY: __build_rsync
__build_rsync:
	mkdir -p tmp
	rsync -r --delete --force --filter=":- webtown-workflow-package/opt/webtown-workflow/symfony/.gitignore" webtown-workflow-package/* tmp

.PHONY: __build_cleanup
__build_cleanup:
	rm -rf tmp

# nginx reverse proxy
.PHONY: rebuild_proxy
rebuild_proxy: build_proxy

# Build nginx proxy deb package
.PHONY: build_proxy
build_proxy: PACKAGE := nginx-reverse-proxy-package
build_proxy: __versionupgrade
	dpkg -b $(PACKAGE) nginx-reverse-proxy.deb

# DEV!
.PHONY: enter
enter:
	webtown-workflow-package/opt/webtown-workflow/host/bin/workflow_runner.sh /bin/bash

# Create a docker image
.PHONY: build_docker
build_docker:
	docker-compose -f docker/docker-compose.yml build --no-cache

# Create a docker image with cache
.PHONY: fast_build_docker
fast_build_docker:
	docker-compose -f docker/docker-compose.yml build

# Push docker image
.PHONY: push_docker
push_docker: USER_IS_LOGGED_IN := `cat ~/.docker/config.json | jq '.auths."https://index.docker.io/v1/"'`
push_docker:
	if [ "$(USER_IS_LOGGED_IN)" = "null" ]; then \
		docker login; \
	fi
	docker-compose -f docker/docker-compose.yml push

# @todo
.PHONY: tests
tests:
	$(MAKE) -f test/tests.mk
