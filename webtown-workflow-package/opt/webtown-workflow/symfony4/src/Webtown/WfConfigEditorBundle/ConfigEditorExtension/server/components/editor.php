<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ACE Autocompletion demo</title>
    <style type="text/css" media="screen">
        body {
            overflow: hidden;
        }

        #editor {
            margin: 0;
            position: absolute;
            top: 0;
            bottom: 0;
            left: 0;
            right: 0;
        }
    </style>
</head>
<body>

<pre id="editor"><?php echo file_get_contents(sprintf('%s/%s', $projectPath, $baseConfigFile)) ?></pre>

<script src="/js/jquery-3.3.1.min.js"></script>
<script src="/js/jquery-ui-1.12.1.custom.zip"></script>
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
        enableLiveAutocompletion: false
    });
    var compConfig = <?php include sprintf('%s/%s/%s/%s', $projectPath, $wfConfigDir, 'config_editor', 'full_config.json') ?>;
    function getLineDepth(lines, row) {
        return lines[row][0].value.split(' ').length - 1;
    }
    function getLineKey(lines, row) {
        return lines[row][0].type === 'meta.tag' ? lines[row][0].value.trim() : null;
    }
    function getConfigWords(target) {
        var current = compConfig, key;
        for (var i=0;i<target.length;i++) {
            key = target[i];
            if (typeof current === 'object' && current.hasOwnProperty(key)) {
                current = current[key];
            } else {
                return [];
            }
        }

        if (typeof current === 'object') {
            return $.isArray(current) ? current : Object.keys(current);
        }

        return [];
    }
    var configCompleter = {
        getCompletions: function(editor, session, pos, prefix, callback) {
            var wordList = [];
            var lines = session.bgTokenizer.lines;
            var depth, key, chain = [], usedWords = [], currentDepth = getLineDepth(lines, pos.row), currentKey = getLineKey(lines, pos.row);
            for (var i=pos.row;i>=0;i--) {
                if (typeof lines[i] === 'object') {
                    if (lines[i][0].type === 'meta.tag') {
                        key = getLineKey(lines, i);
                        depth = getLineDepth(lines, i);
                        if (typeof key === 'string') {
                            if (depth < currentDepth && !chain.hasOwnProperty(depth)) {
                                chain[depth] = key;
                            } else if (depth === currentDepth) {
                                usedWords.push(key);
                            }
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
            wordList = getConfigWords(chain).filter(function (v) {
                return usedWords.indexOf(v) === -1;
            });
            callback(null, wordList.map(function(word) {
                return {name: word, value: word, score: 300, meta: "config"}
            }));
        }
    };
    langTools.setCompleters([configCompleter]);
</script>

</body>
</html>
