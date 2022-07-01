<?php
/**
 * Project: MinervaKB.
 * Copyright: 2015-2017 @KonstruktStudio
 */

class MinervaKB_ContentHooks {

	private $info;

	private $restrict;

	/**
	 * Constructor
	 * @param $deps
	 */
	public function __construct($deps) {

		$this->setup_dependencies( $deps );

		// single template actions
		if (MKB_Options::option('show_last_modified_date')) {
			add_action('minerva_single_title_after', array($this, 'single_modified'), 50);
		}

		if (MKB_Options::option('add_article_versions') && MKB_Options::option('show_article_versions')) {
			add_action('minerva_single_title_after', array($this, 'single_versions'), 50);
		}

		add_action('minerva_single_title_after', array($this, 'single_search'), 50);
		add_action('minerva_single_title_after', array($this, 'single_breadcrumbs'), 100);
		add_action('minerva_single_content_after', array($this, 'single_related_articles'), 500);
		add_action('minerva_single_content_after', array($this, 'single_create_ticket'), 600);
		add_action('minerva_single_content_after', array($this, 'single_next_previous_links'), 200);

		// single entry actions
		add_action('minerva_single_entry_header_meta', array($this, 'single_reading_estimate'), 50);
		add_action('minerva_single_entry_header_meta', array($this, 'single_table_of_contents'), 100);

		if (MKB_Options::option('show_article_author')) {
			add_action('minerva_single_entry_footer_meta', array($this, 'single_author'), 100);
		}

		add_action('minerva_single_entry_footer_meta', array($this, 'single_extra_attachments'), 90);
		add_action('minerva_single_entry_footer_meta', array($this, 'single_tags'), 100);
		add_action('minerva_single_entry_footer_meta', array($this, 'single_extra_rating'), 200);
		add_action('minerva_single_entry_footer_meta', array($this, 'single_extra_pageviews'), 300);
		add_action('minerva_single_entry_footer_meta', array($this, 'single_extra_html'), 400);

		// topic template actions
        if (MKB_Options::option('topic_page_elements_mode') === 'default') {
            add_action('minerva_category_title_after', array($this, 'category_search'), 50);
            add_action('minerva_category_title_after', array($this, 'category_breadcrumbs'), 100);
            add_action('minerva_category_title_after', array($this, 'category_children'), 150);
            add_action('minerva_category_loop_after', array($this, 'category_pagination'), 100);
            // TODO: add to topic page builder
            add_action('minerva_category_loop_after', array($this, 'category_create_ticket'), 200);
        }

		// tag template actions
		add_action('minerva_tag_loop_after', array($this, 'tag_pagination'), 100);

		// search template actions
		add_action('minerva_search_title_after', array($this, 'search_results_search'), 50);
		add_action('minerva_search_title_after', array($this, 'search_results_breadcrumbs'), 100);
		add_action('minerva_search_loop_after', array($this, 'search_results_pagination'), 100);

		// no results
		add_action('minerva_no_content_inside', array($this, 'no_results_search'), 100);
	}

	/**
	 * Sets up dependencies
	 * @param $deps
	 */
	private function setup_dependencies($deps) {
		if (isset($deps['info'])) {
			$this->info = $deps['info'];
		}

		if (isset($deps['restrict'])) {
			$this->restrict = $deps['restrict'];
		}
	}

	/**
	 * Article search
	 */
	public function single_search() {

		if (!$this->restrict->check_access() && !MKB_Options::option('restrict_show_article_search')) {
			return false;
		}

		if (MKB_Options::option('add_article_search')) {
			MKB_TemplateHelper::render_search(array(
				"search_title" => MKB_Options::option( 'article_search_title' ),
				"search_title_color" => MKB_Options::option( 'article_search_title_color' ),
				"search_title_size" => MKB_Options::option( 'article_search_title_size' ),
				"search_theme" => MKB_Options::option( 'article_search_theme' ),
				"search_border_color" => MKB_Options::option( 'article_search_border_color' ),
				"search_min_width" => MKB_Options::option( 'article_search_min_width' ),
				"search_container_padding_top" => MKB_Options::option( 'article_search_container_padding_top' ),
				"search_container_padding_bottom" => MKB_Options::option( 'article_search_container_padding_bottom' ),
				"search_placeholder" => MKB_Options::option( 'article_search_placeholder' ),
				"search_tip_color" => MKB_Options::option( 'article_search_tip_color' ),
				"add_pattern_overlay" => MKB_Options::option( 'article_add_pattern_overlay' ),
				"search_container_image_pattern" => MKB_Options::option( 'article_search_container_image_pattern' ),
				"add_gradient_overlay" => MKB_Options::option( 'article_add_gradient_overlay' ),
				"search_container_gradient_from" => MKB_Options::option( 'article_search_container_gradient_from' ),
				"search_container_gradient_to" => MKB_Options::option( 'article_search_container_gradient_to' ),
				"search_container_gradient_opacity" => MKB_Options::option( 'article_search_container_gradient_opacity' ),
				"show_search_tip" => MKB_Options::option( 'article_show_search_tip' ),
				"disable_autofocus" => MKB_Options::option( 'article_disable_autofocus' ),
				"search_tip" => MKB_Options::option( 'article_search_tip' ),
				"search_container_bg" => MKB_Options::option( 'article_search_container_bg' ),
				"search_container_image_bg" => MKB_Options::option( 'article_search_container_image_bg' ),
				"show_topic_in_results" => MKB_Options::option( 'article_show_topic_in_results' )
			));
		}
	}

	/**
	 * Article breadcrumbs
	 */
	public function single_breadcrumbs() {

		if (!$this->restrict->check_access() && !MKB_Options::option('restrict_show_article_breadcrumbs')) {
			return false;
		}

		if (MKB_Options::option('show_breadcrumbs_single')) {
			MKB_TemplateHelper::breadcrumbs( $this->get_article_main_topic(get_the_ID()), MKB_Options::option( 'article_cpt_category' ), 'single' );
		}
	}

    /**
     * Gets main topic for current article (in loop)
     * @param $id
     * @return null
     */
	private function get_article_main_topic($id) {
        $terms = wp_get_post_terms( $id, MKB_Options::option( 'article_cpt_category' ));
        $term = null;

        if ($terms && !empty($terms) && isset($terms[0])) {
            $term = $terms[0];
        }

        return $term;
    }

	/**
	 * Article reading estimate
	 */
	public function single_reading_estimate() {
		$words_per_minute = 275;
		$content = get_post_field( 'post_content', get_the_ID() );
		$word_count = self::count_unicode_words( strip_tags( $content ) );

		$est_reading_time_raw = round( $word_count / $words_per_minute );

		if ( $est_reading_time_raw < 1 ) {
			$est_reading_time = MKB_Options::option( 'estimated_time_less_than_min' );
		} else {
			$est_reading_time = $est_reading_time_raw . ' ' . MKB_Options::option( 'estimated_time_min' );
		}

		if ( MKB_Options::option( 'show_reading_estimate' ) ): ?>
			<div
				class="mkb-article-header__estimate">
				<i class="mkb-estimated-icon fa <?php echo esc_attr(MKB_Options::option( 'estimated_time_icon' )); ?>"></i>
				<span><?php echo esc_html( MKB_Options::option( 'estimated_time_text' ) ); ?></span> <span><?php echo esc_html( $est_reading_time ); ?></span>
			</div>
		<?php endif;
	}

	private function count_unicode_words( $unicode_string ){
		$unicode_string = preg_replace('/[[:punct:][:digit:]]/', '', $unicode_string);
		$unicode_string = preg_replace('/[[:space:]]/', ' ', $unicode_string);
		$words_array = preg_split( "/[\n\r\t ]+/", $unicode_string, 0, PREG_SPLIT_NO_EMPTY );

		return count($words_array);
	}

	/**
	 * Article table of contents
	 */
	public function single_table_of_contents() {

		if (MKB_Options::option('toc_in_content_disable') &&
	        (!MKB_Options::option('toc_sidebar_desktop_only') || $this->info->is_desktop())) {
			return;
		}

		MKB_TemplateHelper::table_of_contents();
	}

	/**
	 * Article versions
	 */
	public function single_versions() {
		?>
		<div class="mkb-article-versions">
			<?php esc_html_e(MKB_Options::option('article_versions_text')); ?><?php
			if (MKB_Options::option( 'enable_versions_links' ) && MKB_Options::option( 'enable_versions_archive' )):
				echo get_the_term_list(
					get_the_ID(),
					'mkb_version',
					' ',
					' '
				);
			else:
				$versions = wp_get_object_terms(get_the_ID(), 'mkb_version');

				if (sizeof($versions)):
					foreach($versions as $version):
						?><span class="mkb-article-version"><?php esc_html_e($version->name); ?></span><?php
					endforeach;
				endif;
			endif;
		?>
		</div><?php
	}

	/**
	 * Article modified date
	 */
	public function single_modified() {
		?>
		<div class="mkb-article-modified-date">
			<span class="mkb-meta-label">
				<?php esc_html_e(MKB_Options::option('last_modified_date_text')); ?>
			</span><?php

			the_modified_date();

			?>
		</div><?php
	}

	/**
	 * Article author
	 */
	public function single_author () {
		?>
		<div class="mkb-article-author">
			<?php esc_html_e(MKB_Options::option('article_author_text')); ?> <?php the_author(); ?>
		</div>
	<?php
	}

	/**
	 * Attachments here
	 */
	public function single_extra_attachments() {

		$files = MinervaKB_ArticleEdit::get_attachments_data();

		if (!sizeof($files)) {
			return;
		}

		$heading = MKB_Options::option('article_attach_label');
		$is_show_filename = MKB_Options::option('attach_archive_file_label') === 'filename';
		$is_show_icon = !MKB_Options::option('attach_icons_off');
		$is_show_size = MKB_Options::option('show_attach_size');

		?>
		<div class="mkb-attachments js-mkb-attachments">
			<?php if ($heading): ?>
				<h3><?php esc_html_e($heading); ?></h3>
			<?php endif; ?>
			<?php foreach($files as $file):

				$label = $file[$is_show_filename? 'filename' : 'title'];

                if ($file['isExternal'] && isset($file['customLabel']) && $file['customLabel']) {
                    $label = $file['customLabel'];
                }

				?>
				<div class="mkb-attachment-item">
					<a class="js-mkb-attachment-link"
					   data-id="<?php esc_attr_e($file['id']); ?>"
					   href="<?php esc_attr_e($file['url']); ?>"
					   target="_blank"
					   title="<?php esc_attr_e(__( 'Download', 'minerva-kb' )); ?> <?php esc_attr_e($label); ?>">
						<?php if ($is_show_icon): ?>
							<i class="mkb-attachment-icon fa <?php esc_attr_e($file['icon']); ?>" style="color:<?php esc_attr_e($file['color']); ?>"></i>
						<?php endif; ?>
                        <?php if ($file['isExternal']): ?>
                            <i class="mkb-attachment-icon-ext fa fa-external-link"></i>
                        <?php endif; ?>
						<span class="mkb-attachment-label"><?php esc_html_e($label); ?>
							<?php if ($is_show_size && (!$file['isExternal'] || $file['filesizeHumanReadable'])): ?>
								<span class="mkb-attachment-size">(<?php esc_html_e($file['filesizeHumanReadable']); ?>)</span>
							<?php endif; ?>
						</span>
					</a>
				</div>
			<?php endforeach; ?>
		</div>
	<?php
	}

	/**
	 * Article tags
	 */
	public function single_tags() {
        $tags = wp_get_object_terms(get_the_ID(), MKB_Options::option( 'article_cpt_tag' ));

		if (MKB_Options::option('show_article_tags') && sizeof($tags)):
            $tags = wp_get_object_terms(get_the_ID(), MKB_Options::option( 'article_cpt_tag' ))

			?><div class="mkb-article-extra__tags"><?php
			if (MKB_Options::option( 'show_article_tags_icon' )):
				?><i class="fa <?php echo esc_attr(MKB_Options::option( 'article_tags_icon' )); ?>"></i><?php
			endif;
				if (!MKB_Options::option( 'tags_disable' )):
					echo get_the_term_list(
						get_the_ID(),
						MKB_Options::option( 'article_cpt_tag' ),
						MKB_Options::option( 'article_tags_label' ) . ' ',
						' '
					);
				else:
                    foreach($tags as $tag):
                        ?><span class="mkb-tag-nolink"><?php echo esc_html($tag->name); ?></span><?php
                    endforeach;
				endif;
			?></div><?php
		endif;
	}

	/**
	 * Article rating
	 */
	public function single_extra_rating() {
		$id = get_the_ID();

		$already_rated_cookie = 'mkb_article_rated_' . $id;

		if (MKB_Options::option('rating_prevent_multiple') && isset($_COOKIE[$already_rated_cookie]) && $_COOKIE[$already_rated_cookie] == 1) {
		    if (MKB_Options::option('rating_already_voted_message')):
                ?>
                <div class="mkb-alreated-rated-article-message">
                    <?php echo MKB_Options::option('rating_already_voted_message'); ?>
                </div>
                <?php
            endif;

		    return;
        }


		$likes = (int) get_post_meta( $id, '_mkb_likes', true );
		$dislikes = (int) get_post_meta( $id, '_mkb_dislikes', true );
		$total = $likes + $dislikes;

		?>
		<div class="mkb-article-extra__actions">
			<?php if ( MKB_Options::option( 'show_likes_button' ) || MKB_Options::option( 'show_dislikes_button' ) ): ?>
				<div class="mkb-article-extra__rating fn-article-rating">
					<div class="mkb-article-extra__rating-likes-block fn-rating-likes-block">
						<div
							class="mkb-article-extra__rating-title"><?php echo esc_html( MKB_Options::option( 'rating_block_label' ) ); ?></div>
						<?php if ( MKB_Options::option( 'show_likes_button' ) ): ?>
							<a href="#" class="mkb-article-extra__like"
							   data-article-id="<?php echo esc_attr( $id ); ?>"
							   data-article-title="<?php echo esc_attr( get_the_title() ); ?>"
							   title="<?php echo esc_attr( MKB_Options::option( 'like_label' ) ); ?>">
								<?php if ( MKB_Options::option( 'show_likes_icon' ) ): ?>
									<i class="mkb-like-icon fa <?php echo esc_attr( MKB_Options::option( 'like_icon' ) ); ?>"></i>
								<?php endif; ?>
								<?php echo esc_html( MKB_Options::option( 'like_label' ) ); ?>
								<?php if ( MKB_Options::option( 'show_likes_count' ) ): ?>
									<span class="mkb-article-extra__stats-likes">
									<?php echo esc_html( $likes ? $likes : 0 ); ?>
								</span>
								<?php endif; ?>
							</a>
						<?php endif; ?>
						<?php if ( MKB_Options::option( 'show_dislikes_button' ) ): ?>
							<a href="#" class="mkb-article-extra__dislike"
							   data-article-id="<?php echo esc_attr( $id ); ?>"
							   data-article-title="<?php echo esc_attr( get_the_title() ); ?>"
							   title="<?php echo esc_attr( MKB_Options::option( 'dislike_label' ) ); ?>">
								<?php if ( MKB_Options::option( 'show_dislikes_icon' ) ): ?>
									<i class="mkb-dislike-icon fa <?php echo esc_attr( MKB_Options::option( 'dislike_icon' ) ); ?>"></i>
								<?php endif; ?>
								<?php echo esc_html( MKB_Options::option( 'dislike_label' ) ); ?>
								<?php if ( MKB_Options::option( 'show_dislikes_count' ) ): ?>
									<span class="mkb-article-extra__stats-dislikes">
									<?php echo esc_html( $dislikes ? $dislikes : 0 ); ?>
								</span>
								<?php endif; ?>
							</a>
						<?php endif; ?>
						<?php if ( MKB_Options::option( 'show_rating_total' ) ): ?>
							<span class="mkb-article-extra__rating-total">
						<?php printf( esc_html( MKB_Options::option( 'rating_total_text' ) ), $likes, $total ); ?>
					</span>
						<?php endif; ?>
					</div>
					<div class="fn-article-feedback-container">
						<?php if ( MKB_Options::option( 'enable_feedback' ) && MKB_Options::option( 'feedback_mode' ) == 'always' ): ?>
							<div class="mkb-article-extra__feedback-form mkb-article-extra__feedback-form--no-content fn-feedback-form">
                                <?php if(MKB_Options::option('feedback_email_on')): ?>
                                    <div class="mkb-article-extra__feedback-form-email-title">
                                        <?php echo esc_html( MKB_Options::option( 'feedback_email_label' ) ); ?>
                                    </div>
                                    <input type="email" name="mkb_feedback_email" class="mkb-article-extra__feedback-form-email js-mkb-feedback-email">
                                <?php endif; ?>
                                <div class="mkb-article-extra__feedback-form-title">
									<?php echo esc_html( MKB_Options::option( 'feedback_label' ) ); ?>
								</div>
								<div class="mkb-article-extra__feedback-form-message">
									<textarea class="mkb-article-extra__feedback-form-message-area js-mkb-feedback-message" rows="5"></textarea>
									<?php echo wp_kses_post(
										MKB_Options::option( 'feedback_info_text' ) ?
											'<div class="mkb-article-extra__feedback-info">' . MKB_Options::option( 'feedback_info_text' ) . '</div>' :
											'' );
									?>
								</div>
								<div class="mkb-article-extra__feedback-form-submit">
									<a href="#"><?php echo esc_html( MKB_Options::option( 'feedback_submit_label' ) ); ?></a>
								</div>
							</div>
						<?php endif; ?>
					</div>
				</div>
			<?php endif; ?>
		</div>
	<?php
	}

	/**
	 * Article pageviews display
	 */
	public function single_extra_pageviews() {
		$id = get_the_ID();
		$views = get_post_meta( $id, '_mkb_views', true );
		?>
		<div class="mkb-article-extra__stats">
			<?php if ( MKB_Options::option( 'show_pageviews' ) ): ?>
				<div class="mkb-article-extra__stats-pageviews">
					<span><?php echo esc_html(MKB_Options::option( 'pageviews_label' )); ?></span> <span><?php echo esc_html( $views ? $views : 0 ); ?></span>
				</div>
			<?php endif; ?>
		</div>
	<?php
	}

	/**
	 * Article bottom HTML
	 */
	public function single_extra_html() {
		if (MKB_Options::option( 'add_article_html' ) && MKB_Options::option( 'article_html' )): ?>
			<div class="mkb-article-extra__custom-html">
				<?php echo wp_kses_post(MKB_Options::option( 'article_html' )); ?>
			</div>
		<?php endif;
	}

    /**
     * Article next previous links display
     */
    public function single_next_previous_links() {
        if (!MKB_Options::option('show_article_navigation')) {
           return false;
        }

        $current_id = get_the_ID();
        $next_prev = $this->get_adjacent_articles($current_id, $this->get_article_main_topic($current_id));

        $previous = $next_prev['prev'];
        $next = $next_prev['next'];

        if ($previous === null && $next === null) {
            return false;
        }

        ?>
        <div class="mkb-article-extra__navigation-wrap">
            <?php if (MKB_Options::option('show_navigation_heading') && MKB_Options::option('article_navigation_label')): ?>
                <h3><?php esc_html_e(MKB_Options::option('article_navigation_label')); ?></h3>
            <?php endif; ?>
            <div class="mkb-article-extra__navigation">
                <div class="mkb-article-extra__navigation-item mkb-article-extra__previous-article"><?php
                    if ($previous !== null):
                        ?><i class="fa fa-angle-double-left"></i> <?php esc_html_e(MKB_Options::option('article_navigation_prev_label'));
                        ?> <a href="<?php esc_attr_e(esc_url(get_the_permalink($previous))); ?>"><?php esc_html_e(get_the_title($previous)); ?></a><?php
                    endif;
                ?></div>
                <div class="mkb-article-extra__navigation-item mkb-article-extra__next-article"><?php
                    if ($next !== null):
                        esc_html_e(MKB_Options::option('article_navigation_next_label'));
                        ?> <a href="<?php esc_attr_e(esc_url(get_the_permalink($next))); ?>"><?php esc_html_e(get_the_title($next)); ?></a> <i class="fa fa-angle-double-right"></i><?php
                    endif;
                ?></div>
            </div>
        </div>
        <?php
    }

    /**
     * Returns prev/next article links with current ordering
     * @param $current_id
     * @param $term_id
     */
    private function get_adjacent_articles($current_id, $term) {
        if (!$term) {
            return array('prev' => null, 'next' => null);
        }

        $query_args = array(
            'post_type' => MKB_Options::option('article_cpt'),
            'posts_per_page' => -1,
            'ignore_sticky_posts' => 1,
            'tax_query' => array(
                array(
                    'taxonomy' => MKB_Options::option('article_cpt_category'),
                    'field' => 'slug',
                    'terms' => $term->slug,
                    'include_children' => false
                )
            )
        );

        if (MKB_Options::option( 'enable_articles_reorder' )) {
            // NOTE: using meta_key breaks queries if no meta is found (empty results). Need to use query
            $query_args['meta_query'] = array(
                'relation' => 'OR',
                array('key' => 'mkb_tax_order_' . $term->term_id, 'compare' => 'EXISTS'),
                array('key' => 'mkb_tax_order_' . $term->term_id, 'compare' => 'NOT EXISTS'),
            );
            $query_args['orderby'] = 'meta_value_num menu_order';
            $query_args['order'] = 'ASC';
        } else {
            $query_args['orderby'] = MKB_Options::option('articles_orderby');
            $query_args['order'] = MKB_Options::option('articles_order');
        }

        $loop = new WP_Query($query_args);
        $all_articles = array();

        if ( $loop->have_posts() ) :
            while ( $loop->have_posts() ) : $loop->the_post();
                array_push($all_articles, get_the_ID());
            endwhile;
        endif;

        wp_reset_postdata();

        $current_index = array_search($current_id, $all_articles);

        if ($current_index === false) {
            return array('prev' => null, 'next' => null);
        }

        return array(
            'prev' => $current_index > 0 ? $all_articles[$current_index - 1] : null,
            'next' => $current_index < count($all_articles) - 1 ? $all_articles[$current_index + 1] : null
        );
    }

	/**
	 * Article related
	 */
	public function single_related_articles() {

		if (!$this->restrict->check_access() && !MKB_Options::option('restrict_show_article_related')) {
			return false;
		}

		$related = get_post_meta(get_the_ID(), '_mkb_related_articles', true);

		if (MKB_Options::option( 'show_related_articles' ) && $related && is_array($related) && !empty($related)): ?>
			<div class="mkb-related-articles">
				<h3><?php echo esc_html(MKB_Options::option( 'related_articles_label' )); ?></h3>
				<ul class="mkb-related-articles__list">
					<?php foreach($related as $article_id): ?>
						<li class="mkb-related-article">
							<a href="<?php echo esc_url(get_permalink($article_id)); ?>"
							   title="<?php echo esc_attr(get_the_title($article_id)); ?>">
								<?php echo esc_html(get_the_title($article_id)); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php
		endif;
	}

	/**
	 * Article create ticket
	 */
	public function single_create_ticket() {
        if (MKB_Options::option('tickets_article_create_ticket_html')): ?>
            <div class="mkb-create-ticket-link-section mkb-create-ticket-link-section--article">
                <?php echo do_shortcode(MKB_Options::option('tickets_article_create_ticket_html')); ?>
            </div>
        <?php endif;
	}

	/**
	 * Topic search
	 */
	public static function category_search($always_render = false) {
		$term = get_term_by( 'id', get_queried_object_id(), MKB_Options::option( 'article_cpt_category' ) );

		if (MKB_Options::option('add_topic_search') && !MinervaKB::topic_option($term, 'topic_no_search_switch') || $always_render) {
            MKB_TemplateHelper::topic_tmpl_search();
		}
	}

	/**
	 * Topic breadcrumbs
	 */
	public function category_breadcrumbs() {
		$term = get_term_by( 'id', get_queried_object_id(), MKB_Options::option( 'article_cpt_category' ) );

		if (MKB_Options::option('show_breadcrumbs_category') && !MinervaKB::topic_option($term, 'topic_no_breadcrumbs_switch')) {
			MKB_TemplateHelper::breadcrumbs( $term, MKB_Options::option( 'article_cpt_category' ) );
		}
	}

	/**
	 * Topic children
	 */
	public static function category_children() {
		$term = get_term_by( 'id', get_queried_object_id(), MKB_Options::option( 'article_cpt_category' ) );

		// topic via page can have it's own child topic modules (or add children template via SC)
		if (MinervaKB::topic_option($term, 'topic_page_switch') && MinervaKB::topic_option($term, 'topic_page')) {
			return;
		}

		MKB_TemplateHelper::topic_tmpl_children();
	}

	/**
	 * Search results search
	 */
	public function search_results_search() {
		if (MKB_Options::option('show_search_page_search')) {
			MKB_TemplateHelper::topic_tmpl_search();
		}
	}

	/**
	 * Search breadcrumbs
	 */
	public function search_results_breadcrumbs() {
		if (MKB_Options::option('show_breadcrumbs_search')) {
			MKB_TemplateHelper::search_breadcrumbs( $_REQUEST['s'] );
		}
	}

	/**
	 * Pagination for category page
	 */
	public function category_pagination () {
		$term = get_term_by( 'id', get_queried_object_id(), MKB_Options::option( 'article_cpt_category' ) );

		if (MinervaKB::topic_option($term, 'topic_page_switch') && MinervaKB::topic_option($term, 'topic_page')) {
			return;
		}

		MKB_TemplateHelper::pagination();
	}

	/**
	 * Create ticket for category page
	 */
	public function category_create_ticket () {
        if (MKB_Options::option('tickets_topic_create_ticket_html')): ?>
            <div class="mkb-create-ticket-link-section mkb-create-ticket-link-section--topic">
                <?php echo do_shortcode(MKB_Options::option('tickets_topic_create_ticket_html')); ?>
            </div>
        <?php endif;
	}

	/**
	 * Pagination for tag page
	 */
	public function tag_pagination () {
		MKB_TemplateHelper::pagination();
	}

	/**
	 * Pagination for search results page
	 */
	public function search_results_pagination () {
		MKB_TemplateHelper::pagination();
	}

	/**
	 * Pagination for search results page
	 */
	public function no_results_search () {
		MKB_TemplateHelper::render_search(array(
			"search_title" => MKB_Options::option( 'topic_search_title' ),
			"search_title_color" => MKB_Options::option( 'topic_search_title_color' ),
			"search_title_size" => MKB_Options::option( 'topic_search_title_size' ),
			"search_theme" => MKB_Options::option( 'topic_search_theme' ),
			"search_border_color" => MKB_Options::option( 'topic_search_border_color' ),
			"search_min_width" => MKB_Options::option( 'topic_search_min_width' ),
			"search_container_padding_top" => MKB_Options::option( 'topic_search_container_padding_top' ),
			"search_container_padding_bottom" => MKB_Options::option( 'topic_search_container_padding_bottom' ),
			"search_placeholder" => MKB_Options::option( 'topic_search_placeholder' ),
			"search_tip_color" => MKB_Options::option( 'topic_search_tip_color' ),
			"add_pattern_overlay" => MKB_Options::option( 'topic_add_pattern_overlay' ),
			"search_container_image_pattern" => MKB_Options::option( 'topic_search_container_image_pattern' ),
			"add_gradient_overlay" => MKB_Options::option( 'topic_add_gradient_overlay' ),
			"search_container_gradient_from" => MKB_Options::option( 'topic_search_container_gradient_from' ),
			"search_container_gradient_to" => MKB_Options::option( 'topic_search_container_gradient_to' ),
			"search_container_gradient_opacity" => MKB_Options::option( 'topic_search_container_gradient_opacity' ),
			"show_search_tip" => MKB_Options::option( 'topic_show_search_tip' ),
			"disable_autofocus" => MKB_Options::option( 'topic_disable_autofocus' ),
			"search_tip" => MKB_Options::option( 'topic_search_tip' ),
			"search_container_bg" => MKB_Options::option( 'topic_search_container_bg' ),
			"search_container_image_bg" => MKB_Options::option( 'topic_search_container_image_bg' ),
			"show_topic_in_results" => MKB_Options::option( 'topic_show_topic_in_results' )
		));
	}
}

