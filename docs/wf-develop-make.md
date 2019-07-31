Make commands
=============

## Proxy commands

### `build_proxy`

If you change something in the `nginx-reverse-proxy-package`, you have to rebuild the `nginx-reverse-proxy.dep` file. You can do this with it.

#### Versioning

It will increase the version number automatically! If you want to use the "current" or you want to set a new directly, you can use this environments:

| Parameter       | Description                           |
| -------------   | ------------------------------------- |
| `KEEPVERSION=1` | Doesn't change the version number     |
| `VERSION=(...)` | Set the new version number directly   |

> If `KEEPVERSION` is setted then the `VERSION` doesn't matter.
>
> The program increase the last version number! Eg: `1.2` --> `1.3`, `1.2.1` --> `1.2.2`

```bash
$ make -s build_proxy \[KEEPVERSION=1|VERSION=1.2]
```

#### Example

```bash
# Increase the version number and create the new deb package
$ make -s build_proxy
# Keep the current version number
$ make -s build_proxy KEEPVERSION=1
# Set a new version number by hand
$ make -s build_proxy VERSION=2.0.0
```

### `rebuild_proxy`

It is an alias, equal to `build_proxy`.

## Workflow commands

> You can see some working examples in the [build section](wf-develop-build.md)

### `init-developing`

Create an alias into your `~/bin` directory that helps you in developing. See: [starting section](wf-develop-starting.md)

#### Versioning and examples

It is same as `build_proxy`. You can read everything in that section about versioning and samples.

### `build_docker`

After you created a new deb file - `rebuild_wf` command -, you have to rebuild the docker image that will use the new package.

### `fast_build_docker`

Same as `build_docker` except it runs with cache (without `--no-cache` argument)

### `push_docker`

After you built the new docker image, you have to upload/push it. It checks you are logged in. If you not, it will call the `docker login` command.

### `enter`

If you want to check something you can fast enter the `fchris82/wf` "image".

### `tests`

@todo
