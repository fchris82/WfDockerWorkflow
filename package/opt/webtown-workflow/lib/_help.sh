# @todo A helpet még meg kell írni
read -r -d '' HELP <<-EOM
You can create a pure Kunstmaan bundle from standard edition or install one:

  -r --repository   [arg]   $(tput setaf 2)Standard edition alternative repository$(tput sgr0)
  -i --install      [arg]   $(tput setaf 2)Install from this repository$(tput sgr0)

  -b --branch       [arg]   $(tput setaf 2)Branch name$(tput sgr0)

  -u --update               $(tput setaf 2)Update the program. You need sudo permission!$(tput sgr0)

Eg: $(tput setaf 3)create-km-project.sh -i git@gitlab.webtown.hu:php/bssoil.git -b develop$(tput sgr0)
EOM

function showHelp {
    echo -e "${HELP}"
}
