# Build nginx proxy deb package
.PHONY: build_proxy
build_proxy: __versionupgrade_nginx
	dpkg -b src nginx-reverse-proxy.deb

# Upgrade the version number. It needs a PACKAGE version!!!
.PHONY: __versionupgrade_nginx
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
			@if [ -z "$(nochange)" ]; then ov=$$(grep Version src/DEBIAN/control | egrep -o '[0-9\.]*'); \
				nv=$$(echo "$${ov%.*}.$$(($${ov##*.}+1))"); \
				sed -i -e "s/Version: *$${ov}/Version: $${nv}/" src/DEBIAN/control; \
				echo "Version: $${nv}"; \
			fi
        else
			sed -i -e "s/Version: *[0-9\.]*/Version: $(VERSION)/" src/DEBIAN/control; \
				echo "Version: $(VERSION)"
        endif
    endif
