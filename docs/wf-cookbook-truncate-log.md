# Truncate log

> You can read this information in the `wf info` response too!

Sometimes the log file became to big. You can truncate, if you have **sudo**.

```shell
# Find the container ID by name (yes, you have to use 2 different commands)
$ wf ps --services
$ wf ps -q
# truncate the log file
$ sudo -sh -c "truncate -s 0 /var/lib/docker/containers/[ID]/[ID]-json.log"
```
