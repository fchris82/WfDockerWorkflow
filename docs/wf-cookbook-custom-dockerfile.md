# Using custom Dockerfile in a project

In this hypothetical example we would like to extend the `fchris82/symfony:php7.1` image with **mongo** support.

Create your custom Dockerfile (eg `.docker/engine/Dockerfile`)

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

Register it in `.wf.base.yml` file:

```yaml
docker_compose:
    extension:
        services:
            engine:
                # override the original image name! It is important!
                image: [project_name]
                # set the Dockerfile
                build:
                    context: '%wf.project_path%/.docker/engine'
                    dockerfile: Dockerfile

            mongodb:
                image: mongo:3.2
                volumes:
                    - "%wf.project_path%/.docker/.data/mongodb:/data/db"

recipes:
    symfony4:
        # ... The overrided/extended recipe ...
```
