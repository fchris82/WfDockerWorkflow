Webtown Workflow install and uninstall
======================================

## Requirements

The **wf** is exactly a docker image: `fchris82/wf` , https://hub.docker.com/r/fchris82/wf/ . So the installation meens:

1. pull docker image
2. register some command line "alias" to use it

**Programs**

- `Docker`
<!-- TODO A mercurial még nincs! -->
- `GIT` or `Mercurial` version controller
- `dnsmasq` or other dns tool. The program will generate local domains with custom TLD (default: `.loc`).

`dnsmasq` install and config:

```shell
# Install
$ sudo apt-get install dnsmasq
# Config for `loc` TLD
$ echo "address=/loc/127.0.0.1" | sudo tee /etc/NetworkManager/dnsmasq.d/loc-tld
# Restart
$ sudo service network-manager restart
```

**User permissions**

It was a target that you can use it without root permission. It is a "local" installation, each user needs to install itself and each user must have permission to use docker (== user is member of `docker` group)

> Add user to docker group: `sudo usermod -aG docker $USER` - https://docs.docker.com/install/linux/linux-postinstall/

And you need to have a right access to `gitlab.webtown.hu` and to the `webtown/webtown-workflow.git` project.

## Install

Use the `install-wf.sh` installer:

    git archive --remote=git@gitlab.webtown.hu:webtown/webtown-workflow.git ${2:-HEAD} install-wf.sh | tar xO > /tmp/install-wf.sh
    chmod +x /tmp/install-wf.sh
    /tmp/install-wf.sh
    rm /tmp/install-wf.sh

### `PATH` upgrade

**Check**

The installer register some symlink in your `$HOME/bin` directory. Check your `PATH` variable:

    $ echo $PATH | grep -oh ~/bin
    /home/chris/bin

If you can see your home bin directory then you needn't do anything else :) But if you got an empty response, you have to do steps below.

**Register**

First of all you have to find your correct "config" file:

- `~/.profile`
- `~/.bashrc`
- `~/.bash_profile`

There are some differences between linux distributions which file you have to edit. Then run:

    $ echo 'export PATH="$PATH:$HOME/bin"' > [file]

After it you have to reload the file with the `source` command:

    $ source [file]

Now you can check the wf command:

    $ which wf
    /home/chris/bin/wf
    $ echo $PATH | grep -oh ~/bin
    /home/chris/bin
    $ wf
    [... help is shown ...]

**ZSH autocomplete**

> The installer try to do some steps automatically.

You can register the autocomplete function:

```shell
$ mkdir -p ~/.zsh/completion
$ ln -sf ~/.webtown-workflow/bin/zsh_autocomplete.sh ~/.zsh/completion/_wf
```

Edit the `~/.zshrc` file:

1. Add if it doesn't exist: `fpath=(~/.zsh/completion $fpath)`
2. After `fpath` add if it doesn't exist: `autoload -Uz compinit && compinit -i`

After save relaod ZSH:

```shell
$ source ~/.zshrc
```

### <a name="vcignore"></a>Version control ignore files

You have to register 1 directory and you should register 1 file in your global ignore file

- `/.wf` --> mandatory!!!
- `/.wf.yml` --> recommanded

> Both of them you can replace custom in the `~/.webtown-workflow/config/env` file. See [Configuration](/docs/wf-configuration.md).

**GIT**

```shell
# Find your global ignore file
$ git config --global core.excludesfile

# IF (!!!) it is doesn't exist then create one
$ touch ~/.gitignore
$ git config --global core.excludesfile ~/.gitignore

# Register the new ignored files
$ echo "/.wf" > ~/.gitignore
$ echo "/.wf.yml" > ~/.gitignore
```

**HG/Mercurial**

Find your global ignore file in the `~/.hgrc` (user) or in the `/etc/hg/.hgrc` (all users!) file, by `[ui]` section:

```ini
[ui]
ignore = ~/.hgignore
```

```shell
# Register the new ignored files
$ echo "/.wf" > ~/.hgignore
$ echo "/.wf.yml" > ~/.hgignore
```

## Upgrade

    $ wf -u

It exactly download from git repository and run the `install-wf.sh` file again.

> If you are a developer, you can upgrade from a custom branch:
>
> ```
> $ wf -u [branch-name]
> ```

## Uninstall

    # Remove all symlink
    $ find ~/bin -type l -ilname "*/.webtown-workflow/*" -delete
    # Remove files
    $ rm -rf ~/.webtown-workflow

    # @todo Remove source files from shell rc files
