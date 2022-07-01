<?php
/**
 * Project: MinervaKB.
 * Copyright: 2015-2017 @KonstruktStudio
 */

class MinervaKB_RecentlyViewedArticlesShortcode extends KST_Shortcode implements KST_Shortcode_Interface {

	protected $ID = 'recently-viewed-articles';
	protected $name = 'Recently Viewed Articles';
	protected $description = 'Renders recently viewed articles list for user';
	protected $icon = 'fa fa-list';

	/**
	 * Renders recently visited articles
	 * @param $atts
	 * @param string $content
	 */
	public function render($atts, $content = '') {
	    if (!is_user_logged_in()) {
	        return;
        }

        $current_user = wp_get_current_user();

        $recently_viewed_articles = get_user_meta($current_user->ID, '_mkb_recently_viewed_articles', true);

        if (!$recently_viewed_articles) {
            $recently_viewed_articles = array();
        } else {
            $recently_viewed_articles = json_decode($recently_viewed_articles);
        }

        if (sizeof($recently_viewed_articles)) {
            ?><ul><?php
            foreach($recently_viewed_articles as $article_id):
                  ?><li><a href="<?php esc_attr_e(get_the_permalink($article_id)); ?>"><?php esc_html_e(get_the_title($article_id)); ?></a></li><?php
            endforeach;
            ?></ul><?php
        } else {
            echo do_shortcode('[mkb-info]' .
                __('Looks like you haven\t visited our knowledge base yet.', 'minerva-kb') .
                '[/mkb-info]'
            );
        }
	}
}