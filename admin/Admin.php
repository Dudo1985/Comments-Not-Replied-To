<?php

namespace Dudo1985\CNRT;

/**
 * @author Dario Curvino <@dudo>
 * @since
 * @return
 */

class Admin {

    public function init() {
        $this->settingsPage();
        $this->commentPage();

        add_action('admin_enqueue_scripts', array($this, 'enqueueScripts'));
    }

    public function commentPage() {
        $cnrt_admin = new EditComments();
        $cnrt_admin->init();
    }

    /**
     * Init settings page
     *
     * @author Dario Curvino <@dudo>
     * @since  1.5.0
     */
    public function settingsPage() {
        $cnrt_settings = new SettingsPage();
        $cnrt_settings->init();
    }

    //$hook contain the current page in the admin side
    public function enqueueScripts($hook) {
        //This is required to use wp_add_inline_script without dependency
        //https://wordpress.stackexchange.com/a/311279/48442
        wp_register_script( 'cnrt-global-data', '', [], '', true );
        wp_enqueue_script( 'cnrt-global-data' );

        //enqueue css
        $this->enqueueCSS($hook);

        //enqueue js
        $this->enqueueJS($hook);

        //inline scripts
        $this->inlineScripts($hook);
    }

    /**
     * Enqueue css
     *
     * @author Dario Curvino <@dudo>
     * @since 1.5.0
     * @param $hook
     */
    private function enqueueCSS($hook) {
        //filter to enqueue CSS here
        do_action('cnrt_enqueue_css', $hook);

        if ($hook === CNRT_SETTINGS_PAGE) {
            wp_enqueue_style(
                'cnrt_css', CNRT_CSS_DIR_ADMIN . 'admin.css', false, CNRT_VERSION
            );
        }

        if($hook === 'comments-not-replied-to_page_cnrt_settings_page-pricing') {
            wp_enqueue_style(
                'cnrt_pricing_css', CNRT_CSS_DIR_ADMIN . 'pricing-page.css', false, CNRT_VERSION
            );
        }
    }

    /**
     * @author Dario Curvino <@dudo>
     * @since 1.5.0
     * @param $hook
     */
    private function enqueueJS($hook) {
        if($hook === 'comments-not-replied-to_page_cnrt_settings_page-pricing') {
            wp_enqueue_script(
                'cnrtjs-pricing', CNRT_JS_DIR_ADMIN . 'cnrt-pricing-page.js', array('wp-element'), CNRT_VERSION, true
            );
        }

        //filter to enqueue JS here
        do_action('cnrt_enqueue_js', $hook);
    }

    /**
     * @author Dario Curvino <@dudo>
     * @since 1.5.0
     * @param $hook
     */
    private function inlineScripts($hook) {
        $cnrt_common_data = json_encode(array(
            'siteUrl'       => site_url(),
            'ajaxurl'       => admin_url('admin-ajax.php'),
            'adminUrl'      => admin_url()
        ));

        //check if wp_add_inline_script has already run before
        if(!defined('CNRT_GLOBAL_DATA_EXISTS')) {
            wp_add_inline_script(
                'cnrt-global-data', 'var cnrtCommonData = ' . $cnrt_common_data, 'before'
            );

            //use a constant to be sure that yasr-global-data is not loaded twice
            define ('CNRT_GLOBAL_DATA_EXISTS', true);
        }
    }
}