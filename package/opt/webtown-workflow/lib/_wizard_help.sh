read -r -d '' HELP <<-EOM
You can create or decorate custom project to use docker, Gitlab CI or other tool:

  -h --help               $(tput setaf 2)Help, show this$(tput sgr0)
  -i --install            $(tput setaf 2)"Composer install" in symfony directory!$(tput sgr0)

  -r --rebuild            $(tput setaf 2)Clear SF cache and rebuild the docker container!$(tput sgr0)
  -e --enter              $(tput setaf 2)For debugging. Enter the docker container.$(tput sgr0)

  Without any parameter just start the wizard!

Eg: $(tput setaf 3)wizard -i$(tput sgr0)
EOM

function showHelp {
    echo -e "${HELP}"
}
