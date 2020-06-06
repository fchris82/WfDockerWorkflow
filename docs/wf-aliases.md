# Alias recipes

Sometimes we need a program without any configuration, e.g. we want to create the project with this. What should you pay attention to:

- current directory as workdir: `--volume "$PWD":/app --workdir /app`
- user: `--user $(id -u):$(id -g)`
- home directory: `--volume "$HOME":"$HOME"`
- check other environment variables on the site/documentation of docker image
- if exists, use "cli" version
- if you need something special extension you have to create your own image (See below: How can I make custom image?)
- if the program result is a file for example (or it needs a file), you have to pay attention to use shared volume, otherwise the file will be created inside the container, and you won't find it on your "host" computer!

## How can I make custom image?

In most of case you need some extensions, so you will need a custom image. You have to create a `Dockerfile` eg:

```Dockerfile
FROM php:7.2-cli

RUN apt-get update && apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
    && docker-php-ext-install -j$(nproc) iconv \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd

RUN pecl install redis-4.0.1 \
    && pecl install xdebug-2.6.0 \
    && docker-php-ext-enable redis xdebug
```

Than you have to build your new image with a custom tag name - `php-custom`

```shell
#                            ⬇ There is a dot at the end! See the documentation about "docker build"
$ docker build -t php-custom .
#                 ^^^^^^^^^^
#                 Your unique image name

# If you use custom filename and path
$ docker build -t php-custom -f php.Dockerfile ~/my_dockerfiles
#                 ^^^^^^^^^^    ^^^^^^^^^^^^^^ ^^^^^^^^^^^^^^^^
#                 image tag     Dockerfile     Path of the Dockerfile
```

And here is how the alias looks with your new image:

```bash
alias php='docker run --rm --interactive --tty \
                --env HOME \
                --volume "$PWD":/app \
                --volume "$HOME":"$HOME" \
                --volume $SSH_AUTH_SOCK:/ssh-auth.sock \
                --env SSH_AUTH_SOCK=/ssh-auth.sock \
                --user $(id -u):$(id -g) \
                --workdir /app \
                php-custom php'
#               ^^^^^^^^^^
```

## Examples

## Composer example

https://hub.docker.com/_/composer

> Composer can be a little bit tricky, `composer.json` file sometimes contains the minimum or required PHP version and extensions, 
> you should use the same minimum environment while you are running the `composer install/require/update` command! That's
> why highly recommended creating custom composer image(s) for different environments. 

```bash
alias composer='COMPOSER_HOME=$HOME/.config/composer \
                COMPOSER_CACHE_DIR=$HOME/.cache/composer \
                    docker run --rm --interactive --tty \
                    --volume "$PWD":/app \
                    --env COMPOSER_HOME \
                    --env COMPOSER_CACHE_DIR \
                    --volume $COMPOSER_HOME:$COMPOSER_HOME \
                    --volume $COMPOSER_CACHE_DIR:$COMPOSER_CACHE_DIR \
                    --volume $SSH_AUTH_SOCK:/ssh-auth.sock \
                    --env SSH_AUTH_SOCK=/ssh-auth.sock \
                    --user $(id -u):$(id -g) \
                    composer'
```

## PHP example

https://hub.docker.com/_/php

```bash
alias php='docker run --rm --interactive --tty \
                --env HOME \
                --volume "$PWD":/app \
                --volume "$HOME":"$HOME" \
                --volume $SSH_AUTH_SOCK:/ssh-auth.sock \
                --env SSH_AUTH_SOCK=/ssh-auth.sock \
                --user $(id -u):$(id -g) \
                --workdir /app \
                php:7.4-cli php'
alias php74='docker run --rm --interactive --tty \
                --env HOME \
                --volume "$PWD":/app \
                --volume "$HOME":"$HOME" \
                --volume $SSH_AUTH_SOCK:/ssh-auth.sock \
                --env SSH_AUTH_SOCK=/ssh-auth.sock \
                --user $(id -u):$(id -g) \
                --workdir /app \
                php:7.4-cli php'
alias php73='docker run --rm --interactive --tty \
                --env HOME \
                --volume "$PWD":/app \
                --volume "$HOME":"$HOME" \
                --volume $SSH_AUTH_SOCK:/ssh-auth.sock \
                --env SSH_AUTH_SOCK=/ssh-auth.sock \
                --user $(id -u):$(id -g) \
                --workdir /app \
                php:7.3-cli php'
alias php72='docker run --rm --interactive --tty \
                --env HOME \
                --volume "$PWD":/app \
                --volume "$HOME":"$HOME" \
                --volume $SSH_AUTH_SOCK:/ssh-auth.sock \
                --env SSH_AUTH_SOCK=/ssh-auth.sock \
                --user $(id -u):$(id -g) \
                --workdir /app \
                php:7.2-cli php'
alias php71='docker run --rm --interactive --tty \
                --env HOME \
                --volume "$PWD":/app \
                --volume "$HOME":"$HOME" \
                --volume $SSH_AUTH_SOCK:/ssh-auth.sock \
                --env SSH_AUTH_SOCK=/ssh-auth.sock \
                --user $(id -u):$(id -g) \
                --workdir /app \
                php:7.1-cli php'
alias php5='docker run --rm --interactive --tty \
                --env HOME \
                --volume "$PWD":/app \
                --volume "$HOME":"$HOME" \
                --volume $SSH_AUTH_SOCK:/ssh-auth.sock \
                --env SSH_AUTH_SOCK=/ssh-auth.sock \
                --user $(id -u):$(id -g) \
                --workdir /app \
                php:5-cli php'
```


## Node example - not tested

You can use the `NPM_CONFIG_LOGLEVEL`, eg: `NPM_CONFIG_LOGLEVEL=info` . More information: https://hub.docker.com/_/node and https://github.com/nodejs/docker-node/blob/master/README.md#how-to-use-this-image

```bash
alias node='docker run --rm --interactive --tty \
                --env HOME \
                --env NPM_CONFIG_LOGLEVEL \
                --volume "$PWD":/usr/src/app \
                --volume "$HOME":"$HOME" \
                --user $(id -u):$(id -g) \
                --workdir /usr/src/app \
                node:8 node'

alias npm='docker run --rm --interactive --tty \
                --env HOME \
                --volume "$PWD":/usr/src/app \
                --volume "$HOME":"$HOME" \
                --user $(id -u):$(id -g) \
                --workdir /usr/src/app \
                node:8 node npm'
```

## Iojs example - not tested

https://hub.docker.com/_/iojs

```bash
alias node='docker run --rm --interactive --tty \
                --env HOME \
                --volume "$PWD":/usr/src/app \
                --volume "$HOME":"$HOME" \
                --user $(id -u):$(id -g) \
                --workdir /usr/src/app \
                iojs iojs'
```

## Ruby - not tested

https://hub.docker.com/_/ruby

```bash
alias ruby='docker run --rm --interactive --tty \
                --env HOME \
                --env LANG \
                --volume "$PWD":/usr/src/myapp \
                --user $(id -u):$(id -g) \
                --workdir /usr/src/app \
                ruby:2.5 ruby'
```

## MySQL

https://hub.docker.com/_/mysql

> You have to use the `127.0.0.1` instead of `localhost`! More information about network setting: https://docs.docker.com/engine/reference/run/#network-host

```bash
alias mysql='docker run --rm --interactive --tty \
                --network=host \
                --volume "$PWD":/usr/src/myapp \
                --user $(id -u):$(id -g) \
                mysql mysql'

alias mysqldump='docker run --rm --interactive --tty \
                --network=host \
                --volume "$PWD":/usr/src/myapp \
                --user $(id -u):$(id -g) \
                mysql mysqldump'
```

## MongoDB

https://hub.docker.com/_/mongo

```bash
alias mongo='docker run --rm --interactive --tty \
                --network=host \
                --volume "$PWD":/usr/src/myapp \
                --user $(id -u):$(id -g) \
                mongo mongo'
```
