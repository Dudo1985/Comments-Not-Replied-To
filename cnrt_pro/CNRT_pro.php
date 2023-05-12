<?php

namespace Dudo1985\CNRT;

if ( ! defined( 'ABSPATH' ) ) {
    exit( 'You\'re not allowed to see this page' );
} // Exit if accessed directly


/**
 * @author Dario Curvino <@dudo>
 * @since 1.5.0
 * Class CNRT_pro
 */
class CNRT_pro {

    /**
     * In Pro version
     *
     * @author Dario Curvino <@dudo>
     * @since 1.5.0
     */
    public function init() {

        $this->defineConstants();

        //Change lock icon
        add_filter('cnrt_feature_locked', static function (){
            $text = esc_html__('You\'ve unlocked this feature!', 'yasr-pro');
            return '<span class="dashicons dashicons-unlock" title="'.esc_attr($text).'"></span>';
        }, 10, 1);


        if(is_admin()) {
            //init admin pro
            $pro_version_admin = new AdminPro();
            $pro_version_admin->init();
        }

    }

    /**
     * Define constants of pro version
     *
     * @author Dario Curvino <@dudo>
     * @since 1.5.0
     */
    private function defineConstants() {
        //Plugin absolute path
        //e.g. /var/www/html/plugin_development/wp-content/plugins/comments-not-replied-to
        //by default, dirname get the parent
        define('CNRT_ABSOLUTE_PATH_PRO', __DIR__);

        define('CNRT_RELATIVE_PATH_PRO', dirname(plugin_basename(__FILE__)));

        //admin absolute path
        define('CNRT_ABSOLUTE_PATH_ADMIN_PRO', CNRT_ABSOLUTE_PATH_PRO . '/admin');

        //js admin pro
        define('CNRT_JS_DIR_ADMIN_PRO', plugins_url() . '/' . CNRT_RELATIVE_PATH_PRO . '/admin/js/');
    }
}