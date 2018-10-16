Nginx Reverse Proxy
===================

### Install the deb package

    git archive --remote=git@gitlab.webtown.hu:webtown/webtown-workflow.git ${2:-HEAD} nginx-reverse-proxy.deb | tar xO > /tmp/nginx-reverse-proxy.deb
    sudo dpkg -i /tmp/nginx-reverse-proxy.deb

The configuration files are in the `/etc/nginx-reverse-proxy` directory. Eg: you can change the default port in `config` file.

### Build package

For developers:

    make rebuild_proxy

### Connfiguration

#### Proxy

Edit the `/etc/nginx-reverse-proxy/nginx-proxy.conf` file.

#### HTTP Auth

Every file from `/etc/nginx-reverse-proxy/conf.d/*` (include hidden files too) will be shared with docker. The `*.conf`
files will be loaded by nginx. You can create here a `.htpasswd` file for example with http://www.htaccesstools.com/htpasswd-generator/
page.
After that you have to create a `http-auth.conf` file by `http-auth.conf.example`.
