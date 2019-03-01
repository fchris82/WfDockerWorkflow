_get_ezsf_xml() {
    local ezsf_cache_file=${wf_directory_name}/autocomplete.ezsf.xml
    # We load everything into an xml file
    [[ ! -f $ezsf_cache_file ]] || [[ -z $(cat $ezsf_cache_file) ]] && wf ezsf list --format=xml > $ezsf_cache_file
    echo $(cat $ezsf_cache_file)
}

case $state in
    parameters)
        case $words[2] in
            ezsf)
                local ezsf_cache_commands=$(_get_ezsf_xml | grep -oP "(?<=<command>)[^<]+(?=</command>)")

                # Load commands
                _arguments '2: :($(echo ${ezsf_cache_commands:-""}))'

                if [ ! -z $words[3] ]; then
                    local ezsfcmd=${words[3]}
                    local ezsfcmd_cache_options=$(_get_ezsf_xml | tr '\n' '\a' | grep -oP '<command id="'$ezsfcmd'".*?</command>' | grep -oP '(?<=<option name=")[^"]+(?=")')

                    # Load command options
                    _arguments '*: :($(echo ${ezsfcmd_cache_options:-""}))'
                fi
            ;;
        esac
    ;;
esac
