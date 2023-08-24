<?php
namespace Dudo1985\CNRT;

/**
 * Right panel of the setting page
 *
 * @author Dario Curvino <@dudo>
 * @since  1.5.7
 */
class SettingsPageRightPanel {
    /**
     * @author Dario Curvino <@dudo>
     * @since  1.0.0
     */
    public function init () {
        $this->upgradeBox();
        $this->buyACoffee();
        $this->alsoLike();
        $this->askRating();
    }

    private function upgradeBox() {
        if (cnrt_fs()->is_free_plan()) {
            ?>
            <div class="cnrt-donatedivdx">
                <h2 class="cnrt-donate-title" style="color: #34A7C1">
                    <?php esc_html_e('Upgrade to CNRT Pro', 'comments-not-replied-to'); ?>
                </h2>
                <div class="cnrt-upgrade-to-pro">
                    <ul>
                        <li>
                            <strong>
                                + <?php esc_html_e('Mark single comment as read', 'comments-not-replied-to'); ?>
                            </strong>
                        </li>
                        <li>
                            <strong>
                                + <?php esc_html_e('Select the single user who can remove the "not replied" status (available soon)',
                                    'comments-not-replied-to'); ?>
                            </strong>
                        </li>
                        <li>
                            <strong>
                                + <?php esc_html_e('Customize icons and text (available soon)',
                                    'comments-not-replied-to'); ?>
                            </strong>
                        </li>
                        <li>
                            <strong>
                                + <?php esc_html_e('Priority support', 'comments-not-replied-to'); ?>
                            </strong>
                        </li>
                    </ul>
                    <a href="<?php echo esc_url(cnrt_fs()->get_upgrade_url()); ?>">
                        <button class="button button-primary">
                    <span style="font-size: large; font-weight: bold;">
                        <?php esc_html_e('Upgrade Now', 'comments-not-replied-to')?>
                    </span>
                        </button>
                    </a>
                    <div style="display: block; margin-top: 10px; margin-bottom: 10px; ">
                        --- or ---
                    </div>
                    <a href="<?php echo esc_url(cnrt_fs()->get_trial_url()); ?>">
                        <button class="button button-primary">
                    <span style="display: block; font-size: large; font-weight: bold; margin: -3px;">
                        <?php esc_html_e('Start Free Trial', 'comments-not-replied-to') ?>
                    </span>
                            <span style="display: block; margin-top: -10px; font-size: smaller;">
                         <?php esc_html_e('No credit-card, risk free!', 'comments-not-replied-to') ?>
                    </span>
                        </button>
                    </a>
                </div>
            </div>
            <?php
        }
    }

    /**
     * @author Dario Curvino <@dudo>
     * @since 1.5.0
     */
    private function buyACoffee() {
        $buymecofeetext = esc_html__('Coffee is vital to make "Comments Not Replied To" development going on!', 'comments-not-replied-to');
        $buymecofeetext .= '<br />';

        if(cnrt_fs()->is_free_plan()) {
            $buymecofeetext .= esc_html__('If you are enjoying CNRT, and you don\'t need the pro version, please consider to buy me a coffee, thanks!',
                'comments-not-replied-to');
        } else {
            $buymecofeetext .= esc_html__('If you are enjoying CNRT, please consider to buy me a coffee, thanks!',
                'comments-not-replied-to');
        }

        $div   = "<div class='cnrt-donatedivdx' id='cnrt-buy-cofee'>";
        $text  = '<div class="cnrt-donate-title">' . esc_html__('Buy me a coffee!', 'comments-not-replied-to') .'</div>';
        $text .= '<div>';
        $text .= '<a href="https://ko-fi.com/L4L6HBQQ4" target="_blank">
                    <img src="'.CNRT_IMG_DIR_ADMIN.'/kofi.png" alt="kofi" width="150">
                  </a>';
        $text .= '<p>';
        $text .= $buymecofeetext;
        $text .= '</p>';
        $text .= '</div>';
        $div_and_text = $div . $text . '</div>';

        echo wp_kses_post($div_and_text);
    }

    /**
     * @author Dario Curvino <@dudo>
     * @since
     */
    private function askRating() {
        $div = "<div class='cnrt-donatedivdx' id='cnrt-ask-five-stars'>";

        $text = '<div class="cnrt-donate-title">' . esc_html__('Can I ask your help?', 'comments-not-replied-to') .'</div>';
        $text .= '<div class="cnrt-donate-content">';
        $text .= '<div style="font-size: 32px; color: #F1CB32; margin-bottom: 20px; margin-top: -5px;">
                        <span class="dashicons dashicons-star-filled" style="font-size: 26px;"></span>
                        <span class="dashicons dashicons-star-filled" style="font-size: 26px;"></span>
                        <span class="dashicons dashicons-star-filled" style="font-size: 26px;"></span>
                        <span class="dashicons dashicons-star-filled" style="font-size: 26px;"></span>
                        <span class="dashicons dashicons-star-filled" style="font-size: 26px;"></span>
                    </div>';
        $text .= '<p>';
        $text .= esc_html__('Please rate "Comment Not Replied" To 5 stars on', 'comments-not-replied-to');
        $text .= ' <a href="https://wordpress.org/plugins/comments-not-replied-to/">
        WordPress.org.</a><br />';
        $text .= esc_html__(' It will require just 1 min but it\'s a HUGE help for me. Thank you.', 'comments-not-replied-to');
        $text .= '<br /><br />';
        $text .= '<em>> Dario Curvino</em>';
        $text .= '<p>';
        $text .= '</div>';

        $div_and_text = $div . $text . '</div>';

        echo wp_kses_post($div_and_text);
    }

    /**
     * @author Dario Curvino <@dudo>
     * @since 1.0.0
     */
    private function alsoLike() {
        add_thickbox();
        $text  = "<div class='cnrt-donatedivdx' id='alsolike'>";
        $text .= '<div class="cnrt-donate-title">' . esc_html__('You may also like...', 'comments-not-replied-to') .'</div>';
        $text .= '<div>';
        $text .= $this->movieHelper();
        $text .= '</div>'; //second div
        $text .= '</div>'; //first div

        echo wp_kses_post($text);
    }

    /**
     * Movie Helper box
     *
     * @author Dario Curvino <@dudo>
     * @since 1.0.2
     * @return string
     */
    private function movieHelper() {
        $url = add_query_arg(
            array(
                'tab'       => 'plugin-information',
                'plugin'    => 'yet-another-movie',
                'TB_iframe' => 'true',
                'width'     => '772',
                'height'    => '670'
            ),
            network_admin_url( 'plugin-install.php' )
        );

        $movie_helper_description = esc_html__('Movie Helper allows you to easily add links to movie and tv shows, just by searching
    them while you\'re writing your content. Search, click, done!', 'yet-another-stars-rating');
        $text = '<h4>Movie Helper</h4>';
        $text .= '<div style="margin-top: 15px;">';
        $text .= $movie_helper_description;
        $text .= '</div>';
        $text .= '<div style="margin-top: 15px;">
                <a href="'. esc_url( $url ).'"
                   class="install-now button thickbox open-plugin-details-modal"
                   target="_blank">'. __( 'Install', 'yet-another-stars-rating' ).'</a>';
        $text .= '</div>';

        return $text;
    }
}