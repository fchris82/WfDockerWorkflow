#compdef wf

_wf() {
    local state

    _arguments \
        '1: :->command'\
        '*: :->parameters'

    case $state in
        command)
            _arguments '1: :($(wf list))'
        ;;
        parameters)
            case $words[2] in
                feature | hotfix)
                    _arguments '*: :(--from-this --disable-db --reload-d)'
                ;;
                connect)
                    _arguments '2: :($(wf docker-compose-cmd config --services))' '3: :(/bin/bash)'
                ;;
            esac
        ;;
    esac
}

_wf "$@"
