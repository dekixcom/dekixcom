<?php
/**
 * Project: MinervaKB.
 * Copyright: 2015-2017 @KonstruktStudio
 */
class MinervaKB_CreateTicketShortcode extends KST_Shortcode implements KST_Shortcode_Interface {

	protected $ID = 'create-ticket';
	protected $name = 'Create Ticket';
	protected $description = 'Renders create ticket form';
	protected $icon = 'fa fa-paper-plane-o';

	/**
	 * Renders create ticket form
	 * @param $atts
	 * @param string $content
	 */
	public function render($atts, $content = '') {
	    $is_guest = !is_user_logged_in();

	    if (
	        $is_guest && MKB_Options::option('tickets_allow_guest_tickets') || // guest
            !$is_guest && MKB_Tickets::user_can_create_tickets() // user
        ) {
	        $title = MKB_Options::option(
                $is_guest ? 'tickets_create_ticket_form_guest_title' : 'tickets_create_ticket_form_user_title'
            );

            if ($title) {
                ?><h2><?php esc_html_e($title); ?></h2><?php
            }

            $subtitle = MKB_Options::option(
                $is_guest ? 'tickets_create_ticket_form_guest_subtitle' : 'tickets_create_ticket_form_user_subtitle'
            );

            if ($subtitle) {
                echo do_shortcode($subtitle);
            }

            MKB_TemplateHelper::render_ticket_create_form();
        } else {
            echo do_shortcode('[mkb-info]' .
                __('You are not currently allowed to open tickets', 'minerva-kb') .
                '[/mkb-info]'
            );
        }
	}
}