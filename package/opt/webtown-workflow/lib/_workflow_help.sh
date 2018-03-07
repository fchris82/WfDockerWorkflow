read -r -d '' HELP <<-EOM
${BOLD}${WHITE}
↻ Webtown Workflow
==================
${RESTORE}
Anywhere:

  -h    --help                      ${GREEN}Show this help${RESTORE}
  -u    --update                    ${GREEN}Self update. ${BOLD}You need sudo permission!${RESTORE}
        --install-autocomplete      ${GREEN}Install ZSH autocomplete${RESTORE}
        --init-reverse-proxy        ${GREEN}Init nginx reverse proxy with 'nginx-reverse-proxy' name${RESTORE}
  -erp  --enter-reverse-proxy       ${GREEN}Enter nginx reverse proxy with 'nginx-reverse-proxy' name${RESTORE}
  -scrp --show-config-reverse-proxy ${GREEN}Show the current 'nginx-reverse-proxy' nginx config file${RESTORE}

${BOLD}${WHITE}Only any project directory:${RESTORE}

  ${YELLOW}help${RESTORE}                      ${GREEN}Show project workflow help. ${BOLD}Not this help!${RESTORE}
  ${YELLOW}list${RESTORE}                      ${GREEN}Show available commands in project${RESTORE}
  ${YELLOW}info${RESTORE}                      ${GREEN}Show some important project information${RESTORE}

Eg: ${YELLOW}wf help${RESTORE}

${BOLD}${WHITE}
Debug reverse proxy
===================
${RESTORE}
${YELLOW}docker exec nginx-reverse-proxy cat /etc/nginx/conf.d/default.conf${RESTORE}
EOM

function showHelp {
    echo -e "${HELP}"
}
