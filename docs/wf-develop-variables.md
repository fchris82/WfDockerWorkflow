Base, makefile variables
========================

## Base

| Name | Type | Default | Description |
| ---- | ---- | ------- | ----------- |
| `ARGS` | String |  | Space separated args from command: `wf [command] [ARGS="arg1 arg2 arg3"]` |
| `PROJECT_INFO` | Defined text / Exported |  | A template of the project info, for the `wf info` command |
| `PROJECT_HELP` | Defined test / Exported |  | Project help template |
| `CURDIR` | Makefile var |  | https://www.gnu.org/software/make/manual/html_node/Quick-Reference.html |
| `VERSION` | String |  | The framework version what we are currently using. See `webtown-workflow-package/opt/webtown-workflow/versions/Makefile.X.X.X` files, where `X.X.X` is the version. |
| `PROJECT_DIRECOTRY` | String | `.` | Project directory name, if the project is a subdirectory. *Depricated* |
| `BASE_DIRECTORY` | String | 'VCS root' or `pwd` | The project base directory, where we try to find eg `.wf.yml` file |
| `DOCKER_DATA_DIR` | String |  | Where the docker put the data files, eg mysql files. You should ignore this directory from jor VCS. |
| `DOCKER_ENVIRONMENTS` | String |  | You can register custom environment variables to begining of the `CMD_DOCKER_ENV` variable. You can define new variables, you can't override existing with this. |
| `DOCKER_CLI_NAME` | String | `engine` | You have to set a default **cli** container. The program will use it by default. |
| `DOCKER_SHELL` | String | `/bin/bash` | The shell what we call eg `enter` command |
| `SHARED_DIRS` | String |  | *DEPRICATED* **It looks like we haven't used it for a while** |
| `SUDO` | String | `sudo` | It was important because of Gitlab CI runner. **It looks like we haven't used it for a while** |
| `CMD_MAKE` | String | `make ...` | You can call make commands with this. It contains the main parameters! |

## User settings

| Name | Type | Default | Description |
| ---- | ---- | ------- | ----------- |
| `WWW_DATA_UID` | Int | *User ID* | The user ID what the program needs to use. |
| `WWW_DATA_GID` | Int | *GID of /var/run/docker.sock file* | The GID what the program needs to use. |
| `SSH_PATH` | String | `~/.ssh` | Sometimes we need the path of user SSH directory. Eg: deploying |
| `DOCKER_USER` | String | `$(WWW_DATA_UID):$(WWW_DATA_GID)` | We will set it by the `--user` argument: https://docs.docker.com/compose/reference/exec/ and https://docs.docker.com/compose/reference/run/ |

## Docker and docker-compose command variables

| Name | Type | Default | Description |
| ---- | ---- | ------- | ----------- |
| `CMD_DOCKER_ENV` | Defined string |  | Collect of environment variables: `[CMD_DOCKER_ENV] ... docker-compose ...` |
| `FILE_ENVS` | String |  | The content of the `WF_ENV_FILE_NAME` / `.wf.env` file: `[CMD_DOCKER_ENV] [FILE_ENVS] ... docker-compose ...` AND (!!!) we get it to the container too: `... docker-compose -e [FILE_ENVS] ...` |
| `COMMAND_ENVS` | String |  | You can add new or change/override existing env variables from command line: `wf -e NEWVAR=12 -e OLDVAR=newvalue` Using: `... [COMMAND_ENVS] ... docker-compose ...` AND (!!!) we get it to the container too: `... docker-compose -e [COMMAND_ENVS] ...` |
| `DOCKER_BASENAME` | String | `[username]p[project name]` | Docker project name, see the `-p, --project-name` argument: https://docs.docker.com/compose/reference/overview/ |
| `DOCKER_PSEUDO_TTY` | ``/`-T` |  | You can switch the TTY off. By default docker-compose has switched on TTY. It should be eg at gitlab runner. |
| `DOCKER_CONFIG_FILES` | Array |  | Docker compose file list, see `-f, --file` argument: https://docs.docker.com/compose/reference/overview/ |
| `DOCKER_EXEC` | Defined cmd |  | A template command for executing: `docker exec ...` Not `docker-compose`! |
| `CMD_DOCKER_RUN` | Defined cmd |  | Base of a `... docker-compose run --rm ...` command |
| `CMD_DOCKER_EXEC` | Defined cmd |  | Base of a `... docker-compose exec ...` command |
| `CMD_DOCKER_RUN_CLI` | Defined cmd |  | Base of a `... docker-compose exec ...` command in `DOCKER_CLI_NAME` container |
| `CMD_DOCKER_EXEC_CLI` | Defined cmd |  | Base of a `... docker-compose exec ...` command in `DOCKER_CLI_NAME` container |

## Amended

| Name | Type | Default | Description |
| ---- | ---- | ------- | ----------- |
| `CI` | Boolean | - | You have to use it, when the program run in CI. |
| `WF_TTY` | Boolean | `1` | You can handle TTY with this. |
| `XDEBUG_ENABLED` | Boolean | `0` | You can handle WF container xdebug option with this. You can switch it on with `--dev` argument. |
| `WF_DEBUG` | Boolean | `0` | You can handle WF container xdebug option with this. You can switch it on with `--dev` argument. |
| `DOCKER_RUN`  | Boolean | `0` | If it is set, the program will know that it isn't `exec`, it is a `run` |
| `APP_ENV`  | String | `prod` | The Symfony environment of the WF. *Not the project!* |

## Information variables

Don't modified these!

| Name | Type | Default | Description |
| ---- | ---- | ------- | ----------- |
| `LOCAL_USER_ID` | Int | *User ID* | User ID |
| `LOCAL_USER_NAME` | String | *User name* | User name |
| `LOCAL_USER_HOME` | String | *~* | User home directory |
