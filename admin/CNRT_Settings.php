<?php

class CNRT_Settings {
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
            <?php $this->rightPanel(); ?>
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

    /**
     * @author Dario Curvino <@dudo>
     * @since  1.0.0
     */
    private function rightPanel () {
        $this->upgradeBox();
        $this->buyACoffee();
        $this->alsoLike();
        $this->askRating();
    }

    private function upgradeBox() {
        if (cnrt_fs()->is_free_plan()) {
            ?>
            <div class="cnrt-donatedivdx">
                <h2 class="cnrt-donate-title" style="color: #34A7C1">
                    <?php esc_html_e('Upgrade to CNRT Pro', 'comments-not-replied-to'); ?>
                </h2>
                <div class="cnrt-upgrade-to-pro">
                    <ul>
                        <li>
                            <strong>
                                + <?php esc_html_e('Mark single comment as read', 'comments-not-replied-to'); ?>
                            </strong>
                        </li>
                        <li>
                            <strong>
                                + <?php esc_html_e('Select the single user who can remove the "not replied" status (available soon)',
                                    'comments-not-replied-to'); ?>
                            </strong>
                        </li>
                        <li>
                            <strong>
                                + <?php esc_html_e('Customize icons and text (available soon)',
                                    'comments-not-replied-to'); ?>
                            </strong>
                        </li>
                        <li>
                            <strong>
                                + <?php esc_html_e('Priority support', 'comments-not-replied-to'); ?>
                            </strong>
                        </li>
                    </ul>
                    <a href="<?php echo esc_url(cnrt_fs()->get_upgrade_url()); ?>">
                        <button class="button button-primary">
                    <span style="font-size: large; font-weight: bold;">
                        <?php esc_html_e('Upgrade Now', 'comments-not-replied-to')?>
                    </span>
                        </button>
                    </a>
                    <div style="display: block; margin-top: 10px; margin-bottom: 10px; ">
                        --- or ---
                    </div>
                    <a href="<?php echo esc_url(cnrt_fs()->get_trial_url()); ?>">
                        <button class="button button-primary">
                    <span style="display: block; font-size: large; font-weight: bold; margin: -3px;">
                        <?php esc_html_e('Start Free Trial', 'comments-not-replied-to') ?>
                    </span>
                            <span style="display: block; margin-top: -10px; font-size: smaller;">
                         <?php esc_html_e('No credit-card, risk free!', 'comments-not-replied-to') ?>
                    </span>
                        </button>
                    </a>
                </div>
            </div>
            <?php
        }

    }

    /**
     * @author Dario Curvino <@dudo>
     * @since 1.5.0
     */
    private function buyACoffee() {
        $buymecofeetext = esc_html__('Coffee is vital to make "Comments Not Replied To" development going on!', 'comments-not-replied-to');
        $buymecofeetext .= '<br />';

        if(cnrt_fs()->is_free_plan()) {
            $buymecofeetext .= esc_html__('If you are enjoying CNRT, and you don\'t need the pro version, please consider to buy me a coffee, thanks!',
                'comments-not-replied-to');
        } else {
            $buymecofeetext .= esc_html__('If you are enjoying CNRT, please consider to buy me a coffee, thanks!',
                'comments-not-replied-to');
        }

        $div   = "<div class='cnrt-donatedivdx' id='cnrt-buy-cofee'>";
        $text  = '<div class="cnrt-donate-title">' . esc_html__('Buy me a coffee!', 'comments-not-replied-to') .'</div>';
        $text .= '<div class="cnrt-donate-content">';
        $text .= '<a href="https://www.paypal.com/donate/?hosted_button_id=SVTAVUF62QZ4W" target="_blank">
                    <img src="'.CNRT_IMG_DIR_ADMIN.'/button_paypal.png" alt="paypal" width="200">
                  </a>';
        $text .= '<p>';
        $text .= $buymecofeetext;
        $text .= '</p>';
        $text .= '</div>';
        $div_and_text = $div . $text . '</div>';

        echo wp_kses_post($div_and_text);
    }

    /**
     * @author Dario Curvino <@dudo>
     * @since
     */
    private function askRating() {
        $div = "<div class='cnrt-donatedivdx' id='cnrt-ask-five-stars'>";

        $text = '<div class="cnrt-donate-title">' . esc_html__('Can I ask your help?', 'comments-not-replied-to') .'</div>';
        $text .= '<div class="cnrt-donate-content">';
        $text .= '<div style="font-size: 32px; color: #F1CB32; margin-bottom: 20px; margin-top: -5px;">
                        <span class="dashicons dashicons-star-filled" style="font-size: 26px;"></span>
                        <span class="dashicons dashicons-star-filled" style="font-size: 26px;"></span>
                        <span class="dashicons dashicons-star-filled" style="font-size: 26px;"></span>
                        <span class="dashicons dashicons-star-filled" style="font-size: 26px;"></span>
                        <span class="dashicons dashicons-star-filled" style="font-size: 26px;"></span>
                    </div>';
        $text .= '<p>';
        $text .= esc_html__('Please rate "Comment Not Replied" To 5 stars on', 'comments-not-replied-to');
        $text .= ' <a href="https://wordpress.org/plugins/comments-not-replied-to/">
        WordPress.org.</a><br />';
        $text .= esc_html__(' It will require just 1 min but it\'s a HUGE help for me. Thank you.', 'comments-not-replied-to');
        $text .= '<br /><br />';
        $text .= '<em>> Dario Curvino</em>';
        $text .= '<p>';
        $text .= '</div>';

        $div_and_text = $div . $text . '</div>';

        echo wp_kses_post($div_and_text);
    }

    /**
     * @author Dario Curvino <@dudo>
     * @since 1.0.0
     */
    private function alsoLike() {
        $text  = "<div class='cnrt-donatedivdx' id='alsolike'>";
        $text .= '<div class="cnrt-donate-title">' . esc_html__('You may also like...', 'comments-not-replied-to') .'</div>';
        $text .= '<div class="cnrt-donate-content">';
        $text .= $this->yasr();
        $text .= '</p><hr />';
        $text .= $this->movieHelper();
        $text .= '</div>'; //second div
        $text .= '</div>'; //first div

        echo wp_kses_post($text);
    }

    /**
     * Yasr Box
     *
     * @author Dario Curvino <@dudo>
     * @since 1.0.2
     * @return string
     */
    private function yasr() {
        $text = '<a href="https://wordpress.org/plugins/yet-another-stars-rating/">';
        $text .= '<img src="'.esc_url(CNRT_IMG_DIR_ADMIN).'/yasr.png" alt="yasr" width="110">';
        $text .= '<div>YASR - Yet Another Stars Rating</div>';
        $text .= '</a>';
        $text .= '<p>';
        $text .= esc_html__('Boost the way people interact with your site with an easy WordPress stars rating system! 
        With Schema.org rich snippets YASR will improve your SEO!', 'comments-not-replied-to');

        return $text;
    }

    /**
     * Movie Helper box
     *
     * @author Dario Curvino <@dudo>
     * @since 1.0.2
     * @return string
     */
    private function movieHelper() {
        $movie_helper_description =
            esc_html__('Movie Helper allows you to easily add links to movie and tv shows, just by searching them while you\'re 
                writing your content. Search, click, done!',
                'yet-another-stars-rating'
            );
        $text = '<a href="https://wordpress.org/plugins/yet-another-movie/" target="_blank">
                    <img src="'.esc_url(CNRT_IMG_DIR_ADMIN).'/movie_helper.svg" alt="Movie Helper" >
                  </a>';
        $text .= '</a>';
        $text .= '<p>';
        $text .= $movie_helper_description;

        return $text;
    }
}