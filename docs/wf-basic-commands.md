Basic commands
==============

> The commands that start with `--` sign are "globally" commands (+ `-u` and `-h`), you can use them everywhere inspite of the *project commands*.

```shell
# List basic commands
$ wf -h

# Show current version
$ wf --version

# Upgrade
$ wf -u
```

## <a name="recipe-list"></a>List (project) configuration options

You have to create a `.wf.yml` or a `.wf.yml.dist` project configuration file for each project. These commands show you the available options and can you help in any configuration situation.

> The base commands create a colorful response, so you can't use them to put directly a file. If you want to use any commands to put a file then combine the commands with the `--no-ansi` parameter. See below!

```shell
# List ALL available options. It will be very long!
$ wf --config-dump

# List ALL availble recipe names.
$ wf --config-dump --only-recipes

# List configuration options of a concrate recipe
$ wf --config-dump --recipe=mysql
```

**Put the response to file**

These commands create a colorful response. It can help to read the responses on a monitor, but it causes unreadable response in a file. You can disable this "help" with the `--no-ansi` parameter:

```shell
# List ALL available options. It will be very long!
$ wf --no-ansi --config-dump > full-wf-config.yml
#    ^^^^^^^^^               ^^^^^^^^^^^^^^^^^^^^

# List ALL availble recipe names.
$ wf --no-ansi --config-dump --only-recipes > recipes-table.txt
#    ^^^^^^^^^                              ^^^^^^^^^^^^^^^^^^^

# List configuration options of a concrate recipe
$ wf --no-ansi --config-dump --recipe=mysql > .wf.mysql-recipe.yml
#    ^^^^^^^^^                              ^^^^^^^^^^^^^^^^^^^^^^
```

## Basic (project) configuration

### Open a web browser editor \[BETA\]

There are lot of keys so we try to help and have already added an autocomplete editor:

```bash
# It will start a local webserver:
$ wf edit-config

  Editor URL: http://172.17.0.4:8000 ↤ click on the URL to open in browser

PHP 7.2.14 Development Server started at Tue Feb 26 12:46:37 2019
Listening on http://172.17.0.4:8000
Document root is /opt/wf-docker-workflow/symfony4/src/Wf/ConfigEditorBundle/ConfigEditorExtension/server
Press Ctrl-C to quit.
```

Now you can open a browser and load the http://172.17.0.4:8000 . The program will show an ACE editor and filesystem, with help and autocomplete functions. There are some limitations:

- !: you can use it only locally
- !: you can use it only an existing project
- !: you can't create new file
- It isn't nice: The "docker-compose services" autocomplete words can't work "on-the-fly". Maybe you have to run sometimes the `wf reconfigure` command to regenerate autocomplete source files and reload the page.
- It isn't nice: the program will show an error message after you press `CTRL-C`, but just ignore it
- There is a constant `Help` tab where you can see the all of available option with comments
- you can save the file with save button or `CTRL-S` hotkey
- you can show the autocomplete with `CTRL-Space` hotkey

### Summary

```yml
# You can import some other yml files.
imports:

    # Example:
    - .wf.base.yml

# Which WF Makefile version do you want to use? You can combine it with the minimum WF version with the @ symbol: [base]@[wf_minimu
m_version]
version:              # Example: 2.0.0@2.198

    # Which WF Makefile version do you want to use?
    base:                 ~ # Required, Example: 2.0.0

    # You can set what is the minimum WF version.
    wf_minimum_version:   null # Example: 2.198

# You have to set a name for the project.
name:                 ~ # Required

# You can set an alternative docker data directory.
docker_data_dir:      '%wf.target_directory%/.data'

# You can add extra makefile files.
makefile:             [] # Example: ~/dev.mk

# Config the docker compose data.
docker_compose:

    # You can change the docker compose file version.
    version:              '3.4'
    # You can add extra docker-compose.yml files.
    include:              [] # Example: /home/user/dev.docker-compose.yml

    # Docker Compose yaml configuration. You mustn't use the version parameter, it will be automatically.
    extension:            Array

        # Example:
        services:
            web:
                volumes:
                    - ~/dev/nginx.conf:/etc/nginx/conf.d/custom.conf
                environment:
                    TEST:                1

# You can add extra commands.
commands:

    # Prototype
    command:              ~

# The configs of recipes. If you want to disable one from import, set the false value!
recipes:
    # recipes...
```

#### Available placeholders

| Placeholder | Default value | Description |
| ----------- | --------------- | ----------- |
| `%config.name%` |  | The project name from config (`name` parameter). |
| `%wf.target_directory%` | `.wf` | The value of `WF_WORKING_DIRECTORY_NAME` [config parameter](wf-configuration.md). This is the "build/cache" directory. The program will put here the generated files, like **docker-compose** files, **.sh** files and **makefiles** |
| `%wf.project_path%` | "`pwd`" | Dynamically generated string. The project absolute path. |
| `%wf.config_hash%` |  | Dynamically generated string. The hash value of the configuration files (include the first level imports too). See the `imports` project configuration! |
| `%env.*%` |  | The program get you the environment variables too. Eg: `%env.HOME%` |

> Listen to the `%` signs! There are two, one prefix and one postfix!

> You can define custom placeholders in your own recipe!

> You can check the used placeholders when you call `reconfigure` with `-v[vv]` parameter.

#### `imports`

You can load information from other files. There are some limitations with this: you can use the `imports` key in an imported file - "deep import" is allowed -, **BUT** the "changing detector" can't see "these" files! The problem is that there isn't an easy way to read and handle `yaml` files in bash. So if you do some changes in deeper yml configuration files, you have to run the `wf reconfigure` command by hand.

The most common use:

@todo Ezt inkább át kellene nevezni .wf.yml.base-ra, a .dist meg legyen felhasználói példa.

```yml
# In .wf.yml file
imports:
    - .wf.yml.dist
```

> Tipp: Put the `/.wf.yml` into the `.gitignore` or `.hgignore` file to every developer can use its unique settings.

**Order, value overriding**

1. Imported file
2. You can override some (or all) values in the "importing" file!

### `version`

There are 2 versions separately with a `@` sign! First is a "framework" version. The framework define the available basic commands and features. It changes rarely.

The second is a "WF program version", the minimum version that is compatible with the project configuration file! It isn't required, but it may be important.

> As you can see, you can use separately these version numbers.

```yml
# Simple string
version: 2.0.0@2.198
```

```yml
# Array version
version:
    # Required!
    base: 2.0.0
    # Not required
    wf_minimum_version: 2.198
```

### `name`

The program will use this name in many places. Eg: docker container names, dynamic domains, etc... And you can use it also in your custom recipes. Keep it clean, don't use spetial characters or white spaces - for your own sake.

### `docker_data_dir`

In most of case the default value is perfect. If you want to change it, you have to listen the program run a `rm -rf %wf.target_directory%/*` command! So if you use the `%wf.target_directory%` directory and the first letter of the name of top subdirectory isn't a dot, it would be removed!

Correct dirs:

- Start with dot (`.my-own-data`): `%wf.target_directory%/.my-own-data`
- Start with dot the top subdirectory (`.my-own-data`): `%wf.target_directory%/.my-own-data/firstsub/secundsub`
- Outside of the `%wf.target_directory%` directory, but into the project directory: `%wf.project_path%/.data`
- Absolute path outside of the project: `~/project-data/`

"Incorrect" dirs - you will lost data at every reconfigure/rebuild; sometimes it would be what you want to reach:

- Missing dot: `%wf.target_directory%/my-own-data`
- Missing dot for the top subdirectory (`my-own-data`): `%wf.target_directory%/my-own-data/.data`

### `makefile`

You can `include` custom makefiles into the base generated makefile. It will be included **after** the makefiles of recipes!

> **Path limitations**
>
> You can use only "docker volumed" directory, otherwise the `wf` can't reach it while try to include, and you will get "No such file or directory" error. Allowed directories:
>
>  - Project directory: `%wf.project_path%`
>  - Your home directory: `~`
>
> If you are using your home directory, other user can't use this file except it belongs to them too!

**Example**

This example print env values in the **fchris82/wf** container.

```makefile
# ~/example.mk or %wf.project_path%/example.mk
.PHONY: example
example:
# listen to the tab!
	env
```

```yml
# .wf.yml
# ...
makefile:
    - ~/example.mk
    # OR:
    #- '%wf.project_path%/example.mk
```

Now you can call it:

```shell
$ wf example
APP_ENV=prod
WF_DEBUG=
_=/usr/bin/make
ARGS=
PHP_VERSION=7.2.4
WF_DEFAULT_LOCAL_TLD=.loc
...

# playing with args
$ wf example arg1 arg2
.
.
ARGS=arg1 arg2
.
.
```

### `commands`

This is an easy way to add custom (shell) commands. The program will put this commands into a bash script.

> **Limitations**
>
> The scripts will run into the `fchris82/wf` docker container! You can't reach everything on your "local/host" machine!

**Example**

In this example we are creating an `install` and a `reinstall` commands. As you can see, you can use a minimal text formatting - https://symfony.com/doc/current/console/coloring.html .

```yml
# .wf.yml
commands:
    init:
        - mkdir -p .wf/.data/mysql
        # The <info> is green chars
        - echo "<info>✔ Edit the new files before run the <fg=blue;options=bold>install</> command:</info>"
        # This is an IF. If the .wf.yml file doesn't exist.
        - '[[ ! -f ".wf.yml" ]] && printf "imports:\n    - .wf.yml.dist\n    - .wf.dev.yml\n" >> .wf.yml'
        # The <comment> is orange chars
        - echo "   - <comment>.wf.yml</comment>"

    install:
        - wf composer install
        - wf dbreload ${1}
        - echo "<info>✔ Now you can use the project!</info>"

    dbreload:
        - wf up
        # you can use arguments: `wf dbreaload --full`
        - if [ "${1}" == "--full" ]; then wf sf doctrine:database:drop --if-exists --force; fi
        - wf sf doctrine:database:create --if-not-exists
        - wf sf doctrine:migrations:migrate -n --allow-no-migration
        - if [ "${1}" == "--full" ]; then wf sf doctrine:fixtures:load -n; fi
```

Now you can call these commands:

```shell
$ wf init
$ wf install
$ wf dbreload
# With argument
$ wf install --full
$ wf dbreload --full
```

### <a name="#docker-compose"></a>`docker_compose`

Here you can configure docker compose files.

**docker_compose.version**

De recipes template doesn't contain the `version`, because all docer-compose file must use same version number. So, de `version` parameter is the "globally" *docker-compose configuration version*.

**docker_compose.include**

You can add custom docker-comose files. Here there are same limitations what they are at `makefiles` configuration: you can inclue from project path - `%wf.project_path%` - , or your home path - `~`.

**docker_compose.extension**

You can use as a docker-compose file. This block will be put into a `docker-compse.yml` file.

**Example**

```yml
# .wf.yml
# Config the docker compose data.
docker_compose:
    version: '3.4'
    include:
        - '%wf.project_path%/docker/docker-compose.yml'
    extension:
        services:
            web:
                volumes:
                    - ~/dev/nginx.conf:/etc/nginx/conf.d/custom.conf
                environment:
                    TEST: 1
```

**Extension or include?**

Both can be good (or wrong). `docker_compose.extension`'s solution may be readable and helpful if it is short, `docker_compose.include` can be more helpful when it is longer (and you can use the autocomplete despite of the case of `docker_compose.extension`)

## Basic project commands

@todo (up, down, restart, reconfigure)
