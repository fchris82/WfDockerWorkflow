#compdef wf

_wf() {
    local state

    # Get config file
    local config_file=${HOME}/.webtown-workflow/config/env
    if [ -f $config_file ]; then
        local wf_directory_name=$(awk '/^'WF_WORKING_DIRECTORY_NAME'/{split($1,a,"="); print a[2]}' "${config_file}")

        # Create autocomplete list
        local cache_list_file=${wf_directory_name}/autocomplete.list
        [[ ! -f $cache_list_file ]] || [[ -z $(cat $cache_list_file) ]] && wf list > $cache_list_file
        list=$(<$cache_list_file)

        # Create autocomplete services
        local cache_services_file=${wf_directory_name}/autocomplete.services
        [[ ! -f $cache_services_file ]] || [[ -z $(cat $cache_services_file) ]] && wf docker-compose config --services > $cache_services_file
        services=$(<$cache_services_file)
    fi

    _arguments \
        '1: :->command'\
        '*: :->parameters'

    case $state in
        command)
            _arguments '1: :(-ps --docker-ps reconfigure --reload --config-dump)'
            compadd $(echo ${list:-$(wf list)})
        ;;
        parameters)
            case $words[2] in
                feature | hotfix)
                    _arguments '*: :(--from-this --disable-db --reload-d)'
                ;;
                connect | enter | debug-enter | logs)
                    _arguments '2: :($(echo ${services:-$(wf docker-compose config --services)}))'
                ;;
                exec | run | docker-compose)
                    _arguments '*: :($(echo ${services:-$(wf docker-compose config --services)}))'
                ;;
                --config-dump)
                    _arguments '*: :(--only-recipes --no-ansi --recipe=)'
                ;;
                *)
                    _alternative 'files:filename:_files'
                ;;
            esac
            # Allow files from third parameter
            [[ ! -z $words[3] ]] && _alternative 'files:filename:_files'
        ;;
    esac

    # Reload to test: unfunction _wf && autoload -U _wf
    # Here we try to find recipes autocompletes.
    if [ -f $config_file ]; then
        local recipe_autocompletes_file=${wf_directory_name}/autocomplete.recipes
        if [ ! -f $recipe_autocompletes_file ]; then
            find -L ${wf_directory_name} -mindepth 2 -maxdepth 2 -type f -name 'autocomplete.zsh' -printf "source %p\n" > $recipe_autocompletes_file
        fi
        source $recipe_autocompletes_file
    fi
}

_wf "$@"
