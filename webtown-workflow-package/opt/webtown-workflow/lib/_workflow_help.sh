read -r -d '' HELP <<-EOM
${BOLD}${WHITE}
↻ Webtown Workflow
==================
${RESTORE}
Debug modes:

  -v                                ${GREEN}Set ${RESTORE}MAKE_DISABLE_SILENC=1${GREEN} ${BOLD}You must set it first!${RESTORE}
  -vvv                              ${GREEN}Set ${RESTORE}MAKE_DISABLE_SILENC=1 MAKE_DEBUG_MODE=1${GREEN} ${BOLD}You must set it first!${RESTORE}

Eg: ${YELLOW}wf ${BOLD}-v${YELLOW} help${RESTORE}

Anywhere:

  -h    --help                      ${GREEN}Show this help${RESTORE}
  -u    --update                    ${GREEN}Self update. ${BOLD}You need sudo permission!${RESTORE}

${BOLD}${WHITE}Only any project directory:${RESTORE}

  ${YELLOW}help${RESTORE}                      ${GREEN}Show project workflow help. ${BOLD}Not this help!${RESTORE}
  ${YELLOW}list${RESTORE}                      ${GREEN}Show available commands in project${RESTORE}
  ${YELLOW}info${RESTORE}                      ${GREEN}Show some important project information${RESTORE}
  ${YELLOW}reconfigure${RESTORE}               ${GREEN}Rebuild the project config. ${BOLD}You can use symfony args: wf reconfigure -v${RESTORE}

Eg: ${YELLOW}wf help${RESTORE}
EOM

function showHelp {
    echo -e "${HELP}"
}