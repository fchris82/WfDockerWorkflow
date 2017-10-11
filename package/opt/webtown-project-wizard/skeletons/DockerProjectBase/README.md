## Install

> Az alábbiakban egy dockeres telepítés leírása látható, ami a **Webtown Workflow**-t használja.

```bash
wf init
# Szerkeszd a fájlokat
wf install
```

Probléma esetén használd:

```bash
wf reinstall
```

### XDebug használat

Alapból települ az **XDebug**. A probléma, hogy az eZ admin felülettel nem túl hatékony a működése, ezért alapból ki van kapcsolva.

1. A `Languages & Frameworks > PHP > Servers` résznél a zöld `+` jellel adj hozzá egy szervert.
    - Amit itt megadsz **Name**-nek, azt kell majd megadnod a `.project.env` fájlban a `XDEBUG_IDE_SERVER_NAME` értékének. Javaslat: `Docker`
    - Alul pipáld ki a **Use path mappgins** részt. Itt kell beállítanod, hogy a docker image fájlrendszerében melyik könyvtár felel meg a local-ban. A projekt gyökerének add meg a `/var/www` értéket (nyomj entert!)
2. Menü: `Run > Edit configurations` résznél a zöld `+` jellel adj hozzá egy **PHP Web Application**-t
    - Válaszd ki az előbb megadott szervert
    - Adj meg egy tetszőleges nevet
    - Adj meg egy URL-t, amit szeretnél tesztelni
3. Kapcsold be: a `.project.env` fájlban az `XDEBUG_ENABLED` értékét állítsd át `1`-re és indítsd újra a container-eket a `wf reload` paranccsal.

**Tesztelés**

1. A `Run > Break at first line in PHP scripts`-re (alsó rész) kattintva bekapcsolod azt, hogy a futás megálljon az első parancsnál.
2. A `Run > Start listening for PHP Debug Connections`-re kattintva bekapcsolod azt, hogy figyelje a parancssorból érkező Xdebug "jeleket"
3. A `wf sf` parancsra most majd meg kell állnia a futásnak a PHPStorm-ban. Ezzel tesztelted a parancssori működést. Ha megáll, de nem nyílik meg a `console` file, akkor valószínűleg rosszul állítottad be a **Use path mappings**-et. Ha nem áll meg, akkor lehet, hogy a portok beállításával nem stimmel vmi.
4. A `Run > Debug {PHP Web Application name}`-nel elkezdi betölteni a böngészőben a megadott oldalt és meg kell állnia a futásnak.

> **Tipp**
>
> Hozz létre egy szájízednek megfelelő általános `xdebug.ini` fájlt a saját home könyvtáradban, pl: `~/.docker/xdebug.ini`. Így a projektekben a `.docker/docker-compose.local.yml` fájlban ezt beállítva a saját beállításaid lesznek betöltve mindenhol, ahol ezt a módosítást megcsinálod. Az `xdebug.remote_host` értékével annyira nem kell foglalkozni, mert automatikusan felül lesz írva minden indulásnál.

### HTTP AUTH használata

Lehetőség van arra, hogy HTTP AUTH-tal levédd a felületet. Ehhez szükségünk van egy `.htpasswd` fájlra, amit automatikusan létrehoz a program a `HTTP_AUTH_PASS` paraméterből.

1. Menj a http://www.htaccesstools.com/htpasswd-generator/ oldalra és hozz létre egy tetszőleges felhasználónév - jelszó párost.
2. A létrehozott tartalmat jegyezd be a `HTTP_AUTH_PASS` változónak. Ezt a saját `.project.env` fájlodba jegyezd be, ha nem szeretnéd globálisan a projektre alkalmazni. **FONTOS!** Itt escape-elned kell MINDEN `$` jelet, méghozzá így: `\$$`
3. A `docker-compose.local.yml` fájlban a `web` service-nek add meg, hogy töltse be a `.htpasswd` fájlt (Működés: az **nginx** betölt minden `*.conf` fájlt a `conf.d` könyvtárból):
    ```yaml
        web:
            # ...
            volumes:
                # Switch on the HTTP AUTH
                - "${PROJECT_COMPOSE_DIR}/nginx/http_auth.conf:/etc/nginx/conf.d/http_auth.conf:ro"
    ```

## Work

| Parancs | Leírás |
|:------- |:------ |
| `wf help` | Részletes help |
| `wf up` | Docker container-ek indítása |
| `wf debug-*` | Debug parancsok |
| `wf logs <container>` | A megadott container logját kilistázza |
| `wf [ php / composer / sf / mysql ]` | A megadott parancsokat futtatja |
