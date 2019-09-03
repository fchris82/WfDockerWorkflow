WF configuration
================

In most of case the default configuration is more than enough, but if you have spetial needs, you can change some things in the `~/.wf-docker-workflow/config/env` file.

**Don't change** these values except you know what are you doing and what will be the results!

| Parameter | Default | Description |
|:--------- |:------- |:----------- |
| `WF_PROGRAM_REPOSITORY` | `git@github.com:fchris82/WfDockerWorkflow.git` | The `wf -u` command tries to download the new version from here. |
| `WF_WORKING_DIRECTORY_NAME` | `.wf` | The program will be generate project configuration in this directory. [You have to register this name in the version control global ignore file](/docs/wf-install.md#vcignore)! |
| `WF_CONFIGURATION_FILE_NAME` | `.wf.yml` | The program will look for this file or its `.dist` version in the project directory. [It is recommanded to register this name in the version control global ignore file](/docs/wf-install.md#vcignore)! |
| `WF_ENV_FILE_NAME` | `.wf.env` | You can create an environment file for docker containers. |
| `WF_SYMFONY_ENV` | `prod` | **For developing.** You can change the symfony environment to `dev`. |
| `WF_XDEBUG_ENABLED` | `0` | **For developing.** You can switch the *xdebug* on. |
| `WF_DEFAULT_LOCAL_TLD` | `.loc` | You can change the default TLD with that the local domains are created. |
| `WF_HOST_TIMEZONE` | System default | Some recipe needs correct *timezone* |
| `WF_HOST_LOCALE` | System default | Some recipe needs the *local* information |
