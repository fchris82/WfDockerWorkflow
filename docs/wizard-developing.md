Wizard developing
=================

## The `--dev` argument

Use this argument first, if you want to use the xdebug:

```shell
$ wizard --dev
$ wizard --dev --config
#        ^^^^^ First!
# This is invalid:
# $ wizard --config --dev
#          ^^^^^^^^ ^^^^^ Second is the --dev
```
