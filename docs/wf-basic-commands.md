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

## List configuration options

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
