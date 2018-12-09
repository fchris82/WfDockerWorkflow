# @todo
case $state in
    parameters)
        case $words[2] in
            {{ name }})
                _arguments '*: :(--arg1 --arg2 --arg3)'
            ;;
        esac
    ;;
esac
