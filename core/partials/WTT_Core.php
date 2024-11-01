<script>
    try {
        if(typeof jQuery.ui !== 'undefined') {
            jQuery.widget.bridge('uitooltip', jQuery.ui.tooltip);
            jQuery.widget.bridge('uibutton', jQuery.ui.button);
        }
    }
    catch(err) {
        console.warn(err.message);
    }
</script>

<div ng-controller="editPageController" id="wtt-dashboard">
    <?php

    function require_multi($files)
    {
        $files = func_get_args();
        foreach ($files as $file)
            require_once($file);
    }

    require_multi("WTT_BlockHeader.php", "WTT_BlockWarning.php", "WTT_BlockResearch.php", "WTT_BlockSuggestions.php", "WTT_BlockSettings.php");

    ?>
</div>

