<?php
/**
 * Project: MinervaKB.
 * Copyright: 2015-2017 @KonstruktStudio
 */
class MinervaKB_LogoutShortcode extends KST_Shortcode implements KST_Shortcode_Interface {

	protected $ID = 'logout';
	protected $name = 'Logout';
	protected $description = 'Renders logout button';
	protected $icon = 'fa fa-paper-plane-o';
    protected $inline = true;

	/**
	 * Renders create ticket link
	 * @param $atts
	 * @param string $content
	 */
	public function render($atts, $content = '') {
	    if (!is_user_logged_in()) {
	        return;
        }

        $label = isset($atts['label']) ? $atts['label'] : MKB_Options::option('logout_link_text');
        $classes = isset($atts['button']) && $atts['button'] === 'false' ? 'mkb-logout-link' : 'mkb-button mkb-logout-link';

        $redirect_url = '';
        $logout_redirect_page = MKB_Options::option('tickets_redirect_support_user_after_logout_page');

        if ($logout_redirect_page) {
            $redirect_url = get_the_permalink($logout_redirect_page);
        }

		?><a href="<?php echo wp_logout_url($redirect_url); ?>" class="<?php esc_attr_e($classes); ?>"><?php esc_html_e($label); ?></a><?php
	}
}