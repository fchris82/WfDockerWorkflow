Docker Workflow Framework for Docker Compose
============================================

Easy to build an environment for projects.

## Requirements

- Linux system, **bash** . Installed **Oh-My-Zsh** is the best. Please read how you can install it: https://github.com/robbyrussell/oh-my-zsh
- **Docker**. Please follow the installation description: https://docs.docker.com/install/ . Do not forget set permissions: https://docs.docker.com/install/linux/linux-postinstall/ **Docker Compose** isn't required but recommended.
- **dnsmasq**
- Developing: **make**, **jq**, **git**

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
```

### Ubuntu

On **Ubuntu** - since **18.xx** version - `dnsmasq` won't be able to start!

```shell
# Reconfigure the NetworkManager
sudo sed -i '/^plugins=.*/a dns=dnsmasq' /etc/NetworkManager/NetworkManager.conf
# Stop systemd-resolved
sudo systemctl disable systemd-resolved.service
sudo systemctl stop systemd-resolved
sudo rm /etc/resolv.conf ; sudo ln -s /var/run/NetworkManager/resolv.conf /etc/resolv.conf
# Restart
sudo service dnsmasq start
sudo service network-manager restart
```

## Documentations

- [Nginx reverse proxy](/docs/nginx-reverse-proxy.md)
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
- Develop WF
    - [How it works?](/docs/wf-develop-base.md)
    - [Start developing, debug environments](/docs/wf-develop-starting.md)
    - [How to build?](/docs/wf-develop-build.md)
    - [Make commands](/docs/wf-develop-make.md)
    - [Variable references](/docs/wf-develop-variables.md)
- Wizard
    - [How to use?](/docs/wizard-using.md)
    - [How to develop?](/docs/wizard-developing.md)




## Run PHP Unit tests

You can create unit test and run.

    cd [where-the-wizard.sh-is]
    ./wizard.sh --debug symfony4/bin/phpunit -c symfony4

### Create ZSH autocomplete file

If you want to extend the ZSH autocomplete you should read it before: https://github.com/zsh-users/zsh-completions/blob/master/zsh-completions-howto.org It is a short, but fully description about bases.

As you can see in the `packages/wf-docker-workflow/opt/wf-docker-workflow/host/bin/zsh/autocomplete.sh` file, the script include the outer
autcomplete files:

```sh
    local recipe_autocompletes_file=${wf_directory_name}/autocomplete.recipes
    if [ ! -f $recipe_autocompletes_file ]; then
        # find all autocomplete.zsh file in recipes!
        find -L ${wf_directory_name} -mindepth 2 -maxdepth 2 -type f -name 'autocomplete.zsh' -printf "source %p\n" > $recipe_autocompletes_file
    fi
    source $recipe_autocompletes_file
```

The program find and include all of the `autocomplete.zsh` called file.

1. You need to create the `autocomplete.zsh` file into the `skeleton` directory. You mustn't include a directory!
2. Use cache if it needs!
3. Use the `_arguments`

You can learn from the existing `autocomplete.zsh` files!

> If you want to test it while you are developing, you must to edit the installed file directly in the `~/.wf-docker-workflow/bin/zsh/autocomplete.sh` file.
>
> Reload the edited file: `unfunction _wf && autoload -U _wf`

--------------------------------------------------------------------

Docker Workflow Installer
=========================

## Installation

> You need `sudo` permission!

1. Download the `deb` package from repository
    ```bash
    cd /tmp && git archive --remote=git@github.com:fchris82/WfDockerWorkflow.git HEAD webtown-workflow.deb | tar -x
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

Docker Project Wizard
=====================

## Usage

    cd /base/project/src/directory
    wizard

# ★ For developers

## Composer

If you want to run composer command, go to SF directory and run the command:

```
cd [project]/package/opt/wf-docker-workflow/symfony4
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
cd package/opt/wf-docker-workflow

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
/home/chris/www/webtown-workflow/packages/wf-docker-workflow/opt/wf-docker-workflow/wizard.sh
```

### Hogyan hozz létre új Wizard-ot?

Minden Wizard alapvetően arról szól, hogy vmilyen úton-módon fájlokat hoz létre, átalakítja a könyvtár struktúrát vagy vmi hasonlót. Mindegyik Wizard-nak meg kell valósítania a
`WizardInterface`-t, amelyiket "kiválaszthatónak" akarjuk, annak pedig a `PublicWizardInterface`-t is! Jelenleg a rendszer a következő alap wizárdokat ismeri és támogatja:

**AppBundle\Wizard\BaseSkeletonWizard**

A Skeleton Wizard-nál létre kell hozni egy könyvtárat a `/package/opt/wf-docker-workflow/symfony/src/AppBundle/Resources/skeletons` könyvtárban. Ez a könyvtár fog megfelelni a "projekt gyökerének". Az itt található
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
> The PHPStorm can't detect the symfony project. You have to switch on manually.
>   - Settings » Languages & Frameworks » PHP » Symfony ⟶ **Enable plugins for this project**
>   - Set the directories with `package/opt/wf-docker-workflow/symfony/` prefix, and perhaps you have to change `app/cache` to `var/cache`
>   - Settings » Other settings » Framework Integration ⟶ Select the **Symfony**
>
> You have to set `src` directory as *Source Root directory* and the `tests` directory as *Test Root directory*:
>   - right click on the directory » Mark directory as... ⟶ \[select\]

Environment extra information
=============================

