case $state in
    parameters)
        case $words[2] in
            ezsf)
                local sf_cache_file=${wf_directory_name}/autocomplete.ezsf
                [[ ! -f $sf_cache_file ]] || [[ -z $(cat $sf_cache_file) ]] && wf ezsf | egrep -o "^  [a-z]+:[^ ]+" | egrep -o "[^ ]+" > $sf_cache_file
                local sf_cache_commands=$(<$sf_cache_file)

                _arguments '2: :($(echo ${sf_cache_commands:-""}))'
            ;;
        esac
    ;;
esac
