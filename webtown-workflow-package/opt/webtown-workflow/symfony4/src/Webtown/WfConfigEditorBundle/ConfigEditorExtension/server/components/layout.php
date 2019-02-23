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
    <div id="tabs"></div>
    <div id="editors"></div>
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
<!-- load ace modelist extension -->
<script src="/js/ace-noconflict/ext-modelist.js"></script>
<script src="/js/editor.js"></script>
<script>
    // Insert the config json
    var compConfig = <?php include sprintf('%s/%s/%s/%s', $projectPath, $wfConfigDir, 'config_editor', 'full_config.json') ?>;
    $(document).ready( function() {
        $('#sidebar').fileTree({ root: '/', script: 'components/filetree.php'}, function(file) {
            loadFile(file);
        });
        loadFile('/<?php echo $baseConfigFile ?>');
    });
</script>

</body>
</html>
