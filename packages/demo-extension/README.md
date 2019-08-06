WF Demo Extension
=================

## How to use it?

Create your demo WF docker container:

### Create docker image

Create your custom `Dockerfile` in `~/.wf-docker-workflow/Dockerfile`:

> You may need a github OAuth token. More information: https://getcomposer.org/doc/articles/troubleshooting.md#api-rate-limit-and-oauth-tokens
> In this case you have to use the `composer config -g github-oauth.github.com <oauthtoken>` command too.

```Dockerfile
FROM fchris82/wf

ARG WF_SYMFONY_ENV=prod
RUN cd ${SYMFONY_PATH} && \
    composer clearcache && \
    composer config repositories.repo-name vcs https://github.com/fchris82/WfDemoExtension.git && \
#    composer config -g github-oauth.github.com <oauthtoken> && \
    wf-composer-require "wf-chris/demo-extension"
```

Build (there is a `.` in the end of the command!):

```bash
$ wf --rebuild
```

### Check

```bash
$ wizard
```

You have to see the `Demo Extension` wizard.
