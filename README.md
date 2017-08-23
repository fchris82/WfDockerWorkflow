Webtown Kunstmaan Installer
===========================

Dependencies:

    sudo apt-get install dpkg-dev

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
