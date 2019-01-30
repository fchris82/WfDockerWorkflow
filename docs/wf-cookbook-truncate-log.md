# Truncate log

> You can read this information in the `wf info` response too!

Sometimes the log file could be too big. There are different good solutions to handle these situations.

> Here is a good article in this topic: https://medium.com/the-code-review/to-boldly-log-debug-docker-apps-effectively-using-logs-options-tail-and-grep-53d2e655abcb

## Using `wf logs`

`wf logs` is a `docker-compose logs` alias. You can use everything what the `docker-compose logs` knows: https://docs.docker.com/compose/reference/logs/

```shell
# List full log of container
$ wf logs <name>
# List last 5 rows
$ wf logs --tail 5 <name>
# List last 5 rows with timestamp information
$ wf logs -t --tail 5 <name>
# List last 5 rows with timestamp into file:
$ wf logs -t --no-color --tail 5 <name> > log.txt
```

## Using `docker logs`

Sometimes `docker logs` can be more useful because of `--since` and `--until` options: https://docs.docker.com/engine/reference/commandline/logs/ First you need the name of docker-compose container and then you can use this:

```shell
# Find the name:
$ wf ps
         Name                      Command              State          Ports
-----------------------------------------------------------------------------------
xxxxxxxxxxxxx_engine_1   /usr/local/bin/entrypoint.sh   Up      9000/tcp, 9003/tcp
...
# ^^^^^^^^^^^^^^^^^^^^ we need this
# Print log:
$ docker logs --since 20m --until 10m xxxxxxxxxxxxx_engine_1
```

## Directly

You can find and handle log files directly, if you have **sudo**.

```shell
# Find the container ID by name (yes, you have to use 2 different commands)
$ wf ps --services
$ wf ps -q
# truncate the log file
$ sudo -sh -c "truncate -s 0 /var/lib/docker/containers/[ID]/[ID]-json.log"
```
