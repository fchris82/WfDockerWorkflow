# Build nginx proxy deb package
.PHONY: build_proxy
build_proxy: MAKEFILE_PATH := $(patsubst %/,%,$(dir $(abspath $(lastword $(MAKEFILE_LIST)))))
build_proxy: __versionupgrade_nginx
	dpkg -b $(MAKEFILE_PATH)/src $(MAKEFILE_PATH)/nginx-reverse-proxy.deb

# Upgrade the version number. It needs a PACKAGE version!!!
.PHONY: __versionupgrade_nginx
__versionupgrade_nginx: MAKEFILE_PATH := $(patsubst %/,%,$(dir $(abspath $(lastword $(MAKEFILE_LIST)))))
__versionupgrade_nginx: RELATIVE_SRC_PATH=$(shell realpath --relative-to="$(CURDIR)" "$(MAKEFILE_PATH)/src")
__versionupgrade_nginx:
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
			@if [ -z "$(nochange)" ]; then ov=$$(grep Version $(RELATIVE_SRC_PATH)/DEBIAN/control | egrep -o '[0-9\.]*'); \
				nv=$$(echo "$${ov%.*}.$$(($${ov##*.}+1))"); \
				sed -i -e "s/Version: *$${ov}/Version: $${nv}/" $(RELATIVE_SRC_PATH)/DEBIAN/control; \
				echo "Version: $${nv}"; \
			fi
        else
			sed -i -e "s/Version: *[0-9\.]*/Version: $(VERSION)/" $(RELATIVE_SRC_PATH)/DEBIAN/control; \
				echo "Version: $(VERSION)"
        endif
    endif
