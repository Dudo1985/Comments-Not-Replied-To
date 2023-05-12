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

    public function cnrtFields($profile_user) {
        ?>
        <br />
        <h2>CNRT <?php echo esc_html__('settings') . CNRT_LOCKED_FEATURE ?> </h2>
        <table class="form-table" role="presentation">
            <tr>
                <th>
                    <label for="cnrt">
                        <?php _e( 'Mark a comment as read when this user answer to a comment?' ); ?>
                    </label>
                </th>
                <td>
                    <select name="cnrt" id="cnrt">
                        <option>No</option>
                        <option>Yes</option>
                    </select>
                </td>
            </tr>
        </table>
        <?php

    }
}