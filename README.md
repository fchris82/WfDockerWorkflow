Nginx Reverse Proxy
===================

Install the deb package:

    sudo dpkg -i nginx-reverse-proxy.deb

Build package:

    make rebuild_proxy

Webtown Workflow
================

Use the `install-wf.sh` installer:

    git archive --remote=git@gitlab.webtown.hu:webtown/webtown-workflow.git HEAD installer-wf.sh | tar -x | sh

### Uninstall

    export PATH=$(p=$(echo $PATH | tr ":" "\n" | grep -v "/.webtown-workflow/bin/commands$" | tr "\n" ":"); echo ${p%:})
    rm -rf ~/.webtown-workflow

### Build

    make rebuild_wf
    make build_docker
    make push_docker

OR:

    make rebuild_wf build_docker push_docker

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

## Debug modes

You can call commands with `DEBUG` environment. Example: you can set it in `.gitlab-ci.yml` `variables` section and
then you will be able to analyse the program.

`DEBUG=1`

- echo bash **path** of files and docker container host (simple bash trace)
- add symfony commands `-vvv` argument
- remove (!) makefile calls `-s --no-print-directory` arguments

`DEBUG=2`

- ~`DEBUG=2`
- In bash scripts: `set -x`

`DEBUG=3`

- ~`DEBUG=2`
- Add makefile calls `-d` (debug) argument



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
