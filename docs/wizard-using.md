How to use the wizard?
======================

You can install wizards that can you help initialize new project or decorate an existing one. You can install new wizards by copy it to `~/.webtown-workflow/wizards` directory:

```shell
$ git clone [git-url] ~/.webtown-workflow/wizards
```

## Configure

You can fully personalize the wizards:

- add custom name
- enable or disable installed wizards
- grouping
- sort order

Use the `wizard --config` command to start configuration command. This command will save your settings in the `~/.webtown-workflow/config/wizards.yml` file.

On the first page you can select one from all available wizard which you want to edit. On the second page you can edit the wizard:

- `name`: You can change the name what you can see
- `group`: You can change or put a new group the wizard
- `priority`: The highest priority will be shown above
- `enabled`: You can switch off a wizard

You should first run the command to create a basic `wizard.yml` file. If you want to edit a lot of things, it may be comfortable to edit directly the generated `wizard.yml` file. It is a very simple yaml file:

```yaml
# You can edit this file with the `wizard --config` command!

Wizards\Ez\EzBuildWizard:
    name: 'eZ Project Builder'
    enabled: true
    group: Builder
    priority: 0
Wizards\Symfony\SymfonyBuildWizard:
    name: Symfony
    enabled: true
    group: Builder
    priority: 0
Wizards\Symfony\SymfonyComposerBuildWizard:
    name: 'Symfony builder'
    enabled: true
    group: Builder
    priority: 0
```

## Using

```shell
$ cd [workdir]
$ wizard
```

The command will list all enabled AND available wizards. The program will hide the wizard if:

- You disabled it
- You can't use it in the selected working directory. Eg: you can't use the Builders in an existing project directory, and you can't use Decorators outside an existing project directory.
- It has been run

So, if you can't find the wizard in the list, check your working directory OR there are the `--force` and `--full` arguments.

### Arguments

**--help**

You can list the command help, with examples.

```shell
$ wizard --help
```

**--force**

With `--force` argument the program will list the **all enabled** wizards, skip the checking "availabilty", but you won't see the disabled wizards.

```shell
$ wizard --force
```

**--full**

With `--full` argument the program will list the **all installed** wizards, skip any checkers. You will see the disabled wizards too.

```shell
$ wizard --full
```
