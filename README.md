Docker AZID Framework
=====================

Easy to build an environment for projects. **AZID** is an acronym: **A**lmost **Z**ero **I**nstallation **D**evelopment. The goal is a simple framework which works with only one YAML configuration file and builds the full environment from it. On your computer or on the production server, anywhere.

## Requirements

- Linux system, **bash** . Installed **Oh-My-Zsh** is the best. Please read how you can install it: https://github.com/robbyrussell/oh-my-zsh
- **Docker**. Please follow the installation description: https://docs.docker.com/install/ . Do not forget set permissions: https://docs.docker.com/install/linux/linux-postinstall/ **Docker Compose** isn't required but recommended.
- **dnsmasq** or other - on Ubuntu `dnsmasq-base` is installed, see above...

> **IMPORTANT!** You need permission to run `docker`! See above!

```shell
$ sudo apt update
# Minimal install:
$ sudo apt install docker zsh dnsmasq
# Use it for dev:
# $ sudo apt install docker docker-compose zsh dnsmasq make jq git

# Config for `loc` TLD
$ echo "address=/loc/127.0.0.1" | sudo tee /etc/NetworkManager/dnsmasq.d/loc-tld
# Restart
$ sudo service network-manager restart
# ... If it doesn't start then read below!
```

### Dnsmasq + Docker, if you have any problem

If `dnsmasq` doesn't start (eg: you are useing **Ubuntu 18.xx** or **higher**), or docker network doesn't work (well), try here: [dnsmasq troubleshooting](/docs/dnsmasq-troubleshooting.md)

## Documentations

- [Nginx reverse proxy](/docs/nginx-reverse-proxy.md)
    - [Dnsmasq troubleshooting](/docs/dnsmasq-troubleshooting.md)
- Using WF
    - [Install, upgrade and uninstall](/docs/wf-install.md)
    - [Configuration](/docs/wf-configuration.md)
    - [Basic commands and project configuration](/docs/wf-basic-commands.md)
    - [Included recipes](/docs/wf-included-recipes.md)
    - [Create aliases](/docs/wf-aliases.md)
- Cookbook
    - [Using custom recipes](/docs/wf-cookbook-custom-recipes.md)
    - [Using custom Dockerfile in project](/docs/wf-cookbook-custom-dockerfile.md)
    - [Gitlab CI Deploy(er)](/docs/wf-cookbook-gitlab-ci-deploy.md)
    - [Truncate too long log](/docs/wf-cookbook-truncate-log.md)
    - [Create custom docker repository](/docs/wf-cookbook-custom-repo.md)
    - [Create custom nginx error pages (Symfony recipe!)](/docs/wf-cookbook-custom-nginx-error-pages.md)
    - [Create autocomplete files](/docs/wf-cookbook-autocomplete-files.md)
- Develop WF
    - [How it works?](/docs/wf-develop-base.md)
    - [Start developing, debug environments](/docs/wf-develop-starting.md)
    - [How to build?](/docs/wf-develop-build.md)
    - [Make commands](/docs/wf-develop-make.md)
    - [Variable references](/docs/wf-develop-variables.md)
    - [Release](/docs/wf-develop-release.md)
- Wizard
    - [How to use?](/docs/wizard-using.md)
    - [How to develop?](/docs/wizard-developing.md)
