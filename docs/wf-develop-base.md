How it works?
=============

> For developing you need the `jq` program!

```
                                           +----------------------+
   +---------------+      Symfony FW       | Docker Compose files |     = environment
   | YML config(s) | +-------------------> | Makefiles            |     = controll
   +---------------+       (Build)         | .SH files            |     = controll
                              ^            +----------------------+
                              |
                              |
                         +----+----+
                         | Recipes |
                         +---------+
```

## Why ...?

### Why Y(A)ML?

Yaml - http://yaml.org/ - is an easy to use and read config format. Popular and well supported. And the Docker Compose uses this format too. Much more comfortable than XML.

### Why Symfony?

I know it well and I work with it. Well documented and there are lot of helpful library for command lines or create template files: https://symfony.com/ and https://twig.symfony.com/ .

### Why Docker Compose?

Again, easy to use, well documented solution to define even complex environments. Docker Compse gives us simple controlls too.

### Why shell scripts?

Sometimes we need shell scripts to do something. And it could be the fastest way to do it.

### Why makefile?

OK, it looks an odd-one-out and unnecessary. At first glance. In spite of lot of mistakes it has two important advantage:

1. Fast
2. "Easy" to create custom "commands" (easier than create same functionality in pure bash script)

You can define micro services and create connections between them. You can imagine like a "bash yaml" file with key-value pairs, where the key a command name.

### Why run all of them in a docker container that handle other docker containers?

The first version was needed to install. It was a little bit faster, but you had to install with `sudo` or root permission. Now the "install" create only some `.sh` files and symlinks in your **home** directory. So you don't need `sudo` or root permission, and every user can decide to want or don't want to use it. Oh, and there were somne problems with depends. So it is maybe a little bit harder to develop, debug, test or extend, but more simple and clearer to use it. And all you need is the `docker` group.

### Why are deb packages used?

It is simple to use, develop and install, handle versions. You needn't know any special things.

## The steps

I tried as much function to solve as many as I could. Symfony is "slow", I tried to minimise using.

0. Call the **fchris82/wf** docker image (`docker run`)
1. Create hash number from by config yml files. --> \[HASH]
2. Read the wf version --> \[WF_VERSION]
3. Create base makefile name --> \[HASH].\[WF_VERSION].mk
4. If the file doesn't exist --> create by Symfony with environments and recipe files
5. Call the base makefile
6. In most of case a docker or docker-compose command runs

<!-- TODO van egy --dev paraméter, amivel állítólag xdebug-gal futnak a dolgok -->
