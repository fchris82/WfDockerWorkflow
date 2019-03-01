case $COMP_CWORD in
    2)
        case ${COMP_WORDS[1]} in
            ezsf)
                local sf_cache_file=${wf_directory_name}/autocomplete.ezsf
                [[ ! -f $sf_cache_file ]] || [[ -z $(cat $sf_cache_file) ]] && wf ezsf | egrep -o "^  [a-z]+:[^ ]+" | egrep -o "[^ ]+" > $sf_cache_file
                local sf_cache_commands=$(<$sf_cache_file)

                words+=" ${sf_cache_commands:-""}"
            ;;
        esac
    ;;
esac
