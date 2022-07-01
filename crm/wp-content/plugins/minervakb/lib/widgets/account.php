<?php
/**
 * Project: MinervaKB.
 * Copyright: 2015-2017 @KonstruktStudio
 */

class MKB_Account_Widget extends WP_Widget
{
    /**
     * Sets up the widgets name etc
     */
    public function __construct() {
        $widget_ops = array(
            'classname' => 'mkb_account_widget',
            'description' => __('Displays support user info, logout and other links', 'minerva-kb'),
        );
        parent::__construct('kb_account_widget', __('MinervaKB: Account', 'minerva-kb'), $widget_ops);
    }

    /**
     * @param array $args
     * @param array $instance
     */
    public function widget($args, $instance) {
        if (!is_user_logged_in() || !MKB_Tickets::user_can_create_tickets()) {
            return;
        }

        echo $args['before_widget'];

        if ( ! empty( $instance['title'] ) ) {
            echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
        }

        $current_user = wp_get_current_user();

        ?><p>Welcome, <strong><?php echo esc_html($current_user->first_name); ?></strong>!<?php
            if ($instance['show_account_link']) {
                $account_page = MKB_Options::option('tickets_support_account_page');

                ?><br>Open your <strong><a href="<?php echo get_the_permalink($account_page); ?>">Support Account page</a></strong>
            <?php }
        ?></p><?php

        if ($instance['show_logout']) {
            echo do_shortcode('[mkb-logout]');
        }

        echo $args['after_widget'];
    }

    /**
     * Outputs the options form on admin
     * @param array $instance The widget options
     */
    public function form( $instance ) {
        $title = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'My Account', 'minerva-kb' );
        $show_account_link = isset($instance['show_account_link']) ? (bool) $instance['show_account_link'] : true;
        $show_logout = isset($instance['show_logout']) ? (bool) $instance['show_logout'] : true;

        ?>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'minerva-kb' ); ?></label>
            <input class="widefat"
                   id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
                   name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
                   type="text" value="<?php echo esc_attr( $title ); ?>" />
        </p>
        <p>
            <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('show_account_link'); ?>" name="<?php echo $this->get_field_name('show_account_link'); ?>"<?php checked( $show_account_link ); ?> />
            <label for="<?php echo $this->get_field_id('show_account_link'); ?>"><?php _e( 'Show account link?', 'minerva-kb' ); ?></label><br />
        </p>
        <p>
            <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('show_logout'); ?>" name="<?php echo $this->get_field_name('show_logout'); ?>"<?php checked( $show_logout ); ?> />
            <label for="<?php echo $this->get_field_id('show_logout'); ?>"><?php _e( 'Show logout button?', 'minerva-kb' ); ?></label><br />
        </p>
        <?php
    }


    /**
     * Title
     * @param array $new_instance
     * @param array $old_instance
     * @return array
     */
    public function update($new_instance, $old_instance) {
        $instance = array();

        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
        $instance['show_account_link'] = ( ! empty( $new_instance['show_account_link'] ) ) ? (bool)strip_tags( $new_instance['show_account_link'] ) : false;
        $instance['show_logout'] = ( ! empty( $new_instance['show_logout'] ) ) ? (bool)strip_tags( $new_instance['show_logout'] ) : false;

        return $instance;
    }
}