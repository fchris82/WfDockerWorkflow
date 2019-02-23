<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $projectPath ?> | Config editor</title>
    <link rel="stylesheet" href="/js/jquery-ui-1.12.1.custom/jquery-ui.css" />
    <link rel="stylesheet" href="/css/jquery.toastmessage.css" />
    <link rel="stylesheet" href="/js/jqueryfiletree/jQueryFileTree.min.css" />
    <link rel="stylesheet" href="/css/main.css" />
</head>
<body>
<div id="container">
    <div id="sidebar"></div>
    <pre id="editor"><?php echo file_get_contents(sprintf('%s/%s', $projectPath, $baseConfigFile)) ?></pre>
    <div id="help">
        <pre class="reference"></pre>
    </div>
</div>

<script src="/js/jquery-3.3.1.min.js"></script>
<script src="/js/jquery-ui-1.12.1.custom/jquery-ui.js"></script>
<script src="/js/jquery.toastmessage.js"></script>
<script src="/js/jqueryfiletree/jQueryFileTree.min.js"></script>
<!-- load ace -->
<script src="/js/ace-noconflict/ace.js"></script>
<!-- load ace language tools -->
<script src="/js/ace-noconflict/ext-language_tools.js"></script>
<script>
    // trigger extension
    var langTools = ace.require("ace/ext/language_tools");
    var editor = ace.edit("editor");
    editor.session.setMode("ace/mode/yaml");
    editor.setTheme("ace/theme/cobalt");
    // enable autocompletion and snippets
    editor.setOptions({
        enableBasicAutocompletion: true,
        enableSnippets: true,
        enableLiveAutocompletion: false
    });
    // Register helper
    editor.selection.on("changeCursor", function (event, selection) {
        if (selection.$isEmpty) {
            showHelp();
        } else {
            hideHelp();
        }
    });
    // Insert the config json
    var compConfig = <?php include sprintf('%s/%s/%s/%s', $projectPath, $wfConfigDir, 'config_editor', 'full_config.json') ?>;
    // Parse all of the file. Collect "meta.tag"-s and "bracket"-s.
    function parseTree() {
        var s = editor.session,
            lines = s.bgTokenizer.lines,
            currentPath = [],
            tree = [],
            currentDepth,
            // Open bracket: +1 , close bracket: -1
            bracketDepth = 0,
            // fallow the column
            currentColumn = 0,
            tokens;

        for (var i=0;i<lines.length;i++) {
            tokens = lines[i];
            // IF it isn't array OR empty OR the first tag isn't meta.tag...
            if (!$.isArray(tokens) || tokens.length === 0 || tokens[0].type !== 'meta.tag') {
                continue;
            }
            // We use this calculation only if we are out of all brackets.
            if (bracketDepth === 0) {
                currentDepth = parseInt( (tokens[0].value.split(' ').length - 1) / s.getTabSize() );
            }

            currentColumn = 0;
            for (var t=0;t<tokens.length;t++) {
                switch (tokens[t].type) {
                    case 'meta.tag':
                        currentPath = currentPath.slice(0, currentDepth + bracketDepth);
                        currentPath.push(tokens[t].value.trim());
                        tree.push({
                            type: 'node',
                            path: currentPath,
                            depth: currentDepth + bracketDepth,
                            startRow: i,
                            startColumn: currentColumn + currentDepth * s.getTabSize()
                        });
                        break;
                    case 'paren.lparen':
                        bracketDepth++;
                        tree.push({
                            type: 'openBracket',
                            path: currentPath,
                            depth: currentDepth + bracketDepth,
                            startRow: i,
                            startColumn: currentColumn
                        });
                        break;
                    case 'paren.rparen':
                        bracketDepth--;
                        tree.push({
                            type: 'closeBracket',
                            path: currentPath,
                            depth: currentDepth + bracketDepth,
                            startRow: i,
                            startColumn: currentColumn
                        });
                        break;
                }
                currentColumn += tokens[t].value.length;
            }
        }

        return tree;
    }

    /**
     * There are two different options to create "key":
     *
     * <code>
     *     key: value
     *     parent: { key: value, foo: bar }
     * </code>
     *
     * So we have to use different ways that depend on brackets.
     */
    function getCurrentPositionDepth(tree, row, column) {
        var s = editor.session,
            last,
            bracketDepth = 0;

        for (var i=0;i<tree.length;i++) {
            if (tree[i].startRow >= row && tree[i].startColumn > column) {
                break;
            }
            switch (tree[i].type) {
                case 'openBracket':
                    bracketDepth++;
                    last = tree[i];
                    break;
                case 'closeBracket':
                    bracketDepth--;
                    last = tree[i];
                    break;
            }
        }

        return bracketDepth === 0
            ? parseInt(column/s.getTabSize())
            : last.depth;
    }

    /**
     * Finds the "parent node" from cursor position. We use it to find autocomplete and used words.
     *
     * @param tree      Array
     * @param row       Int
     * @param column    Int
     * @returns {Array}
     */
    function getParentMetaTagPath(tree, row, column) {
        var s = editor.session,
            currentDepth = getCurrentPositionDepth(tree, row, column),
            last = [];
        // Find parent
        for (var i=0;i<tree.length;i++) {
            if (tree[i].startRow > row || (tree[i].startRow === row && tree[i].startColumn > column)) {
                return last;
            }
            if (tree[i].path.length <= currentDepth) {
                last = tree[i].path;
            }
        }

        return last;
    }

    /**
     * Gets the last meta.tag/key path from cursor position. We use it to find and show help context.
     *
     * @param tree
     * @param row
     * @param column
     * @returns {Array}
     */
    function getLastMetaTagPath(tree, row, column) {
        var s = editor.session,
            last = [];
        // Find parent
        for (var i=0;i<tree.length;i++) {
            if (tree[i].startRow > row || (tree[i].startRow === row && tree[i].startColumn > column)) {
                return last;
            }
            last = tree[i].path;
        }

        return last;
    }

    /**
     * Finds the used words to skip them at autocomplete.
     *
     * @param tree
     * @param pathArray
     * @returns {Array}
     */
    function getUsedWords(tree, pathArray) {
        var usedWords = [];
        for (var i=0;i<tree.length;i++) {
            if (tree[i].path.length === pathArray.length + 1) {
                if (JSON.stringify(tree[i].path.slice(0, pathArray.length)) === JSON.stringify(pathArray)) {
                    usedWords.push(tree[i].path.slice(-1)[0]);
                }
            }
        }

        return usedWords;
    }

    /**
     * Tries to find the config node with the pathArray.
     *
     * @param pathArray
     * @returns {*}
     */
    function getConfigNode(pathArray) {
        var current = compConfig, key;
        for (var i=0;i<pathArray.length;i++) {
            key = pathArray[i];
            // Base option: current.children.[...] exists
            if (typeof current === 'object' && current.hasOwnProperty('children') && current.children.hasOwnProperty(key)) {
                current = current.children[key];
                // If it is a prototype
            } else if (typeof current === 'object' && current.hasOwnProperty('prototype') && current.prototype.length === 1) {
                key = current.prototype[0];
                current = current.children[key];
            } else {
                return null;
            }
        }

        return current;
    }

    /**
     * Gets node children if them exist.
     *
     * @param pathArray
     * @returns {null}
     */
    function getConfigEnvironment(pathArray) {
        var current = getConfigNode(pathArray);

        if (typeof current === 'object' && current !== null && current.hasOwnProperty('children') && !current.hasOwnProperty('prototype')) {
            return current.children;
        }

        return null;
    }

    /**
     * Gets usable config words.
     *
     * @param env
     * @param usedWords
     * @returns {Array}
     */
    function getConfigWords(env, usedWords) {
        if (env === null) {
            return [];
        }

        var words = [], suffix;
        $.each(env, function (key, value) {
            if (usedWords.indexOf(key) === -1) {
                // Base suffix
                suffix = ":\n";
                // Overridden suffix
                if (value.hasOwnProperty('default')) {
                    // Changes default to simple ~ if it needs.
                    if (value.default === '~') {
                        suffix = ": ~\n";
                    } else {
                        suffix = ": " + JSON.stringify(value.default);
                    }
                }
                words.push({
                    name: key,
                    value: key + suffix,
                    score: value.required ? 300 : 200,
                    meta: value.required ? "required" : "optional"
                });
            }
        });

        return words;
    }

    /**
     * Show the help if there is help content. Now we use only the `reference`, but there are other options:
     *  - reference
     *  - info
     *  - example
     *  - yaml_example
     */
    function showHelp() {
        var pos = editor.getCursorPosition();
        var tree, currentPositionPath, node, helpContainer = $('#help');
        tree = parseTree();
        currentPositionPath = getLastMetaTagPath(tree, pos.row, pos.column);
        node = getConfigNode(currentPositionPath);

        // Hide help if there isn't information or it is the recipes root node.
        if (node === null || node.path === 'project.recipes' || !node.hasOwnProperty('reference')) {
            helpContainer.hide();
        } else {
            var content = node.reference
                .trim()
                .replace(/(# Prototype.*\n)([\w_-]+):/mg, '$1<span class="key"><span class="prototype">[$2]</span>:</span>')
                .replace(/<comment>/g, '<span class="comment">')
                .replace(/<\/comment>/g, '</span>')
                .replace(/<info>/g, '<span class="info">')
                .replace(/<\/info>/g, '</span>')
                .replace(/^( *)(\w+):/mg, '$1<span class="key">$2:</span>')
            ;
            helpContainer.find('.reference').html(content);
            helpContainer.show();
        }
    }
    function hideHelp() {
        $('#help').hide();
    }
    var configCompleter = {
        getCompletions: function(editor, session, pos, prefix, callback) {
            var tree, wordList, currentPositionPath, configEnv, usedWords;
            tree = parseTree();
            // Info: the pos.column is the current cursor position. We need the "start of the word", so we decrease it with length of prefix.
            currentPositionPath = getParentMetaTagPath(tree, pos.row, pos.column - prefix.length);
            configEnv = getConfigEnvironment(currentPositionPath);
            usedWords = getUsedWords(tree, currentPositionPath);
            wordList = getConfigWords(configEnv, usedWords);
            callback(null, wordList);
        }
    };
    // We don't use the addComplementer, because we want to remove the default 'local' complementer. We don't need for it.
    langTools.setCompleters([configCompleter]);

    $(document).ready( function() {
        $('#sidebar').fileTree({ root: '/', script: 'components/filetree.php'}, function(file) {
            alert(file);
        });
    });
</script>

</body>
</html>
