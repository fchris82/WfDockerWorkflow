#-- Vars
RESTORE=$'\x1B[0m'
# Clear to end of line: http://www.isthe.com/chongo/tech/comp/ansi_escapes.html
CLREOL=$'\x1B[K'
# Colors
RED=$'\x1B[00;31m'
GREEN=$'\x1B[00;32m'
YELLOW=$'\x1B[00;33m'
BLUE=$'\x1B[00;34m'
MAGENTA=$'\x1B[00;35m'
PURPLE=$'\x1B[00;35m'
CYAN=$'\x1B[00;36m'
LIGHTGRAY=$'\x1B[00;37m'
LRED=$'\x1B[01;31m'
LGREEN=$'\x1B[01;32m'
LYELLOW=$'\x1B[01;33m'
LBLUE=$'\x1B[01;34m'
LMAGENTA=$'\x1B[01;35m'
LPURPLE=$'\x1B[01;35m'
LCYAN=$'\x1B[01;36m'
WHITE=$'\x1B[01;37m'
BOLD=$'\x1B[1m'


# display a message in red with a cross by it
# example
# echo echo_fail "No"
function echo_fail {
  # echo first argument in red
  printf "${LRED}✘ ${@}${RESTORE}\n"
}

# display a message in green with a tick by it
# example
# echo echo_fail "Yes"
function echo_pass {
  # echo first argument in green
  printf "${GREEN}✔ ${@}${RESTORE}\n"
}

function echo_info {
  printf "${YELLOW}» ${@}${RESTORE}\n"
}
# echo pass or fail
# example
# echo echo_if 1 "Passed"
# echo echo_if 0 "Failed"
function echo_if {
  if [ $1 == 1 ]; then
    echo_pass $2
  else
    echo_fail $2
  fi
}

# echo a 3 lines block
# example
# echo_block
#
# http://misc.flogisoft.com/bash/tip_colors_and_formatting
function echo_block {
  CLASS=$'\x1B['${1}
  echo -en "\n${CLASS}${CLREOL}\n"
  echo -e "${2}${CLREOL}"
  echo -e "${RESTORE}\n"
}

# echo a title
# example
# echo_title "Title of block"
function echo_title {
  echo_block "97;45m" " » ${@}"
}
