Nginx Reverse Proxy
===================

### Install the deb package

    sudo dpkg -i nginx-reverse-proxy.deb

The configuration files are in the `/etc/nginx-reverse-proxy` directory. Eg: you can change the default port in `config` file.

### Build package

For developers:

    make rebuild_proxy

Webtown Workflow
================

Use the `install-wf.sh` installer:

    git archive --remote=git@gitlab.webtown.hu:webtown/webtown-workflow.git ${2:-HEAD} install-wf.sh | tar xO > /tmp/install-wf.sh
    chmod +x /tmp/install-wf.sh
    /tmp/install-wf.sh
    rm /tmp/install-wf.sh

### Uninstall

    export PATH=$(p=$(echo $PATH | tr ":" "\n" | grep -v "/.webtown-workflow/bin/commands$" | tr "\n" ":"); echo ${p%:})
    rm -rf ~/.webtown-workflow

### Developers: build

    make rebuild_wf
    make build_docker
    make push_docker

OR:

    make rebuild_wf build_docker push_docker

FULL:

    make rebuild_wf build_docker push_docker && cp webtown-workflow.deb ../gitlab-runner-docker/etc && cd ../gitlab-runner-docker && make stop rebuild start && cd ../webtown-workflow && wf -u

### Upgrade

Default:

    wf -u

From custom branch:

    wf -u [branch-name]

### Debug

You have to use the `--develop` argument

    cd [project_dir]
    [workflow_root_path]/webtown-workflow-package/opt/webtown-workflow/host/bin/workflow_runner.sh --develop [wf|wizard|...] [...etc...]

Or you can create a symlink:

    mkdir -p ~/bin
    ln -s [workflow_root_path]/webtown-workflow-package/opt/webtown-workflow/host/bin/workflow_runner.sh ~/bin/workflow_runner_test
    [workflow_root_path]/webtown-workflow-package/opt/webtown-workflow/host/bin/workflow_runner.sh --develop wizard --install
    cd [project_dir]
    workflow_runner_test --develop [wf|wizard|...] [...etc...]
    
Or you can create a symlink with makefile:

    cd [workflow_root_path]
    make init-test
    cd [project_dir]
    workflow_runner_test --develop [wf|wizard|...] [...etc...]

### Debug modes

You can call commands with `DEBUG` environment. Example: you can set it in `.gitlab-ci.yml` `variables` section and
then you will be able to analyse the program.

`DEBUG=1`

- echo bash **path** of files and docker container host (simple bash trace)
- add symfony commands `-vvv` argument
- remove (!) makefile calls `-s --no-print-directory` arguments

`DEBUG=2`

- ~`DEBUG=1`
- In bash scripts: `set -x`

`DEBUG=3`

- ~`DEBUG=2`
- Add makefile calls `-d` (debug) argument

### Available configuration parameters

List all:

    wf --config-dump
    
List only names:

    wf --config-dump --only-recipes

List only a recipe:

    wf --config-dump --recipe=symfony3

Save to a file to edit:

    wf --config-dump --no-ansi > .wf.yml

OLD Uninstall
=============

- remove:
```
sudo dpkg -r webtown-workflow
```
- nginx-proxy reset
```
docker stop nginx-reverse-proxy
docker rm nginx-reverse-proxy
docker network rm reverse-proxy
```
- (/etc/bash.bashrc /etc/zsh/zshrc) fájlokból az update check törlése:
```
sudo vi /etc/zsh/zshrc
sudo vi /etc/bash.bashrc
sudo rm -rf /usr/local/bin/wf
sudo rm -rf /usr/local/bin/wizard
rm -rf ~/.zsh/completion/_wf
```

Gitlab CI Deploy(er)
====================

Create an SSH key, and add private key to Secrets (eg: `SSH_PRIVATE_KEY` and `SSH_KNOWN_HOSTS`):

    ssh-keyscan -H [host]

In `.gitlab-ci.yml` file:

```yaml
deploy:demo:
    stage: deploy
    script:
        - ENGINE=$(DEBUG=0 wf ps -q engine)
        - SSH_PATH=/usr/local/etc/ssh
        # Create SSH path (with root user!)
        - docker exec -i $ENGINE mkdir -p $SSH_PATH
        - docker exec -i $ENGINE chown $(id -u) $SSH_PATH
        # Create SSH files (with "gitlab user")
        - docker exec -i -u $(id -u) $ENGINE chmod 700 $SSH_PATH
        - docker exec -i -u $(id -u) $ENGINE bash -c "echo '$SSH_PRIVATE_KEY' | tr -d '\r' > $SSH_PATH/id_rsa"
        - docker exec -i -u $(id -u) $ENGINE chmod 600 $SSH_PATH/id_rsa
        - docker exec -i -u $(id -u) $ENGINE bash -c "echo '$SSH_KNOWN_HOSTS' > $SSH_PATH/known_hosts"
        # Reconfigure the SSH
        - docker exec -i $ENGINE bash -c "echo '    IdentityFile $SSH_PATH/id_rsa' >> /etc/ssh/ssh_config"
        - docker exec -i $ENGINE bash -c "echo '    UserKnownHostsFile $SSH_PATH/known_hosts' >> /etc/ssh/ssh_config"
        # Check changes
        - docker exec -i $ENGINE cat /etc/ssh/ssh_config
        - docker exec -i -u $(id -u) $ENGINE ls -al $SSH_PATH
```

Cookbook
========

## Run PHP Unit tests

You can create unit test and run.

    cd [where-the-wizard.sh-is]
    ./wizard.sh --debug symfony4/bin/phpunit -c symfony4

## Use custom recipes

Create or download your own recipes what you want to use. You can put them directly to the `~/.webtown-workfow/recipes` directory
or you can put anywhere and create a symlink to the `~/.webtown-workfow/recipes` directory:

    ln -s /my/own/recipes/MyOwnRecipe ~/.webtown-workflow/recipes/MyOwnRecipe

You have to reload the cache:

    wf --reload

> If you use an existing recipe directory name, you will override the original recipe! 

## Symfony recipes

### Custom xdebug config

Create your own `xdebug.ini` in your home and get to project (yes, you must use `dist` in container!):

```yaml
[...]

docker_compose:
    extension:
        services:
            engine:
                volumes:
                    - "~/xdebug.ini:/usr/local/etc/php/conf.d/xdebug.ini.dist:ro"
```

> You don't have to look after the value of `xdebug.remote_host`. It will be configured automatically.

### Use custom Dockerfile

Create your custom Dockerfile (eg `.docker/engine/Dockerfile`):

```dockerfile
FROM fchris82/symfony:php7.1

RUN apt-get update \
    && apt-get install -y libcurl4-openssl-dev make \
        gcc pkg-config libreadline-dev libgdbm-dev zlib1g-dev \
        libyaml-dev libffi-dev libgmp-dev openssl libssl-dev \
    && rm -rf /var/lib/apt/lists/*

# PHP ext
RUN docker-php-ext-install pcntl shmop \
    && pecl install mongo && echo "extension=mongo.so" > /usr/local/etc/php/conf.d/mongo.ini
```

Register it in `.wf.yml.dist` file:

```yaml
docker_compose:
    extension:
        services:
            engine:
                # override the original image name!
                image: project_name
                # set the Dockerfile
                build:
                    context: '%wf.project_path%/.docker/engine'
                    dockerfile: Dockerfile

            mongodb:
                image: mongo:3.2
                volumes:
                    - "%wf.project_path%/.docker/.data/mongodb:/data/db"
```

--------------------------------------------------------------------

Webtown Workflow Installer
==========================

## Installation

> You need `sudo` permission!

1. Download the `deb` package from repository
    ```bash
    cd /tmp && git archive --remote=git@gitlab.webtown.hu:webtown/webtown-workflow.git HEAD webtown-workflow.deb | tar -x
    ```
2. Install package:

    ``` bash
    sudo dpkg -i webtown-workflow.deb || sudo apt-get -f install && sudo dpkg -i webtown-workflow.deb
    ```

    A vége azért van ott, ha függőségi probléma lépne fel. Sajnos nem lehet máshogy megoldani.

3. Remove `deb` file:
    ```bash
    rm -f webtown-workflow.deb && cd ~
    ```

### Upgrade the software

> You need `sudo` permission!

    wf -u

### Autocomplete

You can install autocomplete script to ZSH.

    wf --install-autocomplete

## Configuration

If you wish alternative clone repository, change repository parameter in the `/etc/webtown-workflow/config` file

## Usage

```bash
# Show help
wf

# Upgrade
wf -u

# ANY COMPATIBLE PROJECT DIRECTORY
# Project help
wf help

# Project commands
wf list
```

# ★ For developers

## (Re)generate deb package

    make -s [build] (KEEPVERSION=1|VERSION=1.2)

### Actions

| Action           | Description                                                                             |
| ---------------- | --------------------------------------------------------------------------------------- |
| `build`          | **Default.** Build the package                                                          |
| `versionupgrade` | Upgrade the version number in `package/DEBIAN/control` file.                            |

### Parameters

| Parameter       | Description                           |
| -------------   | ------------------------------------- |
| `KEEPVERSION=1` | Doesn't change the version number     |
| `VERSION=(...)` | Set the new version number directly   |

> If `KEEPVERSION` is setted then the `VERSION` doesn't matter.

## Debug mode

You can debug the software with some env variables:

| Parameter           | Description                                                                |
| ------------------- | -------------------------------------------------------------------------- |
| MAKE_DISABLE_SILENC | If you set, make will run **without** `-s --no-print-directory` parameters |
| MAKE_DEBUG_MODE     | If you set, make will run **with** `-d` parameter                          |
| MAKE_ONLY_PRINT     | If you set, make will run **with** `-n` parameter                          |

Eg:
```bash
MAKE_DISABLE_SILENC=1 MAKE_DEBUG_MODE=1 MAKE_ONLY_PRINT=1 wf list
```

## Tests

Általánosan tesztfuttatás:

```bash
make tests
```

> Ezt hívja egyébként: `make -f test/tests.mk`

### Test container

A tesztek egy container-ben futnak. Lásd: `test/docker-compose.yml`. Amennyiben módosítasz a `test/Dockerfile`-on, akkor mindenképpen
futtasd a `rebuild` targetet:

```bash
make -f test/tests.mk rebuild all
```

### Tesztek írása

**Teszt környezet/projekt**

A teszteknek szüksége van egy teszt projektre, amiben futtatva lesznek. A teszteket verziószám szerint kell létrehozni a `test/tests` könyvtárban, pl: `test/tests/test.1.0.0.mk`
Mint látható a fájl elején van megadva, hogy melyik **repo**-ból és melyik **branch**-ből szedjen le tesztprojektet:

```make
TEST_PROJECT_GIT_URL := git@gitlab.webtown.hu:webtown/workflow-test.git
TEST_PROJECT_BRANCH := v1.0.0
```

Érdemes úgy csinálni, hogy minden verzióhoz készül egy külön branch. Tehát ha lesz `2.0.0` verzió, akkor ehhez kell készíteni majd egy `v2.0.0` nevű branch-et.

**`all` target**
Minden tesztnek az elejére csinálj egy `all` targetet, amiben felsorolod a hívandó teszteket. Ne hagyj ki egyet sem:

```make
all: \
    test_update \
    test_list \
    test_info \
    test_docker_config \
    test_sf \
    test_db_export_import
```

**Takarítás**

A tesztekhez csinálj takarító scriptet, lehetőleg ne szemeteld össze a gazdagépet; ugyanakkor lehessen `clean` nélkül is futtatni, hogy csak
részteszteket futtatva ne kelljen mindent előlről létrehozni.

> Mindenképpen figyelj a `<tab>`-okra, a `$$` jelekre és a több soros parancsoknál a `; \` záró részre!

Webtown Project Wizard
======================

## Configuration

If you wish alternative clone repository, change repository parameter in the `/etc/webtown-workflow/repository.txt` file

## Usage

    cd /base/project/src/directory
    wizard

# ★ For developers

## Composer

If you want to run composer command, go to SF directory and run the command:

```
cd [project]/package/opt/webtown-workflow/symfony4
docker run --rm --interactive --tty \
    --volume $PWD/..:/app \
    --workdir /app/symfony4 \
    --user $(id -u):$(id -g) \
    -e APP_ENV=dev \
    composer require --dev symfony/phpunit-bridge
```

## Developing with Symfony

Go to the symfony directory and there you can run the commands:

```bash
cd package/opt/webtown-workflow

# Run tests
wizard.sh -t

# Rebuild docker containers and clean cache
wizard.sh -r

# Clean cache only
rm -rf symfony/var/cache/*

# Enter the container
wizard.sh -e
```

If you want to test in a project, you have to use absolute path to wizard, eg:

```bash
/home/chris/www/webtown-workflow/package/opt/webtown-workflow/wizard.sh
```

### Hogyan hozz létre új Wizard-ot?

Minden Wizard alapvetően arról szól, hogy vmilyen úton-módon fájlokat hoz létre, átalakítja a könyvtár struktúrát vagy vmi hasonlót. Mindegyik Wizard-nak meg kell valósítania a
`WizardInterface`-t, amelyiket "kiválaszthatónak" akarjuk, annak pedig a `PublicWizardInterface`-t is! Jelenleg a rendszer a következő alap wizárdokat ismeri és támogatja:

**AppBundle\Wizard\BaseSkeletonWizard**

A Skeleton Wizard-nál létre kell hozni egy könyvtárat a `/package/opt/webtown-workflow/symfony/src/AppBundle/Resources/skeletons` könyvtárban. Ez a könyvtár fog megfelelni a "projekt gyökerének". Az itt található
könyvtár struktúrát fogja átmásolni a projektbe. Minden fájlt twig-ként kezel, tehát használhatod az `{% if ... %}` vagy a `{{ valtozo }}` megoldásokat. A változók megadását a `setVariables()` metódusban
tudod megtenni, a `$this->ask()` segítségével bekérhetsz a felhasználótól is adatokat. Ez talán a legalkalmasabb a legtöbb feladatra.

> **Tipp:** Megadhatsz több könyvtárat is, ahonnan másolnia kell, így az átfedésben lévő Wizard-oknál nem kell ugyanazt duplikálni. Erre láthatsz példát a `AppBundle/Wizard/Docker/Slim.php` és a
> `AppBundle/Wizard/Docker/Wide/CreateEnvironmentsSkeleton.php` fájlban.

**AppBundle\Wizard\BaseGitCloneWizard**

Git clone-nal tud fájlokat leszedni. Ez leginkább akkor kellhet, amikor inicializálni szeretnél egy projektet.

**AppBundle\Wizard\BaseChainWizard**

Egymás után fűzhetsz wizardokat. Ezekkel komplexebb Wizardok hozhatóak létre, ráadásul van pár extra helper, amivel a futás során `git commit`-okat hozhatsz létre, vagy éppen `composer require`-rel
telepíthetsz.

```php
<?php
class WideExtra extends BaseChainWizard implements PublicWizardInterface
{
    protected function getWizardNames()
    {
        return [
            // Ellenőrizzük, hogy "tiszta-e a terep". Ha nem, itt exception-t dob!
            new CheckGitUncommittedChangesForChain(),
            // Betöltünk egy tetszőleges Wizard-ot. Az alábbi nem public, nem készült belőle service!
            new MoveProjectFiles($this->filesystem),
            // Commitolunk egyet
            new GitCommitWizardForChain('Move project files'),
            // Meghívunk egy másik "unpublic" wizard-ot
            new CreateEnvironmentsSkeleton($this->baseDir, $this->twig, $this->filesystem),

            // Meghívunk egy PUBLIC wizardot, itt elég csak a név megadása
            PhpCsFixSkeleton::class,
            // Futtatjuk `composer require` parancsot
            new ComposerInstallForChain($this),
            // Csinálunk egy git commit-ot megint
            new GitCommitWizardForChain('Add PHP-CS-FIXER'),
        ];
    }

    // [...]
}
```

**Egyéb, AppBundle\Wizard\BaseWizard**

Bármilyen egyéb wizard létrehozható, ehhez használhatjuk a `AppBundle\Wizard\BaseWizard`-ot kiindulási alapnak. A `build()`-ban garázdálkodhatunk szabadon.

> #### Tipps
>
> The PHPStorm can't detect the symfony project. You have to switch on manualy.
>   - Settings » Languages & Frameworks » PHP » Symfony ⟶ **Enable plugins for this project**
>   - Set the directories with `package/opt/webtown-workflow/symfony/` prefix, and perhaps you have to change `app/cache` to `var/cache`
>   - Settings » Other settings » Framework Integration ⟶ Select the **Symfony**
>
> You have to set `src` directory as *Source Root directory* and the `tests` directory as *Test Root directory*:
>   - right click on the directory » Mark directory as... ⟶ \[select\]

Environment extra information
=============================

## Create a docker repository

```
docker run -d \
  -p 5000:5000 \
  --restart=always \
  --name registry \
  -v /mnt/registry:/var/lib/registry \
  registry:2
```

> It is handled in `postinst` file:
>
> You should register the unsecure repository in your local computer in `/etc/docker/daemon.json` :
>
> ```
> {
> "insecure-registries":["amapa.webtown.hu:5000"]
> }
> ```
>
> You have to restart the docker:
> ```
> sudo service docker restart
> ```
