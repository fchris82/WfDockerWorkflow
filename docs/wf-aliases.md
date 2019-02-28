# Alias recipes

Sometimes we need a program without any configuration, e.g. we want to create the project with this.

We collect here some alises

## Composer

```bash
alias composer='COMPOSER_HOME=$HOME/.config/composer \
                COMPOSER_CACHE_DIR=$HOME/.cache/composer \
                    docker run --rm --interactive --tty \
                    --volume $PWD:/app \
                    --env COMPOSER_HOME \
                    --env COMPOSER_CACHE_DIR \
                    --volume $COMPOSER_HOME:$COMPOSER_HOME \
                    --volume $COMPOSER_CACHE_DIR:$COMPOSER_CACHE_DIR \
                    --volume $SSH_AUTH_SOCK:/ssh-auth.sock \
                    --env SSH_AUTH_SOCK=/ssh-auth.sock \
                    --user $(id -u):$(id -g) \
                    composer'
```

## PHP

```bash
alias php='docker run --rm --interactive --tty \
                --env HOME \
                --volume $PWD:/app \
                --volume $HOME:$HOME \
                --volume $SSH_AUTH_SOCK:/ssh-auth.sock \
                --env SSH_AUTH_SOCK=/ssh-auth.sock \
                --user $(id -u):$(id -g) \
                --workdir /app \
                php:7.3-cli php'
alias php73='docker run --rm --interactive --tty \
                --env HOME \
                --volume $PWD:/app \
                --volume $HOME:$HOME \
                --volume $SSH_AUTH_SOCK:/ssh-auth.sock \
                --env SSH_AUTH_SOCK=/ssh-auth.sock \
                --user $(id -u):$(id -g) \
                --workdir /app \
                php:7.3-cli php'
alias php72='docker run --rm --interactive --tty \
                --env HOME \
                --volume $PWD:/app \
                --volume $HOME:$HOME \
                --volume $SSH_AUTH_SOCK:/ssh-auth.sock \
                --env SSH_AUTH_SOCK=/ssh-auth.sock \
                --user $(id -u):$(id -g) \
                --workdir /app \
                php:7.2-cli php'
alias php71='docker run --rm --interactive --tty \
                --env HOME \
                --volume $PWD:/app \
                --volume $HOME:$HOME \
                --volume $SSH_AUTH_SOCK:/ssh-auth.sock \
                --env SSH_AUTH_SOCK=/ssh-auth.sock \
                --user $(id -u):$(id -g) \
                --workdir /app \
                php:7.1-cli php'
alias php5='docker run --rm --interactive --tty \
                --env HOME \
                --volume $PWD:/app \
                --volume $HOME:$HOME \
                --volume $SSH_AUTH_SOCK:/ssh-auth.sock \
                --env SSH_AUTH_SOCK=/ssh-auth.sock \
                --user $(id -u):$(id -g) \
                --workdir /app \
                php:5-cli php'
```
