<?php
/**
 * Project: MinervaKB.
 * Copyright: 2015-2017 @KonstruktStudio
 */

class MinervaKB_LoginRegisterFormShortcode extends KST_Shortcode implements KST_Shortcode_Interface {
	protected $ID = 'login-register-form';
	protected $name = 'Login / Register form';
	protected $description = 'Renders Login / Register form';
	protected $icon = 'fa fa-paper-plane-o';

	/**
	 * Renders Login / Register form
	 * @param $atts
	 * @param string $content
	 */
	public function render($atts, $content = '') {
		if (is_user_logged_in()) {
		    return;
        }

        if (defined('MINERVA_DEMO_MODE')) {
            echo do_shortcode('[mkb-warning]Registration & Login are currently disabled on demo site[/mkb-warning]');

            return;
        }

        MKB_TemplateHelper::render_login_register_form();
	}
}