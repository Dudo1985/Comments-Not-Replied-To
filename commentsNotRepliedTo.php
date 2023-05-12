<?php

namespace Dudo1985\CNRT;

if ( ! defined( 'ABSPATH' ) ) {
    exit( 'You\'re not allowed to see this page' );
} // Exit if accessed directly

/**
 * @version 1.5.0
 */
class commentsNotRepliedTo {

    /**
     * Load all action needed in both admin and public area
     *
     * @author Dario Curvino <@dudo>
     * @since  1.4.0
     */
    public function init() {
        $this->defineConstants();

        //Run this only on plugin activation (doesn't work on update)
        register_activation_hook(CNRT_ABSOLUTE_PATH.'/comments-not-replied-to.php', [$this, 'onActivation']);

        //Init translations
        add_action('init', array($this, 'translate'));

        //Customize optin image
        cnrt_fs()->add_filter('plugin_icon' , array($this, 'changeIcon'));

        //once plugins are loaded, update version
        add_action('plugins_loaded', array($this, 'updateVersion'));

        // add or remove the comment meta to comments on entry
        add_action('comment_post', array($this, 'addMissingMeta'));
        add_action('comment_post', array($this, 'removeMissingMeta'));

    } // end constructor

    /**
     * Define Constants
     *
     * @author Dario Curvino <@dudo>
     * @since 1.0.0
     */
    public function defineConstants() {
        //Plugin absolute path
        //e.g. /var/www/html/plugin_development/wp-content/plugins/comments-not-replied-to
        //by default, dirname get the parent
        define('CNRT_ABSOLUTE_PATH', __DIR__);

        //Plugin RELATIVE PATH without slashes (just the directory's name)
        //Do not use just 'comments-not-replied-to' here, because the directory name
        //can be different, e.g. comments-not-replied-to-premium or
        //CNRT-2.3.1 (branch name)
        define('CNRT_RELATIVE_PATH', dirname(plugin_basename(__FILE__)));

        //admin absolute path
        define('CNRT_ABSOLUTE_PATH_ADMIN', CNRT_ABSOLUTE_PATH . '/admin');

        //admin relative path
        define('CNRT_RELATIVE_PATH_ADMIN', CNRT_RELATIVE_PATH . '/admin');

        //IMG directory absolute URL
        define('CNRT_IMG_DIR_ADMIN', plugins_url() . '/' . CNRT_RELATIVE_PATH_ADMIN . '/img/');

        //js
        define('CNRT_JS_DIR_ADMIN', plugins_url() . '/' . CNRT_RELATIVE_PATH_ADMIN . '/js/');

        //css admin
        define('CNRT_CSS_DIR_ADMIN', plugins_url() . '/' . CNRT_RELATIVE_PATH_ADMIN . '/css/');

        //Plugin language directory: here I've to use relative path
        //because load_plugin_textdomain wants relative and not absolute path
        define('CNRT_LANG_DIR', CNRT_RELATIVE_PATH . '/lang/');

        //version installed
        define('CNRT_VERSION_INSTALLED', $this->versionInstalled());

        define('CNRT_SETTINGS', $this->returnCnrtSettings());

        $this->markAsRepliedSettings();

    }

    /**
     * Do the defines for "mark as replied settings"
     *
     * @author Dario Curvino <@dudo>
     * @since 1.5.0
     */
    private function markAsRepliedSettings () {
        $array_roles = array(
            'admin'  => false,
            'editor' => false,
            'author' => false
        );

        $options = json_decode(CNRT_SETTINGS, true);

        if($options !== false) {
            if(isset($options['mark_as_replied']) && is_array($options['mark_as_replied'])) {
                foreach ($options['mark_as_replied'] as $key => $value) {
                    $array_roles[$key] = true;
                }
            }
        }

        if(!defined('CNRT_MARK_AS_REPLIED_ADMIN')) {
            define('CNRT_MARK_AS_REPLIED_ADMIN',  $array_roles['admin']);
        }
        if(!defined('CNRT_MARK_AS_REPLIED_EDITOR')) {
            define('CNRT_MARK_AS_REPLIED_EDITOR', $array_roles['editor']);
        }
        if(!defined('CNRT_MARK_AS_REPLIED_AUTHOR')) {
            define('CNRT_MARK_AS_REPLIED_AUTHOR', $array_roles['author']);
        }
    }

    /**
     * Actions to do on plugin activation
     *
     * @author Dario Curvino <@dudo>
     * @since 1.0.0
     * @param $network_wide  //indicate if the plugin is network activated
     */
    public function onActivation($network_wide) {
        //do action when plugin is installed for first time
        if(CNRT_VERSION_INSTALLED === 0) {
            $this->install($network_wide);
        }
    }

    /**
     * Change icon for optin form
     *
     * @author Dario Curvino <@dudo>
     * @since  1.5.5
     * @return string
     */
    public function changeIcon() {
        return CNRT_ABSOLUTE_PATH . '/admin/img/comments-not-replied-to.png';
    }

    /**
     * Action to do when plugin is installed for the first time
     * not yet used
     *
     * @author Dario Curvino <@dudo>
     * @since 1.5.0
     * @param $network_wide //indicate if the plugin is network activated
     */
    private function install($network_wide) {
        //default settings
        //not needed yet
        //$this->defaultSettings();
    }

    /**
     * Insert default settings
     *
     * @author Dario Curvino <@dudo>
     * @since  1.5.0
     */
    public function defaultSettings() {
        $settings = get_option('cnrt_settings');

        if(!$settings) {
            //default settings here
            add_option('cnrt_settings', $settings); //Write here the default value if there is not $settings
        }
    }

    /**
     * Update plugin version
     *
     * @author Dario Curvino <@dudo>
     * @since 1.5.0
     */
    public function updateVersion(){
        if (CNRT_VERSION !== CNRT_VERSION_INSTALLED) {
            update_option('cnrt-version', CNRT_VERSION);
        }
    }

    /**
     * Loads the plugin text domain for translation
     *
     * @author Dario Curvino <@dudo>
     * @since 1.4.0
     */
    public function translate() {
        load_plugin_textdomain('comments-not-replied-to', false, CNRT_LANG_DIR);
    } // end plugin_textdomain


    /**
     * Add the meta tag to comments for query logic later
     * @param	int		$comment_id		The ID of the comment for which to retrieve replies.
     * @return  void                    if the comment author is the same of post author
     *                                  at the end of the method after comment meta is added
     *
     * @since	1.0
     */

    public function addMissingMeta($comment_id = 0) {
        $comment_by_authorized = self::commentIsByAuthorizedUser($comment_id);

        //Do nothing if the author of the comment is post author
        if ($comment_by_authorized !== false) {
            return;
        } // end if

        //allow only if this is a comment, exclude pingback and trackback
        if(get_comment_type($comment_id) !== 'comment') {
            return;
        }

        // set an inital false tag on comment set
        add_comment_meta( $comment_id, '_cnrt_missing', true );
    } // end add_missing_meta

    /**
     * Remove the meta tag to comments for query logic later
     * @param	int		$comment_id		The ID of the comment for which to retrieve replies.
     * @return  void                    If is passed an invalid comment id, or comment doesn't exists
     *                                  At the end of the method after meta is removed
     *
     * @since	1.0
     */
    public function removeMissingMeta( $comment_id = 0 ) {

        // get comment object array
        $comm_data      = get_comment($comment_id);

        // get comment parent ID, post ID, and user ID
        $comm_parent    = (int)$comm_data->comment_parent;

        // check for meta key first
        $missing        = get_comment_meta($comm_parent, '_cnrt_missing', true);

        //get comment meta returns false if $comment_id is invalid, empty string if non existing comment ID is passed
        if ($missing === false || $missing === '') {
            return;
        } // end if

        $comment_by_authorized = self::commentIsByAuthorizedUser($comment_id);

        //Delete comment meta if answer is by author or someone authorizeds
        if ($comment_by_authorized !== false) {
            delete_comment_meta( $comm_parent, '_cnrt_missing' );
        } // end if

    } // end remove_missing_meta

    /**
     *
     * Returns
     * - string with the role of the comment author
     * - false otherwise (e.g. the comment is not by the post author, and no one of the selected roles is enabled
     *                   to remove the "not replied status")
     *
     * @author Dario Curvino <@dudo>
     * @since 1.5.0
     *
     * @param int   $comment_id    | id of content to retrive
     *
     * @return false|string
     */
    public static function commentIsByAuthorizedUser($comment_id = 0) {
        $comment      = get_comment($comment_id);

        $post_id      = (int) $comment->comment_post_ID;
        $user_id      = (int) $comment->user_id;
        $email        = $comment->comment_author_email;

        if(self::commentByPostAuthor($email, $post_id)) {
            return 'post_author';
        }
        if (self::adminCanAnswer($user_id)) {
            return 'Admin';
        }
        if (self::editorCanAnswer($user_id)) {
            return 'Editor';
        }
        if (self::authorCanAnswer($user_id)) {
            return 'Author';
        }

        return false;
    }

    /**
     * Determines whether or not the author has replied to the comment.
     *
     * @param string $email the array of the current comment.
     * @param int    $post_id
     *
     * @since    1.0
     * @return    bool                     Whether or not the post author has replied.
     */
    private static function commentByPostAuthor($email, $post_id) {
        // If the comment author email address is the same as the post author's address, then we've found a reply by the author.
        return $email === self::getPostAuthorEmail($post_id);
    } // end get_post_author_email



    /**
     * Check if current user is admin (by checking ig can publish posts)
     * and if CNRT_MARK_AS_REPLIED_ADMIN is true
     *
     * @author Dario Curvino <@dudo>
     * @since 1.5.0
     * @param $admin_id
     *
     * @return bool
     */
    public static function adminCanAnswer($admin_id) {
        if (defined('CNRT_MARK_AS_REPLIED_ADMIN') && CNRT_MARK_AS_REPLIED_ADMIN === true
            && user_can($admin_id, 'activate_plugins')
        ) {
            return true;
        }
        return false;
    }

    /**
     * Check if current user is editor (by checking if can edit_others_posts && can not activate_plugins )
     * and if CNRT_MARK_AS_REPLIED_EDITOR ios true
     *
     * @author Dario Curvino <@dudo>
     * @since 1.5.0
     * @param $editor_id
     *
     * @return bool
     */
    public static function editorCanAnswer($editor_id) {
        if (defined('CNRT_MARK_AS_REPLIED_EDITOR') && CNRT_MARK_AS_REPLIED_EDITOR === true
            && user_can($editor_id, 'edit_others_posts') && !user_can($editor_id, 'activate_plugins')
        ) {
            return true;
        }
        return false;
    }

    /**
     * Check if current user is an author (by checking if can publish_posts && can not edit_others_posts )
     * and if CNRT_MARK_AS_REPLIED_EDITOR ios true
     *
     * @author Dario Curvino <@dudo>
     * @since 1.5.0
     * @param $author_id
     *
     * @return bool
     */
    public static function authorCanAnswer($author_id) {
        if (defined('CNRT_MARK_AS_REPLIED_AUTHOR') && CNRT_MARK_AS_REPLIED_AUTHOR === true
            && user_can($author_id, 'publish_posts') && !user_can($author_id, 'edit_others_posts')
        ) {
            return true;
        }
        return false;
    }

    /**
     * Retrieves the email address for the author of the post.
     *
     * @param    int $post_id The ID of the post for which to retrieve the email address
     *
     * @since    1.0
     * @return  string         The email address of the post author
     */
    public static function getPostAuthorEmail($post_id = 0) {
        // Get the author information for the specified post
        $author = get_user_by('id', self::getPostAuthorId($post_id));

        //return author email
        return $author->data->user_email;
    } // end get_comment_replies

    /**
     * @author Dario Curvino <@dudo>
     * @since  1.3.2
     *
     * @param int $post_id
     * return the post author ID
     *
     * @return int
     */
    public static function getPostAuthorId ( $post_id = 0 ) {
        return (int)get_post_field( 'post_author', $post_id );
    }

    /**
     * Return version installed, if not found, 0 is returned
     *
     * @author Dario Curvino <@dudo>
     * @since 1.4.1
     * @return false|mixed|void
     */
    public function versionInstalled() {
        return get_option('cnrt-version', 0);
    }

    /**
     * Return cnrt settings
     *
     * @author Dario Curvino <@dudo>
     * @since 1.5.0
     * @return string
     */
    public function returnCnrtSettings() {
        return json_encode(get_option('cnrt_settings'));
    }
} // end class