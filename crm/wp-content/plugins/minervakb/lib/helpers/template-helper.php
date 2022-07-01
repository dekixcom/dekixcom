<?php
/**
 * Project: Minerva KB
 * Copyright: 2015-2016 @KonstruktStudio
 */

require_once(MINERVA_KB_PLUGIN_DIR . 'lib/helpers/table-of-contents.php');

class MKB_TemplateHelper {

	public function __construct() {}

	public static function render_admin_alert($message, $type = 'info', $icon = false) {
        ?>
        <div class="mkb-admin-alert mkb-admin-alert--<?php esc_attr_e($type); ?>">
            <?php if ($icon): ?>
                <i class="fa fa-<?php esc_attr_e($icon); ?> mkb-admin-alert__icon"></i>
            <?php endif; ?>
            <?php echo $message; ?>
        </div>
        <?php
    }

	public static function render_admin_notice($message, $type = 'success', $is_dismissable = true) {
	    $available_types = array('success', 'error', 'warning', 'info');

	    if (!in_array($type, $available_types)) {
	        $type = 'info';
        }

        ?>
        <div class="notice notice-<?php esc_attr_e($type); ?><?php if ($is_dismissable): ?> is-dismissible<?php endif; ?>">
            <p><?php echo $message; ?></p>
        </div>
        <?php
    }

	/**
	 * Gets global info
	 * @param $key
	 *
	 * @return mixed|null
	 */
	public static function info($key) {
		global $minerva_kb;

		if (method_exists ( $minerva_kb->info , $key ) && is_callable(array($minerva_kb->info , $key))) {
			return call_user_func(array($minerva_kb->info, $key));
		}

		return null;
	}

    /**
     * Renders escaped classes array
     * @param $classes_array
     */
	public static function classnames($classes_array) {
	    esc_attr_e(implode(' ', $classes_array));
    }

    /**
     * Renders escaped styles array
     * @param $styles_array
     */
    public static function styles($styles_array) {
        $style_rules = array_map(function($key, $value) {
            return $key . ':' . $value . ';';
        }, array_keys($styles_array), $styles_array);

        esc_attr_e(implode('', $style_rules));
    }

	/**
	 * Determines the parent term for current term
	 * @param $term
	 * @param $taxonomy
	 *
	 * @return array|false|WP_Term
	 */
	private static function get_root_term($term, $taxonomy) {

		if (!$term) {
			return $term;
		}

		if ($term->parent != '0') { // child
			$ancestors = get_ancestors( $term->term_id, $taxonomy, 'taxonomy' );

			if (!empty($ancestors)) {
				return get_term_by( 'id', $ancestors[sizeof($ancestors) - 1], $taxonomy );
			}
		}

		return $term;
	}

	/**
	 * Gets KB home page for given term
	 * @param $term
	 * @param $taxonomy
	 *
	 * @return false|string
	 */
	public static function get_home_page_link($term, $taxonomy) {
		$root_term = self::get_root_term($term, $taxonomy);
		$page_id = apply_filters( 'wpml_object_id', self::get_topic_home_page($root_term), 'page', TRUE );

		return get_the_permalink($page_id);
	}

	/**
	 * Determines if there's a custom KB home page set for term
	 * @param $term
	 *
	 * @return null
	 */
	public static function get_topic_home_page($term) {
		// handle custom breadcrumbs link
		if (MKB_Options::option( 'breadcrumbs_custom_home_switch' ) && MKB_Options::option( 'breadcrumbs_custom_home_page' )) {
			return MKB_Options::option( 'breadcrumbs_custom_home_page' );
		}

		$home_page_id = MKB_Options::option( 'kb_page' );

		if (!$term) {
			return $home_page_id;
		}

		$id = $term->term_id;
		$term_meta = get_option('taxonomy_' . MKB_Options::option( 'article_cpt_category' ) . '_' . $id);

		if ($term_meta && isset($term_meta['topic_parent']) && $term_meta['topic_parent'] != "") {
			$home_page_id = $term_meta['topic_parent'];
		}

		return $home_page_id;
	}

	/**
	 * Renders breadcrumbs
	 * @param $term
	 * @param $taxonomy
	 * @param bool $is_single
	 */
	public static function breadcrumbs( $term, $taxonomy, $type = false, $custom_label = null ) {

		$icon = MKB_Options::option('breadcrumbs_separator_icon');

		$home_label = MKB_Options::option('breadcrumbs_home_label') ?
			MKB_Options::option('breadcrumbs_home_label') :
			__( 'KB Home', 'minerva-kb' );

		$breadcrumbs = array(
			array(
				'name' => $home_label,
				'link' => self::get_home_page_link($term, $taxonomy),
				'icon' => $icon
			)
		);

		$ancestors = null;

		if ($term) {
			$ancestors = get_ancestors( $term->term_id, $taxonomy, 'taxonomy' );
		}

		if ( ! empty( $ancestors ) ) {
			$breadcrumbs = array_merge( $breadcrumbs,
				array_reverse(
					array_map( function ( $id ) use ( $taxonomy, $icon ) {
						$parent = get_term_by( 'id', $id, $taxonomy );

						return array(
							'name' => $parent->name,
							'link' => get_term_link( $parent ),
							'icon' => $icon
						);
					}, $ancestors )
				)
			);
		}

		if ($type === 'single'):
			if ($term) {
				array_push( $breadcrumbs, array(
					'name' => $term->name,
					'link' => get_term_link( $term ),
					'icon' => MKB_Options::option('show_breadcrumbs_current_title') ? $icon : null
				) );
			}

			if (MKB_Options::option('show_breadcrumbs_current_title')) :
				array_push($breadcrumbs, array(
					'name' => get_the_title()
				));
			endif;
		else:
			if ($term) {
				array_push( $breadcrumbs, array(
					'name' => $term->name,
				) );
			}
		endif;

		?>
		<div class="mkb-breadcrumbs">
			<div class="mkb-breadcrumbs__gradient"></div>
			<div class="mkb-breadcrumbs__pattern"></div>
			<span
				class="mkb-breadcrumbs__label">
				<?php echo esc_html( $custom_label === null ? MKB_Options::option( 'breadcrumbs_label' ) : $custom_label ); ?>
			</span>
			<ul class="mkb-breadcrumbs__list">
				<?php
				foreach ( $breadcrumbs as $crumb ):
					?>
					<li>
						<?php if (array_key_exists( "link", $crumb ) && ! empty( $crumb["link"] )): ?>
						<a href="<?php echo esc_attr( $crumb["link"] ); ?>">
							<?php endif; ?>
							<?php echo esc_html( $crumb["name"] ); ?>
							<?php if (array_key_exists( "link", $crumb ) && ! empty( $crumb["link"] )): ?>
						</a>
					<?php endif; ?>
						<?php if (isset( $crumb["icon"] )): ?>
					<i class="mkb-breadcrumbs-icon fa <?php echo esc_attr($crumb["icon"]); ?>"></i>
					<?php endif; ?>
					</li>
				<?php
				endforeach;
				?>
			</ul>
		</div>
	<?php
	}

	/**
	 * Search page breadcrumbs
	 * @param $needle
	 */
	public static function search_breadcrumbs( $needle, $custom_label = null ) {
		$icon = MKB_Options::option('breadcrumbs_separator_icon');

		$home_label = MKB_Options::option('breadcrumbs_home_label') ?
			MKB_Options::option('breadcrumbs_home_label') :
			__( 'KB Home', 'minerva-kb' );

		$home_page = MKB_Options::option( 'kb_page' );

		// handle custom breadcrumbs link
		if (MKB_Options::option( 'breadcrumbs_custom_home_switch' ) && MKB_Options::option( 'breadcrumbs_custom_home_page' )) {
			$home_page = MKB_Options::option( 'breadcrumbs_custom_home_page' );
		}

		$home_page = apply_filters( 'wpml_object_id', $home_page, 'page', TRUE  );

		$breadcrumbs = array(
			array(
				'name' => $home_label,
				'link' => get_the_permalink($home_page),
				'icon' => $icon
			)
		);

		// check if product search
		if (isset($_REQUEST['kb_id']) && (int)$_REQUEST['kb_id'] > 0) {

			$product = get_term_by('id', (int)$_REQUEST['kb_id'], MKB_Options::option('article_cpt_category'));

			array_push($breadcrumbs, array(
				'name' => $product->name,
				'link' => esc_url(get_term_link($product, MKB_Options::option('article_cpt_category'))),
				'icon' => $icon
			));
		}

		array_push($breadcrumbs, array(
			'name' => sprintf(MKB_Options::option( 'search_results_breadcrumbs_label' ), $needle)
		));

		?>
		<div class="mkb-breadcrumbs">
			<div class="mkb-breadcrumbs__gradient"></div>
			<div class="mkb-breadcrumbs__pattern"></div>
			<span
				class="mkb-breadcrumbs__label">
				<?php echo esc_html( $custom_label === null ? MKB_Options::option( 'breadcrumbs_label' ) : $custom_label); ?>
			</span>
			<ul class="mkb-breadcrumbs__list">
				<?php
				foreach ( $breadcrumbs as $crumb ):
					?>
					<li>
						<?php if (isset($crumb["link"]) && ! empty($crumb["link"]) ): ?>
							<a href="<?php echo esc_attr( $crumb["link"] ); ?>">
						<?php endif; ?>
						<?php echo esc_html( $crumb["name"] ); ?>
						<?php if ( isset($crumb["link"]) && ! empty($crumb["link"]) ): ?>
							</a>
						<?php endif; ?>
						<?php if (isset( $crumb["icon"])): ?>
							<i class="mkb-breadcrumbs-icon fa <?php echo esc_attr($crumb["icon"]); ?>"></i>
						<?php endif; ?>
					</li>
				<?php
				endforeach;
				?>
			</ul>
		</div>
	<?php
	}

	/**
	 * Content class for use in templates
	 * @param string $type
	 * @return string
	 */
	public static function root_class($type = 'page') {
		$classes = array('mkb-root', 'mkb-clearfix');

		$sidebar_type = self::get_sidebar_type($type);

		if ($sidebar_type !== 'none') {
			array_push($classes, 'mkb-sidebar-' . $sidebar_type);
		}

		if ($type == 'page') {
		    if (MinervaKB_PageTemplates::is_minerva_page_template()) { // page template
                if (MKB_PageOptions::template_option('add_container')) {
                    array_push($classes, 'mkb-container');
                }
            } else if (MKB_PageOptions::is_builder_page()) {
		        if (MKB_PageOptions::option('add_container')) {
                    array_push($classes, 'mkb-container');
                }
            } else if (MKB_Options::option('home_page_container_switch')) {
                array_push($classes, 'mkb-container');
            }
		} else { // any other type, just add container
			array_push($classes, 'mkb-container');
		}

		if ($type === 'article' && MKB_Options::option('article_include_base_html')) {
			array_push($classes, 'mkb-add-base-html');
		}

		return join(' ', $classes);
	}

	/**
	 * Content class for use in templates
	 * @param string $type
	 * @return string
	 */
	public static function content_class($type = 'page') {
		$classes = array('mkb-content-main');

		array_push($classes, 'mkb-content-main--' . $type);

		if (self::get_sidebar_type($type) !== 'none' || $type === 'page-support-account' || $type === 'support-ticket') {
			array_push($classes, 'mkb-content-main--has-sidebar');
		}

		return join(' ', $classes);
	}

	/**
	 * Detects sidebar for page type
	 * @param $type
	 *
	 * @return null|string
	 */
	private static function get_sidebar_type($type) {
		$sidebar_type = 'none';

		if ($type === 'topic') {
			$term = get_term_by( 'id', get_queried_object_id(), MKB_Options::option( 'article_cpt_category' ) );

			$sidebar_type = MinervaKB::topic_option($term, 'topic_sidebar_switch') ?
				MinervaKB::topic_option($term, 'topic_sidebar') :
				MKB_Options::option( $type . '_sidebar' );

		} else {
			$sidebar_type = MKB_Options::option( $type . '_sidebar' );
		}

		return $sidebar_type;
	}

	/**
	 * Left sidebar
	 * @param string $type
	 */
	public static function maybe_render_left_sidebar($type = 'page', $term = null) {
		global $minerva_kb;

		if (!$minerva_kb->info->is_desktop()) {
			return; // we always render sidebar last on devices
		}

		if ($type === 'topic' &&
		    self::get_topic_option($term, 'topic_sidebar_switch') &&
		    self::get_topic_option($term, 'topic_sidebar') === 'left' ||
		    MKB_Options::option( $type . '_sidebar' ) === 'left' ||
            $type === 'support-account' ||
            $type === 'support-ticket') {

			self::render_sidebar($type);
		}
	}

	/**
	 * Right sidebar
	 * @param string $type
	 */
	public static function maybe_render_right_sidebar($type = 'page', $term = null) {
		global $minerva_kb;

		if ($type === 'topic' &&
		    self::get_topic_option($term, 'topic_sidebar_switch') &&
		    self::get_topic_option($term, 'topic_sidebar')) {

			$sidebar_position = self::get_topic_option($term, 'topic_sidebar');
		} else {
			$sidebar_position = MKB_Options::option( $type . '_sidebar' );
		}

		if ($sidebar_position === 'right' || (!$minerva_kb->info->is_desktop() && $sidebar_position === 'left')) {
			self::render_sidebar($type);
		}
	}

	/**
	 * Sidebar render
	 * @param $sidebar_id
	 */
	public static function render_sidebar($sidebar_id) {
		?><aside class="mkb-sidebar" role="complementary">
		<?php
		if (!is_active_sidebar( 'sidebar-kb-' . $sidebar_id ) && is_user_logged_in()) {
			?><div class="widget mkb-widget">
			<h2 class="mkb-widget-title">Empty sidebar</h2>
			<div class="mkb-empty-sidebar-message"><?php
			esc_html_e('Tip: Looks like this sidebar has no widgets yet. Go ahead and add some at ', 'minerva-kb');
			?><a href="<?php echo esc_attr(admin_url('widgets.php')); ?>" target="_blank"><?php
			esc_html_e('Widgets page', 'minerva-kb');
			?></a></div>
			</div><?php
		}
		dynamic_sidebar( 'sidebar-kb-' . $sidebar_id ); ?>
		</aside><?php
	}

	/**
	 * Checks if topic is a dynamic
	 * @param $term
	 */
	private static function is_dynamic_topic($term) {
		return $term == 'recent' || $term == 'updated' || $term == 'top_views' || $term == 'top_likes';
	}

	/**
	 * Gets icon for topic
	 * @param $term
	 * @param array $args
	 * @return null
	 */
	public static function get_topic_icon($term, $args = array()) {
		$icon = empty($args) ? MKB_Options::option('topic_icon') : $args['topic_icon'];

		if (self::is_dynamic_topic($term)) {
			return $icon;
		}

		$id = $term->term_id;
		$term_meta = get_option('taxonomy_' . MKB_Options::option( 'article_cpt_category' ) . '_' . $id);

		if ($term_meta && isset($term_meta['topic_icon'])) {
			$icon = $term_meta['topic_icon'];
		}

		return $icon;
	}

	/**
	 * Get topic custom image
	 * @param $term
	 * @param array $args
	 *
	 * @return string
	 */
	public static function get_topic_image($term, $args = array()) {
		$image = '';

		if (self::is_dynamic_topic($term)) {
			return $image;
		}

		$id = $term->term_id;
		$term_meta = get_option('taxonomy_' . MKB_Options::option( 'article_cpt_category' ) . '_' . $id);

		if ($term_meta && isset($term_meta['topic_image'])) {
			$image = $term_meta['topic_image'];
		}

		return $image;
	}

	/**
	 * Get topic custom color
	 * @param $term
	 * @param array $args
	 *
	 * @return null
	 */
	public static function get_topic_color($term, $args = array()) {
		$color = empty($args) ? MKB_Options::option('topic_color') : $args['topic_color'];

		if (self::is_dynamic_topic($term)) {
			return $color;
		}

		$id = $term->term_id;
		$term_meta = get_option('taxonomy_' . MKB_Options::option( 'article_cpt_category' ) . '_' . $id);

		if ($term_meta && isset($term_meta['topic_color'])) {
			$color = $term_meta['topic_color'];
		}

		return $color;
	}

	public static function get_topic_color_by_id($id) {
		$color = null;
		$term_meta = get_option('taxonomy_' . MKB_Options::option( 'article_cpt_category' ) . '_' . $id);

		if ($term_meta && isset($term_meta['topic_color'])) {
			$color = $term_meta['topic_color'];
		}

		return $color;
	}

	/**
	 * Gets topic option
     * TODO: refactor
	 */
	public static function get_topic_option($term, $key) {
	    if (!isset($term)) {
	        return '';
        }

		$id = $term->term_id;
		$term_meta = get_option('taxonomy_' . MKB_Options::option( 'article_cpt_category' ) . '_' . $id);

		if ($term_meta && isset($term_meta[$key])) {
			return self::normalize_value($term_meta[$key]);
		} else {
			return '';
		}
	}

    /**
     * Gets taxonomy option
     */
    public static function get_taxonomy_option($term, $taxonomy, $key) {
        if (!isset($term)) {
            return '';
        }

        $id = $term->term_id;
        $term_meta = get_option('taxonomy_' . $taxonomy . '_' . $id);

        if ($term_meta && isset($term_meta[$key])) {
            return self::normalize_value($term_meta[$key]);
        } else {
            return '';
        }
    }

	/**
	 * Sets topic option
	 */
	public static function set_topic_option($term_id, $key, $value) {
		$term_meta = get_option('taxonomy_' . MKB_Options::option( 'article_cpt_category' ) . '_' . $term_id);
		$term_meta[$key] = $value;
		update_option( "taxonomy_" . MKB_Options::option( 'article_cpt_category' ) . '_' . $term_id, $term_meta );
	}

    /**
     * Sets taxonomy option
     */
    public static function set_taxonomy_option($term_id, $taxonomy, $key, $value) {
        $term_meta = get_option('taxonomy_' . $taxonomy . '_' . $term_id);
        $term_meta[$key] = $value;
        update_option( 'taxonomy_' . $taxonomy . '_' . $term_id, $term_meta);
    }

	private static function normalize_value($value) {
		if ($value === 'true' || $value === 'on') {
			return true;
		} else if ($value === 'false' || $value === 'off') {
			return false;
		} else if (is_string($value) && strpos($value, 'fa ') !== false) {
			return str_replace('fa ', '', $value);
		} else {
			return $value;
		}
	}

	/**
	 * Renders topics depending on settings
	 * @param array $settings
	 */
	public static function render_topics($settings = array()) {
		/**
		 * Do not render topic if globally restricted
		 */
		global $minerva_kb;

		if (MKB_Options::option('restrict_on') && MKB_Options::option('restrict_remove_from_archives') && $minerva_kb->restrict->is_user_globally_restricted()) {
			return false;
		}

		// parse global options
		$args = wp_parse_args(
			$settings,
			array(
				"home_topics" => MKB_Options::option( 'home_topics' ),
				"home_topics_limit" => MKB_Options::option( 'home_topics_limit' ),
				"home_topics_hide_children" => MKB_Options::option( 'home_topics_hide_children' ),
				"home_view" => MKB_Options::option( 'home_view' ),
				"home_layout" => MKB_Options::option( 'home_layout' ),
				"topics_title" => "", // by default, home page has no topics title
				"topics_title_color" => "#333333",
				"topics_title_size" => "2em",
                "add_container" => false
			)
		);

		// for terms, 0 is used instead of -1
		if ((int)$args['home_topics_limit'] == -1) {
			$args['home_topics_limit'] = 0;
		}

		$topics = array();

		if ($args['home_topics']) {
			$ids = explode(',', $args['home_topics']);

			foreach ($ids as $id) {
				if (self::is_dynamic_topic($id)) {
					array_push($topics, $id);
					continue;
				}

				$topic = get_term_by('id', (int)$id, MKB_Options::option( 'article_cpt_category' ));
				array_push($topics, $topic);
			}
		} else {
			$topics = get_terms( MKB_Options::option( 'article_cpt_category' ), array(
				'hide_empty' => true,
				'number' => $args['home_topics_limit']
			) );

			if ($args['home_topics_hide_children'] && !empty($topics)) {
				$topics = array_filter($topics, function($topic) {
					return $topic->parent == 0;
				});
			}

			@uasort($topics, function($a, $b) {
				$orderA = (int)MKB_TemplateHelper::get_topic_option($a, 'topic_order');
				$orderB = (int)MKB_TemplateHelper::get_topic_option($b, 'topic_order');

				if ($orderA == $orderB) {
					return 0;
				}

				return ($orderA < $orderB) ? -1 : 1;
			});
		}

		$columns = self::get_home_columns($args['home_layout']);
		$view_mode = $args['home_view'];
		$row_open = false;

		if ($args['topics_title']) :
		    $title_styles = array(
                'color' => $args['topics_title_color'],
                'font-size' => MKB_SettingsBuilder::css_size_to_string($settings['topics_title_size'])
            );
		    $title_classes = array('mkb-section-title');

		    if ($args['add_container']) {
		        array_push($title_classes, 'mkb-container');
            }

			?>
			<div class="<?php self::classnames($title_classes); ?>" style="<?php self::styles($title_styles); ?>">
				<?php echo esc_html($settings['topics_title']); ?>
			</div>
		<?php
		endif;

		// in some cases CSS, unique CSS uuid class is required (for example, in shortcodes)
		$has_custom_styles = $args['home_view'] == 'box';
		$css_id = '';

		if ($has_custom_styles) {
			$css_id = uniqid('mkb-uuid-');
		}

		$wrap_classes = array("mkb-home-topics", "mkb-columns", "mkb-columns-" . $columns);

		if ($has_custom_styles) {
		    array_push($wrap_classes, $css_id);
        }

        if ($args['add_container']) {
            array_push($wrap_classes, 'mkb-container');
        }

		?><div class="<?php self::classnames($wrap_classes); ?>"><?php

		if ($has_custom_styles) {
			?><style><?php

			if ($args['home_view'] == 'box' && isset($args['box_view_item_bg'])) {
			?>
			.<?php echo esc_attr( $css_id ); ?> .kb-topic.kb-topic--box-view .kb-topic__inner { background: <?php echo esc_attr( $args['box_view_item_bg'] ); ?>; }
			.<?php echo esc_attr( $css_id ); ?> .kb-topic.kb-topic--box-view .kb-topic__inner:hover { background: <?php echo esc_attr( $args['box_view_item_hover_bg'] ); ?>; }<?php
			}

			?></style><?php
		}

		// remove dynamic topics for box view
		$is_box = $view_mode === 'box';
		$topics = array_filter($topics, function($topic) use ($is_box) {
			if (!$topic) {
				return false;
			}

			if ($is_box) {
				return $topic != 'recent' && $topic != 'updated' && $topic != 'top_views' && $topic != 'top_likes';
			}
			return true;
		});

		// topics loop
		if ( sizeof( $topics ) ):
			$i = 0;
			$row_index = $columns;

			foreach ( $topics as $topic ):

				// skip all restricted topics
				if (MKB_Options::option('restrict_on') &&
				    MKB_Options::option('restrict_remove_from_archives') &&
				    isset($topic->term_id) && !$minerva_kb->restrict->is_topic_allowed($topic)) {

					continue;
				}

				if ($i % $columns === 0):
					echo '<div class="mkb-row">';
					$row_index = $columns;
					$row_open = true;
				endif;

				if ($view_mode === 'list'):
					self::render_as_list($topic, $args);
				else:
					self::render_as_box($topic, $args);
				endif;

				if ( ($i + 1) % $columns === 0 ):
					echo '</div >';
					$row_open = false;
				endif;

				--$row_index;
				++$i;
			endforeach; // end of terms loop

			if ( $row_open ):

				for($j =0; $j<$row_index; $j++):
					?><div class="kb-topic-gap"></div><?php
				endfor;

				echo '</div >';
				$row_open = false;
			endif;

		endif; // end of topics loop
			?>
		</div>
		<?php
	}

	private static function get_topic_id($term) {
		return self::is_dynamic_topic($term) ? $term : $term->term_id;
	}

	/**
	 * Render topic as articles list
	 * @param $term
	 */
	public static function render_as_list($term, $settings = array()) {

		if (!$term) {
			return;
		}

		$topic_link = self::get_term_link( $term );

		$args = wp_parse_args(
			$settings,
			array(
				"show_articles_count" => MKB_Options::option( 'show_articles_count' ),
				"show_all_switch" => MKB_Options::option( 'show_all_switch' ),
				"show_all_label" => MKB_Options::option( 'show_all_label' ),
				"home_topics_articles_limit" => MKB_Options::option( 'home_topics_articles_limit' ),
				"articles_count_bg" => MKB_Options::option( 'articles_count_bg' ),
				"articles_count_color" => MKB_Options::option( 'articles_count_color' ),
				"show_topic_icons" => MKB_Options::option( 'show_topic_icons' ),
				"show_article_icons" => MKB_Options::option( 'show_article_icons' ),
				"article_icon" => MKB_Options::option( 'article_icon' ),
				"topic_color" => MKB_Options::option( 'topic_color' ),
				"topic_icon" => MKB_Options::option( 'topic_icon' ),
				"force_default_topic_color" => MKB_Options::option( 'force_default_topic_color' ),
				"force_default_topic_icon" => MKB_Options::option( 'force_default_topic_icon' ),
			)
		);

		$loop = self::get_term_items_loop($term, $args);

		$count_style = 'background: ' . $args['articles_count_bg'] . '; color: ' . $args['articles_count_color'] . ';';
		$topic_color = $args['force_default_topic_color'] && $args['topic_color'] ? $args['topic_color'] : self::get_topic_color( $term, $args );
		$topic_icon = $args['force_default_topic_icon'] && $args['topic_icon'] ? $args['topic_icon'] : self::get_topic_icon( $term, $args );

		?>
		<div class="kb-topic topic-id-<?php esc_attr_e(self::get_topic_id($term)); ?>">
			<div class="kb-topic__inner">
				<h3 class="kb-topic__title" <?php
				if ($topic_color) { echo 'style="color: ' . esc_attr($topic_color) . ';"'; }
				?>>
					<?php if ($topic_link && $topic_link != '#'): ?>
						<a class="kb-topic__title-link" href="<?php echo esc_attr( $topic_link ); ?>" <?php
							if ($topic_color) { echo 'style="color: ' . esc_attr($topic_color) . ';"'; }
						?>>
					<?php endif; ?>
						<?php if ( isset($args['show_topic_icons']) && $args['show_topic_icons'] ): ?>
							<span class="kb-topic__title-icon">
								<i class="kb-topic__list-icon fa <?php echo esc_attr( $topic_icon ); ?>"></i>
							</span>
						<?php endif; ?>

						<?php echo esc_html( self::get_term_name($term) ); ?>

						<?php if ( isset($args['show_articles_count']) && $args['show_articles_count'] ): ?>
							<span class="kb-topic__count" style="<?php echo esc_attr($count_style); ?>">
								<?php
								$post_count = self::get_term_post_count($term, $loop);
									echo esc_html($post_count); ?> <?php echo esc_html($post_count == 1 ?
										MKB_Options::option( 'article_text' ) :
										MKB_Options::option( 'articles_text' )
									);
								?>
							</span>
						<?php endif; ?>
				<?php if($topic_link && $topic_link!= '#'): ?>
					</a>
				<?php endif; ?>
				</h3>

				<?php

				if (MKB_Options::option('topic_show_child_topics_list')):
					$children = !self::is_dynamic_topic($term) ? self::get_topic_children($term->term_id) : array();

					if (!empty($children)):

                        @uasort($children, function($a, $b) {
                            $orderA = (int)MKB_TemplateHelper::get_topic_option($a, 'topic_order');
                            $orderB = (int)MKB_TemplateHelper::get_topic_option($b, 'topic_order');

                            if ($orderA == $orderB) {
                                return 0;
                            }

                            return ($orderA < $orderB) ? -1 : 1;
                        });

                        ?>
						<ul class="kb-topic__child-topics">
							<?php foreach($children as $child_topic): ?>
							<li>
								<i class="kb-topic__child-icon fa <?php esc_attr_e(MKB_Options::option('topic_child_topic_list_icon')); ?>"></i>
								<a href="<?php esc_attr_e(self::get_term_link( $child_topic ))?>"><?php esc_html_e($child_topic->name); ?></a>
							</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				<?php endif; ?>

                <?php if (!isset($args['home_topics_articles_limit']) || $args['home_topics_articles_limit'] !== '0'): ?>

                    <div class="kb-topic__articles <?php if (isset($args['show_article_icons']) && $args['show_article_icons']):
                        echo ' kb-topic__articles--with-icons';
                    endif; ?>">
                        <ul>
                            <?php

                            if ( $loop->have_posts() ) :
                                while ( $loop->have_posts() ) : $loop->the_post();

                                    $article_id = get_the_ID();
                                    $views = get_post_meta( $article_id, '_mkb_views', true );
                                    $likes = get_post_meta( $article_id, '_mkb_likes', true );

                                    ?>
                                    <li>
                                        <a href="<?php echo esc_attr( get_the_permalink() ); ?>">
                                            <?php if(isset($args['show_article_icons']) && $args['show_article_icons']): ?>
                                            <i class="kb-topic__list-article-icon fa <?php echo esc_attr( $args['article_icon'] ); ?>"></i>
                                            <?php endif; ?>
                                            <span class="kb-topic__list-article-title"><?php echo esc_html(get_the_title()); ?></span>
                                            <?php if (MKB_Options::option( 'show_article_views' ) && $views): ?>
                                                <span class="kb-topic__list-article-views" title="<?php esc_attr_e(__('Article views', 'minerva-kb')); ?>">
                                                    <i class="fa fa-eye kb-topic__list-article-meta-icon"></i><?php echo esc_html($views); ?>
                                                </span>
                                            <?php endif; ?>
                                            <?php if (MKB_Options::option( 'show_article_likes' ) && $likes): ?>
                                                <span class="kb-topic__list-article-likes" title="<?php esc_attr_e(__('Article likes', 'minerva-kb')); ?>">
                                                    <i class="fa fa-heart-o kb-topic__list-article-meta-icon"></i><?php echo esc_html($likes); ?>
                                                </span>
                                            <?php endif; ?>
                                        </a>
                                    </li>
                                <?php endwhile;
                            endif;
                            wp_reset_postdata();
                            ?>
                        </ul>

                        <?php if (!self::is_dynamic_topic($term) && $args['show_all_switch'] && $loop->found_posts > $loop->post_count): ?>
                            <a class="kb-topic__show-all"
                               href="<?php echo esc_attr( $topic_link ); ?>">
                                <?php echo esc_html($args['show_all_label']); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
			</div>
		</div>
	<?php
	}

	/**
	 * Render topic as articles boxes
	 * @param $term
	 */
	public static function render_as_box($term, $settings = array()) {

		if (!$term) {
			return;
		}

		$args = wp_parse_args(
			$settings,
			array(
				"show_articles_count" => MKB_Options::option( 'show_articles_count' ),
				"show_all_switch" => MKB_Options::option( 'show_all_switch' ),
				"home_topics_show_description" => MKB_Options::option( 'home_topics_show_description' ),
				"show_all_label" => MKB_Options::option( 'show_all_label' ),
				"home_topics_articles_limit" => MKB_Options::option( 'home_topics_articles_limit' ),
				"articles_count_bg" => MKB_Options::option( 'articles_count_bg' ),
				"articles_count_color" => MKB_Options::option( 'articles_count_color' ),
				"show_topic_icons" => MKB_Options::option( 'show_topic_icons' ),
				"show_article_icons" => MKB_Options::option( 'show_article_icons' ),
				"article_icon" => MKB_Options::option( 'article_icon' ),
				"topic_color" => MKB_Options::option( 'topic_color' ),
				"force_default_topic_color" => MKB_Options::option( 'force_default_topic_color' ),
				"force_default_topic_icon" => MKB_Options::option( 'force_default_topic_icon' ),
				"topic_icon" => MKB_Options::option( 'topic_icon' ),
				"use_topic_image" => MKB_Options::option( 'use_topic_image' ),
				"image_size" => MKB_Options::option( 'image_size' ),
				"topic_icon_padding_top" => MKB_Options::option( 'topic_icon_padding_top' ),
				"topic_icon_padding_bottom" => MKB_Options::option( 'topic_icon_padding_bottom' ),
			)
		);

		$loop = self::get_term_items_loop($term, $args);
		$post_count = self::get_term_post_count($term, $loop);
		$topic_link = self::get_term_link( $term );

		if (!self::is_dynamic_topic($term) && (int)$post_count === 1 && MKB_Options::option('topic_box_single_item_article_link') && !self::get_topic_children($term->term_id)) {
			$topic_link = get_the_permalink($loop->posts[0]);
		}

		$topic_color = $args['force_default_topic_color'] && $args['topic_color'] ? $args['topic_color'] : self::get_topic_color( $term, $args );
		$topic_icon = $args['force_default_topic_icon'] && $args['topic_icon'] ? $args['topic_icon'] : self::get_topic_icon( $term, $args );

		$icon_holder_style = 'padding-top: ' . MKB_SettingsBuilder::css_size_to_string($args['topic_icon_padding_top']) . ';' .
		                     'padding-bottom: ' . MKB_SettingsBuilder::css_size_to_string($args['topic_icon_padding_bottom']) . ';';

		?>
		<div class="kb-topic topic-id-<?php esc_attr_e(self::get_topic_id($term)); ?> kb-topic--box-view">
			<?php
			if (!self::is_dynamic_topic($term)): ?>
				<a href="<?php echo esc_attr( $topic_link ); ?>">
			<?php endif; ?>
				<div class="kb-topic__inner">
					<div class="kb-topic__box-header" <?php
					if ($topic_color) { echo 'style="color: ' . esc_attr( $topic_color ) . ';"'; }
					?>>
						<?php if (isset($args['show_topic_icons']) && $args['show_topic_icons']): ?>
						<div class="kb-topic__icon-holder" style="<?php echo esc_attr($icon_holder_style); ?>">
							<?php if ($args['use_topic_image']): ?>
							<img class="kb-topic__icon-image"
							     style="width:<?php echo esc_attr(MKB_SettingsBuilder::css_size_to_string($args['image_size'])); ?>"
							     src="<?php echo esc_attr(MKB_SettingsBuilder::media_url(self::get_topic_image($term))); ?>" />
							<?php else: ?>
							<i class="kb-topic__box-icon fa <?php echo esc_attr($topic_icon); ?>"></i>
							<?php endif; ?>
						</div>
						<?php endif; ?>
						<h3 class="kb-topic__title" <?php
						if ($topic_color) { echo 'style="color: ' . esc_attr($topic_color) . ';"'; }
						?>>
							<?php echo esc_html(self::get_term_name($term)); ?>
						</h3>
					</div>

					<div class="kb-topic__articles">
						<?php if ($args['home_topics_show_description'] && self::get_term_description($term)): ?>
							<div class="kb-topic__description">
								<?php echo MKB_Options::option('raw_topic_description_switch') ?
									self::get_term_description($term) :
									esc_html(self::get_term_description($term)); ?>
							</div>
						<?php endif; ?>
						<?php if (isset($args['show_articles_count']) && $args['show_articles_count']): ?>
							<div class="kb-topic__box-count">
								<?php
									echo esc_html($post_count); ?> <?php
									echo esc_html($post_count == 1 ?
										MKB_Options::option( 'article_text' ) :
										MKB_Options::option( 'articles_text' )
									);
								?>
							</div>
						<?php endif; ?>
						<?php if ( !self::is_dynamic_topic($term) && $args['show_all_switch']): ?>
							<div class="kb-topic__show-all">
								<?php echo esc_html($args['show_all_label']); ?>
							</div>
						<?php endif; ?>
					</div>
				</div>
			<?php if (!self::is_dynamic_topic($term)): ?>
				</a>
			<?php endif; ?>
		</div>
	<?php
		wp_reset_postdata();
	}

	/**
	 * Renders single topic
	 */
	public static function render_topic($settings) {
		$term = get_term_by( 'id', $settings["id"], MKB_Options::option( 'article_cpt_category' ) );

		if (!$term) {
			return __('Topic term with selected ID does not exist', 'minerva-kb');
		}

		/**
		 * Do not render topic if globally restricted
		 */
		global $minerva_kb;

		if (MKB_Options::option('restrict_on') &&
		    MKB_Options::option('restrict_remove_from_archives') &&
		    ($minerva_kb->restrict->is_user_globally_restricted() || !$minerva_kb->restrict->is_topic_allowed($term)) ) {

			return false;
		}

		/**
		 * Child topics
		 */
		self::render_topic_children($term, $settings);

		/**
		 * Topic loop
		 */
		$query_args = array(
			'post_type' => MKB_Options::option( 'article_cpt' ),
			'ignore_sticky_posts' => 1,
			'posts_per_page' => $settings["limit"],
			'tax_query' => array(
				array(
					'taxonomy' => MKB_Options::option( 'article_cpt_category' ),
					'field' => 'slug',
					'terms' => $term->slug,
				),
			)
		);

		$topic_loop = new WP_Query( $query_args );

		if ($topic_loop->have_posts()):
			while ( $topic_loop->have_posts() ) : $topic_loop->the_post();
				include( MINERVA_KB_PLUGIN_DIR . 'lib/templates/content.php' );
			endwhile;
		endif;

		wp_reset_postdata();
	}

	/**
	 * Topic children
	 */
	public static function render_topic_children($term, $settings) {
		$children = $terms = self::get_topic_children($term->term_id);

		$children_columns = self::get_columns( $settings['columns'] );
		$view_mode = $settings['view'];
		$row_open = false;

		if ( ! empty( $children ) ):

			do_action('minerva_category_children_before');

			?>
			<div class="mkb-topic__children mkb-columns mkb-columns-<?php echo esc_attr( $children_columns ); ?>">
				<?php

				do_action('minerva_category_children_inside_before');

				$i = 0;

				foreach ( $children as $topic ):

					if ( $i % $children_columns === 0 ):
						echo '<div class="mkb-row">';
						$row_open = true;
					endif;

					if ( $view_mode === 'list' ):
						self::render_as_list( $topic );
					else:
						self::render_as_box( $topic );
					endif;

					if ( ( $i + 1 ) % $children_columns === 0 ):
						echo '</div >';
						$row_open = false;
					endif;

					++ $i;
				endforeach;

				if ( $row_open ):
					echo '</div >';
					$row_open = false;
				endif;

				do_action('minerva_category_children_inside_after');

				?>
			</div>
		<?php

			do_action('minerva_category_children_after');

		endif;
	}

	public static function get_topic_children($term_id) {
		return get_terms( MKB_Options::option( 'article_cpt_category' ), array(
			'taxonomy' => MKB_Options::option( 'article_cpt_category' ),
			'hide_empty' => true,
			'parent' => $term_id
		) );
	}

	/**
	 * FAQ
	 */
	public static function render_faq($settings = array()) {

		if (MKB_Options::option('disable_faq')) {
			return;
		}

		// parse global options
		$args = wp_parse_args(
			$settings,
			array(
				'show_faq_filter_icon' => MKB_Options::option('show_faq_filter_icon'),
				'faq_filter_icon' => MKB_Options::option('faq_filter_icon'),
				'faq_filter_theme' => MKB_Options::option('faq_filter_theme'),
				'faq_filter_placeholder' => MKB_Options::option('faq_filter_placeholder'),
				'faq_filter_clear_icon' => MKB_Options::option('faq_filter_clear_icon'),
				'faq_no_results_text' => MKB_Options::option('faq_no_results_text'),
				'show_faq_toggle_all_icon' => MKB_Options::option('show_faq_toggle_all_icon'),
				'faq_toggle_all_icon' => MKB_Options::option('faq_toggle_all_icon'),
				'faq_toggle_all_icon_open' => MKB_Options::option('faq_toggle_all_icon_open'),
				'faq_toggle_all_open_text' => MKB_Options::option('faq_toggle_all_open_text'),
				'faq_toggle_all_close_text' => MKB_Options::option('faq_toggle_all_close_text'),
                'add_container' => false
			)
		);

		$categories = array();

		if ($args['home_faq_categories']) {
			$ids = explode(',', $args['home_faq_categories']);

			foreach ($ids as $id) {
				array_push($categories, get_term_by('id', (int)$id, 'mkb_faq_category'));
			}
		} else {
			$categories = get_terms( 'mkb_faq_category', array(
				'hide_empty' => true
			) );
		}

		$wrap_classes = array('mkb-home-faq', 'kb-faq', 'fn-kb-faq-container');

		if ($args['add_container']) {
            array_push($wrap_classes, 'mkb-container');
        }

		$wrap_styles = array(
            'margin-top' => MKB_SettingsBuilder::css_size_to_string($args['home_faq_margin_top']),
            'margin-bottom' => MKB_SettingsBuilder::css_size_to_string($args['home_faq_margin_bottom'])
        );

		if ($args['home_faq_limit_width_switch']) {
            $wrap_styles['width'] = MKB_SettingsBuilder::css_size_to_string($args['home_faq_width_limit']);
		}

		?><div class="<?php self::classnames($wrap_classes); ?>" style="<?php self::styles($wrap_styles); ?>"><?php

		if ($args['home_faq_title']) :
		    $title_styles = array(
                'color' => $args['home_faq_title_color'],
                'font-size' => MKB_SettingsBuilder::css_size_to_string($args['home_faq_title_size'])
            );
			?>
			<div class="mkb-section-title">
				<h3 style="<?php self::styles($title_styles); ?>">
                    <?php echo esc_html($args['home_faq_title']); ?>
                </h3>
			</div>
		<?php
		endif;

        $controls_styles = array(
            'margin-top' => MKB_SettingsBuilder::css_size_to_string($args['home_faq_controls_margin_top']),
            'margin-bottom' => MKB_SettingsBuilder::css_size_to_string($args['home_faq_controls_margin_bottom'])
        );

		?><div class="kb-faq__controls mkb-clearfix" style="<?php self::styles($controls_styles); ?>">
			<?php if($args['home_show_faq_filter']):?>
				<div class="kb-faq__filter kb-faq__filter--empty kb-faq__filter--<?php esc_attr_e($args['faq_filter_theme']); ?>-theme fn-kb-faq-filter">
					<form class="kb-faq__filter-form" action="" novalidate>
						<input type="text" class="fn-kb-faq-filter-input kb-faq__filter-input" placeholder="<?php esc_attr_e($args['faq_filter_placeholder']); ?>" />
						<a href="#" class="fn-kb-faq-filter-clear kb-faq__filter-clear">
							<i class="fa <?php echo esc_attr($args['faq_filter_clear_icon']); ?>"></i>
						</a>
						<?php if ($args['show_faq_filter_icon']): ?>
						<span class="kb-faq__filter-icon">
							<i class="fa <?php echo esc_attr($args['faq_filter_icon']); ?>"></i>
						</span>
						<?php endif; ?>
					</form>
				</div>
			<?php endif; ?>
			<?php if ($args['home_show_faq_toggle_all']): ?>
				<div class="kb-faq__toggle-all">
					<a href="#" class="kb-faq__toggle-all-link fn-kb-faq-toggle-all">
						<span class="kb-faq__toggle-all-label">
							<?php if($args['show_faq_toggle_all_icon']): ?>
							<i class="kb-faq__toggle-all-icon fa <?php esc_attr_e($args['faq_toggle_all_icon']); ?>"></i>
							<?php endif; ?>
							<span class="kb-faq__toggle-all-text">
								<?php echo esc_html($args['faq_toggle_all_open_text']); ?>
							</span>
						</span>
						<span class="kb-faq__toggle-all-label-open">
							<?php if($args['show_faq_toggle_all_icon']): ?>
							<i class="kb-faq__toggle-all-icon fa <?php esc_attr_e($args['faq_toggle_all_icon_open']); ?>"></i>
							<?php endif; ?>
							<span class="kb-faq__toggle-all-text">
								<?php echo esc_html($args['faq_toggle_all_close_text']); ?>
							</span>
						</span>
					</a>
				</div>
			<?php endif; ?>
			</div>
		<?php
			// categories loop
			if ( sizeof( $categories ) ):
				foreach ( $categories as $category ):
					self::render_faq_category($category, $args);
				endforeach; // end of terms loop
			endif; // end of topics loop
			?>
			<div class="fn-kb-faq-no-results mkb-hidden kb-faq__no-results">
				<?php echo esc_html($args['faq_no_results_text']); ?>
			</div>
		</div><?php
	}

	public static function render_faq_category($term, $settings = array()) {

		if (MKB_Options::option('disable_faq')) {
			return;
		}

		if (!$term) {
			return;
		}

		$args = wp_parse_args(
			$settings,
			array(
				'home_show_faq_categories' => MKB_Options::option('home_show_faq_categories'),
				'home_show_faq_category_count' => MKB_Options::option('home_show_faq_category_count'),
				'faq_question_shadow' => MKB_Options::option('faq_question_shadow'),
				'show_faq_question_icon' => MKB_Options::option('show_faq_question_icon'),
				'faq_question_icon' => MKB_Options::option('faq_question_icon'),
				'faq_question_icon_open_action' => MKB_Options::option('faq_question_icon_open_action'),
				'faq_question_open_icon' => MKB_Options::option('faq_question_open_icon'),
			)
		);

		$query_args = array(
			'post_type' => 'mkb_faq',
			'posts_per_page' => -1,
			'ignore_sticky_posts' => 1,
			'tax_query' => array(
				array(
					'taxonomy' => 'mkb_faq_category',
					'field'    => 'slug',
					'terms'    => $term->slug
				),
			)
		);

		if (MKB_Options::option('faq_enable_reorder')) {
			$query_args['meta_query'] =  array(
                'relation' => 'OR',
                array('key' => 'mkb_tax_order_' . $term->term_id, 'compare' => 'EXISTS'),
                array('key' => 'mkb_tax_order_' . $term->term_id, 'compare' => 'NOT EXISTS'),
            );
			$query_args['orderby'] = 'meta_value_num menu_order';
			$query_args['order'] = 'ASC';
		}

		$loop = new WP_Query( $query_args );

		?>
		<div class="kb-faq__category fn-kb-faq-section kb-faq-category-<?php esc_attr_e($term->slug); ?> kb-faq-category-id-<?php esc_attr_e($term->term_id); ?>">
			<div class="kb-faq__category-inner">
				<?php if($args['home_show_faq_categories']): ?>
				<div class="kb-faq__category-title fn-kb-faq-category-title" data-slug="<?php echo esc_attr($term->slug); ?>">
					<?php echo esc_html( self::get_term_name($term) ); ?>
					<?php if($args['home_show_faq_category_count']): ?>
					<span class="kb-faq__count fn-kb-faq-section-count">
						<?php echo esc_html($loop->post_count); ?> <?php echo esc_html($loop->post_count == 1 ?
							MKB_Options::option( 'question_text' ) :
							MKB_Options::option( 'questions_text' )); ?>
					</span>
					<?php endif; ?>
				</div>
				<?php endif; ?>
				<div class="kb-faq__questions">
					<ul class="kb-faq__questions-list<?php if($args['faq_question_shadow']) {
						echo esc_attr(' kb-faq__questions-list--with-shadow');
					} ?>">
						<?php
						if ( $loop->have_posts() ) :
							while ( $loop->have_posts() ) : $loop->the_post();
								?>
								<li class="kb-faq__questions-list-item kb-faq__questions-list-item--<?php
									echo esc_attr($args['faq_question_icon_open_action']); ?> fn-kb-faq-item">
									<a class="fn-kb-faq-link" href="#" data-id="<?php esc_attr_e(get_the_ID()); ?>">
										<span class="kb-faq__question-title fn-kb-faq-question">
											<?php if ($args['show_faq_question_icon']): ?>
												<i class="kb-faq__question-toggle-icon fa <?php
													echo esc_attr($args['faq_question_icon']); ?>"></i>
											<?php endif; ?>
											<?php if ($args['faq_question_icon_open_action'] === 'change'): ?>
												<i class="kb-faq__question-toggle-icon-open fa <?php
													echo esc_attr($args['faq_question_open_icon']); ?>"></i>
											<?php endif; ?>
											<?php echo esc_html(get_the_title()); ?>
										</span>
									</a>
									<div class="kb-faq__answer fn-kb-faq-answer">
										<div class="kb-faq__answer-content"><?php self::render_post_content(isset($settings['is_block_editor'])); ?></div>
									</div>
								</li>
							<?php endwhile;
						endif;
						?>
					</ul>
				</div>
			</div>
		</div>
	<?php
		wp_reset_postdata();
	}

    /**
     * WP block editor has a bug, which does not allow using the_content() in dynamic blocks
     * https://core.trac.wordpress.org/ticket/45495
     * TODO: remove fix once Core bug is fixed
     * @param bool $is_block_editor
     */
	static function render_post_content($is_block_editor = false) {
	    if (!$is_block_editor) {
	        the_content();
	        return;
        }

        $blocks_content_filter_current_priority = has_filter( 'the_content', '_restore_wpautop_hook' );

        if ($blocks_content_filter_current_priority) {
            remove_filter('the_content', '_restore_wpautop_hook', $blocks_content_filter_current_priority);
        }

        the_content();

        if ($blocks_content_filter_current_priority) {
            add_filter( 'the_content', '_restore_wpautop_hook', $blocks_content_filter_current_priority );
        }
    }

	/**
	 * TODO: fix count
	 * @param $term
	 * @param $loop
	 *
	 * @return int
	 */
	protected static function get_term_post_count($term, $loop) {
		if ( self::is_dynamic_topic($term)) {
			return $loop->post_count;
		} else if (MKB_Options::option('restrict_on') && MKB_Options::option('restrict_remove_from_archives')) {
			return $loop->found_posts;
		} else {
			// need to use extra query to include articles in subcategories, no matter what setting is used
			$query = new WP_Query( array( MKB_Options::option( 'article_cpt_category' ) => $term->slug ) );
			return $query->found_posts;
		}
	}

	/**
	 * Gets term name
	 * @param $term
	 *
	 * @return null
	 */
	protected static function get_term_name($term) {
		if ( $term == 'recent' ) {
			return MKB_Options::option( 'recent_topic_label' );
		} else if ( $term == 'updated' ) {
			return MKB_Options::option( 'updated_topic_label' );
		} else if ( $term == 'top_views' ) {
			return MKB_Options::option( 'most_viewed_topic_label' );
		} else if ( $term == 'top_likes' ) {
			return MKB_Options::option( 'most_liked_topic_label' );
		} else {
			return $term->name;
		}
	}

	/**
	 * Gets term description
	 * @param $term
	 *
	 * @return null
	 */
	protected static function get_term_description($term) {
		if ( $term == 'recent' ) {
			return MKB_Options::option( 'recent_topic_description' );
		} else if ( $term == 'updated' ) {
			return MKB_Options::option( 'updated_topic_description' );
		} else if ( $term == 'top_views' ) {
			return MKB_Options::option( 'most_viewed_topic_description' );
		} else if ( $term == 'top_likes' ) {
			return MKB_Options::option( 'most_liked_topic_description' );
		} else {
			return $term->description;
		}
	}

	/**
	 * Gets term items
	 * @param $term
	 * @param $options
	 *
	 * @return WP_Query
	 */
	protected static function get_term_items_loop($term, $options) {
		$query_args = array(
			'post_type' => MKB_Options::option( 'article_cpt' ),
			'posts_per_page' => isset($options['home_topics_articles_limit']) ?
				$options['home_topics_articles_limit'] :
				5,
			'ignore_sticky_posts' => 1
		);

		if ( !self::is_dynamic_topic($term) && isset($term->slug) ) {
			$query_args['tax_query'] = array(
				array(
					'taxonomy' => MKB_Options::option( 'article_cpt_category' ),
					'field'    => 'slug',
					'terms'    => $term->slug,
					'include_children' => (bool) MKB_Options::option('topic_children_include_articles')
				)
			);

			// NOTE: articles DnD order is in cpt.php
		}

		if ($term == 'top_views' || $term == 'top_likes') {
			$query_args['orderby'] = 'meta_value_num';
			$query_args['order'] = 'DESC';
		} else if ($term == 'updated') {
			// recently modified
			$query_args['orderby'] = 'modified';
			$query_args['order'] = 'DESC';
		}

		if ($term == 'top_views') {
			$query_args['meta_key'] = '_mkb_views';
		} else if ($term == 'top_likes') {
			$query_args['meta_key'] = '_mkb_likes';
		}

		/**
		 * Remove restricted articles from query, if required
		 */
		if (MKB_Options::option('restrict_on') && MKB_Options::option('restrict_remove_from_archives')) {
			global $minerva_kb;

			$query_args['post__in'] = $minerva_kb->restrict->get_allowed_article_ids_query();
		}

		$loop = new WP_Query( $query_args );

		if ($loop->post_count === 0 && ($term === 'top_views' || $term === 'top_likes')) {
			// fallback to recent, if no likes and views
			$query_args = array(
				'post_type' => MKB_Options::option( 'article_cpt' ),
				'posts_per_page' => isset($options['home_topics_articles_limit']) ?
					$options['home_topics_articles_limit'] :
					5,
				'ignore_sticky_posts' => 1
			);

			$loop = new WP_Query( $query_args );
		}

		return $loop;
	}

	protected static function get_term_link($term) {
		if (self::is_dynamic_topic($term)) {
			return "#";
		}

		return get_term_link($term);
	}

	/**
	 * Search template
	 * @param array $settings
	 */
	public static function render_search($settings = array()) {
		/**
		 * Do not render search if globally restricted
		 */
		global $minerva_kb;

		if (MKB_Options::option('restrict_on') && MKB_Options::option('restrict_remove_search_for_restricted') && $minerva_kb->restrict->is_user_globally_restricted()) {
			return false;
		}

		$args = wp_parse_args(
			$settings,
			array(
				"search_title" => MKB_Options::option( 'search_title' ),
				"search_title_color" => MKB_Options::option( 'search_title_color' ),
				"search_title_size" => MKB_Options::option( 'search_title_size' ),
				"search_theme" => MKB_Options::option( 'search_theme' ),
				"search_border_color" => MKB_Options::option( 'search_border_color' ),
				"search_min_width" => MKB_Options::option( 'search_min_width' ),
				"search_container_padding_top" => MKB_Options::option( 'search_container_padding_top' ),
				"search_container_padding_bottom" => MKB_Options::option( 'search_container_padding_bottom' ),
				"search_placeholder" => MKB_Options::option( 'search_placeholder' ),
				"search_topics" => MKB_Options::option( 'search_topics' ),
				"search_tip_color" => MKB_Options::option( 'search_tip_color' ),
				"add_pattern_overlay" => MKB_Options::option( 'add_pattern_overlay' ),
				"search_container_image_pattern" => MKB_Options::option( 'search_container_image_pattern' ),
				"search_container_image_pattern_opacity" => MKB_Options::option( 'search_container_image_pattern_opacity' ),
				"add_gradient_overlay" => MKB_Options::option( 'add_gradient_overlay' ),
				"search_container_gradient_from" => MKB_Options::option( 'search_container_gradient_from' ),
				"search_container_gradient_to" => MKB_Options::option( 'search_container_gradient_to' ),
				"search_container_gradient_opacity" => MKB_Options::option( 'search_container_gradient_opacity' ),
				"search_icons_left" => MKB_Options::option( 'search_icons_left' ),
				"show_search_icon" => MKB_Options::option( 'show_search_icon' ),
				"search_icon" => MKB_Options::option( 'search_icon' ),
				"search_clear_icon" => MKB_Options::option( 'search_clear_icon' ),
				"search_clear_icon_tooltip" => MKB_Options::option( 'search_clear_icon_tooltip' ),
				"show_search_tip" => MKB_Options::option( 'show_search_tip' ),
				"disable_autofocus" => MKB_Options::option( 'disable_autofocus' ),
				"search_tip" => MKB_Options::option( 'search_tip' ),
				"search_request_icon" => MKB_Options::option( 'search_request_icon' ),
				"search_container_bg" => MKB_Options::option( 'search_container_bg' ),
				"search_container_image_bg" => MKB_Options::option( 'search_container_image_bg' ),
				"show_topic_in_results" => MKB_Options::option( 'show_topic_in_results' ),
				"search_result_topic_label" => MKB_Options::option( 'search_result_topic_label' ),
				"search_results_topic_bg" => MKB_Options::option( 'search_results_topic_bg' ),
				"search_results_topic_color" => MKB_Options::option( 'search_results_topic_color' ),
				"search_results_multiline" => MKB_Options::option( 'search_results_multiline' ),
				"live_search_show_excerpt" => MKB_Options::option( 'live_search_show_excerpt' ),
				"search_results_topic_use_custom" => MKB_Options::option('search_results_topic_use_custom')
			)
		);

		$container_style = 'background-color: ' . $args['search_container_bg'] . ';';

		if (isset($args['search_container_padding_top']) && $args['search_container_padding_top']) {
			$container_style .= 'padding-top: ' . MKB_SettingsBuilder::css_size_to_string($args['search_container_padding_top']) . ';';
		}

		if (isset($args['search_container_padding_bottom']) && $args['search_container_padding_bottom']) {
			$container_style .= 'padding-bottom: ' . MKB_SettingsBuilder::css_size_to_string($args['search_container_padding_bottom']) . ';';
		}

		if (isset($args['search_container_image_bg']) && $args['search_container_image_bg'] && MKB_SettingsBuilder::media_url($args['search_container_image_bg'])) {
			$container_style .= 'background: url(' . MKB_SettingsBuilder::media_url($args['search_container_image_bg']) . ') center center / cover;';
		}

		/**
		 * Gradient
		 */
		$gradient_style = '';

		if (isset($args["search_container_gradient_from"]) && $args["search_container_gradient_to"]) {
			$gradient_style = 'background: linear-gradient(45deg, ' .
			                  $args["search_container_gradient_from"] . ' 0%, ' .
			                  $args["search_container_gradient_to"] . ' 100%);';
		}

		if (isset($args["add_gradient_overlay"]) && $args["add_gradient_overlay"]) {
			if (isset($args["search_container_gradient_from"]) && $args["search_container_gradient_to"]) {
				$gradient_style = 'background: linear-gradient(45deg, ' .
				                  $args["search_container_gradient_from"] . ' 0%, ' .
				                  $args["search_container_gradient_to"] . ' 100%);';
			}

			if (isset($args["search_container_gradient_opacity"]) && $args["search_container_gradient_opacity"]) {
				$gradient_style .= 'opacity: ' . $args["search_container_gradient_opacity"] . ';';
			}
		} else {
			$gradient_style = 'display: none;';
		}

		/**
		 * Pattern
		 */
		$pattern_style = '';

		if (isset($args['search_container_image_pattern']) && $args['search_container_image_pattern']) {
			$pattern_style .= 'background-image: url(' . MKB_SettingsBuilder::media_url($args['search_container_image_pattern']) . ');';

			if (isset($args["search_container_image_pattern_opacity"]) && $args["search_container_image_pattern_opacity"]) {
				$pattern_style .= 'opacity: ' . $args["search_container_image_pattern_opacity"] . ';';
			}
		}

		$title_style = '';

		if (isset($args["search_title"]) && $args["search_title"]) {
			$title_style = 'font-size: ' . MKB_SettingsBuilder::css_size_to_string($args["search_title_size"]) . ';color: ' . $args["search_title_color"] . ';';
		}

		$tip_style = '';

		if (isset($args["search_tip_color"]) && $args["search_tip_color"]) {
			$tip_style = 'color: ' . $args["search_tip_color"];
		}

		$input_wrap_extra_class = 'mkb-search-theme__' . $args['search_theme'];

		if (isset($args['search_icons_left']) && $args['search_icons_left']) {
			$input_wrap_extra_class .= ' kb-search__input-wrap--icons-left';
		}

		if (isset($args['search_results_multiline']) && $args['search_results_multiline']) {
			$input_wrap_extra_class .= ' kb-search__input-wrap--multiline-results';
		}

		if (isset($args['live_search_show_excerpt']) && $args['live_search_show_excerpt']) {
			$input_wrap_extra_class .= ' kb-search__input-wrap--with-excerpt';
		}

		$search_border_style = '';

		if (isset($args['search_border_color']) && $args['search_border_color']) {
			$search_border_style .= 'border-color: ' . $args['search_border_color'] . ';background-color: ' . $args['search_border_color'] . ';';
		}

		$input_wrap_style = '';

		if (isset($args['search_min_width']) && $args['search_min_width']) {
			$input_wrap_style = 'width: ' . MKB_SettingsBuilder::css_size_to_string($args['search_min_width']) . ';';
		}

		// Custom section CSS (shortcodes/API calls)
		$has_custom_styles = isset($settings['search_results_topic_bg']) || isset($settings['search_results_topic_color']);
		$css_id = '';

		if ($has_custom_styles) {
			$css_id = uniqid('mkb-uuid-');
		}

		do_action('minerva_search_block_before');

		?><div class="kb-header<?php
		if ($has_custom_styles) {
			?> <?php echo esc_attr($css_id); ?><?php
		}?>" style="<?php echo esc_attr($container_style); ?>"><?php

		// start of custom styles
		if ($has_custom_styles) {
		?><style><?php

			if (isset($settings['search_results_topic_bg'])) {
			?>.<?php echo esc_attr( $css_id ); ?> .kb-search .kb-search__result-topic-name { background: <?php echo esc_attr( $settings['search_results_topic_bg'] ); ?>; }<?php
			}

			if (isset($settings['search_results_topic_color'])) {
			?>.<?php echo esc_attr( $css_id ); ?> .kb-search .kb-search__result-topic-name { color: <?php echo esc_attr( $settings['search_results_topic_color'] ); ?>; }<?php
			}

		?></style><?php
		}
		// end of custom styles

		    if (isset($args["add_gradient_overlay"]) && $args["add_gradient_overlay"]): ?>
				<div class="kb-search-gradient" style="<?php echo esc_attr($gradient_style); ?>"></div>
			<?php endif; ?>
			<?php if (isset($args["add_pattern_overlay"]) && $args["add_pattern_overlay"]): ?>
				<div class="kb-search-pattern" style="<?php echo esc_attr($pattern_style); ?>"></div>
			<?php endif; ?>

			<?php do_action('minerva_search_block_inside_before'); ?>

			<div class="kb-search">

				<?php do_action('minerva_search_block_content_before'); ?>

				<?php if (isset($args["search_title"]) && $args["search_title"]): ?>
				<div class="kb-search__title" style="<?php echo esc_attr($title_style); ?>">
					<?php echo esc_html($args["search_title"]); ?>
				</div>
				<?php endif; ?>
				<form class="kb-search__form" action="<?php echo home_url(); ?>" method="get" autocomplete="off" novalidate>

					<?php do_action('minerva_search_block_input_before'); ?>

					<div class="kb-search__input-wrap <?php echo esc_attr($input_wrap_extra_class); ?>"
					     style="<?php echo esc_attr($search_border_style . $input_wrap_style); ?>">
						<input type="hidden" name="source" value="kb" />
						<?php if (defined('ICL_LANGUAGE_CODE') && ICL_LANGUAGE_CODE !== 'all'): ?>
							<input type="hidden" name="lang" value="<?php echo esc_attr(ICL_LANGUAGE_CODE); ?>" />
						<?php endif; ?>
						<?php if (MKB_TemplateHelper::info('current_product')): ?>
							<input type="hidden" name="kb_id" value="<?php esc_attr_e(MKB_TemplateHelper::info('current_product')); ?>" />
						<?php endif; ?>
						<?php if(!empty($args['search_topics'])): ?>
							<input type="hidden" name="topics" value="<?php esc_attr_e($args['search_topics']); ?>" />
						<?php endif; ?>
						<input class="kb-search__input"
						       name="s"
						       placeholder="<?php echo esc_attr( $args['search_placeholder'] ); ?>"
						       type="text"
						       data-show-results-topic="<?php echo esc_attr($args['show_topic_in_results']); ?>"
						       data-topic-label="<?php echo esc_attr($args['search_result_topic_label']); ?>"
						       data-custom-topic-colors="<?php echo esc_attr($args['search_results_topic_use_custom']); ?>"
						       data-autofocus="<?php echo esc_attr($args['disable_autofocus'] ? '0' : '1'); ?>"
							/>
						<span class="kb-search__results-summary">
							<i class="kb-search-request-indicator fa <?php echo esc_attr( $args['search_request_icon'] ); ?> fa-spin fa-fw"></i>
							<span class="kb-summary-text-holder"></span>
						</span>
						<?php if ( $args['show_search_icon'] ): ?>
							<span class="kb-search__icon-holder">
								<i class="kb-search__icon fa <?php echo esc_attr( $args['search_icon'] ); ?>"></i>
							</span>
						<?php endif; ?>
						<a href="#" class="kb-search__clear" title="<?php echo esc_attr($args['search_clear_icon_tooltip']); ?>">
							<i class="kb-search__clear-icon fa <?php echo esc_attr( $args['search_clear_icon'] ); ?>"></i>
						</a>

						<div class="kb-search__results<?php if ($args['show_topic_in_results'] == 1) {
							echo esc_attr(' kb-search__results--with-topics');
						}?>"></div>
					</div>

					<?php do_action('minerva_search_block_input_after'); ?>

					<?php if($args['show_search_tip']): ?>

						<div class="kb-search__tip" style="<?php echo esc_attr($tip_style); ?>">
							<?php echo wp_kses_post( $args['search_tip'] ); ?>
						</div>

						<?php do_action('minerva_search_block_tip_after'); ?>

					<?php endif; ?>
				</form>

				<?php do_action('minerva_search_block_content_after'); ?>

			</div>

		<?php do_action('minerva_search_block_inside_after'); ?>

		</div>
		<?php

		do_action('minerva_search_block_after');
	}

	/**
	 * Renders home page content
	 */
	public static function home_content() {
		global $minerva_kb;

		if (!$minerva_kb->restrict->check_access()) {
			return;
		}

		if ( $minerva_kb->info->is_builder_home() ) {
			include( MINERVA_KB_PLUGIN_DIR . '/lib/templates/kb-builder-page.php' );
		} else if ( $minerva_kb->info->is_settings_home() ) {
			if ( MKB_Options::option( 'show_page_content' ) === 'before' ) {
				the_content();
			}

			include( MINERVA_KB_PLUGIN_DIR . '/lib/templates/kb-home.php' );

			if ( MKB_Options::option( 'show_page_content' ) === 'after' ) {
				the_content();
			}
		}
	}

	/**
	 * Renders article content
	 */
	public static function single_content() {
		global $minerva_kb;

		if ($minerva_kb->restrict->check_access()):
			self::single_header_meta();
		endif;

		?><div class="mkb-article-text mkb-clearfix"><?php

		if ($minerva_kb->restrict->check_access()):
			the_content();

			wp_link_pages( array(
				'before'      => '<div class="mkb-page-links"><span class="mkb-page-links__title">' .
				                 MKB_Options::option('article_pagination_label') .
				                 '</span>',
				'after'       => '</div>',
				'link_before' => '<span class="mkb-page-link">',
				'link_after'  => '</span>'
			) );

		else:
			echo $minerva_kb->restrict->get_message();
		endif;

		?></div><?php

		if ($minerva_kb->restrict->check_access()):
			self::single_footer_meta();
		endif;
	}

	/**
	 * Single header meta
	 */
	public static function single_header_meta() {
		?>
		<div class="mkb-article-header">
			<?php

			do_action('minerva_single_entry_header_meta');

			?>
		</div>
	<?php
	}

	/**
	 * Single footer meta
	 */
	public static function single_footer_meta() {
		?>
		<div class="mkb-article-extra">
			<div class="mkb-article-extra__hidden">
				<span class="mkb-article-extra__tracking-data"
				      data-article-id="<?php echo esc_attr( get_the_ID() ); ?>"
				      data-article-title="<?php echo esc_attr( get_the_title() ); ?>"></span>
			</div>
			<?php

			do_action('minerva_single_entry_footer_meta');

			?>
		</div>
	<?php
	}

	/**
	 * Default WP pagination
	 */
	public static function theme_pagination() {
		the_posts_pagination( array(
			'prev_text' => MKB_Options::option('pagination_prev_text'),
			'next_text' => MKB_Options::option('pagination_next_text'),
		) );
	}

	/**
	 * Custom pagination
	 */
	public static function minerva_pagination() {

		// make sure we're not in single
		if (is_singular()) {
			return;
		}

		global $wp_query;

		$max = (int) $wp_query->max_num_pages;

		// make sure we have pages
		if ($max <= 1) {
			return;
		}

		$paged = get_query_var('paged') ? (int) get_query_var('paged') : 1;

		// Add current page to the array
		if ($paged >= 1) {
			$links[] = $paged;
		}

		// Add the pages around the current page to the array
		if ($paged >= 3) {
			$links[] = $paged - 1;
			$links[] = $paged - 2;
		}

		if ($paged + 2 <= $max) {
			$links[] = $paged + 2;
			$links[] = $paged + 1;
		}

		?><div class="mkb-pagination"><ul><?php

		//	Previous Post Link
		if ( get_previous_posts_link() ) {
			printf('<li>%s</li>', get_previous_posts_link(MKB_Options::option('pagination_prev_text')));
		}

		//	Link to first page, plus ellipses if necessary
		if (!in_array(1, $links)) {
			if (1 == $paged) {
				?><li class="active"><span>1</span></li><?php
			} else {
				printf(
					'<li><a href="%s">%s</a></li>',
					esc_url(get_pagenum_link(1)),
					'1'
				);
			}

			if (!in_array(2, $links)) {
				?><li>…</li><?php
			}
		}

		sort($links);

		foreach ((array)$links as $link) {
			if ($paged == $link) {
				?><li class="active"><span><?php echo esc_html($link); ?></span></li><?php
			} else {
				printf(
					'<li><a href="%s">%s</a></li>',
					esc_url(get_pagenum_link($link)),
					$link
				);
			}
		}

		//	Link to last page, plus ellipses if necessary
		if (!in_array($max, $links)) {
			if (!in_array($max-1, $links)) {
				?><li>…</li><?php
			}

			if ($paged == $max) {
				?><li class="active"><span><?php echo esc_html($max); ?></span></li><?php
			} else {
				printf(
					'<li><a href="%s">%s</a></li>',
					esc_url(get_pagenum_link($max)),
					$max
				);
			}
		}

		//	Next Post Link
		if (get_next_posts_link()) {
			printf(
				'<li>%s</li>',
				get_next_posts_link(MKB_Options::option('pagination_next_text'))
			);
		}

		?></ul></div><?php
	}

	/**
	 * Pagination for search results page
	 */
	public static function pagination () {
		if (MKB_Options::option('pagination_style') === 'plugin') {
			self::minerva_pagination();
		} else {
			self::theme_pagination();
		}
	}

	/**
	 * Article table of contents
	 */
	public static function table_of_contents() {
		$toc = new MinervaKB_TableOfContents();
		$toc->render();
	}

	public static function render_guestpost() {
        global $minerva_kb;

        ?>
        <div class="js-mkb-client-submission mkb-client-submission">
            <div class="mkb-client-submission__heading"><?php esc_html_e(MKB_Options::option('submit_form_heading_label')); ?></div>
            <div class="mkb-client-submission__subheading"><?php esc_html_e(MKB_Options::option('submit_form_subheading_label')); ?></div>

            <?php

            do_action('minerva_guestpost_form_subheading_after');

            ?>

            <?php

            if (MKB_Options::option('submit_disable')) {
                if (MKB_Options::option('submit_disable_message')) {
                    ?><p><?php esc_html_e(MKB_Options::option('submit_disable_message')); ?></p><?php
                }
            } else if (MKB_Options::option('submit_restrict_enable') &&
                !$minerva_kb->restrict->check_current_user_against_option('submit_restrict_role')) {

                if (MKB_Options::option('submit_restriction_failed_message')) {
                    ?><p><?php esc_html_e(MKB_Options::option('submit_restriction_failed_message')); ?></p><?php
                }
            } else {

                // all is well, render form
                self::render_guestpost_form();
            }

            ?>
        </div>
        <?php
    }

    public static function render_guestpost_form() {
        ?>
        <div class="mkb-form-messages js-mkb-form-messages mkb-hidden"></div>

        <form class="mkb-client-submission-form js-mkb-client-submission-form" action="" novalidate>
            <div class="mkb-submission-title-wrap">
                <div class="mkb-form-input-label">
                    <?php esc_html_e(MKB_Options::option('submit_article_title_label')); ?>
                </div>
                <input type="text" name="mkb-submission-title" class="js-mkb-submission-title" />
            </div>
            <?php

            do_action('minerva_guestpost_form_title_after');

            ?>
            <br/>
            <div class="mkb-submission-content-wrap">
                <div class="mkb-form-input-label">
                    <?php esc_html_e(MKB_Options::option('submit_content_label')); ?>
                </div>
                <div id="mkb-client-editor">
                    <p><?php esc_html_e(MKB_Options::option('submit_content_default_text')); ?></p>
                </div>
            </div>
            <?php

            do_action('minerva_guestpost_form_content_after');

            ?>
            <br/>
            <?php if (MKB_Options::option('submit_allow_topics_select')):?>
                <div class="mkb-submission-topic-wrap">
                    <div class="mkb-form-input-label">
                        <?php esc_html_e(MKB_Options::option('submit_topic_select_label')); ?>
                    </div>
                    <?php

                    $topics_args = array(
                        'taxonomy'     => MKB_Options::option('article_cpt_category'),
                        'orderby'      => 'name',
                        'show_count'   => false,
                        'pad_counts'   => false,
                        'hierarchical' => true,
                        'name'         => 'mkb-submission-topic',
                        'class'        => 'js-mkb-submission-topic'
                    );

                    wp_dropdown_categories( $topics_args );
                    ?>
                </div>
                <?php

                do_action('minerva_guestpost_form_topics_after');

                ?>
            <?php endif; ?>
            <br/>
            <?php if (MKB_Options::option('antispam_quiz_enable')): ?>
                <p><?php esc_html_e(MKB_Options::option('antispam_quiz_question')); ?> <input name="mkb-submission-answer" class="js-mkb-real-human-answer mkb-real-human-answer" type="text" /></p>
                <?php

                do_action('minerva_guestpost_form_antispam_after');

                ?>
            <?php endif; ?>
            <div>
				<span class="js-mkb-client-submission-send mkb-client-submission-send">
					<?php esc_html_e(MKB_Options::option('submit_send_button_label')); ?>
				</span>
                <?php

                do_action('minerva_guestpost_form_submit_after');

                ?>
            </div>
        </form>
        <?php

        if (MKB_Options::option('submit_content_editor_skin') === 'snow') {
            wp_enqueue_style( 'minerva-kb/client-editor-snow-css', MINERVA_KB_PLUGIN_URL . 'assets/css/vendor/quill/quill.snow.css', false, '1.3.6' );
        } else {
            wp_enqueue_style( 'minerva-kb/client-editor-bubble-css', MINERVA_KB_PLUGIN_URL . 'assets/css/vendor/quill/quill.bubble.css', false, '1.3.6' );
        }

        wp_enqueue_script( 'minerva-kb/client-editor-js', MINERVA_KB_PLUGIN_URL . 'assets/js/vendor/quill/quill.min.js', array(), '1.3.6', true );
    }

    /**
     * Template part: topic title & description
     */
    public static function topic_tmpl_title_description($ignore_global_settings = false) {
        $term = get_queried_object();

        do_action('minerva_category_title_before');

        ?><div class="mkb-page-header"><?php

        do_action('minerva_category_title_inside_before');

        if ((MKB_Options::option('show_topic_title') || $ignore_global_settings) && !MinervaKB::topic_option($term, 'topic_no_title_switch')) {
            if (MKB_Options::option('topic_customize_title')) {
                ?><h1 class="mkb-page-title"><?php
                single_term_title(MKB_Options::option('topic_custom_title_prefix'));
                ?></h1><?php
            } else {
                the_archive_title('<h1 class="mkb-page-title">', '</h1>');
            }
        }

        if (MKB_Options::option('show_topic_description') && !MinervaKB::topic_option($term, 'topic_no_description_switch')) {
            the_archive_description('<div class="mkb-taxonomy-description">', '</div>');
        }

        do_action('minerva_category_title_inside_after');

        ?></div><?php

        do_action('minerva_category_title_after');
    }

    /**
     * Template part: topic search
     */
    public static function topic_tmpl_search() {
        self::render_search(array(
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

    /**
     * Template part: topic breadcrumbs
     */
    public static function topic_tmpl_breadcrumbs() {
        $term = get_queried_object();

        self::breadcrumbs( $term, MKB_Options::option( 'article_cpt_category' ) );
    }

    /**
     * Template part: topic children
     */
    public static function topic_tmpl_children() {
        $term = get_term_by( 'id', get_queried_object_id(), MKB_Options::option( 'article_cpt_category' ) );

        $children = $terms = get_terms( MKB_Options::option( 'article_cpt_category' ), array(
            'taxonomy'   => MKB_Options::option( 'article_cpt_category' ),
            'hide_empty' => true,
            'parent'     => $term->term_id
        ) );

        $children_columns = MKB_TemplateHelper::get_topic_children_columns();
        $view_mode = MKB_Options::option('topic_children_view');
        $row_open = false;

        if ( ! empty( $children ) ):

            @uasort($children, function($a, $b) {
                $orderA = (int)MKB_TemplateHelper::get_topic_option($a, 'topic_order');
                $orderB = (int)MKB_TemplateHelper::get_topic_option($b, 'topic_order');

                if ($orderA == $orderB) {
                    return 0;
                }

                return ($orderA < $orderB) ? -1 : 1;
            });

            ?>
            <div class="mkb-topic__children mkb-columns mkb-columns-<?php echo esc_attr($children_columns); ?>">
                <?php

                $i = 0;

                foreach ( $children as $topic ):

                    // skip all restricted topics
                    if (MKB_Options::option('restrict_on') &&
                        MKB_Options::option('restrict_remove_from_archives') &&
                        isset($topic->term_id) && !MinervaKB_App::instance()->restrict->is_topic_allowed($topic)) {

                        continue;
                    }

                    if ($i % $children_columns === 0):
                        echo '<div class="mkb-row">';
                        $row_open = true;
                    endif;

                    if ($view_mode === 'list'):
                        MKB_TemplateHelper::render_as_list($topic);
                    else:
                        MKB_TemplateHelper::render_as_box($topic);
                    endif;

                    if ( ($i + 1) % $children_columns === 0 ):
                        echo '</div >';
                        $row_open = false;
                    endif;

                    ++$i;
                endforeach;

                if ( $row_open ):
                    echo '</div >';
                    $row_open = false;
                endif;
                ?>
            </div>
        <?php
        endif;
    }

    /**
     * Template part: topic loop
     */
    public static function topic_tmpl_loop() {
        $topic_view = 'content';

        if (MKB_Options::option('topic_template_view') === 'detailed') {
            $topic_view = 'content-detailed';
        }

        ?>
        <div class="mkb-article-list-container article-list-layout-<?php esc_attr_e(MKB_Options::option('topic_list_layout')); ?>">
            <?php
            while (have_posts()) : the_post();
                include(MINERVA_KB_PLUGIN_DIR . 'lib/templates/' . $topic_view . '.php');
            endwhile;
            ?>
        </div>
        <?php
    }

    /**
     * Template part: topic pagination
     */
    public static function topic_tmpl_pagination() {
        self::pagination(); // alias
    }

    /**
     * Login / Register form
     */
    public static function render_login_register_form() {
        $form_mode = MKB_Options::option('tickets_login_register_form_mode');

        if ($form_mode === 'none'):
            echo do_shortcode(MKB_Options::option('tickets_no_login_register_message'));
            return;
        elseif ($form_mode === 'login_register' && MKB_Options::option('tickets_allow_users_registration')):
            ?><div class="mkb-login-and-register-form mkb-form-columns mkb-form-columns__2col">
            <div class="mkb-form-column">
                <?php self::render_login_form(); ?>
            </div>
            <div class="mkb-form-column">
                <?php self::render_register_form(); ?>
            </div>
            </div><?php
        elseif ($form_mode === 'login' || $form_mode === 'login_register' && !MKB_Options::option('tickets_allow_users_registration')):
            ?><div class="mkb-login-and-register-form"><?php self::render_login_form(true); ?></div><?php
        elseif ($form_mode === 'register'):
            ?><div class="mkb-login-and-register-form"><?php self::render_register_form(true); ?></div><?php
        endif;
    }

    /**
     * Register form
     */
    public static function render_register_form($columns = false) {
        if (MKB_Options::option('tickets_register_form_title')): ?>
            <h2><?php esc_html_e(MKB_Options::option('tickets_register_form_title')); ?></h2><?php
        endif; ?>

        <?php

        if (!MKB_Options::option('tickets_allow_users_registration')) {
            echo do_shortcode('[mkb-info]Registration is temporarily disabled[/mkb-info]');
            return;
        }

        MKB_FormsBuilder::render_form(MKB_FormsBuilder::get_form_config('registerForm'));
    }

    /**
     * Support account login
     */
    public static function render_login_form($is_inline = false) {

        if (MKB_Options::option('tickets_login_form_title')): ?>
            <h2><?php esc_html_e(MKB_Options::option('tickets_login_form_title')); ?></h2><?php
        endif;

        MKB_FormsBuilder::render_form(MKB_FormsBuilder::get_form_config('loginForm'));
    }

    /**
     * Guest & User ticket create form
     */
    public static function render_ticket_create_form() {
        wp_enqueue_style( 'minerva-kb/client-editor-snow-css', MINERVA_KB_PLUGIN_URL . 'assets/css/vendor/quill/quill.snow.css', false, '1.3.6' );
        wp_enqueue_script( 'minerva-kb/client-editor-js', MINERVA_KB_PLUGIN_URL . 'assets/js/vendor/quill/quill.min.js', array(), '1.3.6', true );

        $is_guest = !is_user_logged_in();

        MKB_FormsBuilder::render_form(
            MKB_FormsBuilder::get_form_config($is_guest ? 'guestTicketForm' : 'userTicketForm')
        );
    }


    /**
     * Visual editor attachments
     * @param string $field_id
     * @param string $field_name
     */
    public static function render_ticket_editor_attachments($field_id = 'mkb_generic_file_upload', $field_name = 'mkb_generic_file_upload') {
        ?>
        <div class="js-mkb-editor-attachments-section mkb-ticket-attachments-section">
            <?php

            $upload_limits = MKB_Users::instance()->get_current_user_ticket_file_limits();

            $is_demo_site = defined('MINERVA_DEMO_MODE');
            $demo_prefix = '';

            if ($is_demo_site) {
                $upload_limits['max_files'] = 1;
                $upload_limits['max_file_size'] = 1;

                $demo_prefix = '<strong>Demo mode enabled.</strong> ';
            }

            $max_files_text = sprintf($demo_prefix . __('Maximum <strong>%s</strong> file(s), up to <strong>%sMb</strong> each.', 'minerva-kb'),
                $upload_limits['max_files'], $upload_limits['max_file_size']
            );

            $allowed_types_text = sprintf(__('Allowed file types: %s', 'minerva-kb'),
                implode(', ', MKB_Users::instance()->get_current_user_allowed_ticket_filetypes())
            );

            $allowed_files_message = $max_files_text . ' ' . $allowed_types_text;

            if (is_admin()) {
                MKB_TemplateHelper::render_admin_alert($allowed_files_message, 'info');
            } else {
                echo do_shortcode('[mkb-info]' . $allowed_files_message . '[/mkb-info]');
            }

            ?>
            <div class="js-mkb-editor-attachments-drop-area mkb-file-upload-drop-area">
                <i class="mkb-ticket-attachments-bg-icon fa fa-cloud-upload fa-3x"></i>

                <p>
                    <?php _e('Drag n drop files to this area or press button to open file dialog', 'minerva-kb'); ?>
                </p>

                <div class="js-mkb-file-upload-drop-errors mkb-ticket-attachments-drop-errors"></div>
                <?php

                $allowed_filetypes = MKB_Users::instance()->get_current_user_allowed_ticket_filetypes();
                $allowed_filetypes = array_map(function($type) {
                    return '.' . $type;
                }, $allowed_filetypes);
                $allowed_filetypes = implode(',', $allowed_filetypes);

                ?>
                <input type="file"
                       id="<?php echo esc_attr($field_id); ?>"
                       name="<?php echo esc_attr($field_name); ?>[]"
                       class="js-mkb-file-upload-store mkb-file-upload-btn"
                       multiple
                       accept="<?php esc_attr_e($allowed_filetypes); ?>"
                       data-max-files="<?php esc_attr_e($upload_limits['max_files']); ?>"
                       data-max-file-size="<?php esc_attr_e($upload_limits['max_file_size']); ?>"
                >
                <label class="mkb-user-upload-btn mkb-button mkb-button--small" for="<?php echo esc_attr($field_id); ?>"><?php _e('Upload file(s)', 'minerva-kb'); ?></label>

                <a href="#" class="js-mkb-file-upload-clear mkb-ticket-attachments-clear"><?php _e('Remove files', 'minerva-kb'); ?></a>
                <br>
                <br>
                <div class="js-mkb-file-upload-preview mkb-file-upload-preview"></div>
            </div>
        </div>
        <?php
    }

    /**
     * Ticket reply form
     */
    public static function render_ticket_reply_form() {
        $ticket_id = get_the_ID();

        if (!MKB_Tickets::is_ticket_author_view($ticket_id)) {
            return;
        }

        $need_access_token = get_post_meta($ticket_id, '_mkb_guest_ticket_access_token', true);
        $request_access_token = isset($_GET['ticket_access_token']) ? $_GET['ticket_access_token'] : '';

        $status = MKB_Tickets::get_ticket_status($ticket_id);

        if ($status['id'] === 'closed') {
            echo do_shortcode(MKB_Options::option('ticket_cannot_reply_to_closed_ticket_text'));

            if (MKB_Options::option('tickets_allow_user_reopen')):
                self::render_reopen_ticket_form();
            endif;

            return;
        }

        ?>

        <div class="mkb-ticket-reply-form-container">
            <h3><?php echo esc_html(MKB_Options::option('ticket_add_reply_label')); ?></h3>

            <form class="js-mkb-reply-to-ticket mkb-reply-to-ticket-form mkb-form">

                <div class="js-mkb-form-messages mkb-form-messages"></div>

                <input name="ticket_id" type="hidden" value="<?php echo esc_attr($ticket_id); ?>">

                <?php if ($need_access_token): ?>
                    <input name="ticket_access_token" type="hidden" value="<?php echo esc_attr($request_access_token); ?>">
                <?php endif; ?>

                <p>
                    <span class="js-mkb-ticket-reply-content mkb-editor-container mkb-ticket-reply-content"></span>
                </p>

                <?php

                self::render_ticket_editor_attachments(
                    'mkb_ticket_reply_file_upload',
                    'mkb_ticket_reply_files'
                );

                ?>
                <input type="submit"
                       class="js-mkb-form-submit"
                       data-progress-label="<?php echo esc_attr(MKB_Options::option('ticket_adding_reply_button_label')); ?>"
                       value="<?php echo esc_attr(MKB_Options::option('ticket_add_reply_button_label')); ?>" />

                <?php if (MKB_Options::option('tickets_allow_user_close')): ?>
                    <label class="mkb-close-ticket-checkbox-label" for="mkb-ticket-reply-close">
                        <input id="mkb-ticket-reply-close" name="close_ticket" type="checkbox"><?php echo esc_html(MKB_Options::option('ticket_add_reply_close_label')); ?>
                    </label>
                <?php endif; ?>
            </form>
        </div><!--.mkb-ticket-reply-form-container-->

        <?php

        self::render_ticket_reply_credentials($ticket_id, $need_access_token, $request_access_token);

        wp_enqueue_style( 'minerva-kb/client-editor-snow-css', MINERVA_KB_PLUGIN_URL . 'assets/css/vendor/quill/quill.snow.css', false, '1.3.6' );
        wp_enqueue_script( 'minerva-kb/client-editor-js', MINERVA_KB_PLUGIN_URL . 'assets/js/vendor/quill/quill.min.js', array(), '1.3.6', true );
    }

    /**
     * Ticket credentials form
     * @param $ticket_id
     * @param $need_access_token
     * @param $request_access_token
     */
    public static function render_ticket_reply_credentials($ticket_id, $need_access_token, $request_access_token) {
        ?>
        <div class="mkb-ticket-credentials-container">
            <h3>Ticket Credentials</h3>

            <?php

            $credentials = MKB_Tickets::get_ticket_credentials($ticket_id);

            ?>

            <form action="" class="js-mkb-provide-ticket-credentials mkb-provide-ticket-credentials-form mkb-form">

                <div class="js-mkb-form-messages mkb-form-messages"></div>

                <input name="ticket_id" type="hidden" value="<?php esc_attr_e($ticket_id); ?>">

                <?php if ($need_access_token): ?>
                    <input name="ticket_access_token" type="hidden" value="<?php esc_attr_e( $request_access_token ); ?>">
                <?php endif; ?>

                <p>
                    <label for="ticket_credentials"><?php _e('You may provide credentials (for ex. admin or hosting access) if necessary', 'minerva-kb'); ?></label>
                    <textarea name="ticket_credentials" id="ticket_credentials" cols="20" rows="5"><?php echo wp_kses_post($credentials); ?></textarea>
                </p>

                <a href="#" class="js-mkb-credentials-show mkb-credentials-show mkb-button"><?php _e('Show', 'minerva-kb'); ?></a>

                <a href="#" class="js-mkb-credentials-hide mkb-credentials-hide mkb-button"><?php _e('Hide', 'minerva-kb'); ?></a>

                <input type="submit"
                       class="js-mkb-form-submit"
                       data-progress-label="<?php esc_attr_e('Saving Credentials...', 'minerva-kb'); ?>"
                       value="<?php _e('Provide Credentials', 'minerva-kb'); ?>">

                <a href="#" class="js-mkb-delete-ticket-credentials<?php if (!$credentials): ?> mkb-hidden<?php endif; ?> mkb-delete-ticket-credentials mkb-button mkb-button--danger"><?php _e('Delete Credentials', 'minerva-kb'); ?></a>

                <div class="mkb-ticket-credentials-bottom-message"><?php _e('Credentials will be deleted automatically when ticket is closed. You can also delete them at any time.', 'minerva-kb'); ?></div>
            </form>
        </div>

        <?php
    }

    /**
     * Reopen ticket form
     */
    public static function render_reopen_ticket_form() {
        $ticket_id = get_the_ID();
        $need_access_token = get_post_meta($ticket_id, '_mkb_guest_ticket_access_token', true);
        $request_access_token = isset($_GET['ticket_access_token']) ? $_GET['ticket_access_token'] : '';

        ?><form class="js-mkb-reopen-ticket-form mkb-reopen-ticket-form mkb-form">

        <div class="js-mkb-form-messages mkb-form-messages"></div>

        <input name="ticket_id" type="hidden" value="<?php esc_attr_e(get_the_ID()); ?>">

        <?php if ($need_access_token): ?>
            <input name="ticket_access_token" type="hidden" value="<?php esc_attr_e( $request_access_token ); ?>">
        <?php endif; ?>

        <input type="submit"
               data-progress-label="<?php echo esc_attr(MKB_Options::option('ticket_reopening_ticket_text')); ?>"
               value="<?php echo esc_attr(MKB_Options::option('ticket_reopen_ticket_text')); ?>" />
        </form><?php
    }

    /**
     * Render ticket replies
     */
    public static function render_ticket_replies() {

        global $minerva_kb;

        $ticket_id = get_the_ID();

        $guest_user = MKB_Users::get_guest_support_user();
        $author_id = get_the_author_meta('ID');

        $is_guest_ticket = MKB_Tickets::is_guest_ticket($ticket_id);

        $query_args = array(
            'post_type' => 'mkb_ticket_reply',
            'posts_per_page' => -1,
            'ignore_sticky_posts' => 1,
            'post_parent' => $ticket_id
        );

        $loop = new WP_Query( $query_args );

        ?>
        <div class="mkb-ticket-discussion js-mkb-ticket-discussion">

            <h3><?php esc_html_e(MKB_Options::option('ticket_discussion_label')); ?> <span class="mkb-ticket-discussion__top-count"> - <?php
                    esc_html_e($loop->found_posts . ' ');
                    esc_html_e($loop->found_posts === 1 ?
                        MKB_Options::option('ticket_page_reply_text') :
                        MKB_Options::option('ticket_page_replies_text')); ?></span></h3>
            <?php

            if ($loop->have_posts()):
                while ($loop->have_posts()): $loop->the_post();
                    $reply_id = get_the_ID();
                    $reply_author_id = get_the_author_meta('ID');

                    $is_guest_reply = $guest_user && (int)$guest_user->ID === (int)$reply_author_id;

                    $role = 'agent';

                    $reply_side_meta = get_post_meta($reply_id, '_mkb_ticket_reply_side', true);

                    if ($reply_side_meta) {
                        $role = $reply_side_meta === 'admin' ? 'agent' : 'client';
                    } else {
                        // we consider client === ticket author here
                        if ((int)$reply_author_id === (int)$author_id) { // author reply
                            $role = 'client';
                        }
                    }

                    ?>
                    <div class="mkb-ticket-reply mkb-ticket-reply--role-<?php esc_attr_e($role); ?>">

                        <div class="mkb-ticket-reply__author_info">
                            <div class="mkb-ticket-reply__avatar">
                                <?php

                                $default_avatar_url = MKB_SettingsBuilder::media_url(MKB_Options::option(
                                    $role === 'client' ? 'tickets_default_client_avatar' : 'tickets_default_agent_avatar'
                                ));
                                $is_guest_user = $is_guest_ticket && $role === 'client';
                                $avatar_alt = ($is_guest_user ?
                                        __('Guest', 'minerva-kb') :
                                        get_the_author_meta('nickname')) . ' avatar';
                                $custom_avatar_url = MKB_SettingsBuilder::media_url(get_user_option('mkb_option_custom_avatar', $author_id));

                                if (!$custom_avatar_url) {
                                    // TODO: check for users
                                    $custom_avatar_url = $default_avatar_url;
                                }

                                // TODO: user avatars
                                $avatar_type = $role !== 'client' ?
                                    MKB_Options::option('tickets_agent_avatar') :
                                    'gravatar';

                                if ($is_guest_user || $role === 'client') {
                                    $avatar_type = 'custom';
                                }

                                if ($avatar_type === 'gravatar' || !$custom_avatar_url):
                                    echo get_avatar(
                                        get_the_author_meta('email'),
                                        96,
                                        $default_avatar_url,
                                        $avatar_alt
                                    );

                                else: // custom image

                                    ?><img src="<?php esc_attr_e($custom_avatar_url); ?>" alt="<?php esc_attr_e($avatar_alt) ?>"/><?php

                                endif;

                                ?>
                            </div>
                            <div class="mkb-ticket-reply__name">
                                <?php

                                $name = '';

                                if ($is_guest_reply) {
                                    $opener_firstname = get_post_meta($ticket_id, '_mkb_guest_ticket_firstname', true);
                                    $opener_lastname = get_post_meta($ticket_id, '_mkb_guest_ticket_lastname', true);
                                    // TODO: for guest only
                                    $name = $opener_firstname . ' ' . $opener_lastname;
                                } else {
                                    $name = get_the_author_meta('display_name');
                                }

                                ?><?php esc_html_e($name); ?></div>
                            <div class="mkb-ticket-reply__role">
                                <?php esc_html_e(MKB_Options::option($role === 'agent' ? 'ticket_agent_text' : 'ticket_customer_text')); ?>
                            </div>
                            <div class="mkb-ticket-reply__author-stats">
                                <?php

                                $total_reply_count = $is_guest_ticket && $role === 'client' ?
                                    $minerva_kb->info->get_user_replies_count_for_ticket($reply_author_id, $ticket_id) :
                                    $minerva_kb->info->get_user_replies_count($reply_author_id);

                                esc_html_e($total_reply_count . ' ' .
                                    ($total_reply_count === 1 ?
                                        MKB_Options::option('ticket_page_reply_text') :
                                        MKB_Options::option('ticket_page_replies_text'))
                                ); ?>
                            </div>
                        </div>

                        <div class="mkb-ticket-reply__content">
                            <div class="mkb-ticket-reply__content-text">
                                <?php the_content(); ?>
                            </div>

                            <?php
                            $attachments = get_posts( array(
                                'post_type' => 'attachment',
                                'posts_per_page' => -1,
                                'post_parent' => $reply_id,
                                'exclude' => get_post_thumbnail_id($reply_id)
                            ));

                            if ($attachments): ?>
                                <div class="mkb-ticket-reply__attachments-container">
                                    <div class="mkb-ticket-reply__attachments-title">
                                        <?php esc_html_e(MKB_Options::option('ticket_attach_label')); ?>
                                    </div>

                                    <div class="mkb-ticket-reply__attachments mkb-attachments">
                                        <?php foreach($attachments as $attachment):

                                            $url = wp_get_attachment_url($attachment->ID);
                                            $filename = basename($url);
                                            $item_data = wp_prepare_attachment_for_js($attachment->ID);
                                            $icon_config = MinervaKB_ArticleEdit::get_attachment_icon_config($item_data);
                                            $is_show_icon = !MKB_Options::option('attach_icons_off');
                                            $is_show_size = MKB_Options::option('show_attach_size');

                                            ?>
                                            <div class="mkb-attachment-item">
                                                <a href="<?php esc_attr_e($url); ?>"
                                                   target="_blank"
                                                   title="<?php esc_attr_e(__( 'Download', 'minerva-kb' )); ?> <?php esc_attr_e($filename); ?>">
                                                    <?php if ($is_show_icon): ?>
                                                        <i class="mkb-attachment-icon fa <?php esc_attr_e($icon_config['icon']); ?>" style="color:<?php esc_attr_e($icon_config['color']); ?>;"></i>
                                                    <?php endif; ?>
                                                    <span class="mkb-attachment-label"><?php esc_html_e($filename); ?>
                                                        <?php if ($is_show_size): ?>
                                                            <span class="mkb-attachment-size">(<?php esc_attr_e($item_data['filesizeHumanReadable']); ?>)</span>
                                                        <?php endif; ?>
                                                    </span>
                                                </a>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div><!--.mkb-ticket-reply__attachments-container-->
                            <?php endif; ?>

                            <div class="mkb-ticket-reply__meta">
                                <span class="mkb-ticket-reply__date"><?php esc_html_e(MKB_Options::option('ticket_reply_added_text')); ?>&nbsp
                                    <?php
                                    $reply_timestamp = get_post_time('U', false, $reply_id);
                                    $reply_timestamp_gmt = get_post_time('U', true, $reply_id);
                                    MKB_Utils::render_human_date($reply_timestamp_gmt, $reply_timestamp, true);
                                    ?>
                                </span>
                            </div><!--.mkb-ticket-reply__meta-->
                        </div><!--.mkb-ticket-reply__content-->
                    </div><!--.mkb-ticket-reply-->
                <?php
                endwhile;
            else:
                ?><p><?php echo esc_html(MKB_Options::option('ticket_discussion_no_replies_text')); ?></p><?php
            endif;

            wp_reset_postdata();

        ?>
        </div><!--.mkb-ticket-discussion-->
    <?php
    }

    /**
     * Render ticket meta
     */
    public static function render_ticket_meta() {
        $ticket_id = get_the_ID();

        $type = wp_get_post_terms( $ticket_id, array( 'mkb_ticket_type' ) );

        ?><div><?php esc_html_e(MKB_Options::option('ticket_type_label')); ?><?php

        if ($type && isset($type[0])) {
            ?><span class="mkb-ticket-type mkb-ticket-type--<?php esc_attr_e($type[0]->slug); ?>">
                <?php esc_html_e($type[0]->name); ?>
            </span><?php
        } else {
            ?>&nbsp;<?php esc_html_e('Not set', 'minerva-kb');
        }

        ?></div><?php
    }

    /**
     * Ticket template attachments
     */
    public static function render_ticket_attachments() {
        $ticket_id = get_the_ID();

        $attachments = get_posts( array(
            'post_type' => 'attachment',
            'posts_per_page' => -1,
            'post_parent' => $ticket_id,
            'exclude' => get_post_thumbnail_id($ticket_id)
        ));

        if ($attachments): ?>
            <div class="mkb-ticket-reply__attachments-container">
                <div class="mkb-ticket-reply__attachments-title"><?php esc_html_e(MKB_Options::option('ticket_attach_label')); ?></div>

                <div class="mkb-ticket-reply__attachments mkb-attachments">
                    <?php foreach($attachments as $attachment):

                        $url = wp_get_attachment_url($attachment->ID);
                        $filename = basename($url);
                        $item_data = wp_prepare_attachment_for_js($attachment->ID);
                        $icon_config = MinervaKB_ArticleEdit::get_attachment_icon_config($item_data);
                        $is_show_icon = !MKB_Options::option('attach_icons_off');
                        $is_show_size = MKB_Options::option('show_attach_size');

                        ?>
                        <div class="mkb-attachment-item">
                            <a href="<?php esc_attr_e($url); ?>"
                               target="_blank"
                               title="<?php esc_attr_e(__( 'Download', 'minerva-kb' )); ?> <?php esc_attr_e($filename); ?>">
                                <?php if ($is_show_icon): ?>
                                    <i class="mkb-attachment-icon fa <?php esc_attr_e($icon_config['icon']); ?>" style="color:<?php esc_attr_e($icon_config['color']); ?>;"></i>
                                <?php endif; ?>
                                <span class="mkb-attachment-label"><?php esc_html_e($filename); ?>
                                    <?php if ($is_show_size): ?>
                                        <span class="mkb-attachment-size">(<?php esc_attr_e($item_data['filesizeHumanReadable']); ?>)</span>
                                    <?php endif; ?>
                                </span>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif;
    }

    /**
     * Adds referrer params to create ticket link
     * @param $link
     */
    public static function add_ticket_referrer_params($link) {
        /**
         * Possible referrers:
         * - page (id)
         * - article (id)
         * - topic (id)
         * - post (id)
         * - search (term)
         * - custom tracking IDs (for sc in multiple site sections. location param)
         * - menu item (generate url for menu with get parameters)
         */
        $referrer_type = null;
        $referrer_meta = null;

        global $minerva_kb;

        if ($minerva_kb->info->is_page()) {
            $referrer_type = 'page';
            $referrer_meta = get_the_ID();
        } else if ($minerva_kb->info->is_post()) {
            $referrer_type = 'post';
            $referrer_meta = get_the_ID();
        } else if ($minerva_kb->info->is_blog()) {
            $referrer_type = 'blog';
            // TODO: $referrer_meta = ?
        } else if ($minerva_kb->info->is_article()) {
            $referrer_type = 'article';
            $referrer_meta = get_the_ID();
        } else if ($minerva_kb->info->is_topic()) {
            $referrer_type = 'topic';
            $term = get_queried_object();
            $referrer_meta = $term->term_id;
        } else if ($minerva_kb->info->is_search()) {
            $referrer_type = 'search';
            $referrer_meta = $_GET['s'];
        }

        return $link . '?referrer_type=' . $referrer_type . '&referrer_meta=' . $referrer_meta;
    }

	/**
	 * Home page columns layout
	 * @param $home_layout
	 * @return int
	 */
	public static function get_home_columns($home_layout) {
		$columns = 2;

		switch ($home_layout) {
            case '1col':
                $columns = 1;
                break;

			case '2col':
				$columns = 2;
				break;

			case '3col':
				$columns = 3;
				break;

			case '4col':
				$columns = 4;
				break;

			default:
				break;

		}

		return $columns;
	}

	public static function get_topic_children_columns() {
		$columns = 2;

		$home_layout = MKB_Options::option('topic_children_layout');

		switch ($home_layout) {
			case '2col':
				$columns = 2;
				break;

			case '3col':
				$columns = 3;
				break;

			case '4col':
				$columns = 4;
				break;

			default:
				break;

		}

		return $columns;
	}


	public static function get_columns($value) {
		$columns = 2;

		$layout = $value;

		switch ($layout) {
			case '2col':
				$columns = 2;
				break;

			case '3col':
				$columns = 3;
				break;

			case '4col':
				$columns = 4;
				break;

			default:
				break;

		}

		return $columns;
	}

	public static function hextorgb($hex, $alpha = false) {
		$hex = str_replace( '#', '', $hex );

		if ( strlen( $hex ) == 6 ) {
			$rgb['r'] = hexdec( substr( $hex, 0, 2 ) );
			$rgb['g'] = hexdec( substr( $hex, 2, 2 ) );
			$rgb['b'] = hexdec( substr( $hex, 4, 2 ) );
		} else if ( strlen( $hex ) == 3 ) {
			$rgb['r'] = hexdec( str_repeat( substr( $hex, 0, 1 ), 2 ) );
			$rgb['g'] = hexdec( str_repeat( substr( $hex, 1, 1 ), 2 ) );
			$rgb['b'] = hexdec( str_repeat( substr( $hex, 2, 1 ), 2 ) );
		} else {
			$rgb['r'] = '0';
			$rgb['g'] = '0';
			$rgb['b'] = '0';
		}
		if ( $alpha ) {
			$rgb['a'] = $alpha;
		}

		return $rgb;
	}

	public static function hextorgbstring($hex, $alpha = false) {
	    $rgb = self::hextorgb($hex, $alpha);

	    if ($alpha !== false) {
	        return "rgba({$rgb['r']}, {$rgb['g']}, {$rgb['b']}, {$alpha})";
        } else {
            return "rgb({$rgb['r']}, {$rgb['g']}, {$rgb['b']})";
        }
    }
}
