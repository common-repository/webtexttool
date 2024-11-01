<?php
/**
 * Webtexttool login setup and routing
 *
 * @link       http://webtexttool.com
 * @since      1.0.0
 *
 * @package    Webtexttool
 * @subpackage Webtexttool/admin/partials/dashboard
 */
?>

<div id="wtt-dashboard" ng-controller="appController">

    <header>
        <h1><?php echo esc_html(get_admin_page_title()) . " - v" . WTT_VERSION ?></h1>
    </header>

    <?php

    function require_multi($files)
    {
        $files = func_get_args();
        foreach ($files as $file)
            require_once($file);
    }

    require_multi("wtt-login.php", "wtt-account.php");

    ?>

</div>