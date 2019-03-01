case $COMP_CWORD in
    2)
        case ${COMP_WORDS[1]} in
            sf)
                local sf_cache_file=${wf_directory_name}/autocomplete.sf
                [[ ! -f $sf_cache_file ]] || [[ -z $(cat $sf_cache_file) ]] && wf sf | egrep -o "^  [a-z]+:[^ ]+" | egrep -o "[^ ]+" > $sf_cache_file
                local sf_cache_commands=$(<$sf_cache_file)

                words+=" $(echo ${sf_cache_commands:-""})"
            ;;
        esac
    ;;
esac
