<?php
/**
 * Project: MinervaKB.
 * Copyright: 2015-2017 @KonstruktStudio
 */

class MKB_Create_Ticket_Link_Widget extends WP_Widget
{
    /**
     * Sets up the widgets name etc
     */
    public function __construct() {
        $widget_ops = array(
            'classname' => 'mkb_create_ticket_link_widget',
            'description' => __('Displays the create ticket button', 'minerva-kb'),
        );
        parent::__construct('kb_create_ticket_link_widget', __('MinervaKB: Create Ticket', 'minerva-kb'), $widget_ops);
    }

    /**
     * @param array $args
     * @param array $instance
     */
    public function widget($args, $instance) {
        if (!MKB_Tickets::user_can_create_tickets()) {
            return;
        }

        echo $args['before_widget'];

        $create_ticket_url = get_permalink(MKB_Options::option('tickets_create_page'));
        $create_ticket_url = MKB_TemplateHelper::add_ticket_referrer_params($create_ticket_url);

        if ( ! empty( $instance['title'] ) ) {
            echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
        }

        ?>
        <p><?php echo MKB_Options::option('tickets_widgets_create_text'); ?></p>
        <div>
            <a class="mkb-button" href="<?php esc_attr_e($create_ticket_url); ?>"><?php esc_html_e(MKB_Options::option('tickets_widgets_create_link_text')); ?></a>
        </div>

        <?php

        echo $args['after_widget'];
    }

    /**
     * Outputs the options form on admin
     * @param array $instance The widget options
     */
    public function form( $instance ) {
        $title = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'Support', 'minerva-kb' );

        ?>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'minerva-kb' ); ?></label>
            <input class="widefat"
                   id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
                   name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
                   type="text" value="<?php echo esc_attr( $title ); ?>" />
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

        return $instance;
    }
}