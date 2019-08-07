Create ZSH autocomplete file
============================

If you want to extend the ZSH autocomplete you should read it before: https://github.com/zsh-users/zsh-completions/blob/master/zsh-completions-howto.org It is a short, but fully description about bases.

As you can see in the `packages/wf-docker-workflow/src/opt/wf-docker-workflow/host/bin/zsh/autocomplete.sh` file, the script include the outer
autcomplete files:

```sh
    local recipe_autocompletes_file=${wf_directory_name}/autocomplete.recipes
    if [ ! -f $recipe_autocompletes_file ]; then
        # find all autocomplete.zsh file in recipes!
        find -L ${wf_directory_name} -mindepth 2 -maxdepth 2 -type f -name 'autocomplete.zsh' -printf "source %p\n" > $recipe_autocompletes_file
    fi
    source $recipe_autocompletes_file
```

The program find and include all of the `autocomplete.zsh` called file.

1. You need to create the `autocomplete.zsh` file into the `skeleton` directory. You mustn't include a directory!
2. Use cache if it needs!
3. Use the `_arguments`

You can learn from the existing `autocomplete.zsh` files!

> If you want to test it while you are developing, you must to edit the installed file directly in the `~/.wf-docker-workflow/bin/zsh/autocomplete.sh` file.
>
> Reload the edited file: `unfunction _wf && autoload -U _wf`

<!-- @TODO Finish + add bash -->
