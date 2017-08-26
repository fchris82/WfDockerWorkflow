.PHONY: build
build: versionupgrade
	dpkg -b package webtown-workflow.deb

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

.PHONY: tests
tests:
	$(MAKE) -f test/tests.mk
