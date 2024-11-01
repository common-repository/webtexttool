<div id="social-image-settings">
    <label class="select" for="opengraph_image">Use default image:</label>
    <input type="text" id="opengraph_image" name="wtt_social[opengraph_image]" value="<?php echo (($wtt_social['opengraph_image']) <> '') ? $wtt_social['opengraph_image'] : '' ?>">
    <input id="wtt_opengraph-image_button" class="wtt_image_upload_button button"
           type="button" value="Upload image">

    <p><strong>Use image from:</strong></p>
    <div>
        <ol>
            <li>
                <?php $wttform->checkbox('og_image_use_specific', 'Use "Social Meta Image" field on the post'); ?>
            </li>
            <li>
                <?php $wttform->checkbox('og_image_use_featured', 'Use featured image from the post'); ?>
            </li>
            <li>
                <?php $wttform->checkbox('og_image_use_default', 'Use default image specified above'); ?>
            </li>
        </ol>
    </div>
</div>