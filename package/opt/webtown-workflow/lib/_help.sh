read -r -d '' HELP <<-EOM
${BOLD}${WHITE}
↻ Webtown Workflow
==================
${RESTORE}
Anywhere:

  -h --help                 ${GREEN}Show this help${RESTORE}
  -u --update               ${GREEN}Self update. ${BOLD}You need sudo permission!${RESTORE}
  --install-autocomplete    ${GREEN}Install ZSH autocomplete${RESTORE}

${BOLD}${WHITE}Only any project directory:${RESTORE}

  ${YELLOW}help${RESTORE}                      ${GREEN}Show project workflow help. ${BOLD}Not this help!${RESTORE}
  ${YELLOW}list${RESTORE}                      ${GREEN}Show available commands in project${RESTORE}
  ${YELLOW}info${RESTORE}                      ${GREEN}Show some important project information${RESTORE}

Eg: ${YELLOW}wf help${RESTORE}
EOM

function showHelp {
    echo -e "${HELP}"
}
