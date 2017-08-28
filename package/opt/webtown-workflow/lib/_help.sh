read -r -d '' HELP <<-EOM
${BOLD}${WHITE}
↻ Webtown Workflow
==================
${RESTORE}
Anywhere:

  -h --help                 $(tput setaf 2)Show this help$(tput sgr0)
  -u --update               $(tput setaf 2)Self update. ${BOLD}You need sudo permission!$(tput sgr0)
  --install-autocomplete    $(tput setaf 2)Install ZSH autocomplete$(tput sgr0)

${BOLD}${WHITE}Only any project directory:${RESTORE}

  ${YELLOW}help${RESTORE}                      $(tput setaf 2)Show project workflow help. ${BOLD}Not this help!$(tput sgr0)
  ${YELLOW}list${RESTORE}                      $(tput setaf 2)Show available commands in project$(tput sgr0)
  ${YELLOW}info${RESTORE}                      $(tput setaf 2)Show some important project information$(tput sgr0)

Eg: $(tput setaf 3)wf help$(tput sgr0)
EOM

function showHelp {
    echo -e "${HELP}"
}
