read -r -d '' HELP <<-EOM
You can create or decorate custom project to use docker, Gitlab CI or other tool:

  -h --help               ${GREEN}Help, show this${RESTORE}
  -i --install            ${GREEN}"Composer install" in symfony directory!${RESTORE}

  -r --rebuild            ${GREEN}Clear SF cache and rebuild the docker container!${RESTORE}
  -e --enter              ${GREEN}For debugging. Enter the docker container.${RESTORE}

  Without any parameter just start the wizard!

Special argument:

  ${CYAN}--config-dump${RESTORE}    ${GREEN}In a project directory this will show the all available configs. You can put it a file to edit with ${BOLD}--no-ansi${GREEN}${RESTORE}
  ${CYAN}--dev${RESTORE}            ${GREEN}For debugging. You can use this before every command! It can switch on ${BOLD}xdebug${GREEN} and ${BOLD}SF dev${GREEN} mode.${RESTORE}

Eg: ${YELLOW}wizard -i${RESTORE} or with dev ${YELLOW}wizard ${BOLD}--dev${YELLOW} -i${RESTORE}
    ${YELLOW}wizard --config-dump ${BOLD}--no-ansi${YELLOW} > .wf.yml${RESTORE}
EOM

function showHelp {
    echo -e "${HELP}"
}
