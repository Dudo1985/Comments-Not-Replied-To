<?php

namespace Dudo1985\CNRT;


/**
 * @author Dario Curvino <@dudo>
 * @since 1.5.0
 * Class CNRT_AdminPro
 */
class CNRT_AdminPro {

    private $marked_as_read;

    /**
     * Init Pro version of admin side stuff
     *
     * @author Dario Curvino <@dudo>
     * @since 1.5.0
     */
    public function init() {
        //enqueue js
        add_action('cnrt_enqueue_js', array($this, 'enqueueJs'));

        add_action('wp_ajax_cnrt_mark_as_read', array($this, 'markCommentAsRead'));

        add_filter('href_mark_as_read', array($this, 'overwriteMarkAsReadLink'), 10, 1);

        add_filter('cnrt_action_column_reply', array($this, 'checkIfMarkedAsReply'), 10, 1);

        //filter menu to show contact link
        cnrt_fs()->add_filter('is_submenu_visible', array($this, 'addContactLink'), 10, 2);
    }

    /**
     * @author Dario Curvino <@dudo>
     * @since 1.5.0
     * @param $hook
     */
    public function enqueueJs($hook) {
        if($hook === 'edit-comments.php') {
            wp_enqueue_script(
                'cnrt_pro_admin',
                CNRT_JS_DIR_ADMIN_PRO . 'admin-pro.js',
                '',
                CNRT_VERSION,
                true
            );
        }
    }

    /**
     * Overwrite link "mark as read" below the comment
     *
     * @author Dario Curvino <@dudo>
     * @since 1.5.0
     * @param $comment_id
     *
     * @return mixed|string
     */
    public function overwriteMarkAsReadLink($comment_id) {
        $this->marked_as_read = (bool)get_comment_meta($comment_id, '_cnrt_read', true);

        if($this->marked_as_read) {
            //if it was already marked as read, return an empty string
            return '';
        }
        return $comment_id;
    }

    public function markCommentAsRead() {
        if(!current_user_can('activate_plugins')) {
            die (esc_html__('Not enough privileges', 'comment-not-replied-to'));
        }

        if (isset($_POST['comment_id']) && isset($_POST['nonce'])) {
            $comment_id    = (int)$_POST['comment_id'];
            $nonce_admin   = $_POST['nonce'];
        }
        else {
            die (esc_html__('Invalid Data', 'comment-not-replied-to'));
        }

        if(!wp_verify_nonce($nonce_admin, 'cnrt_mark_as_read_nonce_action')) {
            die (esc_html__('Invalid Nonces', 'comment-not-replied-to'));
        }

        // set an inital false tag on comment set
        $response['meta_insert'] = update_comment_meta($comment_id, '_cnrt_read', true);

        //if update has success returns int or true
        //https://developer.wordpress.org/reference/functions/update_comment_meta/
        if($response['meta_insert'] === true || is_int($response['meta_insert'])) {
            //if marked as read, delete comment meta _cnrt_missing
            $response['meta_deleted'] = delete_comment_meta($comment_id, '_cnrt_missing');
            echo json_encode($response);
            die();
        }

        $response['error'] = true;
        echo json_encode($response);

        die();
    }

    /**
     * @author Dario Curvino <@dudo>
     * @since 1.5.0
     * @param $comment_id
     *
     * @return array|int
     */
    public function checkIfMarkedAsReply ($comment_id) {
        $marked_as_read = $this->marked_as_read;

        if($marked_as_read === null) {
            $marked_as_read = (bool)get_comment_meta($comment_id, '_cnrt_read', true);
        }

        if($marked_as_read === false) {
            $this->marked_as_read = null;
            return $comment_id;
        }

        //reset property status
        $this->marked_as_read = null;
        return CNRT_Admin::commentReplyGreenSpan(
            esc_html__('This comment has been marked as read', 'comments-not-replied-to')
        );
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