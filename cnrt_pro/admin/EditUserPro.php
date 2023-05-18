<?php
/**
 * @author Dario Curvino <@dudo>
 * @since
 * @return
 */

namespace Dudo1985\CNRT;

class EditUserPro {
    public function init() {
        //In user-edit page, change the select value in 'yes' if the current user can mark comment as read
        add_filter('cnrt_user_edit_select', [$this, 'defaultSelectValue']);

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