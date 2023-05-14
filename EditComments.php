<?php
namespace Dudo1985\CNRT;

/**
 * @author Dario Curvino <@dudo>
 * @since
 */

class EditComments {

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

}