#compdef wf

_wf() {
    local state

    _arguments \
        '1: :->command'\
        '*: :->parameters'

    case $state in
        command)
            _arguments '1: :($(wf list) --init-reverse-proxy -erp --enter-reverse-proxy -scrp --show-config-reverse-proxy)'
        ;;
        parameters)
            case $words[2] in
                feature | hotfix)
                    _arguments '*: :(--from-this --disable-db --reload-d)'
                ;;
                connect | enter | debug-enter | logs)
                    _arguments '2: :($(wf docker-compose config --services))'
                ;;
                exec | run)
                    _arguments '*: :($(wf docker-compose config --services))'
                ;;
            esac
        ;;
    esac
}

_wf "$@"
