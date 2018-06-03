# Create autocomplete services
local cache_services_file=${wf_directory_name}/autocomplete.services
[[ ! -f $cache_services_file ]] || [[ -z $(cat $cache_services_file) ]] && wf docker-compose config --services > $cache_services_file
services=$(<$cache_services_file)

case $state in
    parameters)
        case $words[2] in
            connect | enter | debug-enter | logs)
                _arguments '2: :($(echo ${services:-$(wf docker-compose config --services)}))'
            ;;
            exec | run | docker-compose)
                _arguments '*: :($(echo ${services:-$(wf docker-compose config --services)}))'
            ;;
        esac
    ;;
esac
