
all: test_help test_update

# Installáljuk a programot, hogy elérhető legyen a `wf` parancs
.PHONY: install
install:
    ifeq ("$(wildcard /usr/local/bin/wf)","")
		cp -r /home/user/.ssh /root/.ssh && chown -R root:root /root/.ssh
		cd /tmp && git archive --remote=git@gitlab.webtown.hu:webtown/webtown-workflow.git HEAD webtown-workflow.deb | tar -x
		dpkg -i /tmp/webtown-workflow.deb
		which wf | grep "/usr/local/bin/wf"
    endif

########################################################################################################################
#                                                                                                                      #
#                                                    T E S T S                                                         #
#                                                                                                                      #
########################################################################################################################

# Teszteljük a help-et
.PHONY: test_help
test_help: install
	wf
	wf -h

# Teszteljük az update-et
.PHONY: test_update
test_update: install
	wf -u
