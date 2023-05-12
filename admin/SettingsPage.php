<?php

namespace Dudo1985\CNRT;

class SettingsPage {
    public function init() {
        //hook to admin menu to add the link to the setting page
        add_action('admin_menu', array($this, 'addOptionsPageLink'));

        add_action('admin_init', array($this, 'cnrtSettingsGroup')); //This is for general options

        define('CNRT_SAVE_All_SETTINGS_TEXT', esc_html__('Save all settings', 'comments-not-replied-to'));
    }


    /**
     * Add page's setting link
     *
     * @author Dario Curvino <@dudo>
     * @since 1.0.0
     */
    public function addOptionsPageLink () {
        $option_page = add_menu_page(
            'Comments Not Replied To: Settings', //Page Title
            esc_html__( 'Comments Not Replied To', 'comments-not-replied-to' ), //Menu Title
            'manage_options', //capability
            'cnrt_settings_page', //menu slug
            array($this, 'optionsPageCallback'), //The function to be called to output the content for this page.
        'dashicons-format-chat'
        );

        add_submenu_page(
            'cnrt_settings_page',
            'Comments Not Replied To: Settings',
            'Settings',
            'manage_options',
            'cnrt_settings_page'
        );

        if (cnrt_fs()->is_free_plan() && !cnrt_fs()->is_trial()) {
            global $submenu;
            $permalink                       = '#';
            $contact_us_string               = sprintf(
                esc_html__('Contact Us %s', 'comments-not-replied-to'),
                '<span class="dashicons dashicons-lock" />'
            );
            $submenu['cnrt_settings_page'][] = array($contact_us_string, 'manage_options', $permalink);
        }

        if(!defined('CNRT_SETTINGS_PAGE')) {
            define('CNRT_SETTINGS_PAGE', $option_page);
        }
    }

    /**
     * Callback for setting page
     *
     * @author Dario Curvino <@dudo>
     * @since 1.0.0
     */
    public function optionsPageCallback () {
        if (!current_user_can('manage_options')) {
            wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'comments-not-replied-to' ) );
        }

        $this->settingsPage();
    }

    /**
     * @author Dario Curvino <@dudo>
     * @since 1.0.0
     * @return void
     */
    private function settingsPage() {
        ?>
        <div class="wrap">
            <h2>
                <?php esc_html_e('Comments Not Replied To: Settings', 'comments-not-replied-to') ?>
            </h2>
            <div class="cnrt-settingsdiv">
                <form action="options.php" method="post" id="cnrt-settings-form">
                    <?php
                        settings_fields('cnrt_settings_group');
                        do_settings_sections('cnrt_settings_page');
                        submit_button(CNRT_SAVE_All_SETTINGS_TEXT);
                    ?>
                </form>
            </div>
            <div class="cnrt-settings-clear"></div>
            <?php
                $panel = new SettingsPageRightPanel();
                $panel->init();
            ?>
        </div>
        <?php
    }

    /**
     * Register settings section and field for TMDB
     *
     * @author Dario Curvino <@dudo>
     * @since 1.5.0
     */
    public function cnrtSettingsGroup() {
        register_setting(
            'cnrt_settings_group', // A settings group name. Must exist prior to the register_setting call.
            // This must match the group name in settings_fields()
            'cnrt_settings', //The name of an option to sanitize and save.
            [$this, 'sanitzeCnrtSettings']
        );

        $cnrt_settings    = get_option('cnrt_settings');
        $cnrt_description = esc_html__('Select when a comment should be marked as replied.', 'comments-not-replied-to');

        add_settings_section(
            'cnrt_section',
            esc_html__('General settings', 'comments-not-replied-to'),
            '',
            'cnrt_settings_page'
        );

        add_settings_field(
            'cnrt_mark_as_replied',
            $cnrt_description,
            [$this, 'markAsRepliedSettings'],
            'cnrt_settings_page',
            'cnrt_section',
            $cnrt_settings
        );

    }

    /**
     * Print the settings to choose when a comment should be marked as replied
     *
     * @author Dario Curvino <@dudo>
     * @since 1.5.0
     *
     * @param $cnrt_settings
     */
    public function markAsRepliedSettings($cnrt_settings) {
        $array_roles= array('admin', 'editor', 'author');

        foreach ($array_roles as $role) {
            if(isset($cnrt_settings['mark_as_replied'][$role]) && $cnrt_settings['mark_as_replied'][$role] === 'yes' ) {
                $array_roles[$role] = true;
            } else {
                $array_roles[$role] = false;
            }
        }

        $this->markAsRepliedHtml($array_roles);
    }

    /**
     * @author Dario Curvino <@dudo>
     * @since 1.5.0
     * @param array $array_roles || An array with keys "admin", "editor" and "author"
     *
     */
    private function markAsRepliedHtml ($array_roles) {
        ?>
        <strong>
            <?php esc_html_e('Remove "not replied" status, when...', 'comments-not-replied-to') ?>
        </strong>
        <p></p>

        <div class="cnrt-group-checkboxes">
            <input type="checkbox" id="author" disabled="disabled" checked="checked">
            <label for="author">
                <?php esc_html_e('Post Author has replied (default)', 'comments-not-replied-to') ?>
            </label>
        </div>

        <div class="cnrt-group-checkboxes">
            <input type="checkbox"
                   id="admin"
                   name="cnrt_settings[mark_as_replied][admin]"
                   value="yes"
                   <?php if ($array_roles['admin'] === true){ echo 'checked="checked"'; }?>
            />
            <label for="admin">
                <?php esc_html_e('An Admin has replied', 'comments-not-replied-to') ?>
            </label>
        </div>

        <div class="cnrt-group-checkboxes">
            <input type="checkbox"
                   id="editor"
                   name="cnrt_settings[mark_as_replied][editor]"
                   value="yes"
                   <?php if ($array_roles['editor'] === true){ echo 'checked="checked"'; }?>
            />
            <label for="editor">
                <?php esc_html_e('An Editor has replied', 'comments-not-replied-to') ?>
            </label>
        </div>

        <div class="cnrt-group-checkboxes">
            <input type="checkbox"
                   id="another-author"
                   name="cnrt_settings[mark_as_replied][author]"
                   value="yes"
                   <?php if ($array_roles['author'] === true){ echo 'checked="checked"'; } ?>
            />
            <label for="another-author">
                <?php esc_html_e('Another Author has replied', 'comments-not-replied-to') ?>
            </label>
        </div>

        <?php
    }


    /***
     * @author Dario Curvino <@dudo>
     * @since 1.5.0
     * @param $options
     *
     * @return array
     */
    public function sanitzeCnrtSettings($options) {
        // Create our array for storing the validated options
        $output = array();

        // Loop through each of the incoming options
        foreach($options as $key => $value ) {
            // Check to see if the current option has a value. If so, process it.
            if(isset($value)) {
                if(!is_array($value)) {
                    // Strip all HTML and PHP tags and properly handle quoted strings
                    $output[$key] = esc_html(strip_tags(stripslashes($value)));
                }
                //If the value is another array, this means that settings come from multiple checkboxes
                else {
                    foreach ($value as $inner_key => $inner_value) {
                        if($inner_value === 'yes') {
                            $output[$key][$inner_key] = $inner_value;
                        }
                    }
                }
            } // end it
        } // end foreach

        return $output;
    }
}