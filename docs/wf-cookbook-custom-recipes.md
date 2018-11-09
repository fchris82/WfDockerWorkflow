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

