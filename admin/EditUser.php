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
        <h2>CNRT <?php esc_html__('settings') ?> </h2>
        <table class="form-table" role="presentation">
            <tr>
                <td>
                    <select name="cnrt" id="cnrt">
                    </select>
                </td>
            </tr>
        </table>
        <?php

    }
}