# Register ~/bin path
if [ -d "$HOME/bin" ] && [ $(echo "$PATH" | grep $HOME/bin | wc -l) == 0 ]; then
    export PATH="$HOME/bin:$PATH"
fi

# Autocomplete
fpath=(~/.zsh/completion $fpath)
autoload -Uz compinit && compinit -i