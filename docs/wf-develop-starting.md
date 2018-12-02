Start developing and debug environments
=======================================

```shell
# Clone the project
$ git clone git@gitlab.webtown.hu:webtown/webtown-workflow.git
# Step into the directory
$ cd webtown-workflow
# Create a `wfdev` helper command to your ~/bin directory and call composer install
$ make init-developing
```

And now you can use the `wfdev` command:

```shell
# original command: [wf|wizard|...] [...etc...]
$ wf --help
# test or debug command: wfdev [wf|wizard|...] [...etc...]
$ wfdev wf --help
```

The `wfdev` run commands in `dev` symfony environment with **xdebug**. If you want to disable it, use the `--no-dev` parameter:

```shell
# Call command with prod SF environment, without xdebug
$ wfdev --no-dev wf --help
# Maybe you need to use the cache clear
$ wfdev --no-dev wf --clean-cache
```

## Background

The `wfdev` adds 2 argument to your calling:

- `--develop`, to switch on wf developing mode
- `--dev`, to set `dev` symfony environment and switch on **xdebug**

`wfdev wf --help` --> `workflow_runner.sh --develop wf --dev --help`

If you use the `--develop` attribute, the program create a docker volume to override the "original" `opt/webtown-workflow` directory with the cloned and edited `[project dir]/webtown-workflow-package/opt/webtown-workflow` directory. Now you can test and check the working with the new code(s). **It is important**: the program will use your `~/.webtown-workflow/config/*` files! If you want to play with configs you have to test with your "host" file, and then you have to copy the changes to "here".

> There are two useful arguments:
>
>  - `--enter` : you can "enter" the `wf` container
>  - `--dev-run` : you can run a command directly in the `wf` container
>
> ```shell
> # Composer update
> $ wfdev wf --dev-run composer update
>
> # Run PHP CS fixer
> $ wfdev wf --dev-run php /opt/webtown-workflow/symfony4/vendor/bin/php-cs-fixer fix --dry-run --config=/opt/webtown-workflow/symfony4/.php_cs.dist
>
> # Run PHPUnit
> $ wfdev wf --dev-run php vendor/bin/phpunit /opt/webtown-workflow/symfony4/tests/Tool
> ```

## Cache !!!

**It is very imporant!** Symfony has caches! When you call the **develop** mode, sometime it cause problems that you have to clean the cache in the docker image! Remember this command:

```
# The --clean-cache command clear the cache in the container!
wfdev wf --clean-cache

# Clean cache in prod mode:
wfdev --no-dev wf --clean-cache
```

## Debug environments

You can call commands with `WF_DEBUG` environment. Example: you can set it in `.gitlab-ci.yml` `variables` section and
then you will be able to analyse the program.

> `make` command arguments: https://www.gnu.org/software/make/manual/html_node/Options-Summary.html
> You can change the behavior in the **webtown-workflow-package/opt/webtown-workflow/lib/_functions.sh** file

### Generally, the `WF_DEBUG`

You can use the `WF_DEBUG` always:

```shell
$ WF_DEBUG=3 wf --help
```

But if you use the `wfdev`, you can use the `-v|-vv|-vvv` arguments:

```shell
$ wfdev -vvv wf --help
```

The `-vvv` == `WF_DEBUG=3`.

`WF_DEBUG=1` or `wfdev -v`

- echo bash **path** of files and docker container host (simple bash trace)
- add symfony commands `-vvv` argument
- remove (!) makefile calls `-s --no-print-directory` arguments.

> Info: makefile command contains `-s --no-print-directory` arguments default.

`WF_DEBUG=2` or `wfdev -vv`

- ~`WF_DEBUG=1`
- In bash scripts: `set -x`
- Add makefile calls `--debug=v` argument

`WF_DEBUG=3` or `wfdev -vvv`

- ~`WF_DEBUG=2`
- Add makefile calls `-d` (debug) argument

### Only `make` command

You can debug the software with some env variables:

| Parameter            | Description                                                                |
| -------------------- | -------------------------------------------------------------------------- |
| MAKE_DISABLE_SILENCE | If you set, make will run **without** `-s --no-print-directory` parameters |
| MAKE_DEBUG_MODE      | If you set, make will run **with** `--debug` parameter. You can set `1` or direct option: https://www.gnu.org/software/make/manual/html_node/Options-Summary.html |
| MAKE_ONLY_PRINT      | If you set, make will run **with** `-n` parameter                          |

Eg:
```bash
$ MAKE_DISABLE_SILENCE=1 MAKE_DEBUG_MODE=1 MAKE_ONLY_PRINT=1 wf list
```

`MAKE_DEBUG_MODE` direct option (we are using the `v` (**verbose**) and `i` (**implicit**) option - https://www.gnu.org/software/make/manual/html_node/Options-Summary.html ):
```bash
$ MAKE_DEBUG_MODE=vi wf list
```

### Compare

|            | WF_DEBUG=0 | WF_DEBUG=1 | WF_DEBUG=2 | WF_DEBUG=3 |
| ---------- | ---------- | ---------- | ---------- | ---------- |
| `MAKE_DISABLE_SILENCE` | `0` | `1` | `1` | `1` |
| `MAKE_DEBUG_MODE`      | `0` | `0` | `v` | `a` |
| `MAKE_ONLY_PRINT`      | ∅ | ∅ | ∅ | ∅ |
| Symfony commands       | ∅ | `-vvv` | `-vvv` | `-vvv` |
| In bash scripts        | ∅ | ∅ | `set -x` | `set -x` |

> Please pay attantion to this! If you create new bash script file, you should start with this:
> ```bash
> # Handle >=1
> if [ ${WF_DEBUG:-0} -ge 1 ]; then
>     [[ -f /.dockerenv ]] && echo -e "\033[1mDocker: \033[33m${WF_DOCKER_HOST_CHAIN}\033[0m"
>     echo -e "\033[1mDEBUG\033[33m $(realpath "$0")\033[0m"
>     SYMFONY_COMMAND_DEBUG="-vvv"
>     DOCKER_DEBUG="-e WF_DEBUG=${WF_DEBUG}"
> fi
>
> # Handle >=2
> [[ ${WF_DEBUG:-0} -ge 2 ]] && set -x
> ```
