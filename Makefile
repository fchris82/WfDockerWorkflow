define DEV_SH_FILE_CONTENT
#!/bin/bash
# Debug mode
#set -x

DEV="--dev"
WF_DEBUG=0
while [ "$${1:0:1}" == "-" ]; do
    case "$$1" in
        --no-dev)
            DEV=""
            ;;
        -v)
            WF_DEBUG=1
            ;;
        -vv)
            WF_DEBUG=2
            ;;
        -vvv)
            WF_DEBUG=3
            ;;
        *)
            echo "Invalid argument: '$$1' Usage: $$0 {--no-dev|-v|-vv|-vvv} ..."
            exit 1
    esac
    shift
done

# COMMAND
CMD=$$1
shift

WF_DEBUG=$${WF_DEBUG} $(patsubst %/,%,$(dir $(abspath $(lastword $(MAKEFILE_LIST)))))/webtown-workflow-package/opt/webtown-workflow/host/bin/workflow_runner.sh --develop $$CMD $$DEV $$@
endef
export DEV_SH_FILE_CONTENT

# Create a symlink file to user ~/bin directory.
.PHONY: init-developing
init-developing:
	mkdir -p ~/bin
	@echo "$$DEV_SH_FILE_CONTENT" > ~/bin/wfdev && chmod +x ~/bin/wfdev
	$(patsubst %/,%,$(dir $(abspath $(lastword $(MAKEFILE_LIST)))))/webtown-workflow-package/opt/webtown-workflow/host/bin/workflow_runner.sh --develop wf --dev --composer-install --dev

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
    # We automatically change in master and develop branch!
    # Don't use variable in ifeq! The $(shell) is only way!
    ifneq ($(shell git rev-parse --abbrev-ref HEAD),master)
        ifneq ($(shell git rev-parse --abbrev-ref HEAD),develop)
			$(eval nochange = 1)
        endif
    endif
    ifeq (,$(KEEPVERSION))
        ifeq (,$(VERSION))
            # Original Version + New Version
			@if [ -z "$(nochange)" ]; then ov=$$(grep Version $(PACKAGE)/DEBIAN/control | egrep -o '[0-9\.]*'); \
				nv=$$(echo "$${ov%.*}.$$(($${ov##*.}+1))"); \
				sed -i -e "s/Version: *$${ov}/Version: $${nv}/" $(PACKAGE)/DEBIAN/control; \
				echo "Version: $${nv}"; \
			fi
        else
			sed -i -e "s/Version: *[0-9\.]*/Version: $(VERSION)/" $(PACKAGE)/DEBIAN/control; \
				echo "Version: $(VERSION)"
        endif
    endif

# We skip the ".gitignore" files. We copy everything to a tmp directory, and we will delete it in the `__build_cleanup` command
# @see https://stackoverflow.com/a/50059607/99834
.PHONY: __build_rsync
__build_rsync:
	mkdir -p tmp
	rsync -r --delete --delete-excluded --delete-before --force \
        --exclude=.git \
        --exclude-from="$$(git -C webtown-workflow-package ls-files \
            --exclude-standard -oi --directory >.git/ignores.tmp && \
            echo .git/ignores.tmp)" \
        webtown-workflow-package/* tmp

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

.PHONY: __get_image_tag
# Don't use this variable in ifeq!!!
__get_image_tag: GIT_BRANCH := $(shell git rev-parse --abbrev-ref HEAD)
__get_image_tag:
    # Don't use variable in ifeq! The $(shell) is only way!
    ifeq ($(shell git rev-parse --abbrev-ref HEAD),master)
		$(eval IMAGE=fchris82/wf)
    else
		$(eval IMAGE=$(shell echo "fchris82/wf:$$(basename $(GIT_BRANCH))"))
    endif

# Create a docker image
.PHONY: build_docker
build_docker: __get_image_tag
	docker build --no-cache -t $(IMAGE) .

# Create a docker image with cache
.PHONY: fast_build_docker
fast_build_docker: __get_image_tag
	docker build -t $(IMAGE) .

# Push docker image
.PHONY: push_docker
push_docker: USER_IS_LOGGED_IN := `cat ~/.docker/config.json | jq '.auths."https://index.docker.io/v1/"'`
push_docker: __get_image_tag
	if [ "$(USER_IS_LOGGED_IN)" = "null" ]; then \
		docker login; \
	fi
	docker push $(IMAGE)

.PHONY: phpunit
phpunit:
	~/bin/wfdev wf --sf-run bin/phpunit

.PHONY: phpcsfix
phpcsfix:
	~/bin/wfdev wf --sf-run vendor/bin/php-cs-fixer fix

# @todo
.PHONY: tests
tests:
	$(MAKE) -f test/tests.mk
