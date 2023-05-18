<?php

namespace Dudo1985\CNRT;

if ( ! defined( 'ABSPATH' ) ) {
    exit( 'You\'re not allowed to see this page' );
} // Exit if accessed directly


/**
 * @author Dario Curvino <@dudo>
 * @since 1.5.0
 * Class CNRT_AdminPro
 */
class AdminPro {

    /**
     * Init Pro version of admin side stuff
     *
     * @author Dario Curvino <@dudo>
     * @since 1.5.0
     */
    public function init() {
        $comment_list_page = new EditCommentsPro();
        $comment_list_page->init();

        $edit_uer = new EditUserPro();
        $edit_uer->init();

        //filter menu to show contact link
        cnrt_fs()->add_filter('is_submenu_visible', array($this, 'addContactLink'), 10, 2);
    }

    public function addContactLink ($is_visible, $menu_id) {
        if ('contact' !== $menu_id) {
            return $is_visible;
        }

        if(cnrt_fs()->is_plan('pro') || cnrt_fs()->is_trial())  {
            return cnrt_fs()->can_use_premium_code();
        }

        return null;
    }
}