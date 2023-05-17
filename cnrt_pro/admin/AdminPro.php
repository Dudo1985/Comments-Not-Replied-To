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

        //In user-edit page, change the select value in 'yes' if the current user can mark comment as read
        add_filter('cnrt_user_edit_select', [$this, 'defaultSelectValue']);

        //filter menu to show contact link
        cnrt_fs()->add_filter('is_submenu_visible', array($this, 'addContactLink'), 10, 2);

        add_action('profile_update', [$this, 'onProfileUpdate']);
    }


    /**
     * Callback for filter cnrt_user_edit_select
     *
     * @author Dario Curvino <@dudo>
     *
     * @since 1.5.7
     *
     * @param $user_id
     *
     * @return string|void
     */
    public function defaultSelectValue($user_id) {
        $user = new \WP_User($user_id);
        if($user->has_cap('moderate_comments')) {
            return 'yes';
        }
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

    /**
     * Update user meta adding 'cnrt_user_can_answer
     *
     * @author Dario Curvino <@dudo>
     *
     * @since 1.5.7
     *
     * @param $user_id
     *
     * @return void
     */
    public function onProfileUpdate($user_id) {
        $user = new \WP_User($user_id);
        if(isset($_POST['cnrt']) && $_POST['cnrt'] === 'yes') {
            $user->add_cap('moderate_comments');
        } else {
            $user->remove_cap('moderate_comments');
        }
    }
}