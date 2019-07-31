Build
=====

> You can see more information about commands: [make commands](wf-develop-make.md)

After modifications you have to create a new `.deb` package, and a new `fchris82/wf` docker image from this package.

    make build_docker
    make push_docker

OR:

    make -s build_docker push_docker

> Keep the current version: `KEEPVERSION=1 make build_docker`
