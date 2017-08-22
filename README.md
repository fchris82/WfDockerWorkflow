Webtown Kunstmaan Installer
===========================

(Re)generate deb package:

1. Change the version number in `package/DEBIAN/control` file
2. Build the package:
    ```bash
    dpkg -b package webtown-workflow.deb
    ```

## Installation

> You need `sudo` permission!

1. Download the `deb` package from repository
    ```bash
    cd /tmp && git archive --remote=git@gitlab.webtown.hu:webtown/webtown-workflow.git HEAD webtown-workflow.deb | tar -x
    ```
2. Install package:
    ``` bash
    sudo dpkg -i webtown-workflow.deb
    ```
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