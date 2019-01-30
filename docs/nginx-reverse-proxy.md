Nginx Reverse Proxy
===================

## How to use?

### Install the deb package

    git archive --remote=git@gitlab.webtown.hu:webtown/webtown-workflow.git ${2:-HEAD} nginx-reverse-proxy.deb | tar xO > /tmp/nginx-reverse-proxy.deb
    sudo dpkg -i /tmp/nginx-reverse-proxy.deb

The configuration files are in the `/etc/nginx-reverse-proxy` directory. Eg: you can change the default port in `config` file.

### Connfiguration

#### Proxy

Edit the `/etc/nginx-reverse-proxy/nginx-proxy.conf` file.

#### HTTP Auth

Every file from `/etc/nginx-reverse-proxy/conf.d/*` (include hidden files too) will be shared with docker. The `*.conf`
files will be loaded by nginx. You can create here a `.htpasswd` file for example with http://www.htaccesstools.com/htpasswd-generator/
page.
After that you have to create a `http-auth.conf` file by `http-auth.conf.example`.

## Developing

After you changed files in the `nginx-reverse-proxy-package` directory you can rebuild the deb package with the following command:

```shell
    $ cd [wf-project-root]
    $ make rebuild_proxy

This will increase the version number and build the new deb package.
