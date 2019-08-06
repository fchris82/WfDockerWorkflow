Nginx Reverse Proxy
===================

## How to use?

### Install the deb package

Select you preferred way (`wget` OR `curl` OR `git`):

```shell
# Use wget
wget https://raw.githubusercontent.com/fchris82/WfDockerWorkflow/master/nginx-reverse-proxy.deb -P /tmp/
sudo dpkg -i /tmp/nginx-reverse-proxy.deb

# --------------------
# Use curl
curl -o /tmp/nginx-reverse-proxy.deb https://raw.githubusercontent.com/fchris82/WfDockerWorkflow/master/nginx-reverse-proxy.deb
sudo dpkg -i /tmp/nginx-reverse-proxy.deb

# --------------------
# Use git
git archive --remote=git@github.com:fchris82/WfDockerWorkflow.git ${2:-HEAD} nginx-reverse-proxy.deb | tar xO > /tmp/nginx-reverse-proxy.deb
sudo dpkg -i /tmp/nginx-reverse-proxy.deb
```

The configuration files are in the `/etc/nginx-reverse-proxy` directory. Eg: you can change the default port in `config` file.

### Connfiguration

#### Proxy

Edit the `/etc/nginx-reverse-proxy/nginx-proxy.conf` file.

#### HTTP Auth

Every file from `/etc/nginx-reverse-proxy/conf.d/*` (include hidden files too) will be shared with docker. The `*.conf`
files will be loaded by nginx. You can create here a `.htpasswd` file for example with http://www.htaccesstools.com/htpasswd-generator/
page.
After that you have to create a `http-auth.conf` file by `http-auth.conf.example`.

#### Change the 503 page

Default 503 page lists every available hosts. If you are using this proxy on a prod server maybe you want to change it.
You can find the page template in `/etc/nginx-reverse-proxy/nginx-proxy-503.tmpl` . There is a backup file if you want
restore the original version (`/etc/nginx-reverse-proxy/nginx-proxy-503.tmpl.orig`)

## Developing

After you changed files in the `packages/nginx-reverse-proxy` directory you can rebuild the deb package with the following command:

```shell
    $ cd [wf-project-root]
    $ make rebuild_proxy

This will increase the version number and build the new deb package.
