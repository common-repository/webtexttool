<div id="schema-card-settings">
    <p><strong>Choose whether the site represents a person or an organization:</strong></p>
    <div class="wtt_select">
        <?php $wttform->select_option_field('knowledgegraph_type', array('organization' => 'Organization', 'person' => 'Person')); ?>
    </div>

    <div class="wtt-input-group wtt-knowledgegraph-group">
        <?php
        $wttform->text_field('knowledgegraph_name', 'The organization or person name:', 'wtt-knowledgegraph-form-control', 'wtt_social');
        ?>
    </div>

    <div id="social-schema-settings">
        <?php
        $wttform->input_file('knowledgegraph_logo', 'Organization logo', 'wtt_social');
        ?>
    </div>

    <div class="wtt-input-group wtt-knowledgegraph-group">
        <?php
        $wttform->text_field('knowledgegraph_location_streetAddress', 'Address:', array('class' => 'wtt-knowledgegraph-form-control', 'placeholder' => 'Street Address'), 'wtt_social');
        $wttform->text_field('knowledgegraph_location_addressLocality', null, array('class' => 'wtt-knowledgegraph-form-control', 'placeholder' => 'Locality e.g. "Amsterdam"'), 'wtt_social');
        $wttform->text_field('knowledgegraph_location_addressRegion', null, array('class' => 'wtt-knowledgegraph-form-control', 'placeholder' => 'Region'), 'wtt_social');
        $wttform->text_field('knowledgegraph_location_postalCode', null, array('class' => 'wtt-knowledgegraph-form-control', 'placeholder' => 'Postal Code'), 'wtt_social');
        $wttform->text_field('knowledgegraph_location_addressCountry', null, array('class' => 'wtt-knowledgegraph-form-control', 'placeholder' => 'Country'), 'wtt_social');
        ?>
    </div>
</div>