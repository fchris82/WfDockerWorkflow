Webtown Kunstmaan Installer
===========================

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

## Configuration

If you wish alternative clone repository, change repository parameter in the `/etc/webtown-workflow/config` file

## Usage

A program önmagában nem működik!

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

# Tests

Általánosan tesztfuttatás:

```bash
make -f test/tests.mk
```

## Test container

A tesztek egy container-ben futnak. Lásd: `test/docker-compose.yml`. Amennyiben módosítasz a `test/Dockerfile`-on, akkor mindenképpen
futtasd a `rebuild` targetet:

```bash
make -f test/tests.mk rebuild all
```

## Tesztek írása

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
