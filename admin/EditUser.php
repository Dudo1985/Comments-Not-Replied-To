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
        if(CNRT_PRO_VERSION === true) {
            $disabled  = '';
            $desc_text = '';
        } else {
            $disabled = 'disabled';
            $desc_text = esc_html__('This feature is available only in the pro version', 'comments-not-replied-to');
        }

        ?>
        <br />
        <h2>CNRT <?php echo esc_html__('settings') . CNRT_LOCKED_FEATURE ?> </h2>
        <table class="form-table" role="presentation">
            <tr>
                <th>
                    <label for="cnrt">
                        <?php _e( 'Mark a comment as read when this user answer to a comment?' ); ?>
                    </label>
                    <p class="description" style="font-weight: 400">
                        <?php echo wp_kses_post($desc_text) ?>
                    </p>
                </th>
                <td>
                    <select name="cnrt" id="cnrt" <?php echo esc_attr($disabled) ?>>
                        <option>No</option>
                        <option>Yes</option>
                    </select>
                </td>
            </tr>
        </table>
        <?php

    }
}