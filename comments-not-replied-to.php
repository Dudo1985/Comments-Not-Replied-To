<?php
/**
 * Plugin Name: Comments Not Replied To
 * Plugin URI: https://wordpress.org/plugins/comments-not-replied-to/
 * Description: Easily see which comments have not received a reply from each post's author.
 * Version: 1.5.3
 * Text Domain: comments-not-replied-to
 * Domain Path: /lang
 * Author: Dario Curvino
 * License: GPL2
 *
 * @fs_premium_only /cnrt_pro/
 *
 *
License:

  Copyright 2013 - 2014 Pippin Williamson, Andrew Norcross, Tom McFarlin
  Copyright 2021 Dario Curvino

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

if ( ! defined( 'ABSPATH' ) ) {
    exit( 'You\'re not allowed to see this page' );
} // Exit if accessed directly



if ( ! function_exists( 'cnrt_fs' ) ) {
    // Create a helper function for easy SDK access.
    function cnrt_fs() {
        global $cnrt_fs;

        if ( ! isset( $cnrt_fs ) ) {
            // Include Freemius SDK.
            require_once __DIR__ . '/freemius/start.php';

            try {
                $cnrt_fs = fs_dynamic_init(array(
                    'id'                  => '9260',
                    'slug'                => 'comments-not-replied-to',
                    'type'                => 'plugin',
                    'public_key'          => 'pk_a38e9e8391d7e26bcd8abfb2ba778',
                    'is_premium'          => true,
                    // If your plugin is a serviceware, set this option to false.
                    'has_premium_version' => true,
                    'has_addons'          => false,
                    'has_paid_plans'      => true,
                    'trial'               => array(
                        'days'               => 14,
                        'is_require_payment' => false,
                    ),
                    'menu'                => array(
                        'slug'    => 'cnrt_settings_page',
                        'contact' => false,
                        'support' => false,
                    ),
                ));
            } catch (Freemius_Exception $e) {
            }
        }

        return $cnrt_fs;
    }

    // Init Freemius.
    cnrt_fs();
    // Signal that SDK was initiated.
    do_action( 'cnrt_fs_loaded' );
}

if(!defined('CNRT_VERSION')) {
    define('CNRT_VERSION', '1.5.4');
} // end if

require 'commentsNotRepliedTo.php';
$cnrt_inc = new commentsNotRepliedTo;
$cnrt_inc->init();

if(is_admin()) {
    require 'admin/CNRT_Admin.php';
    $cnrt_admin = new CNRT_Admin();
    $cnrt_admin->init();

    //this add a link under the plugin name, must be in the main plugin file
    add_filter('plugin_action_links_' . plugin_basename( __FILE__ ), static function($links) {
        $settings_link = '<a href="' . esc_url(admin_url( 'options-general.php?page=cnrt_settings_page' )) . '">';
        $settings_link .= esc_html__('Settings', 'comments-not-replied-to');
        $settings_link .= '</a>';

        //array_unshit adds to the begin of array
        array_unshift($links, $settings_link);

        return $links;
    });
}

//this define must triggered after the active theme's functions.php file is loaded
add_action('init', function (){
    $text = esc_html__('This feature is available only in the pro version', 'comments-not-replied-to');
    $lockImage =
        apply_filters('cnrt_feature_locked',
            '<a href="'.esc_url(cnrt_fs()->get_upgrade_url()).'">
                <span class="dashicons dashicons-lock" title="'.esc_attr($text).'"></span>
            </a>',
            10, 1);
    define ('CNRT_LOCKED_FEATURE', $lockImage);
});


if (cnrt_fs()->is__premium_only()) { //these if can't be merged
    if (cnrt_fs()->can_use_premium_code()) {
        //Init CNRT Pro
        require CNRT_ABSOLUTE_PATH . '/cnrt_pro/CNRT_pro.php';
        $pro_version = new CNRT_pro();
        $pro_version->init();
    }
}
