Build
=====

> You can see more information about commands: [make commands](wf-develop-make.md)

After modifications you have to create a new `.deb` package, and a new `fchris82/wf` docker image from this package.

    make rebuild_wf
    make build_docker
    make push_docker

OR:

    make -s rebuild_wf build_docker push_docker
