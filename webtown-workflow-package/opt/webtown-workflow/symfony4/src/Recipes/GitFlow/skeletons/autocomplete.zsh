case $COMP_CWORD in
    1)
        # do nothing
    ;;
    *)
        case ${COMP_WORDS[1]} in
            feature | hotfix)
                words+=" --from-this --disable-db --reload-d"
            ;;
        esac
    ;;
esac
