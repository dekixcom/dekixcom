<?php
/**
 * Project: MinervaKB.
 * Copyright: 2015-2017 @KonstruktStudio
 */

class MinervaKB_FAQContentShortcode extends KST_Shortcode implements KST_Shortcode_Interface {

	protected $ID = 'faq-content';
	protected $name = 'FAQ Content';
	protected $description = 'Displays FAQ content';
	protected $icon = 'fa fa-text';
	protected $has_content = false;

	public function render($atts, $content = '') {
	    $faq = get_post($atts['id']);

		echo apply_filters('the_content', $faq->post_content);
	}
}