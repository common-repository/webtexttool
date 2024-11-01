<?php

class WTT_Form
{

    /**
     * @var object    Instance of this class
     */
    public static $instance;

    /**
     * Get the singleton instance of this class
     *
     * @return WTT_Form
     */
    public static function get_instance()
    {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Handles the switch field for the settings page
     *
     * @param string $var the variable
     * @param array $values the options to choose from
     * @param null $label
     * @param string|null $type the type of wp option to save
     */
    public function switch_field($var, $values, $label = null, string $type = null)
    {

        $option_name = $type;
        $options = get_option($option_name);

        if (!is_array($values) || $values === array()) {
            return;
        }
        if (!isset($options[$var])) {
            $options[$var] = false;
        }
        if ($options[$var] === true) {
            $options[$var] = 'on';
        }
        if ($options[$var] === false) {
            $options[$var] = 'off';
        }

        $var_esc = esc_attr($var);

        echo '<div class="wtt-switch-container">';
        echo '<div id="' . $var_esc . '">';
        echo $label != null ? '<label class="switch_label"><strong>' . $label . '</strong></label>' : "";
        echo '<div class="wtt-switch-field">';

        foreach ($values as $key => $value) {
            $key_esc = esc_attr($key);
            $for = $var_esc . '-' . $key_esc;
            echo '<input type="radio" id="' . $for . '" name="' . esc_attr($option_name) . '[' . $var_esc . ']" value="' . $key_esc . '" ' . checked($options[$var], $key_esc, false) . ' />',
            '<label class="wtt_switch_label" for="', $for, '">', $value, '</label>';
        }

        echo '<a></a></div></div><div class="clear"></div></div>' . "\n\n";
    }

    /**
     * Handles the text input fields
     *
     * @param string $var the variable
     * @param string $label the label of the text input field
     * @param string $url_example the url example
     */
    public function text_input_field($var, $label, $url_example)
    {
        $option_name = "wtt_social";
        $options = get_option($option_name);

        $val = (isset($options[$var])) ? $options[$var] : '';

        $this->label($label . ':', array('for' => $var));

        echo '<div class="wtt-input-group">';
        if (!empty($url_example)) {
            echo '<span class="wtt-input-group-addon">', $url_example, '</span>';
        }
        echo '<input class="wtt-form-control" type="text" id="', esc_attr($var), '" name="', esc_attr($option_name), '[', esc_attr($var), ']" value="', esc_attr($val), '"/>', '<br class="clear" />';
        echo '</div>';
    }

    /**
     * Displays a label
     *
     * @param string $text the text to diplay for the label
     * @param array $attr attributes for label including for and class
     */
    public function label($text, $attr)
    {
        $attr = wp_parse_args($attr, array(
                'for' => '',
                'class' => '',
            )
        );
        echo "<label class='". esc_attr($attr['class']) . "' for='" . esc_attr($attr['for']) . "'>$text" . '</label>';
    }

    /**
     * @param $field_name
     * @param $field_options
     */
    public function select_option_field($field_name, $field_options)
    {
        $option_name = "wtt_social";
        $options = get_option($option_name);

        if (empty($options)) {
            return;
        }

        $select_name = esc_attr($option_name) . '[' . esc_attr($field_name) . ']';
        $active_option = (isset($options[$field_name])) ? $options[$field_name] : '';

        echo '<select name="', esc_attr($select_name), '" id="', esc_attr($field_name), '">';
        foreach ($field_options as $option_attr_value => $option_html_value) : ?>
            <option value="<?php echo esc_attr($option_attr_value); ?>"<?php echo selected($active_option, $option_attr_value, false); ?>><?php echo esc_html($option_html_value); ?></option>
        <?php
        endforeach;
        echo '</select>';
    }


    /**
     * @param $var
     * @param $name
     */
    public function checkbox($var, $name)
    {
        $option_name = "wtt_social";
        $options = get_option($option_name);

        if (!isset($options[$var])) {
            $options[$var] = false;
        }

        if ($options[$var] === true) {
            $options[$var] = 'on';
        }

        echo '<input class="checkbox" type="checkbox" id="', esc_attr($var), '" name="', esc_attr($option_name), '[', esc_attr($var), ']" value="on"', checked($options[$var], 'on', false), '/>';
        echo '<small>' . $name . '</small>';
    }

    /**
     * @param $var
     * @param $values
     */
    public function radio($var, $values)
    {
        $option_name = "wtt_settings";
        $options = get_option($option_name);

        if (!isset($options[$var])) {
            $options[$var] = false;
        }

        if (!is_array($values) || $values === array()) {
            return;
        };

        $var_esc = esc_attr($var);

        foreach ($values as $key => $value) {
            $key_esc = esc_attr($key);
            echo '<input type="radio" class="radio" id="' . $var_esc . '-' . $key_esc . '" name="' . esc_attr($option_name) . '[' . $var_esc . ']" value="' . $key_esc . '" ' . checked($options[$var], $key_esc, false) . ' />';
            $this->label(
                $value,
                array('for' => $var_esc . '-' . $key_esc,
                    'class' => 'radio',
                )
            );
        }
    }

    /**
     * @param $var
     * @param $label
     * @param array $attributes
     * @param $type
     */
    public function text_field($var, $label, $attributes = array(), $type = null)
    {
        $option_name = $type;
        $options = get_option($option_name);

        if (!is_array($attributes)) {
            $attributes = array(
                'class' => $attributes,
            );
        }
        $attributes = wp_parse_args($attributes, array(
            'placeholder' => '',
            'class' => '',
        ));

        $values = (isset ($options[$var])) ? $options[$var] : '';

        $this->label(
            $label, array(
                'for' => $var,
                'class' => 'text_field_label',
            )
        );
        echo '<input class="text_field ' . esc_attr($attributes['class']) . ' " id="' . esc_attr($var) . '" placeholder="' . esc_attr($attributes['placeholder']) . '" type="text" id="', esc_attr($var), '" name="', esc_attr($option_name), '[', esc_attr($var), ']" value="', esc_attr($values), '"/>', '<br class="clear" />';
    }

    /**
     * @param $var
     * @param $label
     * @param array $attributes
     * @param $type
     */
    public function text_area($var, $label, $attributes = array(), $type = null)
    {
        $option_name = $type;
        $options = get_option($option_name);

        if (!is_array($attributes)) {
            $attributes = array(
                'class' => $attributes,
            );
        }
        $attributes = wp_parse_args($attributes, array(
            'cols' => '',
            'rows' => '',
            'class' => '',
        ));
        $values = (isset($options[$var])) ? $options[$var] : '';

        $this->label(
            $label,
            array(
                'for' => $var,
                'class' => 'text_area_label',
            )
        );
        echo '<textarea cols="' . esc_attr($attributes['cols']) . '" rows="' . esc_attr($attributes['rows']) . '" class="text_area ' . esc_attr($attributes['class']) . '" id="' . esc_attr($var) . '" name="' . esc_attr($option_name) . '[' . esc_attr($var) . ']">' . esc_textarea($values) . '</textarea><br class="clear" />';
    }

    /**
     * Displays label and file input field.
     *
     * @param string $key
     * @param string  $label
     * @param string $type
     */
    public static function input_file( $key, $label, $type ) {
        $option_name = $type;
        $options = get_option($option_name);

        $values = (isset($options[$key])) ? $options[$key] : '';

        ?>

        <label class="select" for="<?php echo $key ?>"><?php echo esc_html(wp_strip_all_tags($label)) ?></label>
        <input type="text" id="<?php echo $key ?>" name="<?php echo $option_name . '[' . $key . ']'; ?>" value="<?php echo esc_attr( $values ); ?>">
        <input id="wtt_knowledgegraph-image_button" class="wtt_image_upload_button button" type="button" value="Upload image">
        <?php
    }
}