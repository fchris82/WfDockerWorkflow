# Create a custom docker repository

```shell
$ docker run -d \
    -p 5000:5000 \
    --restart=always \
    --name registry \
    -v /mnt/registry:/var/lib/registry \
    registry:2
```

> You have to register the unsecure repository in your local computer in `/etc/docker/daemon.json` if you don't have secure connection:
>
> ```json
> {
> "insecure-registries":["example.com:5000"]
> }
> ```
>
> You have to restart the docker:
> ```shell
> $ sudo service docker restart
> ```
