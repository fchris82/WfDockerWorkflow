<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $projectPath ?> | Config editor</title>
    <link rel="stylesheet" href="/js/jquery-ui-1.12.1.custom/jquery-ui.css" />
    <style type="text/css" media="screen">
        body {
            overflow: hidden;
        }

        #container {
            margin: 0;
            position: absolute;
            top: 0;
            bottom: 0;
            left: 0;
            right: 0;
        }

        #sidebar {
            margin: 0;
            position: absolute;
            top: 0;
            bottom: 0;
            left: 0;
            right: 80%;
        }

        #editor {
            margin: 0;
            position: absolute;
            top: 0;
            bottom: 0;
            left: 20%;
            right: 0;
        }
    </style>
</head>
<body>
<div id="container">
    <div id="sidebar">Sidebar</div>
    <pre id="editor"><?php echo file_get_contents(sprintf('%s/%s', $projectPath, $baseConfigFile)) ?></pre>
</div>

<script src="/js/jquery-3.3.1.min.js"></script>
<script src="/js/jquery-ui-1.12.1.custom/jquery-ui.js"></script>
<script src="/js/jquery.toastmessage.js"></script>
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
        enableLiveAutocompletion: true
    });
    var compConfig = <?php include sprintf('%s/%s/%s/%s', $projectPath, $wfConfigDir, 'config_editor', 'full_config.json') ?>;
    function getLineDepth(lines, row) {
        return $.isArray(lines[row]) && lines[row].length > 0
            ? lines[row][0].value.split(' ').length - 1
            : 0;
    }
    function getLineKey(lines, row) {
        return $.isArray(lines[row]) && lines[row].length > 0 && lines[row][0].type === 'meta.tag'
            ? lines[row][0].value.trim()
            : null;
    }
    function getCurrentNode(target) {
        var current = compConfig, key;
        for (var i=0;i<target.length;i++) {
            key = target[i];
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
    function getConfigEnvironment(target) {
        var current = getCurrentNode(target);

        if (typeof current === 'object' && current.hasOwnProperty('children') && !current.hasOwnProperty('prototype')) {
            return current.children;
        }

        return null;
    }
    function getConfigWords(env, usedWords) {
        if (env === null) {
            return [];
        }

        var words = [];
        $.each(env, function (key, value) {
            if (usedWords.indexOf(key) === -1) {
                words.push({
                    name: key,
                    value: value.hasOwnProperty('default')
                        ? key + ': ' + JSON.stringify(value.default)
                        : key + ":\n",
                    score: value.required ? 300 : 200,
                    meta: value.required ? "require" : "optional"
                });
            }
        });

        return words;
    }
    var configCompleter = {
        getCompletions: function(editor, session, pos, prefix, callback) {
            var wordList = [], configEnv;
            var lines = session.bgTokenizer.lines;
            var depth, key, chain = [], usedWords = [],
                currentDepth = getLineDepth(lines, pos.row) ? getLineDepth(lines, pos.row) : pos.column - prefix.length,
                currentKey = getLineKey(lines, pos.row);
            // We go backwards to find the "breadcrumbs"
            for (var i=pos.row;i>=0;i--) {
                // The current line is the `lines[i]`. If the line is object (Array) && the line isn't empty...
                if ($.isArray(lines[i]) && lines[i].length > 0) {
                    // If the first "tag"/"word" is a `meta.tag`
                    if (lines[i][0].type === 'meta.tag') {
                        key = getLineKey(lines, i);
                        depth = getLineDepth(lines, i);
                        // If the key isn't null
                        if (typeof key === 'string') {
                            // If the current key is a parent (the first is the real parent)
                            if (depth < currentDepth && !chain.hasOwnProperty(depth)) {
                                chain[depth] = key;
                            // Collecting the used words. Same depth and below the first parent
                            } else if (depth === currentDepth && chain.length === 0) {
                                usedWords.push(key);
                            }
                            // If we found the top parent
                            if (depth === 0) {
                                break;
                            }
                        }
                    }
                }
            }
            chain = chain.flat();
            if (typeof currentKey === 'string') {
                chain.push(currentKey);
            }
            configEnv = getConfigEnvironment(chain);
            wordList = getConfigWords(configEnv, usedWords);
            callback(null, wordList);
        }
    };
    langTools.setCompleters([configCompleter]);
</script>

</body>
</html>
