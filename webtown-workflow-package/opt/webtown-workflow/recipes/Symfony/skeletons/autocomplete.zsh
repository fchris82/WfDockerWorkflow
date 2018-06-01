local sf_cache_file=${wf_directory_name}/autocomplete.sf
[[ ! -f $sf_cache_file ]] || [[ -z $(cat $sf_cache_file) ]] && wf sf | egrep -o "^  [a-z]+:[^ ]+" | egrep -o "[^ ]+" > $sf_cache_file
local sf_cache_commands=$(<$sf_cache_file)

case $state in
    parameters)
        case $words[2] in
            sf)
                _arguments '2: :($(echo ${sf_cache_commands:-""}))'
            ;;
        esac
    ;;
esac
