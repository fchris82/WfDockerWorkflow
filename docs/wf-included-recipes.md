Included recipes
================

Keep in mind that there would be other recipes. You can list all available recipes with the `wf --config-dump --only-recipes` [command](wf-basic-commands.md#recipe-list). You can reach more information about each recipes with the `wf --config-dump --recipe=[recipe_name]`.

## `symfonyX`

It is a little bit complex but very useful recipe to support Symfony based projects.

| Recipe | Default PHP | Description |
| ------ | ----------- | ----------- |
| `symfony2` | 7.1 | Add a Symfony 2.x support. |
| `symfony3` | 7.1 | Add a Symfony 3.x support. |
| `symfony4` | 7.2 | Add a Symfony 4.x support. |
| `symfony_ez1` | 7.1 | Add an eZ 1.x support. Based on `symfony2`. |
| `symfony_ez2` | 7.1 | Add an eZ 2.x support. Based on `symfony3`. |

#### Docker compose

It adds you two container:

- `engine`: it is a main container with php **cli** and **fpm** support + **composer**. The PHP version is different for each version.
- `web`: a simple **nginx** service with custom config for Symfony


#### Commands

| Command | Example | Description |
| ------- | ------- | ----------- |
| `php` | `wf php -v` | `docker run` a php command. There are differencies between `run` and `exec` |
| `php-exec` | `wf php-exec -i` | `docker exec` a php command |
| `composer` | `wf composer install` | `docker run` a composer command |
| `sf` | `wf sf doctrine:migration:migrate -n  --allow-no-migration` | docker exec` a Symfony console command. It can handle the `app/console` or `bin/console` |

#### Services

**PHP configuration**

- `version`: You can change manually the image **tag** (!!!). The base engine image is the `fchris82/symfony` image. If you want to change it, you can change it at `docker_compose.include` or `docker_compose.extension` section by overriden the `engine.image` value. ( [See here](wf-basic-commands.md#docker-compose) )
- `server.xdebug`: You can enable or disable **xdebug**
- `server.error_log`: You can enable or disable the **PHP error log**
- `server.max_post_size`: You can set the **nginx** `client_max_body_size` and **php** `max_post` and `max_file_upload`
- `server.timeout`: You can set the **nginx** `fastcgi_read_timeout` and **php** `max_execution_time`
- `server.timezone`: You can set a timezone. The default is your system's value from the `/etc/timezone` file.
- `server.locale`: You can set a locale. The default is your systems's value from the `$_ENV[LOCALE]`.

**HTTP Auth**

You can set an nginx http auth. You have to create an `.htpasswd` content on the http://www.htaccesstools.com/htpasswd-generator/ page. Set it to the `http_auth.htpasswd`, and switch on it at the `http_auth.enabled`:

```yml
# .wf.yml
recipes:
    symfony2:
        http_auth:
            enabled: true
            title: It is a secret!
            # test - test
            htpasswd: "test:$apr1$l5M2Ws./$WF/VNTv0wLzfGwDmV8NP90"
```

> After that maybe you need restart: `wf restart`

**Others**

- `env`: The Symfony environment.
- `share_base_user_configs`: In dev mode you may need you **ssh**, **composer cache** or other user things. So default the engine container gets the user's home directory. But sometimes it causes some problems, like CI. You can switch this sharing off.

## `gitlab_ci` and `gitlab_ci_webtown_workflow`

The Gitlab CI needs some spetial configuration:

- disable **sudo**
- enable **pseudo tty** in docker commands with `-T`
- replace the basename of docker containers to prevent the name conflicts

The `gitlab_ci_webtown_workflow` add some extra to above:

- create named local volumes for the data to prevent permission problems (eg: database files with root owner)

## `mysql`

