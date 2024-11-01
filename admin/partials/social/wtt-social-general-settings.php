<div id="social-metabox-settings">
    <h3>Textmetrics metabox settings</h3>

    <p><strong>Activate the <?php echo __(WTT_PLUGIN_NAME); ?> settings metabox:</strong></p>
    <?php
    $wttform->switch_field('socialmetabox', array('on' => 'Yes', 'off' => 'No'), '','wtt_social');
    ?>
</div>

<div id="canonical-settings">
    <h3>Canonical URL</h3>

    <p><strong>Set Canonical URL in the header:</strong></p>
    <p class="wtt-description"><code>&lt;link rel="canonical" href="..."/&gt;</code></p>
    <?php
    $wttform->switch_field('canonical_url', array('on' => 'Enabled', 'off' => 'Disabled'), '','wtt_social');
    ?>
</div>

<div id="seo-title-settings">
    <h3>SEO title tag</h3>

    <p><strong>Enable SEO title in the header:</strong></p>
    <p class="wtt-description"><code>&lt;title>...&lt;/title></code></p>
    <?php
    $wttform->switch_field('enable_seo_title', array('on' => 'Enabled', 'off' => 'Disabled'), '','wtt_social');
    ?>
</div>

<div id="meta-description-settings">
    <h3>Meta Description tag</h3>

    <p><strong>Include Meta Description tag in the header:</strong></p>
    <p class="wtt-description"><code>&lt;meta name="description" content="..." /&gt;</code></p>
    <?php
    $wttform->switch_field('show_meta_desc', array('on' => 'Enabled', 'off' => 'Disabled'), '','wtt_social');
    ?>
</div>