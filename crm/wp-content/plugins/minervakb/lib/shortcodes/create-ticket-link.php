<?php
/**
 * Project: MinervaKB.
 * Copyright: 2015-2017 @KonstruktStudio
 */
class MinervaKB_CreateTicketLinkShortcode extends KST_Shortcode implements KST_Shortcode_Interface {

	protected $ID = 'create-ticket-link';
	protected $name = 'Create Ticket Link';
	protected $description = 'Renders create ticket page link';
	protected $icon = 'fa fa-paper-plane-o';
	protected $inline = true;

	/**
	 * Renders create ticket link
	 * @param $atts
	 * @param string $content
	 */
	public function render($atts, $content = '') {
	    if (!MKB_Tickets::user_can_create_tickets()) {
	        return;
        }

	    $create_ticket_url = get_permalink(MKB_Options::option('tickets_create_page'));

	    if (MKB_Options::option('tickets_create_use_woo_account_tab') && MKB_Options::option('woo_add_support_account_tab') && MinervaKB_App::instance()->info->is_woocommerce_active()) {
	        if (function_exists('wc_get_page_permalink')) {
                $create_ticket_url = wc_get_page_permalink('myaccount') . MKB_Options::option('woo_account_section_url');
            }
        }

	    $create_ticket_url = MKB_TemplateHelper::add_ticket_referrer_params($create_ticket_url);
	    $label = isset($atts['label']) ? $atts['label'] : MKB_Options::option('tickets_widgets_create_link_text');
	    $classes = isset($atts['button']) && $atts['button'] !== 'false' ? 'mkb-button mkb-create-ticket-link' : 'mkb-create-ticket-link';

		?><a href="<?php esc_attr_e($create_ticket_url); ?>" class="<?php esc_attr_e($classes); ?>"><?php esc_html_e($label); ?></a><?php
	}
}