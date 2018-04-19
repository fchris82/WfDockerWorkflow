Install
=======

    docker run --rm --interactive --tty \
        --volume $PWD:/app \
        --user $(id -u):$(id -g) \
        composer install

Run
===

    docker run -it \
        -v "$PWD":/usr/src \
        -v "~/.ssh":/home/root/.ssh
        --user $(id -u):$(id -g) \
        -w /usr/src \
        php:7.1-cli vendor/bin/dep deploy demo

