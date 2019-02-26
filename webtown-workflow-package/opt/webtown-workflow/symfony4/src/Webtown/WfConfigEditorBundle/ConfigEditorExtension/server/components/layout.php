<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $projectPath ?> | Config editor</title>
    <link rel="stylesheet" href="/js/jquery-ui-1.12.1.custom/jquery-ui.css" />
    <link rel="stylesheet" href="/css/jquery.toastmessage.css" />
    <link rel="stylesheet" href="/js/jqueryfiletree/jQueryFileTree.min.css" />
    <link rel="stylesheet" href="/js/bootstrap-4.3.1-dist/css/bootstrap.css" />
    <link rel="stylesheet" href="/css/main.css" />
</head>
<body>
<div id="container">
    <div id="sidebar"></div>
    <div id="editors">
        <div id="buttons" class="btn-group btn-group-sm float-right" role="group">
            <button class="save btn btn-secondary disabled">Save</button>
            <button class="save-all btn btn-secondary disabled">Save all</button>
            <button class="fold-all btn btn-secondary">Fold all</button>
            <button class="unfold-all btn btn-secondary">Unfold all</button>
        </div>
        <ul></ul>
    </div>
    <div id="help">
        <pre class="reference"></pre>
    </div>
</div>

<script src="/js/jquery-3.3.1.min.js"></script>
<script src="/js/jquery-ui-1.12.1.custom/jquery-ui.js"></script>
<script src="/js/bootstrap-4.3.1-dist/js/bootstrap.js"></script>
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
        tabs = $( "#editors" ).tabs();
        openHelpReference();
        // Load base file
        loadFile('/<?php echo $baseConfigFile ?>');
        // Init tabs
        // Close icon: removing the tab on click
        tabs.on( "click", "span.ui-icon-close", function() {
            var panelId = $( this ).closest( "li" ).remove().attr( "aria-controls" );
            $( "#" + panelId ).remove();
            reset();
        });
        tabs.on("tabsactivate", function() {
            reset();
            refreshUnsavedTabs();
        });
        $('#buttons .save').on('click', function() {
            if (!$(this).hasClass('disabled')) {
                $(this).addClass('disabled');
                saveActiveTab();
            }
        });
        $('#buttons .save-all').on('click', function() {
            if (!$(this).hasClass('disabled')) {
                $(this).addClass('disabled');
                saveAll();
            }
        });
        $('#buttons .fold-all').on('click', function() {
            getActiveEditor().getSession().foldAll();
        });
        $('#buttons .unfold-all').on('click', function() {
            getActiveEditor().getSession().unfold();
        });
    });
</script>

</body>
</html>
