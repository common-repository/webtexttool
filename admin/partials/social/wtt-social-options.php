<div id="facebook-opengraph-settings">
    <h3><i class="dashicons-before dashicons-facebook-alt"></i> Facebook settings</h3>

    <p><strong>Add Facebook Open Graph Tags:</strong></p>
    <?php
    $wttform->switch_field('opengraph', array('on' => 'Enabled', 'off' => 'Disabled'), '', 'wtt_social');
    ?>
</div>

<div id="twitter-card-settings">
    <h3><i class="dashicons-before dashicons-twitter"></i> Twitter settings</h3>

    <p><strong>Add Twitter Card Tags:</strong></p>
    <?php $wttform->switch_field('twitter', array('on' => 'Enabled', 'off' => 'Disabled'), '', 'wtt_social'); ?>

    <p><strong>The default card type to use:</strong></p>
    <div class="wtt_select">
        <?php $wttform->select_option_field('twitter_card_type', array('summary' => 'Summary', 'summary_large_image' => 'Summary with large image')); ?>
    </div>
</div>