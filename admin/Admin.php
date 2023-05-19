<?php

namespace Dudo1985\CNRT;

if (!defined('ABSPATH')) {
    exit('You\'re not allowed to see this page');
} // Exit if accessed directly


class Admin {

    private $comment_by_authorized;
    private $comment_childs;
    private $comment_replied_by;

    /**
     * Load all actions and filters needed in admin dashboard
     *
     * @author Dario Curvino <@dudo>
     * @since  1.4.0
     */
    public function init() {
        $this->settingsPage();

        add_action('admin_enqueue_scripts', array($this, 'enqueueScripts'));

        //this filter run before the others
        add_filter('comment_row_actions', array($this, 'markAsReadLink'), 10, 2);

        // Add the 'Comment Reply' custom column, into the standard comment table
        //column name
        add_filter('manage_edit-comments_columns',  array($this, 'missingReplyColumn'));
        //column content
        add_filter('manage_comments_custom_column', array($this, 'missingReplyDisplay'), 10, 2);

        // add 'Missing Reply' link in status row
        add_filter('comment_status_links', array($this, 'missingReplyStatusLink'));

        // return just the missing replies in the comment table
        add_action('pre_get_comments', array($this, 'returnMissingList'));

        //delete the comment_meta when a comment is deleted
        add_action('deleted_comment', array($this, 'deleteMetaKey'));

        //Filter the pricing page only if trial is not set
        if(isset($_GET['page']) && $_GET['page'] === 'cnrt_settings_page-pricing' && !isset($_GET['trial'])) {
            cnrt_fs()->add_filter( 'templates/pricing.php', array($this, 'pricingPageCallback') );
        }
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
     * Init settings page
     *
     * @author Dario Curvino <@dudo>
     * @since  1.5.0
     */
    public function settingsPage() {
        $cnrt_settings = new SettingsPage();
        $cnrt_settings->init();
    }

    /**
     * Return the link "mark as read"
     *
     * @author Dario Curvino <@dudo>
     * @since 1.5.0
     * @param $actions
     * @param $comment
     *
     * @return mixed
     */
    public function markAsReadLink ($actions, $comment) {
        if(!current_user_can('activate_plugins')) {
            return $actions;
        }

        $comment_id = (int)$comment->comment_ID;
        $post_id    = (int)$comment->comment_post_ID;

        if (wp_get_comment_status($comment_id) !== 'approved' || get_comment_type() !== 'comment') {
            return $actions;
        }

        $custom_link = apply_filters('href_mark_as_read', $comment_id);

        if($custom_link !== $comment_id) {
            $actions['cnrt'] = $custom_link;
            return $actions;
        }

        $this->comment_by_authorized = commentsNotRepliedTo::commentIsByAuthorizedUser($comment_id);

        if($this->comment_by_authorized === false) {
            //if return false (e.g. comment is by a visitor), we must check if comment has replies
            //get comment replies
            $this->comment_childs     = $this->getCommentReplies($comment_id);
            //find who has replied to the comment
            $this->comment_replied_by = $this->commentRelpiedBy($this->comment_childs);

            if($this->comment_replied_by === false) {
                $actions['cnrt'] = $this->markAsReadReturnHref($comment_id, $post_id);
            }
        }

        return $actions;
    }

    /**
     * Adds a new column to the 'edit-comments.php' page for indicating whether or not
     * the given comment has not received a reply from the post author.
     *
     * @param array $columns The array of columns for the 'edit-comments' page
     *
     * @since    1.0
     * @return   array                The array of columns to display
     */
    public function missingReplyColumn($columns = array()) {
        // do not add column if we are in "trash" page
        if ((isset($_GET['comment_status']) && $_GET['comment_status'] === 'trash') ||
            (isset($_GET['comment_type']) && $_GET['comment_type'] === 'pings')) {
            return $columns;
        } // end if

        $columns['missing-reply'] = esc_html__('Comment Reply', 'comments-not-replied-to');

        return $columns;
    } // end missing_reply_column

    /**
     * "Comment reply" column content, callback for the hook manage_comments_custom_column
     * https://developer.wordpress.org/reference/hooks/manage_comments_custom_column/
     *
     * @param string $column_name | The custom column's name.
     * @param int    $comment_id  | The custom column's unique ID number.
     *
     * @since    1.5
     * @return   void
     */
    public function missingReplyDisplay($column_name = '', $comment_id = 0) {

        // If we're looking at the 'Missing Reply' column...
        if ('missing-reply' !== trim($column_name)) {
            return;
        } // end if

        if(get_comment_type($comment_id) !== 'comment') {
            $span_attributes = $this->commentReplySpanAttributes(false, false, false);
            $this->commentReplyPrintSpan($span_attributes, $comment_id);
            return;
        }

        $custom_span = apply_filters('cnrt_action_column_reply', $comment_id);

        if($custom_span !== $comment_id) {
            $this->commentReplyPrintSpan($custom_span, $comment_id);
            return;
        }

        $comment_by_authorized = $this->comment_by_authorized;

        //the method markAsReadLink was already called when we are here
        //but this can be still null if comment is not approved or is not a valid one
        if($comment_by_authorized === null) {
            $comment_by_authorized = commentsNotRepliedTo::commentIsByAuthorizedUser($comment_id);
        }

        // If the comment is by the author, or somebody else enabled to reply
        if ($comment_by_authorized !== false) {
            //get span attributes for comments
            $span_attributes = $this->commentReplySpanAttributes($comment_by_authorized);
            //print the span
            $this->commentReplyPrintSpan($span_attributes, $comment_id);
            return;
        }
        //if return false (e.g. comment is by a visitor), we must check if comment has replies
        //get comment replies
        $this->commentFromUnauthorized($comment_id);

        //reset class properties
        $this->resetClassProperties();

    } // end get_missing_count

    /**
     * Adds a new item in the comment status links to select those missing a reply
     *
     * @since    1.0
     * @return    array                The array of columns to display
     */
    public function missingReplyStatusLink($status_links = array()) {

        // add check for including 'current' class
        $current = isset($_GET['missing_reply']) ? 'class="current"' : '';

        // get missing count
        $missing_num = $this->getMissingCount();

        // create link
        $status_link = '<a href="edit-comments.php?comment_status=all&missing_reply=1" ' . $current . '>';
        $status_link .= esc_html__('Missing Reply', 'comments-not-replied-to');
        $status_link .= ' <span class="count">
                            (<span class="pending-count" id="cnrt-pending-count">' . $missing_num . '</span>)
                          </span>';
        $status_link .= '</a>';

        // set new link
        $status_links['missing_reply'] = $status_link;

        // return all the status links
        return $status_links;

    } // end missing_reply_status_link

    /**
     * Return the missing replies in a list on the comments table
     *
     * @param array $comments The object array of comments
     *
     * @since    1.0
     * @return  void                The filtered comment data
     */
    public function returnMissingList($comments = array()) {

        // bail on anything not admin
        if (!function_exists('get_current_screen') || !is_admin()) {
            return;
        } // end if

        // only run this on the comments table
        $current_screen = get_current_screen();

        if ($current_screen !== null
            && (property_exists($current_screen, 'base') && $current_screen->base !== 'edit-comments')
        ) {
            return;
        } // end if

        // check for query param
        if (!isset($_GET['missing_reply'])) {
            return;
        } // end if

        //get comment with meta key _cnrt_missing with value = 1 AND where no _cnrt_read meta key exists
        $meta_query = array(
            // Include comments having the meta.
            'missing' => array(
                'key'     => '_cnrt_missing',
                'compare' => 'EXISTS',
            ),

            // Only get comments that has not the meta _cnrt_read
            // relation is not declared, so it is an AND by default
            // this should be useless, just to be safe
            'read' => array(
                'key'     => '_cnrt_read',
                'compare' => 'NOT EXISTS',
            ),
        );

        $comments->query_vars['meta_query']  = $meta_query;

        //allow only 'comment' type, exclude pingback and trackback
        $comments->query_vars['type__in']    = 'comment';

        // Because at this point, the meta query has already been parsed,
        // we need to re-parse it to incorporate our changes
        $comments->meta_query->parse_query_vars( $comments->query_vars );

    } // end missing_reply_list

    /**
     * @author Dario Curvino <@dudo>
     * @since 1.5.0
     * @param $comment_id
     */
    public function deleteMetaKey($comment_id) {
        delete_comment_meta($comment_id, '_cnrt_missing');
    }

    /**
     * @author Dario Curvino <@dudo>
     * @since 1.5.0
     */
    public function pricingPageCallback() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'yet-another-stars-rating'));
        }

        include(CNRT_ABSOLUTE_PATH_ADMIN . '/pricing-page.php');
    }

    /************************  HELPER FUNCTIONS BELOW THIS LINE  ************************/

    /**
     * return the <a> tag with link for mark a comment as read
     *
     * @author Dario Curvino <@dudo>
     * @since 1.5.0
     * @param int $comment_id
     * @param int $post_id
     *
     * @return string
     */
    private function markAsReadReturnHref ($comment_id, $post_id) {
        $nonce = wp_create_nonce('cnrt_mark_as_read_nonce_action');
        $text = esc_html__('Mark this comment as read', 'comments-not-replied-to');
        return (
            '<a href="#" 
                title="' . esc_attr($text) . '" 
                id="cnrt-read-' . esc_attr($comment_id) . '" 
                class="cnrt-mark-as-read"
                data-comment-id="' . esc_attr($comment_id) . '" 
                data-post-id="'    . esc_attr($post_id) . '" 
                data-nonce="'.esc_attr($nonce).'"
            />'
            . esc_html__('Mark as read')
            . '</a>'
            . CNRT_LOCKED_FEATURE
        );
    }

    /**
     * Print column content in child comments
     *
     * @author Dario Curvino <@dudo>
     * @since 1.5.0
     *
     * @return void
     */
    private function commentFromUnauthorized ($comment_id) {
        $comment_childs     = $this->comment_childs;
        $comment_replied_by = $this->comment_replied_by;

        //Here we're not sure that $comment_childs has been assigned, so check if not null
        if($comment_childs === null) {
            $comment_childs = $this->getCommentReplies($comment_id);
        }

        //Here we're not sure that $comment_replied_by has been assigned, so check if not null
        if($comment_replied_by === null) {
            //find who has replied to the comment
            $comment_replied_by = $this->commentRelpiedBy($comment_childs);
        }

        //get span attributes
        $span_attributes = $this->commentReplySpanAttributes($comment_replied_by, true);

        //print the span
        $this->commentReplyPrintSpan($span_attributes, $comment_id);
    }

    /**
     * Retrieves all of the replies for the given comment.
     *
     * @param int $comment_id The ID of the comment for which to retrieve replies.
     *
     * @since    1.0
     * @return   array        The array of replies
     */
    private function getCommentReplies($comment_id = 0) {
        global $wpdb;

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT 
                          comment_ID, comment_author_email, comment_post_ID, user_id
                       FROM $wpdb->comments 
                       WHERE comment_parent = %d", $comment_id
            )
        );
    }

    /**
     * @author Dario Curvino <@dudo>
     * @since 1.5.0
     * @param array $replies
     *
     * @return bool|string
     */
     private function commentRelpiedBy($replies = array()) {
         // loop the replies array only if > 0
         if (count($replies) > 0) {
             $comment_count = 0;
             while ($comment_count < count($replies)) {
                 // Read the current comment
                 $current_comment    = $replies[$comment_count];
                 $comment_replied_by = commentsNotRepliedTo::commentIsByAuthorizedUser($current_comment->comment_ID);

                 if ($comment_replied_by !== false) {
                     return $comment_replied_by;
                 }

                 $comment_count++;
             } // end while
         } // end if/else

        return false;
    } // end commentRelpiedBy

    /**
     * @author Dario Curvino <@dudo>
     * @since 1.5.0
     * @param $span_attributes
     * @param $comment_id
     */
    private function commentReplyPrintSpan ($span_attributes, $comment_id) {
        $icon = '<span class="' . $span_attributes['icon'] . '" 
                       id="cnrt-reply-column-icon-'.$comment_id. '" '.
                       $span_attributes['icon_style'] . '
                 />
                 </span>';

        wp_kses_post(printf(
            '<div class="cnrt" id="cnrt-reply-column-div-%d">%s%s</div>',
            $comment_id,
            $icon,
            '<span id="cnrt-reply-column-text-'.$comment_id.'">'.$span_attributes['message'].'</span>'
        ));
    }

    /**
     * Return Span attributes for the column "Comment Reply"
     *
     * @author Dario Curvino <@dudo>
     * @since       1.5.0
     *
     * @param       $comment_replied_by
     * @param false $comment_is_child
     * @param false $is_comment
     *
     * @return string[]
     */
    private function commentReplySpanAttributes ($comment_replied_by, $comment_is_child=false, $is_comment=true) {
        if($is_comment === false) {
             return self::commentReplyGreenSpan(esc_html__('Not a comment', 'comments-not-replied-to'));
        }

        if($comment_replied_by === false) {
             return self::commentReplyYellowSpan(esc_html__('No reply yet', 'comments-not-replied-to'));
        }

        if($comment_is_child === false) {
            if ($comment_replied_by === 'post_author') {
               $message = esc_html__('This comment is by the post author.', 'comments-not-replied-to');
            }
            //otherwise, somebody else that is enabled to replied
            else {
                $message = sprintf(
                     esc_html__('This comment is by an %s', 'comments-not-replied-to'), $comment_replied_by
                 );
            }
            return self::commentReplyAuthorSpan($message);
        }

        if($comment_replied_by === 'post_author') {
            $message = esc_html__('The author has replied.', 'comments-not-replied-to');
        }
        else {
            $message = sprintf(esc_html__('An %s has replied.', 'comments-not-replied-to'), $comment_replied_by);
        }

        return self::commentReplyGreenSpan($message);

    }

    /**
     * Return attributes for green span
     *
     * @author Dario Curvino <@dudo>
     * @since  1.5.0
     *
     * @param $message
     *
     * @return array
     */
    public static function commentReplyGreenSpan($message) {
        $span_attributes['message']    = $message;
        $span_attributes['icon']       = 'dashicons dashicons-yes-alt';
        $span_attributes['icon_style'] = 'style="color:green"';

        return $span_attributes;
    }

    /**
     * Return attributes for yellow span
     *
     * @author Dario Curvino <@dudo>
     * @since  1.5.0
     *
     * @param $message
     *
     * @return array
     */
    public static function commentReplyYellowSpan($message) {
        $span_attributes['message']    = $message;
        $span_attributes['icon']       = 'dashicons dashicons-welcome-comments';
        $span_attributes['icon_style'] = 'style="color:#ec9b07"';

        return $span_attributes;
    }

    /**
     * Return attributes for author span
     *
     * @author Dario Curvino <@dudo>
     * @since  1.5.0
     *
     * @param $message
     *
     * @return array
     */
    public static function commentReplyAuthorSpan($message) {
        $span_attributes['message']    = $message;
        $span_attributes['icon']       = 'dashicons dashicons-admin-users';
        $span_attributes['icon_style'] = '';

        return $span_attributes;
    }

    /**
     * Return number of comments with missing replies, either global or per post
     *
     * @since    1.0
     * @return   int                        the count
     */
    private function getMissingCount() {

        $args = array(
            'type__in'   => 'comment', //count only comment, exclude pingback and trackback
            'meta_query' => array(
                array(
                    'key'      => '_cnrt_missing',
                    'compare'  => 'EXISTS',
                ),
                array(
                    'key'     => '_cnrt_read',
                    'compare' => 'NOT EXISTS',
                )
            )
        );

        $count    = 0;
        $comments = get_comments($args);

        if (!empty($comments)) {
            $count = count($comments);
        }

        return $count;

    } // end missing_reply_display

    /**
     * Reset class properties
     *
     * @author Dario Curvino <@dudo>
     * @since  1.5.0
     */
    private function resetClassProperties() {
        $this->comment_by_authorized = null;
        $this->comment_childs        = null;
        $this->comment_replied_by    = null;
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