# Using custom recipes

## Install

Create or download your own recipes what you want to use. You can put them directly to the `~/.webtown-workfow/recipes` directory
or you can put anywhere and create a symlink to the `~/.webtown-workfow/recipes` directory:

```shell
$ ln -s /my/own/recipes/MyOwnRecipe ~/.webtown-workflow/recipes/MyOwnRecipe
```

You have to reload the cache:

```shell
$ wf --reload
```

> If you use an existing recipe directory name, you will override the original recipe!

> **Background**
>
> The program will share the directories or symlinks of the `~/.webtown-workfow/recipes` directory as docker volumes.

## How to write custom recipe

> You can find the included recipes in the `webtown-workflow-package/opt/webtown-workflow/recipes` directory.

**Important**

The skeleton "twig" files are cached! You can clear cache with the `wf clear-cache` command!

---

Base related documentations:

- Twig: https://twig.symfony.com/doc/2.x/
- How to create configuration definition: https://symfony.com/doc/current/components/config/definition.html
- Symfony Events: https://symfony.com/doc/current/components/event_dispatcher.html
- Docker Compose: https://docs.docker.com/compose/ , config file reference: https://docs.docker.com/compose/compose-file/
- Makefile: https://www.gnu.org/software/make/manual/make.html
- ZSH autocomplete: https://github.com/zsh-users/zsh-completions/blob/master/zsh-completions-howto.org

### Directory structure

The base directory structure is very simple:

```
CustomRecipe
├── Recipe.php
└── skeletons
    └── ... twig skeleton files ...
```

Here is a "complex" example:

```
Symfony
├── AbstractRecipe.php
└── skeletons
    ├── autocomplete.zsh
    ├── docker-compose.symfony-env.yml
    ├── docker-compose.user-volumes.yml
    ├── docker-compose.yml
    ├── etc
    │   └── vhost.conf
    └── makefile
```

### How to work?

The `Recipe.php` work like a Controller from MVC. Create some placeholders/variables to twig engine, and the twig engine parse the contain of `skeletons` directory. The result will be save to the `.wf` directory of the project.

There are 3 "base" file type.

#### `docker-compose.yml` and `docker-compose.*.yml` files

Surprise: docker-compose configuration files :) The reason they could be more then one file is the readability OR maybe you want to handle which will be build. See `App\Exception\SkipSkeletonFileException` exception.

#### `makefile`

You can register vars or commands that you can call from command line in the project directory:

```makefile
.PHONY: hello-world
hello-world:
	echo "Hello World!"
```

And the command:

```shell
$ wf hello-world
Hello Wolrd!
```

You can extend the 'finally docker command arguments', eg:

```makefile
ORIGINAL_CMD_DOCKER_ENV := $(CMD_DOCKER_ENV)
define CMD_DOCKER_ENV
    $(ORIGINAL_CMD_DOCKER_ENV) \
    WF_TARGET_DIRECTORY=$(WF_TARGET_DIRECTORY)
endef
```

> Use the define instead of `:=` solution!

#### `autocomplete.zsh`

You can write autocomplete file to **ZSH**. More info: https://github.com/zsh-users/zsh-completions/blob/master/zsh-completions-howto.org

**Important**

1. Use cache! You have to create unique cache file name!
2. If you define a function, you must use unique function name!

Eg:
```zsh
# wf sf [symfony command]
case $state in
    parameters)
        case $words[2] in
            sf)
                local sf_cache_file=${wf_directory_name}/autocomplete.sf
                [[ ! -f $sf_cache_file ]] || [[ -z $(cat $sf_cache_file) ]] && wf sf | egrep -o "^  [a-z]+:[^ ]+" | egrep -o "[^ ]+" > $sf_cache_file
                local sf_cache_commands=$(<$sf_cache_file)

                _arguments '2: :($(echo ${sf_cache_commands:-""}))'
            ;;
        esac
    ;;
esac
```

#### Other files

You can create other files, what you want to use during the processes. Eg: configuration files what you want to use in docker-compose files.

#### Skeleton file types

After build the program needs to know which file is which type. In the `Recipes\BaseRecipe::buildSkeletonFile()` function you can sign the spetial files.

**`App\Skeleton\DockerComposeSkeletonFile`**

These files are docker-compose configuration files. By default the program detect them by filename. You should start `docker-compose` and finish set the `yml` extension. You can use custom name, but it wont be handled automatically! You need to take care of handle it.

Automatically detected filenames:

- `docker-compose.yml`
- `docker-compose.user.yml`
- `docker-compose-volumes.yml`

**`App\Skeleton\MakefileSkeletonFile`**

These files are makefiles. By default the program detect them by filename too. You should use `makefile` name.

**`App\Skeleton\ExecutableSkeletonFile`**

These files are executable files. By default the program detect them by file permission. If the skeleton is executable, the target file will be also.

## The conductor: `Recipe.php`

The `Recipe.php` parse the configuration file and generate new files under the project directory from skeleton files.

### Configuration

The program uses the symfony configuration solution. There is a detailed documetation how works: https://symfony.com/doc/current/components/config/definition.html . You can define configuration in the `getConfig()` method.

Here is a complex example:

```php
<?php
class Recipe extends BaseRecipe
{
    // ...

    public function getConfig()
    {
        $rootNode = parent::getConfig();

        $rootNode
            ->info('<comment>You can enable the nginx-reverse-proxy.</comment>')
            ->children()
                ->scalarNode('network_name')
                    ->info('<comment>The nginx-reverse-proxy network name.</comment>')
                    ->cannotBeEmpty()
                    ->defaultValue('reverse-proxy')
                ->end()
                ->arrayNode('settings')
                    ->info('<comment>You have to set the service and its <info>host</info> and <info>port</info> settings.</comment>')
                    ->useAttributeAsKey('service')
                    ->variablePrototype()
                        ->beforeNormalization()
                            ->always(function ($v) {
                                $defaultTld = trim(
                                    $this->environment->getConfigValue(Environment::CONFIG_DEFAULT_LOCAL_TLD, '.loc'),
                                    '.'
                                );
                                $defaultHost = sprintf(
                                    '%s.%s.%s',
                                    static::SERVICE_NAME_PARAMETER_NAME,
                                    static::PROJECT_NAME_PARAMETER_NAME,
                                    $defaultTld
                                );
                                $defaultPort = 80;

                                return [
                                    // If the project name: `project` --> `project.loc`
                                    'host' => is_array($v) && array_key_exists('host', $v) ? $v['host'] : $defaultHost,
                                    'port' => (int) (is_array($v) && array_key_exists('port', $v) ? $v['port'] : (!is_array($v) && $v ? $v : $defaultPort)),
                                ];
                            })
                            ->end()
                        ->end()
                        // Replace the service names in domains
                        ->validate()
                            ->always(function ($v) {
                                foreach ($v as $serviceName => $settings) {
                                    $settings['host'] = strtr($settings['host'], [static::SERVICE_NAME_PARAMETER_NAME => $serviceName]);
                                    $v[$serviceName] = $settings;
                                }

                                return $v;
                            })
                        ->end()
                        ->example([
                            'service1' => '~',
                            'service2' => ['host' => 'phpmyadmin.project.loc', 'port' => 81],
                            'service3' => 82,
                        ])
                    ->end()
                ->end()
            ->end()
        ;

        return $rootNode;
    }
}
```

### Event handler

There are some configuration/build events. You can find them into the `App\Event\ConfigurationEvents` class. If you want to use it somewhere, you have to implement the `Symfony\Component\EventDispatcher\EventSubscriberInterface` into your `Recipe`.

### How to extend an existing recipe (inheritance)

<!-- TODO -->
