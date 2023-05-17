<?php
namespace Dudo1985\CNRT;

if ( ! defined( 'ABSPATH' ) ) {
    exit( 'You\'re not allowed to see this page' );
} // Exit if accessed directly


/**
 * @author Dario Curvino <@dudo>
 * @since  1.5.7
 */
class EditUser {
    //hook into edit_user_profile to add new fields
    public function init() {
        add_action('edit_user_profile', [$this, 'cnrtFields']);
        add_action('profile_update', [$this, 'onProfileUpdate']);
    }

    /**
     * Display new fields into the user-edit.php page
     *
     * @author Dario Curvino <@dudo>
     *
     * @since 1.5.7
     *
     * @param $profile_user
     *
     * @return void
     */
    public function cnrtFields($profile_user) {
        $selected = '';
        $disabled = 'disabled';
        $desc_text = esc_html__('This feature is available only in the pro version', 'comments-not-replied-to');
        if (cnrt_fs()->is__premium_only()) { //these if can't be merged
            if (cnrt_fs()->can_use_premium_code()) {
            /**
             * Hook here to change the selected value
             */
            $current_user_can_mark_as_read = apply_filters('cnrt_user_edit_select', $profile_user->ID);

            if($current_user_can_mark_as_read === 'yes') {
                $selected = 'selected';
            }

            $disabled  = '';
            $desc_text = '';
            }
        }

        ?>
        <br />
        <h2>CNRT <?php echo esc_html__('settings') . CNRT_LOCKED_FEATURE ?> </h2>
        <table class="form-table" role="presentation">
            <tr>
                <th>
                    <label for="cnrt">
                        <?php esc_html_e('Mark a comment as read when this user answer to a comment?'); ?>
                    </label>
                    <p class="description" style="font-weight: 400">
                        <?php echo wp_kses_post($desc_text) ?>
                    </p>
                </th>
                <td>
                    <select name="cnrt" id="cnrt" <?php echo esc_attr($disabled) ?>>
                        <option value="no"><?php esc_html_e('No', 'yet-another-stars-rating')?></option>
                        <option value="yes" <?php echo esc_attr($selected) ?>>
                            <?php esc_html_e('Yes', 'yet-another-stars-rating')?>
                        </option>
                    </select>
                </td>
            </tr>
        </table>
        <?php
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
        if(isset($_POST['cnrt']) && $_POST['cnrt'] === 'yes') {
            update_user_meta($user_id, 'cnrt_user_can_mark_as_read', 'yes');
        } else {
            delete_user_meta($user_id, 'cnrt_user_can_mark_as_read');
        }
    }

}