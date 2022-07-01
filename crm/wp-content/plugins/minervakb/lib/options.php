<?php
/**
 * Project: Minerva KB
 * Copyright: 2015-2016 @KonstruktStudio
 */

class MKB_Options {

	const OPTION_KEY = 'minerva-kb-options';

	const WPML_DOMAIN = 'MinervaKB';

	public function __construct() {
		self::register();
	}

	public static function register() {}

    /**
     * Returns all options by id key
     * @return mixed
     */
	public static function get_options_defaults() {
        return array_reduce(self::get_non_ui_options(self::get_options_cached()), function($defaults, $option) {
            $defaults[$option['id']] = isset($option['default']) ? $option['default'] : '';
            return $defaults;
        }, array());
	}

	/**
	 * Returns all options by id key
	 * @return mixed
	 */
	public static function get_options_by_id() {
        return array_reduce(self::get_non_ui_options(self::get_options_cached()), function($options, $option) {
            $options[$option["id"]] = $option;
            return $options;
        }, array());
	}

    /**
     * TODO: investigate, caching breaks some flows, for ex CPT
     * @return array
     */
    public static function get_options_cached() {
        return self::get_options();
    }

	public static function get_options() {
		return array(
			/**
			 * Home page
			 */
			array(
				'id' => 'home_tab',
				'type' => 'tab',
				'label' => __( 'Home page: Layout', 'minerva-kb' ),
				'icon' => 'fa-home'
			),
			array(
				'id' => 'home_content_title',
				'type' => 'title',
				'label' => __( 'Home page content & layout', 'minerva-kb' ),
				'description' => __( 'Configure the content to display on home KB page', 'minerva-kb' )
			),
			array(
				'id' => 'home_options_info',
				'type' => 'info',
				'label' => 'This section controls parameters of KB Home Page created with plugin settings. ' .
							'Currently you can also use shortcode builder or VC elements to create KB Home Page. Shortcodes are more flexible, '.
				            'as they allow you to easily insert your content between KB sections and add multiple KB blocks as well. ' .
				            'Note, that if you\'re using page created with shortcodes or page builder, these settings won\'t apply to it, so you will not see changes. ',
			),
			array(
				'id' => 'kb_page',
				'type' => 'page_select',
				'label' => __( 'Select page to display KB content', 'minerva-kb' ),
				'options' => self::get_pages_options(),
				'default' => '',
				'description' => __( 'Don\'t forget to save settings before page preview', 'minerva-kb' )
			),
			array(
				'id' => 'kb_page_wpml_warning',
				'type' => 'warning',
				'label' => __( 'WPML note: home page via settings works only for one-language sites. To create multiple home pages for WPML, please use our page builder or shortcode builder.', 'minerva-kb' ),
				'show_if' => defined('ICL_LANGUAGE_CODE')
			),
			array(
				'id' => 'home_sections_switch',
				'type' => 'checkbox',
				'label' => __( 'Let me select home page sections', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'Alternatively you can use page builder in page edit screen', 'minerva-kb' )
			),
			array(
				'id' => 'home_sections',
				'type' => 'layout_select',
				'label' => __( 'Select sections to display on home page', 'minerva-kb' ),
				'default' => 'search,topics',
				'options' => self::get_home_sections_options(),
				'dependency' => array(
					'target' => 'home_sections_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'page_template',
				'type' => 'select',
				'label' => __( 'Which page template to use?', 'minerva-kb' ),
				'options' => array(
					'theme' => __( 'Theme page template', 'minerva-kb' ),
					'plugin' => __( 'Plugin page template', 'minerva-kb' )
				),
				'default' => 'plugin',
				'experimental' => __( 'This is experimental feature and depends a lot on theme styles and layout', 'minerva-kb' ),
				'description' => __( 'Note, that you can override plugin templates in your theme. See documentation for details', 'minerva-kb' )
			),
			array(
				'id' => 'home_top_padding',
				'type' => 'css_size',
				'label' => __( 'Home page top padding', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "0"),
				'description' => __( 'Distance between header and home page content', 'minerva-kb' )
			),
			array(
				'id' => 'home_bottom_padding',
				'type' => 'css_size',
				'label' => __( 'Home page bottom padding', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "0"),
				'description' => __( 'Distance between home page content and footer', 'minerva-kb' )
			),
			array(
				'id' => 'home_page_container_switch',
				'type' => 'checkbox',
				'label' => __( 'Add container to home page content?', 'minerva-kb' ),
				'default' => true,
				'description' => __( 'You can use this if your theme supports fullwidth layout', 'minerva-kb' )
			),
			array(
				'id' => 'home_page_title_switch',
				'type' => 'checkbox',
				'label' => __( 'Show home page title?', 'minerva-kb' ),
				'default' => true
			),
			array(
				'id' => 'show_page_content',
				'type' => 'select',
				'label' => __( 'Show page content?', 'minerva-kb' ),
				'options' => array(
					'no' => __( 'No', 'minerva-kb' ),
					'before' => __( 'Before KB', 'minerva-kb' ),
					'after' => __( 'After KB', 'minerva-kb' )
				),
				'default' => 'no'
			),
			array(
				'id' => 'page_sidebar',
				'type' => 'image_select',
				'label' => __( 'Page sidebar position', 'minerva-kb' ),
				'options' => array(
					'none' => array(
						'label' => __( 'None', 'minerva-kb' ),
						'img' => MINERVA_KB_IMG_URL . 'no-sidebar.png'
					),
					'left' => array(
						'label' => __( 'Left', 'minerva-kb' ),
						'img' => MINERVA_KB_IMG_URL . 'left-sidebar.png'
					),
					'right' => array(
						'label' => __( 'Right', 'minerva-kb' ),
						'img' => MINERVA_KB_IMG_URL . 'right-sidebar.png'
					),
				),
				'default' => 'none',
				'description' => __( 'You can add widgets to sidebars under Appearance - Widgets', 'minerva-kb' )
			),
			/**
			 * Home page: Topics
			 */
			array(
				'id' => 'home_topics_tab',
				'type' => 'tab',
				'label' => __( 'Home page: Topics', 'minerva-kb' ),
				'icon' => 'fa-home'
			),
			array(
				'id' => 'home_topics_title',
				'type' => 'title',
				'label' => __( 'Home page topics', 'minerva-kb' ),
				'description' => __( 'Configure the display of topics on home KB page', 'minerva-kb' )
			),
			array(
				'id' => 'home_view',
				'type' => 'image_select',
				'label' => __( 'Home topics view', 'minerva-kb' ),
				'options' => array(
					'list' => array(
						'label' => __( 'List view', 'minerva-kb' ),
						'img' => MINERVA_KB_IMG_URL . 'list-view.png'
					),
					'box' => array(
						'label' => __( 'Box view', 'minerva-kb' ),
						'img' => MINERVA_KB_IMG_URL . 'box-view.png'
					)
				),
				'default' => 'list'
			),
			array(
				'id' => 'home_layout',
				'type' => 'image_select',
				'label' => __( 'Page topics layout', 'minerva-kb' ),
				'options' => array(
                    '1col' => array(
                        'label' => __( '1 column', 'minerva-kb' ),
                        'img' => MINERVA_KB_IMG_URL . 'col-1.png'
                    ),
					'2col' => array(
						'label' => __( '2 columns', 'minerva-kb' ),
						'img' => MINERVA_KB_IMG_URL . 'col-2.png'
					),
					'3col' => array(
						'label' => __( '3 columns', 'minerva-kb' ),
						'img' => MINERVA_KB_IMG_URL . 'col-3.png'
					),
					'4col' => array(
						'label' => __( '4 columns', 'minerva-kb' ),
						'img' => MINERVA_KB_IMG_URL . 'col-4.png'
					),
				),
				'default' => '3col'
			),
			array(
				'id' => 'home_topics',
				'type' => 'term_select',
				'label' => __( 'Select topics to display on home page', 'minerva-kb' ),
				'default' => '',
				'tax' => self::get_saved_option('article_cpt_category', 'kbtopic'),
				'extra_items' => array(
					array(
						'key' => 'recent',
						'label' => __('Recent', 'minerva-kb')
					),
					array(
						'key' => 'updated',
						'label' => __('Recently updated', 'minerva-kb')
					),
					array(
						'key' => 'top_views',
						'label' => __('Most viewed', 'minerva-kb')
					),
					array(
						'key' => 'top_likes',
						'label' => __('Most liked', 'minerva-kb')
					)
				),
				'description' => __( 'You can leave it empty to display all recent topics. NOTE: dynamic topics only work for list view', 'minerva-kb' )
			),
			array(
				'id' => 'home_topics_limit',
				'type' => 'input',
				'label' => __( 'Number of topics to display', 'minerva-kb' ),
				'default' => -1,
				'description' => __( 'Used in case no specific topics are selected. You can use -1 to display all', 'minerva-kb' )
			),
			array(
				'id' => 'home_topics_hide_children',
				'type' => 'checkbox',
				'label' => __( 'Hide child topics?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'If you don\'t select specific topics, you can use this option to show only top-level topics', 'minerva-kb' )
			),
			array(
				'id' => 'home_topics_articles_limit',
				'type' => 'input',
				'label' => __( 'Number of article to display', 'minerva-kb' ),
				'default' => 5,
				'description' => __( 'You can use -1 to display all', 'minerva-kb' )
			),
			array(
				'id' => 'home_topics_show_description',
				'type' => 'checkbox',
				'label' => __( 'Show description?', 'minerva-kb' ),
				'default' => true,
				'dependency' => array(
					'target' => 'home_view',
					'type' => 'EQ',
					'value' => 'box'
				)
			),
			array(
				'id' => 'show_all_switch',
				'type' => 'checkbox',
				'label' => __( 'Add "Show all" link?', 'minerva-kb' ),
				'default' => true
			),
			array(
				'id' => 'show_all_label',
				'type' => 'input_text',
				'label' => __( 'Show all link label', 'minerva-kb' ),
				'default' => __( 'Show all', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'show_all_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'show_articles_count',
				'type' => 'checkbox',
				'label' => __( 'Show articles count?', 'minerva-kb' ),
				'default' => true
			),

			// COLORS
			array(
				'id' => 'home_topic_colors_title',
				'type' => 'title',
				'label' => __( 'Topic colors', 'minerva-kb' ),
				'description' => __( 'Configure topic colors', 'minerva-kb' )
			),
			array(
				'id' => 'topic_color',
				'type' => 'color',
				'label' => __( 'Topic color', 'minerva-kb' ),
				'default' => '#4a90e2',
				'description' => __( 'Note, that topic color can be changed for each topic individually on topic edit page', 'minerva-kb' )
			),
			array(
				'id' => 'force_default_topic_color',
				'type' => 'checkbox',
				'label' => __( 'Force topic color (override topic custom colors)?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'By default, colors from topic settings have higher priority. You can override it with this setting', 'minerva-kb' )
			),
			array(
				'id' => 'box_view_item_bg',
				'type' => 'color',
				'label' => __( 'Box view background', 'minerva-kb' ),
				'default' => '#ffffff',
				'dependency' => array(
					'target' => 'home_view',
					'type' => 'EQ',
					'value' => 'box'
				)
			),
			array(
				'id' => 'box_view_item_hover_bg',
				'type' => 'color',
				'label' => __( 'Box view hover background', 'minerva-kb' ),
				'default' => '#f8f8f8',
				'dependency' => array(
					'target' => 'home_view',
					'type' => 'EQ',
					'value' => 'box'
				)
			),
			array(
				'id' => 'articles_count_bg',
				'type' => 'color',
				'label' => __( 'Articles count background', 'minerva-kb' ),
				'default' => '#4a90e2',
				'dependency' => array(
					'target' => 'show_articles_count',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'articles_count_color',
				'type' => 'color',
				'label' => __( 'Articles count color', 'minerva-kb' ),
				'default' => '#ffffff',
				'dependency' => array(
					'target' => 'show_articles_count',
					'type' => 'EQ',
					'value' => true
				)
			),

			// ICONS
			array(
				'id' => 'home_topic_icons_title',
				'type' => 'title',
				'label' => __( 'Topic icons', 'minerva-kb' ),
				'description' => __( 'Configure topic icons settings', 'minerva-kb' )
			),
			array(
				'id' => 'show_topic_icons',
				'type' => 'checkbox',
				'label' => __( 'Show topic icons?', 'minerva-kb' ),
				'default' => true
			),
			array(
				'id' => 'topic_icon',
				'type' => 'icon_select',
				'label' => __( 'Default topic icon', 'minerva-kb' ),
				'default' => 'fa-list-alt',
				'description' => __( 'Note, that topic icon can be changed for each topic individually on topic edit page', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'show_topic_icons',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'force_default_topic_icon',
				'type' => 'checkbox',
				'label' => __( 'Force topic icon (override topic custom icons)?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'By default, icons from topic settings have higher priority. You can override it with this setting', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'show_topic_icons',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'use_topic_image',
				'type' => 'checkbox',
				'label' => __( 'Box view only: Show image instead of icon? Image URL can be added on each topic page', 'minerva-kb' ),
				'default' => false,
				'dependency' => array(
					'target' => 'show_topic_icons',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'image_size',
				'type' => 'css_size',
				'label' => __( 'Topic image size', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "10"),
				'description' => 'Use any CSS value, for ex. 2em or 20px',
				'dependency' => array(
					'target' => 'show_topic_icons',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'topic_icon_padding_top',
				'type' => 'css_size',
				'label' => __( 'Topic icon/image top padding', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "0"),
				'description' => 'Use any CSS value, for ex. 2em or 20px',
				'dependency' => array(
					'target' => 'show_topic_icons',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'topic_icon_padding_bottom',
				'type' => 'css_size',
				'label' => __( 'Topic icon/image bottom padding', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "0"),
				'description' => 'Use any CSS value, for ex. 2em or 20px',
				'dependency' => array(
					'target' => 'show_topic_icons',
					'type' => 'EQ',
					'value' => true
				)
			),

			// ARTICLES
			array(
				'id' => 'home_articles_title',
				'type' => 'title',
				'label' => __( 'Articles settings', 'minerva-kb' ),
				'description' => __( 'Configure how articles list should look on home KB page', 'minerva-kb' )
			),
			array(
				'id' => 'show_article_icons',
				'type' => 'checkbox',
				'label' => __( 'List view only: Show article icons?', 'minerva-kb' ),
				'default' => true
			),
			array(
				'id' => 'article_icon',
				'type' => 'icon_select',
				'label' => __( 'Article icon', 'minerva-kb' ),
				'default' => 'fa-book',
				'dependency' => array(
					'target' => 'show_article_icons',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'show_article_views',
				'type' => 'checkbox',
				'label' => __( 'List view only: Show article views?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'show_article_likes',
				'type' => 'checkbox',
				'label' => __( 'List view only: Show article likes?', 'minerva-kb' ),
				'default' => false
			),
			/**
			 * Search home
			 */
			array(
				'id' => 'search_home_tab',
				'type' => 'tab',
				'label' => __( 'Home page: Search', 'minerva-kb' ),
				'icon' => 'fa-home'
			),
			array(
				'id' => 'home_search_title',
				'type' => 'title',
				'label' => __( 'Home page search', 'minerva-kb' ),
				'description' => __( 'Configure the display of search box on home KB page', 'minerva-kb' )
			),
			array(
				'id' => 'search_title',
				'type' => 'input_text',
				'label' => __( 'Search title', 'minerva-kb' ),
				'default' => __( 'Need some help?', 'minerva-kb' )
			),
			array(
				'id' => 'search_title_size',
				'type' => 'css_size',
				'label' => __( 'Search title font size', 'minerva-kb' ),
				'default' => array('unit' => 'em', 'size' => '3'),
				'description' => 'Use any CSS value, for ex. 3em or 20px',
				'dependency' => array(
					'target' => 'search_title',
					'type' => 'NEQ',
					'value' => ''
				)
			),
			array(
				'id' => 'search_theme',
				'type' => 'select',
				'label' => __( 'Which search input theme to use?', 'minerva-kb' ),
				'options' => array(
					'minerva' => __( 'Minerva', 'minerva-kb' ),
					'clean' => __( 'Clean', 'minerva-kb' ),
					'mini' => __( 'Mini', 'minerva-kb' ),
					'bold' => __( 'Bold', 'minerva-kb' ),
					'invisible' => __( 'Invisible', 'minerva-kb' ),
					'thick' => __( 'Thick', 'minerva-kb' ),
					'3d' => __( '3d', 'minerva-kb' ),
				),
				'default' => 'minerva',
				'description' => __( 'Use predefined styles for search bar', 'minerva-kb' )
			),
			array(
				'id' => 'search_min_width',
				'type' => 'css_size',
				'label' => __( 'Search input minimum width', 'minerva-kb' ),
                'default' => array('unit' => 'em', 'size' => '38'),
				'description' => 'Use any CSS value, for ex. 40em or 300px. em are better for mobile devices'
			),
			array(
				'id' => 'search_container_padding_top',
				'type' => 'css_size',
				'label' => __( 'Search container top padding', 'minerva-kb' ),
                'default' => array('unit' => 'em', 'size' => '3'),
				'description' => 'Use any CSS value, for ex. 3em or 50px'
			),
			array(
				'id' => 'search_container_padding_bottom',
				'type' => 'css_size',
				'label' => __( 'Search container bottom padding', 'minerva-kb' ),
                'default' => array('unit' => 'em', 'size' => '3'),
				'description' => 'Use any CSS value, for ex. 3em or 50px'
			),
			array(
				'id' => 'search_placeholder',
				'type' => 'input_text',
				'label' => __( 'Search placeholder', 'minerva-kb' ),
				'default' => __( 'ex.: Installation', 'minerva-kb' )
			),
			array(
				'id' => 'search_topics',
				'type' => 'term_select',
				'label' => __( 'Optional: you can limit search to specific topics', 'minerva-kb' ),
				'default' => '',
				'tax' => self::get_saved_option('article_cpt_category', 'kbtopic'),
				'description' => __( 'You can leave it empty to search all topics (default).', 'minerva-kb' )
			),
			array(
				'id' => 'disable_autofocus',
				'type' => 'checkbox',
				'label' => __( 'Disable search field autofocus?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'show_search_tip',
				'type' => 'checkbox',
				'label' => __( 'Show search tip?', 'minerva-kb' ),
				'default' => true
			),
			array(
				'id' => 'search_tip',
				'type' => 'input_text',
				'label' => __( 'Search tip (under the input)', 'minerva-kb' ),
				'default' => __( 'Tip: Use arrows to navigate results, ESC to focus search input', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'show_search_tip',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'show_topic_in_results',
				'type' => 'checkbox',
				'label' => __( 'Show topic in results?', 'minerva-kb' ),
				'default' => true
			),
			array(
				'id' => 'search_results_multiline',
				'type' => 'checkbox',
				'label' => __( 'Allow multiline titles in results?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'By default, results are fit in one line. You can change this to allow multiline titles', 'minerva-kb' )
			),
			array(
				'id' => 'search_result_topic_label',
				'type' => 'input_text',
				'label' => __( 'Search result topic label', 'minerva-kb' ),
				'default' => __( 'Topic', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'show_topic_in_results',
					'type' => 'EQ',
					'value' => true
				)
			),
			// COLORS
			array(
				'id' => 'home_search_colors_title',
				'type' => 'title',
				'label' => __( 'Search colors and background', 'minerva-kb' ),
				'description' => __( 'Configure search style', 'minerva-kb' )
			),
			array(
				'id' => 'search_title_color',
				'type' => 'color',
				'label' => __( 'Search title color', 'minerva-kb' ),
				'default' => '#333333',
				'dependency' => array(
					'target' => 'search_title',
					'type' => 'NEQ',
					'value' => ''
				)
			),
			array(
				'id' => 'search_border_color',
				'type' => 'color',
				'label' => __( 'Search wrap border color (not in all themes)', 'minerva-kb' ),
				'default' => '#ffffff'
			),
			array(
				'id' => 'search_container_bg',
				'type' => 'color',
				'label' => __( 'Search container background color', 'minerva-kb' ),
				'default' => '#ffffff'
			),
			array(
				'id' => 'search_container_image_bg',
				'type' => 'media',
				'label' => __( 'Search container background image URL (optional)', 'minerva-kb' ),
				'default' => array('isUrl' => true, 'img' => '')
			),
			array(
				'id' => 'add_gradient_overlay',
				'type' => 'checkbox',
				'label' => __( 'Add gradient overlay?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'search_container_gradient_from',
				'type' => 'color',
				'label' => __( 'Container gradient from', 'minerva-kb' ),
				'default' => '#00c1b6',
				'dependency' => array(
					'target' => 'add_gradient_overlay',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'search_container_gradient_to',
				'type' => 'color',
				'label' => __( 'Container gradient to', 'minerva-kb' ),
				'default' => '#136eb5',
				'dependency' => array(
					'target' => 'add_gradient_overlay',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'search_container_gradient_opacity',
				'type' => 'range',
				'label' => __( 'Search container background gradient opacity', 'minerva-kb' ),
				'default' => 1,
				'min' => 0,
				'max' => 1,
				'step' => 0.05,
				'description' => __( 'Use any CSS opacity value, for example 1 or 0.7', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'add_gradient_overlay',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'add_pattern_overlay',
				'type' => 'checkbox',
				'label' => __( 'Add pattern overlay?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'search_container_image_pattern',
				'type' => 'media',
				'label' => __( 'Search container background pattern image (optional)', 'minerva-kb' ),
                'default' => array('isUrl' => true, 'img' => ''),
				'dependency' => array(
					'target' => 'add_pattern_overlay',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'search_container_image_pattern_opacity',
				'type' => 'range',
				'label' => __( 'Search container background pattern opacity', 'minerva-kb' ),
				'default' => 1,
                'min' => 0,
                'max' => 1,
                'step' => 0.05,
				'description' => __( 'Use any CSS opacity value, for example 1 or 0.7. You can also use transparent .png and set opacity to 1', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'add_pattern_overlay',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'search_tip_color',
				'type' => 'color',
				'label' => __( 'Search tip color', 'minerva-kb' ),
				'default' => '#cccccc',
				'dependency' => array(
					'target' => 'show_search_tip',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'search_results_topic_bg',
				'type' => 'color',
				'label' => __( 'Search results topic background', 'minerva-kb' ),
				'default' => '#4a90e2',
				'dependency' => array(
					'target' => 'show_topic_in_results',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'search_results_topic_color',
				'type' => 'color',
				'label' => __( 'Search results topic color', 'minerva-kb' ),
				'default' => '#ffffff',
				'dependency' => array(
					'target' => 'show_topic_in_results',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'search_results_topic_use_custom',
				'type' => 'checkbox',
				'label' => __( 'Use custom topic colors in search results?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'Topic custom color will be used as background color for topic label', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'show_topic_in_results',
					'type' => 'EQ',
					'value' => true
				)
			),

			// ICONS
			array(
				'id' => 'home_search_icons_title',
				'type' => 'title',
				'label' => __( 'Search icons', 'minerva-kb' ),
				'description' => __( 'Configure search icons', 'minerva-kb' )
			),
			array(
				'id' => 'search_icons_left',
				'type' => 'checkbox',
				'label' => __( 'Show search bar icons on the left side?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'show_search_icon',
				'type' => 'checkbox',
				'label' => __( 'Show search icon?', 'minerva-kb' ),
				'default' => true
			),
			array(
				'id' => 'search_icon',
				'type' => 'icon_select',
				'label' => __( 'Search icon', 'minerva-kb' ),
				'default' => 'fa-search',
				'dependency' => array(
					'target' => 'show_search_icon',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'search_clear_icon',
				'type' => 'icon_select',
				'label' => __( 'Search clear icon', 'minerva-kb' ),
				'default' => 'fa-times-circle'
			),
			/**
			 * FAQ home
			 */
			array(
				'id' => 'faq_home_tab',
				'type' => 'tab',
				'label' => __( 'Home page: FAQ', 'minerva-kb' ),
				'icon' => 'fa-home'
			),
			array(
				'id' => 'home_faq_section_title',
				'type' => 'title',
				'label' => __( 'Home page FAQ section', 'minerva-kb' ),
				'description' => __( 'Configure the display of FAQ on home KB page', 'minerva-kb' )
			),
			array(
				'id' => 'home_faq_title',
				'type' => 'input_text',
				'label' => __( 'FAQ title', 'minerva-kb' ),
				'default' => __( 'Frequently Asked Questions', 'minerva-kb' )
			),
			array(
				'id' => 'home_faq_title_size',
				'type' => 'css_size',
				'label' => __( 'FAQ title font size', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "3"),
				'description' => 'Use any CSS value, for ex. 3em or 20px',
				'dependency' => array(
					'target' => 'home_faq_title',
					'type' => 'NEQ',
					'value' => ''
				)
			),
			array(
				'id' => 'home_faq_title_color',
				'type' => 'color',
				'label' => __( 'FAQ title color', 'minerva-kb' ),
				'default' => '#333333',
				'dependency' => array(
					'target' => 'home_faq_title',
					'type' => 'NEQ',
					'value' => ''
				)
			),
			array(
				'id' => 'home_faq_layout_section_title',
				'type' => 'title',
				'label' => __( 'Home FAQ layout', 'minerva-kb' ),
				'description' => __( 'Configure FAQ layout on home page', 'minerva-kb' )
			),
			array(
				'id' => 'home_faq_margin_top',
				'type' => 'css_size',
				'label' => __( 'FAQ section top margin', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "3"),
				'description' => __( 'Distance between FAQ and previous section', 'minerva-kb' ),
			),
			array(
				'id' => 'home_faq_margin_bottom',
				'type' => 'css_size',
				'label' => __( 'FAQ section bottom margin', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "3"),
				'description' => __( 'Distance between FAQ and next sections', 'minerva-kb' ),
			),
			array(
				'id' => 'home_faq_limit_width_switch',
				'type' => 'checkbox',
				'label' => __( 'Limit FAQ container width?', 'minerva-kb' ),
				'default' => true
			),
			array(
				'id' => 'home_faq_width_limit',
				'type' => 'css_size',
				'label' => __( 'FAQ container maximum width', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "60"),
				'description' => __( 'You can make FAQ section more narrow, than your content width', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'home_faq_limit_width_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'home_faq_controls_margin_top',
				'type' => 'css_size',
				'label' => __( 'FAQ controls top margin', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "2"),
				'description' => __( 'Distance between FAQ controls and title', 'minerva-kb' ),
			),
			array(
				'id' => 'home_faq_controls_margin_bottom',
				'type' => 'css_size',
				'label' => __( 'FAQ controls bottom margin', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "2"),
				'description' => __( 'Distance between FAQ controls and questions', 'minerva-kb' ),
			),
			array(
				'id' => 'home_faq_controls_section_title',
				'type' => 'title',
				'label' => __( 'Home FAQ controls', 'minerva-kb' ),
				'description' => __( 'Configure FAQ controls on home page', 'minerva-kb' )
			),
			array(
				'id' => 'home_show_faq_filter',
				'type' => 'checkbox',
				'label' => __( 'Show FAQ live filter?', 'minerva-kb' ),
				'default' => true
			),
			array(
				'id' => 'home_show_faq_toggle_all',
				'type' => 'checkbox',
				'label' => __( 'Show FAQ toggle all button?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'home_faq_categories_section_title',
				'type' => 'title',
				'label' => __( 'FAQ categories settings', 'minerva-kb' ),
				'description' => __( 'Configure FAQ categories', 'minerva-kb' )
			),
			array(
				'id' => 'home_faq_categories',
				'type' => 'term_select',
				'label' => __( 'Select FAQ categories to display on home page', 'minerva-kb' ),
				'default' => '',
				'tax' => 'mkb_faq_category',
				'description' => __( 'You can leave it empty to display all categories.', 'minerva-kb' )
			),

			array(
				'id' => 'home_show_faq_categories',
				'type' => 'checkbox',
				'label' => __( 'Show FAQ categories?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'home_show_faq_category_count',
				'type' => 'checkbox',
				'label' => __( 'Show FAQ category question count?', 'minerva-kb' ),
				'default' => true,
			),
			array(
				'id' => 'home_faq_styles_note_title',
				'type' => 'title',
				'label' => __( 'NOTE: You can configure FAQ styles in FAQ (global)', 'minerva-kb' )
			),
			/**
			 * General
			 */
			array(
				'id' => 'general_tab',
				'type' => 'tab',
				'label' => __( 'General', 'minerva-kb' ),
				'icon' => 'fa-cogs'
			),
			array(
				'id' => 'general_content_title',
				'type' => 'title',
				'label' => __( 'General settings', 'minerva-kb' ),
				'description' => __( 'Configure general KB settings', 'minerva-kb' )
			),
			array(
				'id' => 'layout_title',
				'type' => 'title',
				'label' => __( 'Layout', 'minerva-kb' ),
				'description' => __( 'Configure KB layout', 'minerva-kb' )
			),
			array(
				'id' => 'container_width',
				'type' => 'css_size',
				'label' => __( 'Root container width', 'minerva-kb' ),
				'default' => array("unit" => 'px', "size" => "1180"),
				'units' => array('px', '%'),
				'description' => __( 'Container is the top level element that limits the width of KB content', 'minerva-kb' )
			),
			array(
				'id' => 'content_width',
				'type' => 'css_size',
				'label' => __( 'Content width (%)', 'minerva-kb' ),
				'default' => array("unit" => '%', "size" => "66"),
				'units' => array('%'),
				'description' => __( 'Use this setting to configure width of content vs sidebar, when sidebar is on. Sidebar will take rest of available space', 'minerva-kb' )
			),
            array(
                'id' => 'global_scroll_offset',
                'type' => 'css_size',
                'label' => __( 'General scroll-to-top offset', 'minerva-kb' ),
                'units' => array('px'),
                'default' => array("unit" => 'px', "size" => "30"),
            ),
			array(
				'id' => 'css_title',
				'type' => 'title',
				'label' => __( 'Custom CSS', 'minerva-kb' ),
				'description' => __( 'Add custom styling', 'minerva-kb' )
			),
			array(
				'id' => 'custom_css',
				'type' => 'textarea',
				'label' => __( 'CSS to add after plugin styles', 'minerva-kb' ),
				'height' => 20,
				'width' => 80,
				'default' => __( '', 'minerva-kb' )
			),
			array(
				'id' => 'pagination_title',
				'type' => 'title',
				'label' => __( 'Pagination', 'minerva-kb' ),
				'description' => __( 'Configure KB pagination', 'minerva-kb' )
			),
			array(
				'id' => 'pagination_style',
				'type' => 'select',
				'label' => __( 'Which pagination style to use on topic, tag, archive and search results pages?', 'minerva-kb' ),
				'options' => array(
					'plugin' => __( 'Minerva', 'minerva-kb' ),
					'theme' => __( 'WordPress default', 'minerva-kb' )
				),
				'default' => 'plugin',
				'description' => __( 'When WordPress default selected, theme styled pagination should appear', 'minerva-kb' )
			),
			array(
				'id' => 'pagination_bg',
				'type' => 'color',
				'label' => __( 'Pagination item background color', 'minerva-kb' ),
				'default' => '#f7f7f7',
				'dependency' => array(
					'target' => 'pagination_style',
					'type' => 'EQ',
					'value' => 'plugin'
				)
			),
			array(
				'id' => 'pagination_color',
				'type' => 'color',
				'label' => __( 'Pagination item text color', 'minerva-kb' ),
				'default' => '#333',
				'dependency' => array(
					'target' => 'pagination_style',
					'type' => 'EQ',
					'value' => 'plugin'
				)
			),
			array(
				'id' => 'pagination_link_color',
				'type' => 'color',
				'label' => __( 'Pagination item link color', 'minerva-kb' ),
				'default' => '#007acc',
				'dependency' => array(
					'target' => 'pagination_style',
					'type' => 'EQ',
					'value' => 'plugin'
				)
			),
			/**
			 * Styles
			 */
			array(
				'id' => 'styles_tab',
				'type' => 'tab',
				'label' => __( 'Typography & Styles', 'minerva-kb' ),
				'icon' => 'fa-paint-brush'
			),
			array(
				'id' => 'typography_title',
				'type' => 'title',
				'label' => __( 'Typography', 'minerva-kb' ),
				'description' => __( 'Configure KB fonts', 'minerva-kb' )
			),
			// typography
			array(
				'id' => 'typography_on',
				'type' => 'checkbox',
				'label' => __( 'Enable typography options?', 'minerva-kb' ),
				'default' => true,
				'description' => __( 'When off, theme styles will be used', 'minerva-kb' )
			),
			array(
				'id' => 'style_font',
				'type' => 'font',
				'label' => __( 'Font', 'minerva-kb' ),
				'default' => 'Roboto',
				'description' => __( 'Select font to use for KB', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'typography_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'style_font_gf_weights',
				'type' => 'google_font_weights',
				'label' => __( 'Font weights to load (for Google Fonts only)', 'minerva-kb' ),
				'default' => array('400', '600'),
				'description' => __( 'Font weights to load from Google. Use Shift or Ctrl/Cmd to select multiple values. Note: more weights mean more load time', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'typography_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'style_font_gf_languages',
				'type' => 'google_font_languages',
				'label' => __( 'Font languages to load (for Google Fonts only)', 'minerva-kb' ),
				'default' => array(),
				'description' => __( 'Font languages to load from Google. Latin set is always loaded. Use Shift or Ctrl/Cmd to select multiple values. Note: more languages mean more load time', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'typography_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'dont_load_font',
				'type' => 'checkbox',
				'label' => __( 'Don\'t load font?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'Can be useful if your theme or other plugin loads this font already', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'typography_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'content_font_size',
				'type' => 'css_size',
				'label' => __( 'Article content font size', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "1"),
				'description' => __( 'Content font size is used to proportionally change size article text', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'typography_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'content_line_height',
				'type' => 'css_size',
				'label' => __( 'Article content line-height', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "1.7"),
				'description' => __( 'Content line height', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'typography_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'h1_font_size',
				'type' => 'css_size',
				'label' => __( 'H1 heading font size', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "2"),
				'description' => __( 'H1 heading', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'typography_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'h2_font_size',
				'type' => 'css_size',
				'label' => __( 'H2 heading font size', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "1.8"),
				'description' => __( 'H2 heading', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'typography_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'h3_font_size',
				'type' => 'css_size',
				'label' => __( 'H3 heading font size', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "1.6"),
				'description' => __( 'H3 heading', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'typography_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'h4_font_size',
				'type' => 'css_size',
				'label' => __( 'H4 heading font size', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "1.4"),
				'description' => __( 'H4 heading', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'typography_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'h5_font_size',
				'type' => 'css_size',
				'label' => __( 'H5 heading font size', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "1.2"),
				'description' => __( 'H5 heading', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'typography_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'h6_font_size',
				'type' => 'css_size',
				'label' => __( 'H6 heading font size', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "1"),
				'description' => __( 'H6 heading', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'typography_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'widget_font_size',
				'type' => 'css_size',
				'label' => __( 'Widget content font size', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "1"),
				'description' => __( 'Widget content font size', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'typography_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'widget_heading_font_size',
				'type' => 'css_size',
				'label' => __( 'Widget heading font size', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "1.3"),
				'description' => __( 'Widget heading font size', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'typography_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			// text colors
			array(
				'id' => 'content_colors_title',
				'type' => 'title',
				'label' => __( 'Text styles', 'minerva-kb' ),
				'description' => __( 'Configure text and heading colors', 'minerva-kb' )
			),
			array(
				'id' => 'text_color',
				'type' => 'color',
				'label' => __( 'Article text color', 'minerva-kb' ),
				'default' => '#333'
			),
			array(
				'id' => 'text_link_color',
				'type' => 'color',
				'label' => __( 'Article text link color', 'minerva-kb' ),
				'default' => '#007acc'
			),
			array(
				'id' => 'h1_color',
				'type' => 'color',
				'label' => __( 'H1 heading color', 'minerva-kb' ),
				'default' => '#333'
			),
			array(
				'id' => 'h2_color',
				'type' => 'color',
				'label' => __( 'H2 heading color', 'minerva-kb' ),
				'default' => '#333'
			),
			array(
				'id' => 'h3_color',
				'type' => 'color',
				'label' => __( 'H3 heading color', 'minerva-kb' ),
				'default' => '#333'
			),
			array(
				'id' => 'h4_color',
				'type' => 'color',
				'label' => __( 'H4 heading color', 'minerva-kb' ),
				'default' => '#333'
			),
			array(
				'id' => 'h5_color',
				'type' => 'color',
				'label' => __( 'H5 heading color', 'minerva-kb' ),
				'default' => '#333'
			),
			array(
				'id' => 'h6_color',
				'type' => 'color',
				'label' => __( 'H6 heading color', 'minerva-kb' ),
				'default' => '#333'
			),

            /**
             * Email settings
             */
            array(
                'id' => 'email_tab',
                'type' => 'tab',
                'label' => __( 'Email settings', 'minerva-kb' ),
                'icon' => 'fa-envelope-o'
            ),
            array(
                'id' => 'email_notify_info',
                'type' => 'info',
                'label' => 'Email functionality depends on proper WordPress and server configuration to work correctly. ' .
                    'If your emails are not delivered, please review your server email settings ' .
                    'or install email plugin, like <a href="https://wordpress.org/plugins/wp-mail-smtp/" target="_blank">WP Mail SMTP</a> ' .
                    'to configure advanced email settings',
            ),
            array(
                'id' => 'email_notify_demo_info',
                'type' => 'warning',
                'label' => __( 'Demo Mode On - all emails are disabled', 'minerva-kb' ),
                'show_if' => defined('MINERVA_DEMO_MODE') && MINERVA_DEMO_MODE
            ),
            // test emails
            array(
                'id' => 'test_email_title',
                'type' => 'title',
                'label' => __( 'Email delivery test', 'minerva-kb' ),
                'description' => __( 'Check that emails are delivered properly on your current setup', 'minerva-kb' )
            ),
            array(
                'id' => 'email_notify_default_email',
                'type' => 'input',
                'label' => __( 'Default email address for email notifications', 'minerva-kb' ),
                'default' => __( '', 'minerva-kb' ),
            ),
            array(
                'id' => 'email_notify_test',
                'type' => 'test_email',
                'label' => __( 'Send test email to above address. You need to save settings after you change email.', 'minerva-kb' ),
                'default' => __( '', 'minerva-kb' ),
                'description' => __( 'Don\'t forget to check SPAM folder', 'minerva-kb' ),
            ),
            // general email settings
            array(
                'id' => 'common_email_settings_title',
                'type' => 'title',
                'label' => __( 'Email templates', 'minerva-kb' ),
                'description' => __( 'Configure email templates', 'minerva-kb' )
            ),
            array(
                'id' => 'email_sender_name',
                'type' => 'input',
                'label' => __( 'Sender name', 'minerva-kb' ),
                'default' => __( 'WordPress', 'minerva-kb' )
            ),
            array(
                'id' => 'email_sender_from_email',
                'type' => 'input',
                'label' => __( 'Sender email ("From" field)', 'minerva-kb' ),
                'default' => get_bloginfo('admin_email')
            ),
            array(
                'id' => 'email_sender_replyto_email',
                'type' => 'input',
                'label' => __( 'Sender reply email ("Reply To" field)', 'minerva-kb' ),
                'default' => get_bloginfo('admin_email')
            ),
            array(
                'id' => 'email_templates_info',
                'type' => 'info',
                'label' => 'Our responsive email templates are optimized to work in all modern email clients. ' .
                    'If you choose visual editing mode be careful not to break layout. ' .
                    'In any case it is a good idea to test emails after you have edited them before using on live site.',
            ),
            array(
                'id' => 'email_templates_remove_header',
                'type' => 'checkbox',
                'label' => __( 'Remove email header', 'minerva-kb' ),
                'default' => false,
            ),
            array(
                'id' => 'email_templates_remove_footer',
                'type' => 'checkbox',
                'label' => __( 'Remove email footer', 'minerva-kb' ),
                'default' => false,
            ),
            // email header
            array(
                'id' => 'email_header_title',
                'type' => 'title',
                'label' => __( 'Email header', 'minerva-kb' ),
                'description' => __( 'Configure email header settings', 'minerva-kb' ),
            ),
            array(
                'id' => 'email_header_template',
                'type' => 'select',
                'label' => __( 'Which email header template to use?', 'minerva-kb' ),
                'options' => array(
                    'logo' => __( 'Logo', 'minerva-kb' ),
                    'name' => __( 'Company Name', 'minerva-kb' ),
                    'none' => __( 'None', 'minerva-kb' )
                ),
                'default' => 'name',
                'description' => __( 'Select email header branding template', 'minerva-kb' ),
            ),
            // email footer
            array(
                'id' => 'email_footer_title',
                'type' => 'title',
                'label' => __( 'Email footer', 'minerva-kb' ),
                'description' => __( 'Configure email footer settings', 'minerva-kb' ),
            ),
            array(
                'id' => 'email_footer_copyright',
                'type' => 'input',
                'label' => __( 'Footer copyright', 'minerva-kb' ),
                'default' => __( '&copy; 2020 Your Company. All rights reserved.', 'minerva-kb' ),
            ),
            array(
                'id' => 'email_footer_text',
                'type' => 'textarea',
                'label' => __( 'Footer bottom text', 'minerva-kb' ),
                'default' => __( 'You can use this field to add some info about your company', 'minerva-kb' ),
            ),
            // ticket template tags
            array(
                'id' => 'email_tags_title',
                'type' => 'title',
                'label' => __( 'Template tags (global)', 'minerva-kb' ),
                'description' => __( 'Configure tags values to use in templates', 'minerva-kb' ),
            ),
            array(
                'id' => 'email_company_logo',
                'type' => 'media',
                'label' => __( 'Logo', 'minerva-kb' ),
                'default' => '',
                'description' => __( 'Use <strong>{{company_logo}}</strong> tag in templates', 'minerva-kb' ),
            ),
            array(
                'id' => 'email_company_name',
                'type' => 'input',
                'label' => __( 'Company name', 'minerva-kb' ),
                'default' => get_bloginfo('name'),
                'description' => __( 'Use <strong>{{company_name}}</strong> tag in templates', 'minerva-kb' ),
            ),
            array(
                'id' => 'email_company_link',
                'type' => 'input',
                'label' => __( 'Logo / Company name link URL', 'minerva-kb' ),
                'default' => get_bloginfo('url'),
                'description' => __( 'Use <strong>{{company_url}}</strong> tag in templates', 'minerva-kb' ),
            ),
            array(
                'id' => 'email_action_button_fallback_text',
                'type' => 'textarea',
                'label' => __( 'Caption text for email action button fallback link', 'minerva-kb' ),
                'default' => __( "If you're having trouble with the button above, copy and paste the URL below into your web browser.", 'minerva-kb' ),
                'height' => 3,
                'description' => __( 'Action button fallback is Used as <strong>{{action_button_fallback}}</strong> tag in templates', 'minerva-kb' ),
            ),

            /**
             * Email templates settings
             */
            array(
                'id' => 'email_templates_tab',
                'type' => 'tab',
                'label' => __( 'Email templates', 'minerva-kb' ),
                'icon' => 'fa-envelope-o'
            ),

            // admin new feedback email template
            array(
                'id' => 'email_admin_new_article_feedback_options_title',
                'type' => 'title',
                'label' => __( '[Admin Email] New Article Feedback', 'minerva-kb' ),
                'description' => __( 'Configure email that is sent on new article feedback', 'minerva-kb' )
            ),
            array(
                'id' => 'email_notify_feedback_switch',
                'type' => 'checkbox',
                'label' => __( 'Enable?', 'minerva-kb' ),
                'default' => true,
                'description' => __( 'Admin will receive this email whenever article feedback is submitted', 'minerva-kb' ),
            ),
            array(
                'id' => 'email_notify_feedback_subject',
                'type' => 'input',
                'label' => __( 'Email subject', 'minerva-kb' ),
                'default' => __( 'A new feedback received for KB article', 'minerva-kb' ),
                'dependency' => array(
                    'target' => 'email_notify_feedback_switch',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
            array(
                'id' => 'email_admin_new_article_feedback_message',
                'type' => 'editor',
                'label' => __( 'Email message', 'minerva-kb' ),
                'default' =>
'<p>A new feedback has been submitted for article <strong>{{article_title}}</strong> on site <strong>{{site_url}}</strong>:</p>
{{message}}
<p>Follow the link below to open article admin page</p>
{{action_button}}
<p>If you think you have received this email by mistake, please contact the site administrator.</p>
{{action_button_fallback}}',
                'dependency' => array(
                    'target' => 'email_notify_feedback_switch',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
            array(
                'id' => 'email_admin_new_article_feedback_action_label',
                'type' => 'input',
                'label' => __( 'Action button label', 'minerva-kb' ),
                'default' => __( 'Open Article Admin Page', 'minerva-kb' ),
                'dependency' => array(
                    'target' => 'email_notify_feedback_switch',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
            array(
                'id' => 'email_admin_new_article_feedback_info',
                'type' => 'info',
                'label' => 'To preview this email template in browser <a href="' . get_bloginfo('url') . '?mkb_email_template_preview=1&mkb_email_template_id=' . MKB_Emails::EMAIL_TYPE_ADMIN_NEW_ARTICLE_FEEDBACK . '" target="_blank">click here</a>. Don\'t forget to save settings before preview to see your changes.',
                'dependency' => array(
                    'target' => 'email_notify_feedback_switch',
                    'type' => 'EQ',
                    'value' => true
                )
            ),

            // admin new guest article email template
            array(
                'id' => 'email_admin_new_guest_article_options_title',
                'type' => 'title',
                'label' => __( '[Admin Email] New Guest Article', 'minerva-kb' ),
                'description' => __( 'Configure email that is sent on new guest article submit', 'minerva-kb' )
            ),
            array(
                'id' => 'email_admin_new_guest_article_switch',
                'type' => 'checkbox',
                'label' => __( 'Enable?', 'minerva-kb' ),
                'default' => true,
                'description' => __( 'Admin will receive this email whenever someone submits a draft article on site', 'minerva-kb' ),
            ),
            array(
                'id' => 'email_admin_new_guest_article_subject',
                'type' => 'input',
                'label' => __( 'Email subject', 'minerva-kb' ),
                'default' => __( 'A new guest KB article draft submitted', 'minerva-kb' ),
                'dependency' => array(
                    'target' => 'email_admin_new_guest_article_switch',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
            array(
                'id' => 'email_admin_new_guest_article_message',
                'type' => 'editor',
                'label' => __( 'Email message', 'minerva-kb' ),
                'default' =>
'<p>A new guest article has been submitted on site <strong>{{site_url}}</strong>.</p>
<p>Follow the link below to review and publish it</p>
{{action_button}}
<p>If you think you have received this email by mistake, please contact the site administrator.</p>
{{action_button_fallback}}',
                'dependency' => array(
                    'target' => 'email_admin_new_guest_article_switch',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
            array(
                'id' => 'email_admin_new_guest_article_action_label',
                'type' => 'input',
                'label' => __( 'Action button label', 'minerva-kb' ),
                'default' => __( 'Edit Article', 'minerva-kb' ),
                'dependency' => array(
                    'target' => 'email_admin_new_guest_article_switch',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
            array(
                'id' => 'email_admin_new_guest_article_info',
                'type' => 'info',
                'label' => 'To preview this email template in browser <a href="' . get_bloginfo('url') . '?mkb_email_template_preview=1&mkb_email_template_id=' . MKB_Emails::EMAIL_TYPE_ADMIN_NEW_GUEST_ARTICLE . '" target="_blank">click here</a>. Don\'t forget to save settings before preview to see your changes.',
                'dependency' => array(
                    'target' => 'email_admin_new_guest_article_switch',
                    'type' => 'EQ',
                    'value' => true
                )
            ),

            // admin new registration request email template
            array(
                'id' => 'email_admin_new_registration_request_options_title',
                'type' => 'title',
                'label' => __( '[Admin Email] New Registration Request', 'minerva-kb' ),
                'description' => __( 'Configure email that is sent on new user registration requests', 'minerva-kb' )
            ),
            array(
                'id' => 'email_admin_new_registration_request_switch',
                'type' => 'checkbox',
                'label' => __( 'Enable?', 'minerva-kb' ),
                'default' => true,
                'description' => __( 'Admin will receive this email whenever someone registers via support account form on site', 'minerva-kb' ),
            ),
            array(
                'id' => 'email_admin_new_registration_request_subject',
                'type' => 'input',
                'label' => __( 'Email subject', 'minerva-kb' ),
                'default' => __( 'A new user registration request received', 'minerva-kb' ),
                'dependency' => array(
                    'target' => 'email_admin_new_registration_request_switch',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
            array(
                'id' => 'email_admin_new_registration_request_message',
                'type' => 'editor',
                'label' => __( 'Email message', 'minerva-kb' ),
                'default' =>
'<p>A new Support User registration request received on site <strong>{{site_url}}</strong> from user:</p>
<p>First name: <strong>{{user_firstname}}</strong><br>
Last name: <strong>{{user_lastname}}</strong><br>
Email: <strong>{{user_email}}</strong></p>
<p>Follow the link below to approve or deny this request.</p>
{{action_button}}
<p>If you think you have received this email by mistake, please contact the site administrator.</p>
{{action_button_fallback}}',
                'dependency' => array(
                    'target' => 'email_admin_new_registration_request_switch',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
            array(
                'id' => 'email_admin_new_registration_request_action_label',
                'type' => 'input',
                'label' => __( 'Action button label', 'minerva-kb' ),
                'default' => __( 'Open User Profile', 'minerva-kb' ),
                'dependency' => array(
                    'target' => 'email_admin_new_registration_request_switch',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
            array(
                'id' => 'email_admin_new_registration_request_info',
                'type' => 'info',
                'label' => 'To preview this email template in browser <a href="' . get_bloginfo('url') . '?mkb_email_template_preview=1&mkb_email_template_id=' . MKB_Emails::EMAIL_TYPE_ADMIN_NEW_REGISTRATION_REQUEST . '" target="_blank">click here</a>. Don\'t forget to save settings before preview to see your changes.',
                'dependency' => array(
                    'target' => 'email_admin_new_registration_request_switch',
                    'type' => 'EQ',
                    'value' => true
                )
            ),

            // agent ticket assigned email template
            array(
                'id' => 'email_agent_ticket_assigned_options_title',
                'type' => 'title',
                'label' => __( '[Agent Email] Ticket Assigned', 'minerva-kb' ),
                'description' => __( 'Configure email that is sent on ticket assignment', 'minerva-kb' )
            ),
            array(
                'id' => 'email_agent_ticket_assigned_switch',
                'type' => 'checkbox',
                'label' => __( 'Enable?', 'minerva-kb' ),
                'default' => true,
                'description' => __( 'Agent will receive this email whenever a ticket is assigned', 'minerva-kb' ),
            ),
            array(
                'id' => 'email_agent_ticket_assigned_subject',
                'type' => 'input',
                'label' => __( 'Email subject', 'minerva-kb' ),
                'default' => __( 'A ticket has been assigned to you', 'minerva-kb' ),
                'dependency' => array(
                    'target' => 'email_agent_ticket_assigned_switch',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
            array(
                'id' => 'email_agent_ticket_assigned_message',
                'type' => 'editor',
                'label' => __( 'Email message', 'minerva-kb' ),
                'default' =>
'<h1>Hi, {{agent_firstname}}!</h1>
<p>Support ticket <strong>{{ticket_title}}</strong> has been assigned to you. Follow the link below to view it.</p>
{{action_button}}
<p>If you think you have received this email by mistake, please contact the site administrator.</p>
{{action_button_fallback}}',
                'dependency' => array(
                    'target' => 'email_agent_ticket_assigned_switch',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
            array(
                'id' => 'email_agent_ticket_assigned_action_label',
                'type' => 'input',
                'label' => __( 'Action button label', 'minerva-kb' ),
                'default' => __( 'View Ticket', 'minerva-kb' ),
                'dependency' => array(
                    'target' => 'email_agent_ticket_assigned_switch',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
            array(
                'id' => 'email_agent_ticket_assigned_info',
                'type' => 'info',
                'label' => 'To preview this email template in browser <a href="' . get_bloginfo('url') . '?mkb_email_template_preview=1&mkb_email_template_id=' . MKB_Emails::EMAIL_TYPE_AGENT_TICKET_ASSIGNED . '" target="_blank">click here</a>. Don\'t forget to save settings before preview to see your changes.',
                'dependency' => array(
                    'target' => 'email_agent_ticket_assigned_switch',
                    'type' => 'EQ',
                    'value' => true
                )
            ),

            // agent ticket reply added email template
            array(
                'id' => 'email_agent_ticket_reply_added_options_title',
                'type' => 'title',
                'label' => __( '[Agent Email] Ticket Reply Added', 'minerva-kb' ),
                'description' => __( 'Configure email that is sent to agent when customer adds a new reply to ticket', 'minerva-kb' )
            ),
            array(
                'id' => 'email_agent_ticket_reply_added_switch',
                'type' => 'checkbox',
                'label' => __( 'Enable?', 'minerva-kb' ),
                'default' => true,
                'description' => __( 'Agent will receive this email whenever a reply is added', 'minerva-kb' ),
            ),
            array(
                'id' => 'email_agent_ticket_reply_added_subject',
                'type' => 'input',
                'label' => __( 'Email subject', 'minerva-kb' ),
                'default' => __( 'A new reply has been added to your ticket', 'minerva-kb' ),
                'dependency' => array(
                    'target' => 'email_agent_ticket_reply_added_switch',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
            array(
                'id' => 'email_agent_ticket_reply_added_message',
                'type' => 'editor',
                'label' => __( 'Email message', 'minerva-kb' ),
                'default' =>
'<h1>Hi, {{agent_firstname}}!</h1>
<p>A new reply has been added by customer to support ticket <strong>{{ticket_title}}</strong> assigned to you. Follow the link below to view the ticket.</p>
{{action_button}}
<p>If you think you have received this email by mistake, please contact the site administrator.</p>
{{action_button_fallback}}',
                'dependency' => array(
                    'target' => 'email_agent_ticket_reply_added_switch',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
            array(
                'id' => 'email_agent_ticket_reply_added_action_label',
                'type' => 'input',
                'label' => __( 'Action button label', 'minerva-kb' ),
                'default' => __( 'View Ticket', 'minerva-kb' ),
                'dependency' => array(
                    'target' => 'email_agent_ticket_reply_added_switch',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
            array(
                'id' => 'email_agent_ticket_reply_added_info',
                'type' => 'info',
                'label' => 'To preview this email template in browser <a href="' . get_bloginfo('url') . '?mkb_email_template_preview=1&mkb_email_template_id=' . MKB_Emails::EMAIL_TYPE_AGENT_TICKET_REPLY_ADDED . '" target="_blank">click here</a>. Don\'t forget to save settings before preview to see your changes.',
                'dependency' => array(
                    'target' => 'email_agent_ticket_reply_added_switch',
                    'type' => 'EQ',
                    'value' => true
                )
            ),

            // agent ticket closed email template
            array(
                'id' => 'email_agent_ticket_closed_options_title',
                'type' => 'title',
                'label' => __( '[Agent Email] Ticket Closed', 'minerva-kb' ),
                'description' => __( 'Configure email that is sent to agent when the ticket is closed', 'minerva-kb' )
            ),
            array(
                'id' => 'email_agent_ticket_closed_switch',
                'type' => 'checkbox',
                'label' => __( 'Enable?', 'minerva-kb' ),
                'default' => true,
                'description' => __( 'Agent will receive this email whenever a ticket assigned to him is closed', 'minerva-kb' ),
            ),
            array(
                'id' => 'email_agent_ticket_closed_subject',
                'type' => 'input',
                'label' => __( 'Email subject', 'minerva-kb' ),
                'default' => __( 'The ticket assigned to you has been closed', 'minerva-kb' ),
                'dependency' => array(
                    'target' => 'email_agent_ticket_closed_switch',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
            array(
                'id' => 'email_agent_ticket_closed_message',
                'type' => 'editor',
                'label' => __( 'Email message', 'minerva-kb' ),
                'default' =>
'<h1>Hi, {{agent_firstname}}!</h1>
<p>Support ticket <strong>{{ticket_title}}</strong> assigned to you has been closed. Follow the link below if you need to review the ticket or perform any additional actions.</p>
{{action_button}}
<p>If you think you have received this email by mistake, please contact the site administrator.</p>
{{action_button_fallback}}',
                'dependency' => array(
                    'target' => 'email_agent_ticket_closed_switch',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
            array(
                'id' => 'email_agent_ticket_closed_action_label',
                'type' => 'input',
                'label' => __( 'Action button label', 'minerva-kb' ),
                'default' => __( 'View Ticket', 'minerva-kb' ),
                'dependency' => array(
                    'target' => 'email_agent_ticket_closed_switch',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
            array(
                'id' => 'email_agent_ticket_closed_info',
                'type' => 'info',
                'label' => 'To preview this email template in browser <a href="' . get_bloginfo('url') . '?mkb_email_template_preview=1&mkb_email_template_id=' . MKB_Emails::EMAIL_TYPE_AGENT_TICKET_CLOSED . '" target="_blank">click here</a>. Don\'t forget to save settings before preview to see your changes.',
                'dependency' => array(
                    'target' => 'email_agent_ticket_closed_switch',
                    'type' => 'EQ',
                    'value' => true
                )
            ),

            // agent ticket reopened email template
            array(
                'id' => 'email_agent_ticket_reopened_options_title',
                'type' => 'title',
                'label' => __( '[Agent Email] Ticket Reopened', 'minerva-kb' ),
                'description' => __( 'Configure email that is sent to agent when the ticket is reopened', 'minerva-kb' )
            ),
            array(
                'id' => 'email_agent_ticket_reopened_switch',
                'type' => 'checkbox',
                'label' => __( 'Enable?', 'minerva-kb' ),
                'default' => true,
                'description' => __( 'Agent will receive this email whenever a ticket assigned to him is reopened', 'minerva-kb' ),
            ),
            array(
                'id' => 'email_agent_ticket_reopened_subject',
                'type' => 'input',
                'label' => __( 'Email subject', 'minerva-kb' ),
                'default' => __( 'The ticket assigned to you has been reopened', 'minerva-kb' ),
                'dependency' => array(
                    'target' => 'email_agent_ticket_reopened_switch',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
            array(
                'id' => 'email_agent_ticket_reopened_message',
                'type' => 'editor',
                'label' => __( 'Email message', 'minerva-kb' ),
                'default' =>
'<h1>Hi, {{agent_firstname}}!</h1>
<p>Support ticket <strong>{{ticket_title}}</strong> assigned to you has been reopened. Follow the link below to view it.</p>
{{action_button}}
<p>If you think you have received this email by mistake, please contact the site administrator.</p>
{{action_button_fallback}}',
                'dependency' => array(
                    'target' => 'email_agent_ticket_reopened_switch',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
            array(
                'id' => 'email_agent_ticket_reopened_action_label',
                'type' => 'input',
                'label' => __( 'Action button label', 'minerva-kb' ),
                'default' => __( 'View Ticket', 'minerva-kb' ),
                'dependency' => array(
                    'target' => 'email_agent_ticket_reopened_switch',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
            array(
                'id' => 'email_agent_ticket_reopened_info',
                'type' => 'info',
                'label' => 'To preview this email template in browser <a href="' . get_bloginfo('url') . '?mkb_email_template_preview=1&mkb_email_template_id=' . MKB_Emails::EMAIL_TYPE_AGENT_TICKET_REOPENED . '" target="_blank">click here</a>. Don\'t forget to save settings before preview to see your changes.',
                'dependency' => array(
                    'target' => 'email_agent_ticket_reopened_switch',
                    'type' => 'EQ',
                    'value' => true
                )
            ),

            // guest ticket created email template
            array(
                'id' => 'email_guest_ticket_created_options_title',
                'type' => 'title',
                'label' => __( '[Guest Email] Ticket Created', 'minerva-kb' ),
                'description' => __( 'Configure email that is sent to guest when he opens a ticket', 'minerva-kb' )
            ),
            array(
                'id' => 'email_guest_ticket_created_switch',
                'type' => 'checkbox',
                'label' => __( 'Enable?', 'minerva-kb' ),
                'default' => true,
                'description' => __( 'Guest will receive this email whenever his ticket is created', 'minerva-kb' ),
            ),
            array(
                'id' => 'email_guest_ticket_created_subject',
                'type' => 'input',
                'label' => __( 'Email subject', 'minerva-kb' ),
                'default' => __( 'Your support ticket has been created', 'minerva-kb' ),
                'dependency' => array(
                    'target' => 'email_guest_ticket_created_switch',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
            array(
                'id' => 'email_guest_ticket_created_message',
                'type' => 'editor',
                'label' => __( 'Email message', 'minerva-kb' ),
                'default' =>
'<h1>Hi, {{guest_firstname}}!</h1>
<p>Your support ticket <strong>{{ticket_title}}</strong> has been registered. Our support staff will contact you soon to resolve your problem.</p>
<p>Please, don\'t share the ticket link online, anyone with this link will be able to reply to and manage your ticket on your behalf.</p>
{{action_button}}
{{action_button_fallback}}',
                'dependency' => array(
                    'target' => 'email_guest_ticket_created_switch',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
            array(
                'id' => 'email_guest_ticket_created_action_label',
                'type' => 'input',
                'label' => __( 'Action button label', 'minerva-kb' ),
                'default' => __( 'View Ticket', 'minerva-kb' ),
                'dependency' => array(
                    'target' => 'email_guest_ticket_created_switch',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
            array(
                'id' => 'email_guest_ticket_created_info',
                'type' => 'info',
                'label' => 'To preview this email template in browser <a href="' . get_bloginfo('url') . '?mkb_email_template_preview=1&mkb_email_template_id=' . MKB_Emails::EMAIL_TYPE_GUEST_TICKET_CREATED . '" target="_blank">click here</a>. Don\'t forget to save settings before preview to see your changes.',
                'dependency' => array(
                    'target' => 'email_guest_ticket_created_switch',
                    'type' => 'EQ',
                    'value' => true
                )
            ),

            // guest ticket reply added email template
            array(
                'id' => 'email_guest_ticket_reply_added_options_title',
                'type' => 'title',
                'label' => __( '[Guest Email] Ticket Reply Added', 'minerva-kb' ),
                'description' => __( 'Configure email that is sent to guest when a reply is added to his ticket', 'minerva-kb' )
            ),
            array(
                'id' => 'email_guest_ticket_reply_added_switch',
                'type' => 'checkbox',
                'label' => __( 'Enable?', 'minerva-kb' ),
                'default' => true,
                'description' => __( 'Guest will receive this email whenever his ticket receives a reply', 'minerva-kb' ),
            ),
            array(
                'id' => 'email_guest_ticket_reply_added_subject',
                'type' => 'input',
                'label' => __( 'Email subject', 'minerva-kb' ),
                'default' => __( 'A new reply has been added to your ticket', 'minerva-kb' ),
                'dependency' => array(
                    'target' => 'email_guest_ticket_reply_added_switch',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
            array(
                'id' => 'email_guest_ticket_reply_added_message',
                'type' => 'editor',
                'label' => __( 'Email message', 'minerva-kb' ),
                'default' =>
'<h1>Hi, {{guest_firstname}}!</h1>
<p>A new reply has been added by support staff to your ticket <strong>{{ticket_title}}</strong>. Follow the link below to read it.</p>
{{action_button}}
{{action_button_fallback}}',
                'dependency' => array(
                    'target' => 'email_guest_ticket_reply_added_switch',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
            array(
                'id' => 'email_guest_ticket_reply_added_action_label',
                'type' => 'input',
                'label' => __( 'Action button label', 'minerva-kb' ),
                'default' => __( 'View Ticket', 'minerva-kb' ),
                'dependency' => array(
                    'target' => 'email_guest_ticket_reply_added_switch',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
            array(
                'id' => 'email_guest_ticket_reply_added_info',
                'type' => 'info',
                'label' => 'To preview this email template in browser <a href="' . get_bloginfo('url') . '?mkb_email_template_preview=1&mkb_email_template_id=' . MKB_Emails::EMAIL_TYPE_GUEST_TICKET_REPLY_ADDED . '" target="_blank">click here</a>. Don\'t forget to save settings before preview to see your changes.',
                'dependency' => array(
                    'target' => 'email_guest_ticket_reply_added_switch',
                    'type' => 'EQ',
                    'value' => true
                )
            ),

            // guest ticket closed email template
            array(
                'id' => 'email_guest_ticket_closed_options_title',
                'type' => 'title',
                'label' => __( '[Guest Email] Ticket Closed', 'minerva-kb' ),
                'description' => __( 'Configure email that is sent to guest when the ticket is closed', 'minerva-kb' )
            ),
            array(
                'id' => 'email_guest_ticket_closed_switch',
                'type' => 'checkbox',
                'label' => __( 'Enable?', 'minerva-kb' ),
                'default' => true,
                'description' => __( 'Guest will receive this email whenever his ticket is closed', 'minerva-kb' ),
            ),
            array(
                'id' => 'email_guest_ticket_closed_subject',
                'type' => 'input',
                'label' => __( 'Email subject', 'minerva-kb' ),
                'default' => __( 'Your support ticket has been closed', 'minerva-kb' ),
                'dependency' => array(
                    'target' => 'email_guest_ticket_closed_switch',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
            array(
                'id' => 'email_guest_ticket_closed_message',
                'type' => 'editor',
                'label' => __( 'Email message', 'minerva-kb' ),
                'default' =>
'<h1>Hi, {{guest_firstname}}!</h1>
<p>Your support ticket <strong>{{ticket_title}}</strong> has been closed. If your problem was not solved, feel free to reopen the ticket.</p>
{{action_button}}
{{action_button_fallback}}',
                'dependency' => array(
                    'target' => 'email_guest_ticket_closed_switch',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
            array(
                'id' => 'email_guest_ticket_closed_action_label',
                'type' => 'input',
                'label' => __( 'Action button label', 'minerva-kb' ),
                'default' => __( 'View Ticket', 'minerva-kb' ),
                'dependency' => array(
                    'target' => 'email_guest_ticket_closed_switch',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
            array(
                'id' => 'email_guest_ticket_closed_info',
                'type' => 'info',
                'label' => 'To preview this email template in browser <a href="' . get_bloginfo('url') . '?mkb_email_template_preview=1&mkb_email_template_id=' . MKB_Emails::EMAIL_TYPE_GUEST_TICKET_CLOSED . '" target="_blank">click here</a>. Don\'t forget to save settings before preview to see your changes.',
                'dependency' => array(
                    'target' => 'email_guest_ticket_closed_switch',
                    'type' => 'EQ',
                    'value' => true
                )
            ),

            // user ticket created email template
            array(
                'id' => 'email_user_ticket_created_options_title',
                'type' => 'title',
                'label' => __( '[User Email] Ticket Created', 'minerva-kb' ),
                'description' => __( 'Configure email that is sent to user when he opens a ticket', 'minerva-kb' )
            ),
            array(
                'id' => 'email_user_ticket_created_switch',
                'type' => 'checkbox',
                'label' => __( 'Enable?', 'minerva-kb' ),
                'default' => true,
                'description' => __( 'User will receive this email whenever his ticket is created', 'minerva-kb' ),
            ),
            array(
                'id' => 'email_user_ticket_created_subject',
                'type' => 'input',
                'label' => __( 'Email subject', 'minerva-kb' ),
                'default' => __( 'Your support ticket has been created', 'minerva-kb' ),
                'dependency' => array(
                    'target' => 'email_user_ticket_created_switch',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
            array(
                'id' => 'email_user_ticket_created_message',
                'type' => 'editor',
                'label' => __( 'Email message', 'minerva-kb' ),
                'default' =>
'<h1>Hi, {{user_firstname}}!</h1>
<p>Your support ticket <strong>{{ticket_title}}</strong> has been registered. Our support staff will contact you soon to resolve your problem.</p>
{{action_button}}
{{action_button_fallback}}',
                'dependency' => array(
                    'target' => 'email_user_ticket_created_switch',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
            array(
                'id' => 'email_user_ticket_created_action_label',
                'type' => 'input',
                'label' => __( 'Action button label', 'minerva-kb' ),
                'default' => __( 'View Ticket', 'minerva-kb' ),
                'dependency' => array(
                    'target' => 'email_user_ticket_created_switch',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
            array(
                'id' => 'email_user_ticket_created_info',
                'type' => 'info',
                'label' => 'To preview this email template in browser <a href="' . get_bloginfo('url') . '?mkb_email_template_preview=1&mkb_email_template_id=' . MKB_Emails::EMAIL_TYPE_USER_TICKET_CREATED . '" target="_blank">click here</a>. Don\'t forget to save settings before preview to see your changes.',
                'dependency' => array(
                    'target' => 'email_user_ticket_created_switch',
                    'type' => 'EQ',
                    'value' => true
                )
            ),

            // user ticket reply added email template
            array(
                'id' => 'email_user_ticket_reply_added_options_title',
                'type' => 'title',
                'label' => __( '[User Email] Ticket Reply Added', 'minerva-kb' ),
                'description' => __( 'Configure email that is sent to user when a reply is added to his ticket', 'minerva-kb' )
            ),
            array(
                'id' => 'email_user_ticket_reply_added_switch',
                'type' => 'checkbox',
                'label' => __( 'Enable?', 'minerva-kb' ),
                'default' => true,
                'description' => __( 'User will receive this email whenever his ticket receives a reply', 'minerva-kb' ),
            ),
            array(
                'id' => 'email_user_ticket_reply_added_subject',
                'type' => 'input',
                'label' => __( 'Email subject', 'minerva-kb' ),
                'default' => __( 'A new reply has been added to your ticket', 'minerva-kb' ),
                'dependency' => array(
                    'target' => 'email_user_ticket_reply_added_switch',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
            array(
                'id' => 'email_user_ticket_reply_added_message',
                'type' => 'editor',
                'label' => __( 'Email message', 'minerva-kb' ),
                'default' =>
'<h1>Hi, {{user_firstname}}!</h1>
<p>A new reply has been added by support staff to your ticket <strong>{{ticket_title}}</strong>. Follow the link below to read it.</p>
{{action_button}}
{{action_button_fallback}}',
                'dependency' => array(
                    'target' => 'email_user_ticket_reply_added_switch',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
            array(
                'id' => 'email_user_ticket_reply_added_action_label',
                'type' => 'input',
                'label' => __( 'Action button label', 'minerva-kb' ),
                'default' => __( 'View Ticket', 'minerva-kb' ),
                'dependency' => array(
                    'target' => 'email_user_ticket_reply_added_switch',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
            array(
                'id' => 'email_user_ticket_reply_added_info',
                'type' => 'info',
                'label' => 'To preview this email template in browser <a href="' . get_bloginfo('url') . '?mkb_email_template_preview=1&mkb_email_template_id=' . MKB_Emails::EMAIL_TYPE_USER_TICKET_REPLY_ADDED . '" target="_blank">click here</a>. Don\'t forget to save settings before preview to see your changes.',
                'dependency' => array(
                    'target' => 'email_user_ticket_reply_added_switch',
                    'type' => 'EQ',
                    'value' => true
                )
            ),

            // user ticket closed email template
            array(
                'id' => 'email_user_ticket_closed_options_title',
                'type' => 'title',
                'label' => __( '[User Email] Ticket Closed', 'minerva-kb' ),
                'description' => __( 'Configure email that is sent to user when the ticket is closed', 'minerva-kb' )
            ),
            array(
                'id' => 'email_user_ticket_closed_switch',
                'type' => 'checkbox',
                'label' => __( 'Enable?', 'minerva-kb' ),
                'default' => true,
                'description' => __( 'User will receive this email whenever his ticket is closed', 'minerva-kb' ),
            ),
            array(
                'id' => 'email_user_ticket_closed_subject',
                'type' => 'input',
                'label' => __( 'Email subject', 'minerva-kb' ),
                'default' => __( 'Your support ticket has been closed', 'minerva-kb' ),
                'dependency' => array(
                    'target' => 'email_user_ticket_closed_switch',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
            array(
                'id' => 'email_user_ticket_closed_message',
                'type' => 'editor',
                'label' => __( 'Email message', 'minerva-kb' ),
                'default' =>
'<h1>Hi, {{user_firstname}}!</h1>
<p>Your support ticket <strong>{{ticket_title}}</strong> has been closed. If your problem was not solved, feel free to reopen the ticket.</p>
{{action_button}}
{{action_button_fallback}}',
                'dependency' => array(
                    'target' => 'email_user_ticket_closed_switch',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
            array(
                'id' => 'email_user_ticket_closed_action_label',
                'type' => 'input',
                'label' => __( 'Action button label', 'minerva-kb' ),
                'default' => __( 'View Ticket', 'minerva-kb' ),
                'dependency' => array(
                    'target' => 'email_user_ticket_closed_switch',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
            array(
                'id' => 'email_user_ticket_closed_info',
                'type' => 'info',
                'label' => 'To preview this email template in browser <a href="' . get_bloginfo('url') . '?mkb_email_template_preview=1&mkb_email_template_id=' . MKB_Emails::EMAIL_TYPE_USER_TICKET_CLOSED . '" target="_blank">click here</a>. Don\'t forget to save settings before preview to see your changes.',
                'dependency' => array(
                    'target' => 'email_user_ticket_closed_switch',
                    'type' => 'EQ',
                    'value' => true
                )
            ),

            // user registration received email template
            array(
                'id' => 'email_user_registration_received_options_title',
                'type' => 'title',
                'label' => __( '[User Email] Registration Request Received', 'minerva-kb' ),
                'description' => __( 'Configure email that is sent to user when he sends a registration request', 'minerva-kb' )
            ),
            array(
                'id' => 'email_user_registration_received_switch',
                'type' => 'checkbox',
                'label' => __( 'Enable?', 'minerva-kb' ),
                'default' => true,
                'description' => __( 'User will receive this email whenever he tries to register', 'minerva-kb' ),
            ),
            array(
                'id' => 'email_user_registration_received_subject',
                'type' => 'input',
                'label' => __( 'Email subject', 'minerva-kb' ),
                'default' => __( 'Your support account registration request has been received', 'minerva-kb' ),
                'dependency' => array(
                    'target' => 'email_user_registration_received_switch',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
            array(
                'id' => 'email_user_registration_received_message',
                'type' => 'editor',
                'label' => __( 'Email message', 'minerva-kb' ),
                'default' =>
'<h1>Hi, {{user_firstname}}!</h1>
<p>We received your registration request on site <strong>{{site_url}}</strong>. Site administrator will process it soon.</p>
<p>If you think you have received this email by mistake, please contact the site administrator.</p>',
                'dependency' => array(
                    'target' => 'email_user_registration_received_switch',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
            array(
                'id' => 'email_user_registration_received_info',
                'type' => 'info',
                'label' => 'To preview this email template in browser <a href="' . get_bloginfo('url') . '?mkb_email_template_preview=1&mkb_email_template_id=' . MKB_Emails::EMAIL_TYPE_USER_REGISTRATION_RECEIVED . '" target="_blank">click here</a>. Don\'t forget to save settings before preview to see your changes.',
                'dependency' => array(
                    'target' => 'email_user_registration_received_switch',
                    'type' => 'EQ',
                    'value' => true
                )
            ),

            // user registration approved email template
            array(
                'id' => 'email_user_registration_approved_options_title',
                'type' => 'title',
                'label' => __( '[User Email] Registration Request Approved', 'minerva-kb' ),
                'description' => __( 'Configure email that is sent to user when his registration is approved', 'minerva-kb' )
            ),
            array(
                'id' => 'email_user_registration_approved_switch',
                'type' => 'checkbox',
                'label' => __( 'Enable?', 'minerva-kb' ),
                'default' => true,
                'description' => __( 'User will receive this email if site admin approves his registration', 'minerva-kb' ),
            ),
            array(
                'id' => 'email_user_registration_approved_subject',
                'type' => 'input',
                'label' => __( 'Email subject', 'minerva-kb' ),
                'default' => __( 'Your support account registration request has been approved', 'minerva-kb' ),
                'dependency' => array(
                    'target' => 'email_user_registration_approved_switch',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
            // TODO: what is the action?
            array(
                'id' => 'email_user_registration_approved_message',
                'type' => 'editor',
                'label' => __( 'Email message', 'minerva-kb' ),
                'default' =>
'<h1>Hi, {{user_firstname}}!</h1>
<p>Your registration request on site <strong>{{site_url}}</strong> has been approved. Now you can login and create support tickets.</p>
<p>If you think you have received this email by mistake, please contact the site administrator.</p>',
                'dependency' => array(
                    'target' => 'email_user_registration_approved_switch',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
            array(
                'id' => 'email_user_registration_approved_info',
                'type' => 'info',
                'label' => 'To preview this email template in browser <a href="' . get_bloginfo('url') . '?mkb_email_template_preview=1&mkb_email_template_id=' . MKB_Emails::EMAIL_TYPE_USER_REGISTRATION_APPROVED . '" target="_blank">click here</a>. Don\'t forget to save settings before preview to see your changes.',
                'dependency' => array(
                    'target' => 'email_user_registration_approved_switch',
                    'type' => 'EQ',
                    'value' => true
                )
            ),

            // user registration denied email template
            array(
                'id' => 'email_user_registration_denied_options_title',
                'type' => 'title',
                'label' => __( '[User Email] Registration Request Denied', 'minerva-kb' ),
                'description' => __( 'Configure email that is sent to user when his registration is denied', 'minerva-kb' )
            ),
            array(
                'id' => 'email_user_registration_denied_switch',
                'type' => 'checkbox',
                'label' => __( 'Enable?', 'minerva-kb' ),
                'default' => true,
                'description' => __( 'User will receive this email if site admin denies his registration', 'minerva-kb' ),
            ),
            array(
                'id' => 'email_user_registration_denied_subject',
                'type' => 'input',
                'label' => __( 'Email subject', 'minerva-kb' ),
                'default' => __( 'Your support account registration request has been denied', 'minerva-kb' ),
                'dependency' => array(
                    'target' => 'email_user_registration_denied_switch',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
            array(
                'id' => 'email_user_registration_denied_message',
                'type' => 'editor',
                'label' => __( 'Email message', 'minerva-kb' ),
                'default' =>
'<h1>Hi, {{user_firstname}}!</h1>
<p>Unfortunately, your registration request on site <strong>{{site_url}}</strong> has not been approved by administrator.</p>
<p>If you think you have received this email by mistake, please contact the site administrator.</p>',
                'dependency' => array(
                    'target' => 'email_user_registration_denied_switch',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
            array(
                'id' => 'email_user_registration_denied_info',
                'type' => 'info',
                'label' => 'To preview this email template in browser <a href="' . get_bloginfo('url') . '?mkb_email_template_preview=1&mkb_email_template_id=' . MKB_Emails::EMAIL_TYPE_USER_REGISTRATION_DENIED . '" target="_blank">click here</a>. Don\'t forget to save settings before preview to see your changes.',
                'dependency' => array(
                    'target' => 'email_user_registration_denied_switch',
                    'type' => 'EQ',
                    'value' => true
                )
            ),

            /**
             * Tickets
             */
            array(
                'id' => 'tickets_tab',
                'type' => 'tab',
                'label' => 'Tickets <span class="mkb-tab-label" style="background: darkorange; color: white;">beta</span>',
                'icon' => 'fa-life-ring'
            ),
            array(
                'id' => 'tickets_alpha_info',
                'type' => 'info',
                'label' => 'Please note, we don\'t recommend yet to use tickets for production sites, as they are still being actively developed. Things like client-side layout and styles may change without warning. Feel free to try it and send any feature requests to <a href="mailto:konstrukteam@gmail.com?subject=MinervaKB Tickets" target="_blank">konstrukteam@gmail.com</a>',
            ),
            array(
                'id' => 'tickets_disable_tickets',
                'type' => 'checkbox',
                'label' => __( 'Disable tickets?', 'minerva-kb' ),
                'default' => false
            ),
            /**
             * Tax defaults
             */
            array(
                'id' => 'tickets_defaults_title',
                'type' => 'title',
                'label' => __( 'New Ticket defaults', 'minerva-kb' ),
                'description' => __( 'Configure default taxonomies for the new tickets', 'minerva-kb' )
            ),
            array(
                'id' => 'tickets_default_type',
                'type' => 'select',
                'label' => __( 'Select default type for new tickets', 'minerva-kb' ),
                'options' => self::get_tax_term_options('mkb_ticket_type'),
                'description' => __( 'You can set default value so that all new ticket have type value', 'minerva-kb' )
            ),
            array(
                'id' => 'tickets_default_priority',
                'type' => 'select',
                'label' => __( 'Select default priority for new tickets', 'minerva-kb' ),
                'options' => self::get_tax_term_options('mkb_ticket_priority'),
                'description' => __( 'You can set default value so that all new ticket have priority value', 'minerva-kb' )
            ),
            array(
                'id' => 'tickets_default_product',
                'type' => 'select',
                'label' => __( 'Select default product for new tickets', 'minerva-kb' ),
                'options' => self::get_tax_term_options('mkb_ticket_product'),
                'description' => __( 'You can set default value so that all new ticket have product value', 'minerva-kb' )
            ),
            array(
                'id' => 'tickets_default_department',
                'type' => 'select',
                'label' => __( 'Select default department for new tickets', 'minerva-kb' ),
                'options' => self::get_tax_term_options('mkb_ticket_department'),
                'description' => __( 'You can set default value so that all new ticket have department value', 'minerva-kb' )
            ),
            /**
             * Ticket assignment mode
             */
            array(
                'id' => 'tickets_assignment_mode',
                'type' => 'select',
                'label' => __( 'Ticket assignment mode', 'minerva-kb' ),
                'options' => array(
                    '' => __( 'None', 'minerva-kb' ),
                    'user' => __( 'Assign to user', 'minerva-kb' )
                ),
                'default' => '',
                'description' => __( 'Select ticket auto-assignment mode', 'minerva-kb' )
            ),
            array(
                'id' => 'tickets_default_assignee',
                'type' => 'select',
                'label' => __( 'Default ticket assignee', 'minerva-kb' ),
                'options' => self::get_user_options(['administrator', 'mkb_support_agent']),
                'description' => __( 'Select default ticket assignee', 'minerva-kb' ),
                'dependency' => array(
                    'target' => 'tickets_assignment_mode',
                    'type' => 'EQ',
                    'value' => 'user'
                ),
            ),

            /**
             * Ticket IDs
             */
            array(
                'id' => 'tickets_ids_config_title',
                'type' => 'title',
                'label' => __( 'Ticket IDs', 'minerva-kb' ),
                'description' => __( 'Configure ticket IDs mode', 'minerva-kb' )
            ),
            array(
                'id' => 'tickets_use_custom_ids',
                'type' => 'checkbox',
                'label' => __( 'Use custom ticket IDs', 'minerva-kb' ),
                'description' => __( 'By default, WordPress post IDs are used', 'minerva-kb' ),
                'default' => false
            ),
            array(
                'id' => 'tickets_custom_ids_start_from',
                'type' => 'tickets_id_tool',
                'label' => __( 'First ticket ID', 'minerva-kb' ),
                'description' => __( 'When you reset the IDs, all existing tickets (including closed and in trash) will be assigned numbers starting from the above value', 'minerva-kb' ),
                'default' => 1,
                'dependency' => array(
                    'target' => 'tickets_use_custom_ids',
                    'type' => 'EQ',
                    'value' => true
                ),
            ),

            /**
             * Tickets register / login flow
             */
            array(
                'id' => 'tickets_permissions_title',
                'type' => 'title',
                'label' => __( 'User Permissions', 'minerva-kb' ),
                'description' => __( 'Configure user permissions for ticket management', 'minerva-kb' )
            ),
            array(
                'id' => 'tickets_allow_guest_tickets',
                'type' => 'checkbox',
                'label' => __( 'Allow guests to open tickets?', 'minerva-kb' ),
                'default' => false
            ),
            array(
                'id' => 'tickets_allow_user_tickets',
                'type' => 'checkbox',
                'label' => __( 'Allow logged-in users to open tickets?', 'minerva-kb' ),
                'default' => false
            ),
            // TODO: use filter also
            array(
                'id' => 'tickets_users_mode',
                'type' => 'select',
                'label' => __( 'Select which users can open support tickets', 'minerva-kb' ),
                'options' => array(
                    'minerva' => __( 'Users with Minerva Support User role (default)', 'minerva-kb' ),
                    'roles' => __( 'Let me select roles', 'minerva-kb' ),
                    //'cap' => __( 'Use capability', 'minerva-kb' ), PII, on request
//                    'meta' => __( 'Users with specific meta field value', 'minerva-kb' ) // TODO: PII, investigate
                ),
                'default' => 'minerva',
                'description' => __( 'You can use existing users, such as WooCommerce customers, or allow registration via ticket system', 'minerva-kb' )
            ),
            array(
                'id' => 'tickets_users_roles',
                'type' => 'roles_select',
                'label' => __( 'Select which roles can submit tickets as customers', 'minerva-kb' ),
                'default' => '[]',
                'flush' => false,
                'view_log' => false,
                'no_guest' => true,
                'no_toggle_all' => true,
                'dependency' => array(
                    'target' => 'tickets_users_mode',
                    'type' => 'EQ',
                    'value' => 'roles'
                ),
                'description' => __( 'You can use multiple roles as well', 'minerva-kb' ),
            ),
            array(
                'id' => 'tickets_redirect_support_user_from_admin',
                'type' => 'checkbox',
                'label' => __( 'Redirect users with Minerva Support User role from admin?', 'minerva-kb' ),
                'default' => true
            ),
            array(
                'id' => 'tickets_redirect_support_user_from_admin_page',
                'type' => 'page_select',
                'label' => __( '(Optional) Select page where support users should be redirected', 'minerva-kb' ),
                'options' => self::get_pages_options(),
                'default' => '',
                'description' => __( 'Don\'t forget to save settings before page preview', 'minerva-kb' )
            ),
            array(
                'id' => 'tickets_redirect_support_user_after_logout_page',
                'type' => 'page_select',
                'label' => __( '(Optional) Select page where support users should be redirected after logout', 'minerva-kb' ),
                'options' => self::get_pages_options(),
                'default' => '',
                'description' => __( 'Don\'t forget to save settings before page preview', 'minerva-kb' )
            ),
            array(
                'id' => 'tickets_hide_admin_bar_for_support_user',
                'type' => 'checkbox',
                'label' => __( 'Hide admin bar for users with Minerva Support User role?', 'minerva-kb' ),
                'default' => true
            ),
            array(
                'id' => 'tickets_allow_user_close',
                'type' => 'checkbox',
                'label' => __( 'Allow customers to close their tickets?', 'minerva-kb' ),
                'default' => true
            ),
            array(
                'id' => 'tickets_allow_user_reopen',
                'type' => 'checkbox',
                'label' => __( 'Allow customers to reopen their closed tickets?', 'minerva-kb' ),
                'default' => true
            ),

            /**
             * Registration
             */
            array(
                'id' => 'tickets_user_registration_title',
                'type' => 'title',
                'label' => __( 'Support Users Registration', 'minerva-kb' ),
                'description' => __( 'Configure new support users registration', 'minerva-kb' )
            ),
            array(
                'id' => 'tickets_allow_users_registration',
                'type' => 'checkbox',
                'label' => __( 'Allow Support Users registration?', 'minerva-kb' ),
                'default' => false
            ),
            array(
                'id' => 'tickets_register_info',
                'type' => 'info',
                'label' => 'In order to allow new support users to register via plugin, you need to add [mkb-login-register-form] at some page and allow registration in plugin settings. You can edit this and other forms in MinervaKB - Form Editor.',
            ),
            array(
                'id' => 'tickets_require_admin_approve_for_new_users',
                'type' => 'checkbox',
                'label' => __( 'All new support users must be approved by site admin', 'minerva-kb' ),
                'default' => true,
                'description' => __( 'When enabled, support user will be registered without roles or permissions. Site admin will receive a notification via email and will need to approve or delete the user', 'minerva-kb' )
            ),
            array(
                'id' => 'tickets_login_register_form_mode',
                'type' => 'select',
                'label' => __( 'Select Login/Register form mode', 'minerva-kb' ),
                'options' => array(
                    'login_register' => __( 'Login and Register', 'minerva-kb' ),
                    'login' => __( 'Login only', 'minerva-kb' ),
                    'register' => __( 'Register only', 'minerva-kb' ),
                    'none' => __( 'None (use theme login / register)', 'minerva-kb' )
                ),
                'default' => 'login_register',
                'description' => __( 'Login / Register form can be displayed for guests.', 'minerva-kb' )
            ),
            array(
                'id' => 'tickets_login_form_title',
                'type' => 'input_text',
                'label' => __( 'Login Form heading', 'minerva-kb' ),
                'default' => __( 'Login', 'minerva-kb' ),
            ),
            array(
                'id' => 'tickets_register_form_title',
                'type' => 'input_text',
                'label' => __( 'Register Form heading', 'minerva-kb' ),
                'default' => __( 'Register', 'minerva-kb' ),
            ),
            array(
                'id' => 'tickets_no_login_register_message',
                'type' => 'textarea_text',
                'label' => __( 'Message to display when user needs to login/register elsewhere', 'minerva-kb' ),
                'default' => __('[mkb-info]You need to login or register to create a ticket[/mkb-info]', 'minerva-kb'),
                'dependency' => array(
                    'target' => 'tickets_login_register_form_mode',
                    'type' => 'EQ',
                    'value' => 'none'
                ),
            ),
            /**
             * Create ticket page
             */
            array(
                'id' => 'tickets_create_ticket_title',
                'type' => 'title',
                'label' => __( 'Create Ticket Page', 'minerva-kb' ),
                'description' => __( 'Configure the new tickets page', 'minerva-kb' )
            ),
            array(
                'id' => 'tickets_create_page_info',
                'type' => 'info',
                'label' => 'In order to allow guests or users to create tickets, you need to add [mkb-create-ticket] shortcode at some page, select it as Create Ticket Page and configure the create ticket form settings.</a>',
            ),
            array(
                'id' => 'tickets_create_use_woo_account_tab',
                'type' => 'checkbox',
                'label' => __( 'Use WooCommerce My Account Support tab (when available)?', 'minerva-kb' ),
                'default' => false,
                'description' => __( 'You will need to use [mkb-create-ticket] shortcode instead of [mkb-create-ticket-link] in My account support tab content', 'minerva-kb' )
            ),
            // create page
            array(
                'id' => 'tickets_create_page',
                'type' => 'page_select',
                'label' => __( 'Select create ticket page (page must use [mkb-create-ticket] shortcode)', 'minerva-kb' ),
                'options' => self::get_pages_options(),
                'default' => '',
                'description' => __( 'Don\'t forget to save settings before page preview', 'minerva-kb' )
            ),
            array(
                'id' => 'ticket_create_page_template',
                'type' => 'select',
                'label' => __( 'Which create ticket page template to use?', 'minerva-kb' ),
                'options' => array(
                    'theme' => __( 'Theme page template', 'minerva-kb' ),
                    'plugin' => __( 'Plugin template', 'minerva-kb' )
                ),
                'default' => 'plugin'
            ),
            array(
                'id' => 'create_ticket_sidebar',
                'type' => 'image_select',
                'label' => __( 'Create Ticket page sidebar position', 'minerva-kb' ),
                'options' => array(
                    'none' => array(
                        'label' => __( 'None', 'minerva-kb' ),
                        'img' => MINERVA_KB_IMG_URL . 'no-sidebar.png'
                    ),
                    'left' => array(
                        'label' => __( 'Left', 'minerva-kb' ),
                        'img' => MINERVA_KB_IMG_URL . 'left-sidebar.png'
                    ),
                    'right' => array(
                        'label' => __( 'Right', 'minerva-kb' ),
                        'img' => MINERVA_KB_IMG_URL . 'right-sidebar.png'
                    ),
                ),
                'default' => 'right',
                'dependency' => array(
                    'target' => 'ticket_create_page_template',
                    'type' => 'EQ',
                    'value' => 'plugin'
                ),
                'description' => __( 'You can add widgets to sidebars under Appearance - Widgets', 'minerva-kb' ),
            ),
            array(
                'id' => 'create_ticket_top_padding',
                'type' => 'css_size',
                'label' => __( 'Create ticket page top padding', 'minerva-kb' ),
                'default' => array("unit" => 'em', "size" => "3"),
                'dependency' => array(
                    'target' => 'ticket_create_page_template',
                    'type' => 'EQ',
                    'value' => 'plugin'
                ),
                'description' => __( 'Distance between header and page content', 'minerva-kb' )
            ),
            array(
                'id' => 'create_ticket_bottom_padding',
                'type' => 'css_size',
                'label' => __( 'Create ticket page bottom padding', 'minerva-kb' ),
                'default' => array("unit" => 'em', "size" => "3"),
                'dependency' => array(
                    'target' => 'ticket_create_page_template',
                    'type' => 'EQ',
                    'value' => 'plugin'
                ),
                'description' => __( 'Distance between page content and footer', 'minerva-kb' )
            ),
            /**
             * Create ticket form
             */
            array(
                'id' => 'tickets_create_ticket_form_title',
                'type' => 'title',
                'label' => __( 'Create Ticket Form', 'minerva-kb' ),
                'description' => __( 'Configure the form for opening new tickets', 'minerva-kb' )
            ),
            array(
                'id' => 'tickets_create_ticket_form_info',
                'type' => 'info',
                'label' => 'You can edit labels and fields for this and other forms in MinervaKB - Form Editor',
            ),
            array(
                'id' => 'tickets_create_ticket_form_user_title',
                'type' => 'input_text',
                'label' => __( 'User Create Ticket Form heading', 'minerva-kb' ),
                'default' => 'Open New Ticket', 'minerva-kb',
            ),
            array(
                'id' => 'tickets_create_ticket_form_user_subtitle',
                'type' => 'textarea_text',
                'label' => __( 'User Create Ticket Form subheading', 'minerva-kb' ),
                'default' => '[mkb-info]We’re sorry that you haven’t found a solution in our docs. Please, fill the form to create a ticket.[/mkb-info]',
            ),
            array(
                'id' => 'tickets_create_ticket_form_guest_title',
                'type' => 'input_text',
                'label' => __( 'Guest Create Ticket Form heading', 'minerva-kb' ),
                'default' => 'Open New Guest Ticket', 'minerva-kb',
            ),
            array(
                'id' => 'tickets_create_ticket_form_guest_subtitle',
                'type' => 'textarea_text',
                'label' => __( 'Guest Create Ticket Form subheading', 'minerva-kb' ),
                'default' => '[mkb-info]Please note, you are creating support ticket as guest. You will access ticket by direct link and receive email notifications on new replies.[/mkb-info]',
            ),

            /**
             * Create ticket widget
             */
            array(
                'id' => 'tickets_widgets_title',
                'type' => 'title',
                'label' => __( 'Create Ticket Widgets', 'minerva-kb' ),
                'description' => __( 'Configure the widget for opening new tickets', 'minerva-kb' )
            ),
            array(
                'id' => 'tickets_widgets_create_text',
                'type' => 'textarea_text',
                'label' => __( 'Message to display in create ticket widget', 'minerva-kb' ),
                'default' => __('Still have questions? Feel free to open a support ticket!', 'minerva-kb'),
            ),
            array(
                'id' => 'tickets_widgets_create_link_text',
                'type' => 'input_text',
                'label' => __( 'Create ticket button text', 'minerva-kb' ),
                'default' => __( 'Create a ticket', 'minerva-kb' ),
            ),
            array(
                'id' => 'tickets_article_create_ticket_html',
                'type' => 'textarea_text',
                'label' => __( 'Create ticket block for articles', 'minerva-kb' ),
                'default' => '',
                'description' => __( 'This HTML will be displayed after each article content. Use [mkb-create-ticket-link] to add create ticket page link.', 'minerva-kb' ),
            ),
            array(
                'id' => 'tickets_topic_create_ticket_html',
                'type' => 'textarea_text',
                'label' => __( 'Create ticket block for topics', 'minerva-kb' ),
                'default' => '',
                'description' => __( 'This HTML will be displayed after each topic content. Use [mkb-create-ticket-link] to add create ticket page link.', 'minerva-kb' ),
            ),

            /**
             * Ticket template page
             */
            array(
                'id' => 'tickets_ticket_template_title',
                'type' => 'title',
                'label' => __( 'Ticket Template', 'minerva-kb' ),
                'description' => __( 'Configure the ticket template', 'minerva-kb' )
            ),
            array(
                'id' => 'ticket_sidebar',
                'type' => 'image_select',
                'label' => __( 'Ticket template sidebar position', 'minerva-kb' ),
                'options' => array(
                    'none' => array(
                        'label' => __( 'None', 'minerva-kb' ),
                        'img' => MINERVA_KB_IMG_URL . 'no-sidebar.png'
                    ),
                    'left' => array(
                        'label' => __( 'Left', 'minerva-kb' ),
                        'img' => MINERVA_KB_IMG_URL . 'left-sidebar.png'
                    ),
                    'right' => array(
                        'label' => __( 'Right', 'minerva-kb' ),
                        'img' => MINERVA_KB_IMG_URL . 'right-sidebar.png'
                    ),
                ),
                'default' => 'right',
                'description' => __( 'You can add widgets to sidebars under Appearance - Widgets', 'minerva-kb' ),
            ),
            array(
                'id' => 'ticket_top_padding',
                'type' => 'css_size',
                'label' => __( 'Ticket template top padding', 'minerva-kb' ),
                'default' => array("unit" => 'em', "size" => "3"),
                'description' => __( 'Distance between header and page content', 'minerva-kb' )
            ),
            array(
                'id' => 'ticket_bottom_padding',
                'type' => 'css_size',
                'label' => __( 'Ticket template bottom padding', 'minerva-kb' ),
                'default' => array("unit" => 'em', "size" => "3"),
                'description' => __( 'Distance between page content and footer', 'minerva-kb' )
            ),
            /**
             * Support Account page
             */
            array(
                'id' => 'tickets_support_account_title',
                'type' => 'title',
                'label' => __( 'Support Account Page', 'minerva-kb' ),
                'description' => __( 'You can configure the Support Account page for registered users. If you use WooCommerce you may use My account page and ignore this option.', 'minerva-kb' )
            ),
            // create page
            array(
                'id' => 'tickets_support_account_page',
                'type' => 'page_select',
                'label' => __( 'Select Support Account page', 'minerva-kb' ),
                'options' => self::get_pages_options(),
                'default' => '',
                'description' => __( 'Don\'t forget to save settings before page preview. Use [mkb-user-tickets-list] to display a list of all user tickets', 'minerva-kb' )
            ),
            array(
                'id' => 'ticket_support_account_page_template',
                'type' => 'select',
                'label' => __( 'Which Support Account page template to use?', 'minerva-kb' ),
                'options' => array(
                    'theme' => __( 'Theme page template', 'minerva-kb' ),
                    'plugin' => __( 'Plugin template', 'minerva-kb' )
                ),
                'default' => 'plugin'
            ),
            array(
                'id' => 'support_account_sidebar',
                'type' => 'image_select',
                'label' => __( 'Support Account page sidebar position', 'minerva-kb' ),
                'options' => array(
                    'none' => array(
                        'label' => __( 'None', 'minerva-kb' ),
                        'img' => MINERVA_KB_IMG_URL . 'no-sidebar.png'
                    ),
                    'left' => array(
                        'label' => __( 'Left', 'minerva-kb' ),
                        'img' => MINERVA_KB_IMG_URL . 'left-sidebar.png'
                    ),
                    'right' => array(
                        'label' => __( 'Right', 'minerva-kb' ),
                        'img' => MINERVA_KB_IMG_URL . 'right-sidebar.png'
                    ),
                ),
                'default' => 'right',
                'dependency' => array(
                    'target' => 'ticket_support_account_page_template',
                    'type' => 'EQ',
                    'value' => 'plugin'
                ),
                'description' => __( 'You can add widgets to sidebars under Appearance - Widgets', 'minerva-kb' ),
            ),
            array(
                'id' => 'support_account_top_padding',
                'type' => 'css_size',
                'label' => __( 'Support account page top padding', 'minerva-kb' ),
                'default' => array("unit" => 'em', "size" => "3"),
                'dependency' => array(
                    'target' => 'ticket_support_account_page_template',
                    'type' => 'EQ',
                    'value' => 'plugin'
                ),
                'description' => __( 'Distance between header and page content', 'minerva-kb' )
            ),
            array(
                'id' => 'support_account_bottom_padding',
                'type' => 'css_size',
                'label' => __( 'Support account page bottom padding', 'minerva-kb' ),
                'default' => array("unit" => 'em', "size" => "3"),
                'dependency' => array(
                    'target' => 'ticket_support_account_page_template',
                    'type' => 'EQ',
                    'value' => 'plugin'
                ),
                'description' => __( 'Distance between page content and footer', 'minerva-kb' )
            ),
            // quick replies
            array(
                'id' => 'tickets_insert_title',
                'type' => 'title',
                'label' => __( 'Quick replies', 'minerva-kb' ),
                'description' => __( 'Configure quick reply templates', 'minerva-kb' )
            ),
            array(
                'id' => 'tickets_insert_faq_html',
                'type' => 'textarea_text',
                'label' => __( 'Insert FAQ link text', 'minerva-kb' ),
                'default' => __(
'<p>This question has been answered in our FAQ, please check: {{FAQ_LINK}}</p>
<p>Please, let me know if you have any other questions.</p>', 'minerva-kb'),
                'description' => __( 'This HTML will be used when you insert FAQ link into ticket reply', 'minerva-kb' ),
            ),
            array(
                'id' => 'tickets_insert_kb_html',
                'type' => 'textarea_text',
                'label' => __( 'Insert KB link text', 'minerva-kb' ),
                'default' => __(
'<p>This topic has been covered in our Knowledge Base, please check this guide: {{KB_LINK}}</p>
<p>Please, let me know if you have any other questions.</p>', 'minerva-kb'),
                'description' => __( 'This HTML will be used when you insert KB link into ticket reply', 'minerva-kb' ),
            ),

            // localization
            array(
                'id' => 'tickets_localization_title',
                'type' => 'title',
                'label' => __( 'Ticket system labels', 'minerva-kb' ),
                'description' => __( 'Configure localization for client-side ticket modules', 'minerva-kb' )
            ),
            array(
                'id' => 'ticket_page_title_prefix',
                'type' => 'input_text',
                'label' => __( 'Select ticket page prefix (optional)', 'minerva-kb' ),
                'default' => __( '[Support Ticket] - ', 'minerva-kb' ),
                'description' => __( 'Prefix will be added on ticket page before title', 'minerva-kb' ),
            ),
            array(
                'id' => 'ticket_page_opened_text',
                'type' => 'input_text',
                'label' => __( 'Select ticket opened by text for user', 'minerva-kb' ),
                'default' => __( 'Opened <em>{{DATE}}</em> by <em>{{USER}}</em>', 'minerva-kb' ),
                'description' => __( 'Prefix will be added on ticket page after title for users', 'minerva-kb' ),
            ),
            array(
                'id' => 'ticket_page_opened_guest_text',
                'type' => 'input_text',
                'label' => __( 'Select ticket opened by text for guest', 'minerva-kb' ),
                'default' => __( 'Opened <em>{{DATE}}</em> by <em>Guest user</em>', 'minerva-kb' ),
                'description' => __( 'Prefix will be added on ticket page after title for guests', 'minerva-kb' ),
            ),
            array(
                'id' => 'ticket_page_reply_text',
                'type' => 'input_text',
                'label' => __( 'Reply text', 'minerva-kb' ),
                'default' => __( 'reply', 'minerva-kb' ),
                'description' => __( 'reply single text', 'minerva-kb' ),
            ),
            array(
                'id' => 'ticket_page_replies_text',
                'type' => 'input_text',
                'label' => __( 'Replies text', 'minerva-kb' ),
                'default' => __( 'replies', 'minerva-kb' ),
                'description' => __( 'reply plural text', 'minerva-kb' ),
            ),
            array(
                'id' => 'ticket_page_message_label',
                'type' => 'input_text',
                'label' => __( 'Ticket Message label', 'minerva-kb' ),
                'default' => __( 'Ticket message', 'minerva-kb' ),
                'description' => __( 'Ticket message label text', 'minerva-kb' ),
            ),
            array(
                'id' => 'ticket_attach_label',
                'type' => 'input_text',
                'label' => __( 'Ticket attachments label', 'minerva-kb' ),
                'default' => __( 'Attached files:', 'minerva-kb' ),
                'description' => __( 'Set this field empty to remove text label', 'minerva-kb' )
            ),
            array(
                'id' => 'ticket_type_label',
                'type' => 'input_text',
                'label' => __( 'Ticket type label', 'minerva-kb' ),
                'default' => __( 'Type:', 'minerva-kb' ),
                'description' => __( 'Set this field empty to remove text label', 'minerva-kb' )
            ),
            array(
                'id' => 'ticket_discussion_label',
                'type' => 'input_text',
                'label' => __( 'Ticket Discussion label', 'minerva-kb' ),
                'default' => __( 'Ticket Discussion', 'minerva-kb' ),
            ),
            array(
                'id' => 'ticket_discussion_no_replies_text',
                'type' => 'input_text',
                'label' => __( 'Ticket Discussion no replies text', 'minerva-kb' ),
                'default' => __( 'There are no replies yet', 'minerva-kb' ),
            ),
            array(
                'id' => 'ticket_agent_text',
                'type' => 'input_text',
                'label' => __( 'Support Agent text', 'minerva-kb' ),
                'default' => __( 'Support Agent', 'minerva-kb' ),
            ),
            array(
                'id' => 'ticket_customer_text',
                'type' => 'input_text',
                'label' => __( 'Customer text', 'minerva-kb' ),
                'default' => __( 'Customer', 'minerva-kb' ),
            ),
            array(
                'id' => 'ticket_reply_added_text',
                'type' => 'input_text',
                'label' => __( 'Reply added text', 'minerva-kb' ),
                'default' => __( 'Reply added:', 'minerva-kb' ),
            ),
            array(
                'id' => 'ticket_add_reply_label',
                'type' => 'input_text',
                'label' => __( 'Reply to ticket form heading text', 'minerva-kb' ),
                'default' => __( 'Reply to ticket', 'minerva-kb' ),
            ),
            array(
                'id' => 'ticket_add_reply_button_label',
                'type' => 'input_text',
                'label' => __( 'Ticket reply form submit button label', 'minerva-kb' ),
                'default' => __( 'Submit Reply', 'minerva-kb' ),
            ),
            array(
                'id' => 'ticket_adding_reply_button_label',
                'type' => 'input_text',
                'label' => __( 'Ticket reply form submit button progress label', 'minerva-kb' ),
                'default' => __( 'Saving Reply...', 'minerva-kb' ),
            ),
            array(
                'id' => 'ticket_add_reply_close_label',
                'type' => 'input_text',
                'label' => __( 'Ticket reply form close label', 'minerva-kb' ),
                'default' => __( 'Close ticket?', 'minerva-kb' ),
            ),
            array(
                'id' => 'ticket_cannot_reply_to_closed_ticket_text',
                'type' => 'input_text',
                'label' => __( 'Reply to ticket form heading text', 'minerva-kb' ),
                'default' => __( '[mkb-info]You cannot reply to closed ticket[/mkb-info]', 'minerva-kb' ),
            ),
            array(
                'id' => 'ticket_reopen_ticket_text',
                'type' => 'input_text',
                'label' => __( 'Reopen ticket text', 'minerva-kb' ),
                'default' => __( 'Reopen Ticket', 'minerva-kb' ),
            ),
            array(
                'id' => 'ticket_reopening_ticket_text',
                'type' => 'input_text',
                'label' => __( 'Reopening ticket text', 'minerva-kb' ),
                'default' => __( 'Reopening Ticket...', 'minerva-kb' ),
            ),
            array(
                'id' => 'logout_link_text',
                'type' => 'input_text',
                'label' => __( 'Logout button text', 'minerva-kb' ),
                'default' => __( 'Logout', 'minerva-kb' ),
                'description' => __( 'You can use [mkb-logout] shortcode to display logout button', 'minerva-kb' ),
            ),

            // agent file uploads
            array(
                'id' => 'tickets_agent_uploads_title',
                'type' => 'title',
                'label' => __( 'Agent file uploads', 'minerva-kb' ),
                'description' => __( 'Configure file upload settings for agents', 'minerva-kb' )
            ),
            array(
                'id' => 'tickets_admin_allowed_filetypes',
                'type' => 'textarea',
                'label' => __( 'Allowed ticket attachments file types for agents', 'minerva-kb' ),
                'default' => 'jpg, jpeg, png, pdf, doc, docx, xls, xlsx, txt, zip',
                'description' => __( 'Use comma-separated list of extensions, without the dots (for ex: doc, zip, xls, png)', 'minerva-kb' ),
            ),
            array(
                'id' => 'tickets_agent_max_files',
                'type' => 'input',
                'label' => __( 'Maximum files for agents per reply', 'minerva-kb' ),
                'default' => 3,
                'description' => __( 'How many files are agents allowed to upload', 'minerva-kb' )
            ),
            array(
                'id' => 'tickets_agent_max_file_size',
                'type' => 'input',
                'label' => __( 'Maximum file size for agents (in MB)', 'minerva-kb' ),
                'default' => 5,
                'description' =>
                    sprintf(__( 'File size is also controlled by server settings. Current WordPress limit is <strong style="color:#000;">%sMb</strong>', 'minerva-kb' ),
                        wp_max_upload_size() / 1024 / 1024
                    )
            ),
            array(
                'id' => 'tickets_admin_include_system_filetypes',
                'type' => 'checkbox',
                'label' => __( 'Also include WordPress system allowed file types (allowed only for admins / managers by default)?', 'minerva-kb' ),
                'default' => false
            ),

            // user uploads
            array(
                'id' => 'tickets_user_uploads_title',
                'type' => 'title',
                'label' => __( 'User file uploads', 'minerva-kb' ),
                'description' => __( 'Configure file upload settings for users', 'minerva-kb' )
            ),
            array(
                'id' => 'tickets_allow_user_attachments',
                'type' => 'checkbox',
                'label' => __( 'Allow users to attach files to tickets / replies?', 'minerva-kb' ),
                'default' => false
            ),
            array(
                'id' => 'tickets_user_max_files',
                'type' => 'input',
                'label' => __( 'Maximum files for end users per reply', 'minerva-kb' ),
                'default' => 3,
                'description' => __( 'How many files are end users allowed to upload', 'minerva-kb' ),
                'dependency' => array(
                    'target' => 'tickets_allow_user_attachments',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
            array(
                'id' => 'tickets_user_max_file_size',
                'type' => 'input',
                'label' => __( 'Maximum file size for end users (in MB)', 'minerva-kb' ),
                'default' => 3,
                'description' =>
                    sprintf(__( 'File size is also controlled by server settings. Current WordPress limit is <strong style="color:#000;">%sMb</strong>', 'minerva-kb' ),
                        wp_max_upload_size() / 1024 / 1024
                    ),
                'dependency' => array(
                    'target' => 'tickets_allow_user_attachments',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
            array(
                'id' => 'tickets_user_allowed_filetypes',
                'type' => 'textarea',
                'label' => __( 'Allowed ticket attachments file types for end users', 'minerva-kb' ),
                'default' => 'jpg, jpeg, png, pdf, doc, docx, xls, xlsx, txt, zip',
                'description' => __( 'Use comma-separated list of extensions, without the dots (for ex: doc, zip, xls, png)', 'minerva-kb' ),
                'dependency' => array(
                    'target' => 'tickets_allow_user_attachments',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
            array(
                'id' => 'tickets_user_include_system_filetypes',
                'type' => 'checkbox',
                'label' => __( 'Also include WordPress system allowed file types for users (allowed only for admins / managers by default)?', 'minerva-kb' ),
                'default' => false,
                'dependency' => array(
                    'target' => 'tickets_allow_user_attachments',
                    'type' => 'EQ',
                    'value' => true
                )
            ),

            // guest uploads
            array(
                'id' => 'tickets_guest_uploads_title',
                'type' => 'title',
                'label' => __( 'Guest file uploads', 'minerva-kb' ),
                'description' => __( 'Configure file upload settings for guests', 'minerva-kb' )
            ),
            array(
                'id' => 'tickets_allow_guest_attachments',
                'type' => 'checkbox',
                'label' => __( 'Allow guests to attach files to tickets / replies?', 'minerva-kb' ),
                'default' => false
            ),
            array(
                'id' => 'tickets_guest_max_files',
                'type' => 'input',
                'label' => __( 'Maximum files for guest users per reply', 'minerva-kb' ),
                'default' => 2,
                'description' => __( 'How many files are guest users allowed to upload', 'minerva-kb' ),
                'dependency' => array(
                    'target' => 'tickets_allow_guest_attachments',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
            array(
                'id' => 'tickets_guest_max_file_size',
                'type' => 'input',
                'label' => __( 'Maximum file size for guest users (in MB)', 'minerva-kb' ),
                'default' => 2,
                'description' =>
                    sprintf(__( 'File size is also controlled by server settings. Current WordPress limit is <strong style="color:#000;">%sMb</strong>', 'minerva-kb' ),
                        wp_max_upload_size() / 1024 / 1024
                    ),
                'dependency' => array(
                    'target' => 'tickets_allow_guest_attachments',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
            array(
                'id' => 'tickets_guest_allowed_filetypes',
                'type' => 'textarea',
                'label' => __( 'Allowed ticket attachments file types for guest users', 'minerva-kb' ),
                'default' => 'jpg, jpeg, png, pdf, doc, docx, xls, xlsx, txt, zip',
                'description' => __( 'Use comma-separated list of extensions, without the dots (for ex: doc, zip, xls, png)', 'minerva-kb' ),
                'dependency' => array(
                    'target' => 'tickets_allow_guest_attachments',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
            array(
                'id' => 'tickets_guest_include_system_filetypes',
                'type' => 'checkbox',
                'label' => __( 'Also include WordPress system allowed file types for guests (allowed only for admins / managers by default)?', 'minerva-kb' ),
                'default' => false,
                'dependency' => array(
                    'target' => 'tickets_allow_guest_attachments',
                    'type' => 'EQ',
                    'value' => true
                )
            ),

            // avatars
            array(
                'id' => 'tickets_avatars_title',
                'type' => 'title',
                'label' => __( 'Avatars', 'minerva-kb' ),
                'description' => __( 'Configure ticket system avatars', 'minerva-kb' )
            ),
            array(
                'id' => 'tickets_agent_avatar',
                'type' => 'select',
                'label' => __( 'Which avatar to use for support agents?', 'minerva-kb' ),
                'options' => array(
                    'custom' => __( 'Custom image', 'minerva-kb' ),
                    'gravatar' => __( 'Gravatar (WordPress default)', 'minerva-kb' )
                ),
                'default' => 'custom',
                'description' => __( 'By default, Gravatar is used', 'minerva-kb' )
            ),
            array(
                'id' => 'tickets_default_agent_avatar',
                'type' => 'media',
                'label' => __( 'Default custom avatar for all agents', 'minerva-kb' ),
                'default' => MINERVA_KB_IMG_URL . 'user-avatar.svg'
            ),
            array(
                'id' => 'tickets_default_client_avatar',
                'type' => 'media',
                'label' => __( 'Default customer avatar', 'minerva-kb' ),
                'default' => MINERVA_KB_IMG_URL . 'user-avatar.svg'
            ),

			/**
			 * Widgets
			 */
			array(
				'id' => 'widgets_tab',
				'type' => 'tab',
				'label' => __( 'Widgets', 'minerva-kb' ),
				'icon' => 'fa-cube'
			),
            array(
                'id' => 'widget_heading_type',
                'type' => 'select',
                'label' => __( 'Widget heading type', 'minerva-kb' ),
                'options' => array(
                    'h1' => __( 'H1', 'minerva-kb' ),
                    'h2' => __( 'H2', 'minerva-kb' ),
                    'h3' => __( 'H3', 'minerva-kb' ),
                    'h4' => __( 'H4', 'minerva-kb' ),
                    'h5' => __( 'H5', 'minerva-kb' ),
                    'h6' => __( 'H6', 'minerva-kb' ),
                ),
                'description' => __( 'HTML heading tag to use for widget heading', 'minerva-kb' ),
                'default' => 'h2'
            ),
			array(
				'id' => 'widget_icons_on',
				'type' => 'checkbox',
				'label' => __( 'Show topic/article icons in widgets?', 'minerva-kb' ),
				'default' => true
			),
			array(
				'id' => 'content_tree_widget_icon',
				'type' => 'icon_select',
				'label' => __( 'Content tree widget topic icon', 'minerva-kb' ),
				'default' => 'fa-folder'
			),
			array(
				'id' => 'content_tree_widget_icon_open',
				'type' => 'icon_select',
				'label' => __( 'Content tree widget topic icon (open)', 'minerva-kb' ),
				'default' => 'fa-folder-open'
			),
			array(
				'id' => 'content_tree_widget_active_color',
				'type' => 'color',
				'label' => __( 'Content tree widget current article indicator color', 'minerva-kb' ),
				'default' => '#32CD32',
			),
			array(
				'id' => 'content_tree_widget_open_active_branch',
				'type' => 'checkbox',
				'label' => __( 'Open current article branch?', 'minerva-kb' ),
				'default' => true
			),
			array(
				'id' => 'widget_style_on',
				'type' => 'checkbox',
				'label' => __( 'Enable general widget styling?', 'minerva-kb' ),
				'default' => true,
				'description' => __( 'When off, theme styles will be used', 'minerva-kb' )
			),
			array(
				'id' => 'widget_bg',
				'type' => 'color',
				'label' => __( 'Widget background color', 'minerva-kb' ),
				'default' => '#f7f7f7',
				'dependency' => array(
					'target' => 'widget_style_on',
					'type' => 'EQ',
					'value' => true
				)
			),

			array(
				'id' => 'widget_color',
				'type' => 'color',
				'label' => __( 'Widget text color', 'minerva-kb' ),
				'default' => '#888',
				'dependency' => array(
					'target' => 'widget_style_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'widget_link_color',
				'type' => 'color',
				'label' => __( 'Widget link color', 'minerva-kb' ),
				'default' => '#888',
				'dependency' => array(
					'target' => 'widget_style_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'widget_icon_color',
				'type' => 'color',
				'label' => __( 'Widget icons color', 'minerva-kb' ),
				'default' => '#888',
				'dependency' => array(
					'target' => 'widget_style_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'widget_heading_color',
				'type' => 'color',
				'label' => __( 'Widget heading color', 'minerva-kb' ),
				'default' => '#333',
				'dependency' => array(
					'target' => 'widget_style_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			/**
			 * FAQ
			 */
			array(
				'id' => 'faq_tab',
				'type' => 'tab',
				'label' => __( 'FAQ (global)', 'minerva-kb' ),
				'icon' => 'fa-question-circle'
			),
			array(
				'id' => 'disable_faq',
				'type' => 'checkbox',
				'label' => __( 'Disable FAQ?', 'minerva-kb' ),
				'default' => false
			),
            array(
                'id' => 'faq_enable_section_info',
                'type' => 'info',
                'label' => 'Note: you need to refresh page after disable / enable FAQ to update Dashboard menu',
            ),
            array(
                'id' => 'faq_disable_block_editor',
                'type' => 'checkbox',
                'label' => __( 'Disable block editor for FAQ? (WordPress v5.0+)', 'minerva-kb' ),
                'default' => false
            ),
			// cpt
			array(
				'id' => 'faq_title',
				'type' => 'title',
				'label' => __( 'FAQ global settings', 'minerva-kb' ),
				'description' => __( 'Configure FAQ settings', 'minerva-kb' )
			),
			array(
				'id' => 'faq_enable_pages',
				'type' => 'checkbox',
				'label' => __( 'Enable standalone answer pages?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'When enabled, each FAQ Q/A will have its own page with unique URL.', 'minerva-kb' ),
			),
			array(
				'id' => 'faq_slug',
				'type' => 'input',
				'label' => __( 'FAQ items URL sluq (must be unique and not used by posts or pages)', 'minerva-kb' ),
				'default' => __( 'questions', 'minerva-kb' ),
				'description' => __( 'NOTE: these setting affects WordPress rewrite rules. After changing them you need to go to Settings - Permalinks and press Save to update rewrite rules.', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'faq_enable_pages',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'faq_include_in_search',
				'type' => 'checkbox',
				'label' => __( 'Include faq answers in global search results?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'When enabled, wordpress search will include matches from FAQ. Standard posts templates will be used.', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'faq_enable_pages',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'faq_enable_reorder',
				'type' => 'checkbox',
				'label' => __( 'Enable FAQ Drag n Drop reorder?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'faq_url_update',
				'type' => 'checkbox',
				'label' => __( 'Add question hash to URL on question open?', 'minerva-kb' ),
				'default' => false,
			),
			array(
				'id' => 'faq_scroll_offset',
				'type' => 'css_size',
				'label' => __( 'Scroll offset for FAQ question', 'minerva-kb' ),
				'units' => array('px'),
				'default' => array("unit" => 'px', "size" => "0"),
			),
			array(
				'id' => 'faq_slow_animation',
				'type' => 'checkbox',
				'label' => __( 'Slow FAQ open animation?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'faq_toggle_mode',
				'type' => 'checkbox',
				'label' => __( 'Toggle mode?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'In toggle mode opening one item closes others', 'minerva-kb' )
			),
			array(
				'id' => 'faq_toggle_all_title',
				'type' => 'title',
				'label' => __( 'FAQ Toggle All button', 'minerva-kb' ),
				'description' => __( 'Configure toggle all styling', 'minerva-kb' )
			),
			array(
				'id' => 'faq_toggle_all_open_text',
				'type' => 'input_text',
				'label' => __( 'FAQ Toggle All open text', 'minerva-kb' ),
				'default' => __( 'Open all', 'minerva-kb' ),
			),
			array(
				'id' => 'faq_toggle_all_close_text',
				'type' => 'input_text',
				'label' => __( 'FAQ Toggle All close text', 'minerva-kb' ),
				'default' => __( 'Close all', 'minerva-kb' ),
			),
			array(
				'id' => 'show_faq_toggle_all_icon',
				'type' => 'checkbox',
				'label' => __( 'Show FAQ toggle all icon?', 'minerva-kb' ),
				'default' => true
			),
			array(
				'id' => 'faq_toggle_all_icon',
				'type' => 'icon_select',
				'label' => __( 'FAQ toggle all icon (open)', 'minerva-kb' ),
				'default' => 'fa-plus-circle',
				'dependency' => array(
					'target' => 'show_faq_toggle_all_icon',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'faq_toggle_all_icon_open',
				'type' => 'icon_select',
				'label' => __( 'FAQ toggle all icon (close)', 'minerva-kb' ),
				'default' => 'fa-minus-circle',
				'dependency' => array(
					'target' => 'show_faq_toggle_all_icon',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'faq_toggle_all_bg',
				'type' => 'color',
				'label' => __( 'FAQ toggle all background color', 'minerva-kb' ),
				'default' => '#4bb7e5'
			),
			array(
				'id' => 'faq_toggle_all_bg_hover',
				'type' => 'color',
				'label' => __( 'FAQ toggle all background color on mouse hover', 'minerva-kb' ),
				'default' => '#64bee5'
			),
			array(
				'id' => 'faq_toggle_all_color',
				'type' => 'color',
				'label' => __( 'FAQ toggle all link color', 'minerva-kb' ),
				'default' => '#ffffff'
			),
			array(
				'id' => 'faq_questions_title',
				'type' => 'title',
				'label' => __( 'FAQ Questions style', 'minerva-kb' ),
				'description' => __( 'Configure questions styling', 'minerva-kb' )
			),
			array(
				'id' => 'show_faq_question_icon',
				'type' => 'checkbox',
				'label' => __( 'Show FAQ question icon?', 'minerva-kb' ),
				'default' => true
			),
			array(
				'id' => 'faq_question_icon',
				'type' => 'icon_select',
				'label' => __( 'FAQ question icon', 'minerva-kb' ),
				'default' => 'fa-plus-circle'
			),
			array(
				'id' => 'faq_question_icon_open_action',
				'type' => 'select',
				'label' => __( 'FAQ question icon action on open', 'minerva-kb' ),
				'options' => array(
					'rotate' => __( 'Rotate', 'minerva-kb' ),
					'change' => __( 'Change', 'minerva-kb' )
				),
				'default' => 'change'
			),
			array(
				'id' => 'faq_question_open_icon',
				'type' => 'icon_select',
				'label' => __( 'FAQ question open icon', 'minerva-kb' ),
				'default' => 'fa-minus-circle',
				'dependency' => array(
					'target' => 'faq_question_icon_open_action',
					'type' => 'EQ',
					'value' => 'change'
				)
			),
			array(
				'id' => 'faq_question_bg',
				'type' => 'color',
				'label' => __( 'FAQ question background color', 'minerva-kb' ),
				'default' => '#4bb7e5'
			),
			array(
				'id' => 'faq_question_bg_hover',
				'type' => 'color',
				'label' => __( 'FAQ question background color on mouse hover', 'minerva-kb' ),
				'default' => '#64bee5'
			),
			array(
				'id' => 'faq_question_font_size',
				'type' => 'css_size',
				'label' => __( 'Question font size', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "1.5"),
			),
			array(
				'id' => 'faq_question_color',
				'type' => 'color',
				'label' => __( 'FAQ question text color', 'minerva-kb' ),
				'default' => '#ffffff'
			),
			array(
				'id' => 'faq_question_shadow',
				'type' => 'checkbox',
				'label' => __( 'Add FAQ question shadow?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'faq_answers_title',
				'type' => 'title',
				'label' => __( 'FAQ Answers style', 'minerva-kb' ),
				'description' => __( 'Configure answers styling', 'minerva-kb' )
			),
			array(
				'id' => 'faq_answer_bg',
				'type' => 'color',
				'label' => __( 'FAQ answer background color', 'minerva-kb' ),
				'default' => '#ffffff'
			),
			array(
				'id' => 'faq_answer_color',
				'type' => 'color',
				'label' => __( 'FAQ answer text color', 'minerva-kb' ),
				'default' => '#333'
			),
			array(
				'id' => 'faq_categories_title',
				'type' => 'title',
				'label' => __( 'FAQ Categories style', 'minerva-kb' ),
				'description' => __( 'Configure categories styling', 'minerva-kb' )
			),
			array(
				'id' => 'faq_category_margin_top',
				'type' => 'css_size',
				'label' => __( 'Category name top margin', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "1"),
				'description' => __( 'Distance between category title and previous section', 'minerva-kb' ),
			),
			array(
				'id' => 'faq_category_margin_bottom',
				'type' => 'css_size',
				'label' => __( 'Category name bottom margin', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "0.3"),
				'description' => __( 'Distance between category title and questions', 'minerva-kb' ),
			),
			array(
				'id' => 'faq_count_bg',
				'type' => 'color',
				'label' => __( 'FAQ category count background color', 'minerva-kb' ),
				'default' => '#4bb7e5',
			),
			array(
				'id' => 'faq_count_color',
				'type' => 'color',
				'label' => __( 'FAQ category count text color', 'minerva-kb' ),
				'default' => '#ffffff',
			),
			array(
				'id' => 'faq_filter_title',
				'type' => 'title',
				'label' => __( 'FAQ Live Filter style', 'minerva-kb' ),
				'description' => __( 'Configure filter styling', 'minerva-kb' )
			),
			array(
				'id' => 'faq_filter_theme',
				'type' => 'select',
				'label' => __( 'FAQ filter theme', 'minerva-kb' ),
				'options' => array(
					'minerva' => __( 'Minerva', 'minerva-kb' ),
					'invisible' => __( 'Invisible', 'minerva-kb' )
				),
				'default' => 'minerva'
			),
			array(
				'id' => 'faq_filter_placeholder',
				'type' => 'input_text',
				'label' => __( 'FAQ filter placeholder', 'minerva-kb' ),
				'default' => __( 'FAQ filter', 'minerva-kb' ),
			),
			array(
				'id' => 'show_faq_filter_icon',
				'type' => 'checkbox',
				'label' => __( 'Show FAQ filter icon?', 'minerva-kb' ),
				'default' => true
			),
			array(
				'id' => 'faq_filter_icon',
				'type' => 'icon_select',
				'label' => __( 'FAQ filter icon', 'minerva-kb' ),
				'default' => 'fa-filter',
			),
			array(
				'id' => 'faq_filter_clear_icon',
				'type' => 'icon_select',
				'label' => __( 'FAQ filter clear icon', 'minerva-kb' ),
				'default' => 'fa-times-circle',
			),
			array(
				'id' => 'faq_no_results_text',
				'type' => 'input_text',
				'label' => __( 'FAQ filter no results text', 'minerva-kb' ),
				'default' => __( 'No questions matching current filter', 'minerva-kb' ),
			),
			array(
				'id' => 'faq_no_results_bg',
				'type' => 'color',
				'label' => __( 'FAQ no results background color', 'minerva-kb' ),
				'default' => '#f7f7f7'
			),
			array(
				'id' => 'faq_no_results_color',
				'type' => 'color',
				'label' => __( 'FAQ no results text color', 'minerva-kb' ),
				'default' => '#333'
			),
			array(
				'id' => 'faq_filter_open_single',
				'type' => 'checkbox',
				'label' => __( 'Open question when single item matches filter?', 'minerva-kb' ),
				'default' => false,
			),
            /**
             * Glossary
             */
            array(
                'id' => 'glossary_tab',
                'type' => 'tab',
                'label' => __( 'Glossary', 'minerva-kb' ),
                'icon' => 'fa-comment-o'
            ),
            array(
                'id' => 'disable_glossary',
                'type' => 'checkbox',
                'label' => __( 'Disable Glossary?', 'minerva-kb' ),
                'default' => false
            ),
            array(
                'id' => 'glossary_enable_section_info',
                'type' => 'info',
                'label' => 'Note: you need to refresh page after disable / enable Glossary to update Dashboard menu',
            ),
            array(
                'id' => 'glossary_enable_pages',
                'type' => 'checkbox',
                'label' => __( 'Enable standalone glossary term pages?', 'minerva-kb' ),
                'default' => false,
                'description' => __( 'When enabled, each term will have its own page with unique URL.', 'minerva-kb' ),
            ),
            array(
                'id' => 'glossary_slug',
                'type' => 'input',
                'label' => __( 'Glossary items URL slug (must be unique and not used by posts or pages)', 'minerva-kb' ),
                'default' => __( 'glossary', 'minerva-kb' ),
                'description' => __( 'NOTE: these setting affects WordPress rewrite rules. After changing them you need to go to Settings - Permalinks and press Save to update rewrite rules.', 'minerva-kb' ),
                'dependency' => array(
                    'target' => 'glossary_enable_pages',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
            array(
                'id' => 'enable_kb_glossary_highlight',
                'type' => 'checkbox',
                'label' => __( 'Highlight Glossary terms in KB Articles?', 'minerva-kb' ),
                'default' => true
            ),
            array(
                'id' => 'enable_posts_glossary_highlight',
                'type' => 'checkbox',
                'label' => __( 'Highlight Glossary terms in blog posts?', 'minerva-kb' ),
                'experimental' => __( 'This is experimental feature and depends a lot on theme styles and layout', 'minerva-kb' ),
                'default' => false
            ),
            array(
                'id' => 'blog_posts_glossary_highlight_selector',
                'type' => 'input',
                'label' => __( 'Customize blog post content CSS selector', 'minerva-kb' ),
                'default' => __( '.post.type-post', 'minerva-kb' ),
                'description' => __( 'This CSS selector will be used to find the content block in single post template of your theme', 'minerva-kb' ),
                'dependency' => array(
                    'target' => 'enable_posts_glossary_highlight',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
            array(
                'id' => 'glossary_highlight_limit',
                'type' => 'input',
                'label' => __( 'Glossary items highlight limit', 'minerva-kb' ),
                'default' => 0,
                'description' => __( 'Use any non-zero value to limit number of highlight per each page (n times for each term)', 'minerva-kb' )
            ),
            array(
                'id' => 'glossary_mobile_mode',
                'type' => 'select',
                'label' => __( 'Glossary mobile devices display mode', 'minerva-kb' ),
                'options' => array(
                    'popup' => __( 'Popup', 'minerva-kb' ),
                    'link' => __( 'Link (same tab)', 'minerva-kb' ),
                    'link_new' => __( 'Link (new tab)', 'minerva-kb' ),
                    'none' => __( 'None', 'minerva-kb' )
                ),
                'default' => 'popup',
                'description' => __( 'Note: Link display mode requires Glossary standalone page to be enabled', 'minerva-kb' )
            ),
            array(
                'id' => 'glossary_list_title',
                'type' => 'title',
                'label' => __( 'Glossary list', 'minerva-kb' ),
                'description' => __( 'Configure glossary list settings', 'minerva-kb' )
            ),
            array(
                'id' => 'glossary_shortcode_info',
                'type' => 'info',
                'label' => 'You can use [mkb-glossary] shortcode to display a list of all Glossary terms',
            ),
            array(
                'id' => 'glossary_scroll_offset',
                'type' => 'css_size',
                'label' => __( 'Scroll offset for Glossary letters/terms', 'minerva-kb' ),
                'units' => array('px'),
                'default' => array("unit" => 'px', "size" => "0")
            ),
            array(
                'id' => 'glossary_back_to_top',
                'type' => 'input_text',
                'label' => __( 'Glossary list back to top text', 'minerva-kb' ),
                'default' => __( 'Back to top', 'minerva-kb' ),
            ),
            array(
                'id' => 'glossary_tooltips_title',
                'type' => 'title',
                'label' => __( 'Glossary tooltips', 'minerva-kb' ),
                'description' => __( 'Configure glossary tooltips settings', 'minerva-kb' )
            ),
            array(
                'id' => 'glossary_term_bg',
                'type' => 'color',
                'label' => __( 'Glossary term highlight background color', 'minerva-kb' ),
                'default' => '#00aae8'
            ),
            array(
                'id' => 'glossary_term_bg_opacity',
                'type' => 'input',
                'label' => __( 'Glossary term highlight background color opacity', 'minerva-kb' ),
                'default' => 0.2,
                'description' => __( 'Use any CSS opacity value, for example 1 or 0.7', 'minerva-kb' ),
            ),
            array(
                'id' => 'glossary_term_color',
                'type' => 'color',
                'label' => __( 'Glossary term text color', 'minerva-kb' ),
                'default' => '#000'
            ),
            array(
                'id' => 'glossary_underline',
                'type' => 'select',
                'label' => __( 'Glossary underline style', 'minerva-kb' ),
                'options' => array(
                    'dotted' => __( 'Dotted', 'minerva-kb' ),
                    'solid' => __( 'Solid', 'minerva-kb' ),
                    'dashed' => __( 'Dashed', 'minerva-kb' ),
                    'none' => __( 'None', 'minerva-kb' )
                ),
                'default' => 'dotted'
            ),
            array(
                'id' => 'glossary_underline_color',
                'type' => 'color',
                'label' => __( 'Glossary underline color', 'minerva-kb' ),
                'default' => '#505050'
            ),
            array(
                'id' => 'glossary_loader_icon',
                'type' => 'icon_select',
                'label' => __( 'Glossary loader icon', 'minerva-kb' ),
                'default' => 'fa-circle-o-notch',
            ),
            array(
                'id' => 'glossary_tooltip_width',
                'type' => 'css_size',
                'label' => __( 'Glossary tooltip width', 'minerva-kb' ),
                'units' => array('rem', 'px'),
                'default' => array("unit" => 'rem', "size" => "20"),
                'description' => __( 'Tooltip width limit', 'minerva-kb' ),
            ),













            /**
             * WooCommerce
             */
            array(
                'id' => 'woocommerce_tab',
                'type' => 'tab',
                'label' => __( 'WooCommerce', 'minerva-kb' ),
                'icon' => 'fa-shopping-cart'
            ),
            array(
                'id' => 'woo_add_support_account_tab',
                'type' => 'checkbox',
                'label' => __( 'Add Support section to My Account?', 'minerva-kb' ),
                'default' => false,
                'description' => __( 'You can use support section to show open support tickets and other useful info to customer', 'minerva-kb' ),
            ),
            array(
                'id' => 'woo_section_permalinks_info',
                'type' => 'info',
                'label' => 'Note: this section modifies WordPress rewrite rules, which are usually cached. ' .
                    'If you experience any 404 errors or redirects after editing these settings, go to ' .
                    '<a href="' . esc_attr(admin_url('options-permalink.php')) . '">' .
                    'Settings - Permalinks' . '</a>' . ' and press Save ' .
                    'without editing to clear rewrite rules cache.',
            ),
            array(
                'id' => 'woo_account_section_title',
                'type' => 'input_text',
                'label' => __( 'Section title', 'minerva-kb' ),
                'default' => __('Customer support', 'minerva-kb'),
                'dependency' => array(
                    'target' => 'woo_add_support_account_tab',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
            array(
                'id' => 'woo_account_section_url',
                'type' => 'input',
                'label' => __( 'Section URL', 'minerva-kb' ),
                'default' => 'support',
                'description' => __( 'Use only lowercase letters, underscores and dashes. Must be a valid URL part', 'minerva-kb' ),
                'dependency' => array(
                    'target' => 'woo_add_support_account_tab',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
            array(
                'id' => 'woo_account_section_content',
                'type' => 'textarea_text',
                'label' => __( 'Section content', 'minerva-kb' ),
                'default' => '<p>Our customer support team is working Monday through Friday from 10:00 to 19:00. Call us: +1234567890.</p>

<h3>Support Tickets</h3>
[mkb-user-tickets-list]
[mkb-create-ticket-link]

<h3>Recently viewed articles</h3>
[mkb-recently-viewed-articles]',
                'dependency' => array(
                    'target' => 'woo_add_support_account_tab',
                    'type' => 'EQ',
                    'value' => true
                )
            ),

			/**
			 * Post type
			 */
			array(
				'id' => 'cpt_tab',
				'type' => 'tab',
				'label' => __( 'Post type & URLs', 'minerva-kb' ),
				'icon' => 'fa-address-card-o'
			),
			array(
				'id' => 'article_cpt_section_info',
				'type' => 'info',
				'label' => 'Note: this section modifies WordPress rewrite rules, which are usually cached. ' .
				               'If you experience any 404 errors after editing these settings, go to ' .
				               '<a href="' . esc_attr(admin_url('options-permalink.php')) . '">' .
				               'Settings - Permalinks' . '</a>' . ' and press Save ' .
				               'without editing to clear rewrite rules cache.',
			),
			// cpt
			array(
				'id' => 'article_cpt_title',
				'type' => 'title',
				'label' => __( 'Article URL', 'minerva-kb' ),
				'description' => __( 'Configure article post type URL', 'minerva-kb' )
			),
			array(
				'id' => 'cpt_archive_disable_switch',
				'type' => 'checkbox',
				'label' => __( 'Disable article archive?', 'minerva-kb' ),
				'default' => true,
				'description' => __( 'By default, articles archive takes same URL as article URL base (for example, /kb), so disabling archive will allow you to use this slug for your KB home page', 'minerva-kb' ),
			),
			array(
				'id' => 'cpt_slug_switch',
				'type' => 'checkbox',
				'label' => __( 'Edit article slug (URL part)?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'article_slug',
				'type' => 'input',
				'label' => __( 'Article slug (URL part)', 'minerva-kb' ),
				'default' => 'kb',
				'description' => __( 'Use only lowercase letters, underscores and dashes. Slug must be a valid URL part', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'cpt_slug_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'cpt_slug_front_switch',
				'type' => 'checkbox',
				'label' => __( 'Add global front base to article url?', 'minerva-kb' ),
				'default' => true,
				'description' => __( 'If you have configured global front base, like /blog, you can remove it for KB items with this switch', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'cpt_slug_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
			// topics
			array(
				'id' => 'article_cpt_category_title',
				'type' => 'title',
				'label' => __( 'Topic URL', 'minerva-kb' ),
				'description' => __( 'Configure topic taxonomy URL slug', 'minerva-kb' )
			),
			array(
				'id' => 'cpt_category_slug_switch',
				'type' => 'checkbox',
				'label' => __( 'Edit topic slug (URL part)?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'category_slug',
				'type' => 'input',
				'label' => __( 'Topic slug (URL part)', 'minerva-kb' ),
				'default' => 'kbtopic',
				'description' => __( 'Use only lowercase letters, underscores and dashes. Slug must be a valid URL part', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'cpt_category_slug_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'cpt_category_slug_front_switch',
				'type' => 'checkbox',
				'label' => __( 'Add global front base to topic url?', 'minerva-kb' ),
				'default' => true,
				'description' => __( 'If you have configured global front base, like /blog, you can remove it for KB items with this switch', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'cpt_category_slug_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
			// tags
			array(
				'id' => 'article_cpt_tag_title',
				'type' => 'title',
				'label' => __( 'Tag URL', 'minerva-kb' ),
				'description' => __( 'Configure tag taxonomy URL slug', 'minerva-kb' )
			),
			array(
				'id' => 'cpt_tag_slug_switch',
				'type' => 'checkbox',
				'label' => __( 'Edit tag slug (URL part)', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'tag_slug',
				'type' => 'input',
				'label' => __( 'Tag slug (URL part)', 'minerva-kb' ),
				'default' => 'kbtag',
				'description' => __( 'Use only lowercase letters, underscores and dashes. Slug must be a valid URL part', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'cpt_tag_slug_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'cpt_tag_slug_front_switch',
				'type' => 'checkbox',
				'label' => __( 'Add global front base to tag url?', 'minerva-kb' ),
				'default' => true,
				'description' => __( 'If you have configured global front base, like /blog, you can remove it for KB items with this switch', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'cpt_tag_slug_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
			// tickets
            array(
                'id' => 'ticket_slug_warning',
                'type' => 'warning',
                'label' => __( 'Ticket slug change will make all previous ticket URL broken, be careful if you have support tickets accessed by direct links', 'minerva-kb' ),
            ),
            array(
                'id' => 'cpt_ticket_slug_switch',
                'type' => 'checkbox',
                'label' => __( 'Edit ticket slug (URL part)', 'minerva-kb' ),
                'default' => false
            ),
            array(
                'id' => 'ticket_slug',
                'type' => 'input',
                'label' => __( 'Select ticket slug', 'minerva-kb' ),
                'default' => __( 'support-ticket', 'minerva-kb' ),
                'dependency' => array(
                    'target' => 'cpt_ticket_slug_switch',
                    'type' => 'EQ',
                    'value' => true
                ),
                'description' => __( 'NOTE: these setting affects WordPress rewrite rules. After changing them you need to go to Settings - Permalinks and press Save to update rewrite rules.', 'minerva-kb' ),
            ),
			// CPT advanced
			array(
				'id' => 'article_cpt_names_title',
				'type' => 'title',
				'label' => __( 'Post type and taxonomy advanced settings', 'minerva-kb' ),
				'description' => __( 'These setting are available to resolve conflicts with other plugins', 'minerva-kb' ),
                'is_dangerous' => true
			),
			array(
				'id' => 'cpt_advanced_switch',
				'type' => 'checkbox',
				'label' => __( 'Edit post type settings?', 'minerva-kb' ),
				'default' => false,
                'is_dangerous' => true
			),
			array(
				'id' => 'article_cpt_warning',
				'type' => 'warning',
				'label' => __( 'Following settings are available for compatibility with other plugins and change the actual post type and taxonomy. ' .
				               'If you change them, already added KB content will be hidden until you change it back. ' .
				               'If you need to change URL part, please use the slug settings above instead.', 'minerva-kb' ),
                'is_dangerous' => true
			),
			array(
				'id' => 'article_cpt',
				'type' => 'input',
				'label' => __( 'Article post type', 'minerva-kb' ),
				'default' => 'kb',
				'description' => __( 'Use only lowercase letters. Note, that if you have already added articles changing this setting will make them invisible.', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'cpt_advanced_switch',
					'type' => 'EQ',
					'value' => true
				),
                'is_dangerous' => true
			),
			array(
				'id' => 'article_cpt_category',
				'type' => 'input',
				'label' => __( 'Article topic taxonomy', 'minerva-kb' ),
				'default' => 'kbtopic',
				'description' => __( 'Use only lowercase letters. Do not use "category", as it is reserved for standard posts. Note, that if you have already added topics changing this setting will make them invisible.', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'cpt_advanced_switch',
					'type' => 'EQ',
					'value' => true
				),
                'is_dangerous' => true
			),
			array(
				'id' => 'article_cpt_tag',
				'type' => 'input',
				'label' => __( 'Article tag taxonomy', 'minerva-kb' ),
				'default' => 'kbtag',
				'description' => __( 'Use only lowercase letters. Do not use "tag", as it is reserved for standard posts. Note, that if you have already added tags changing this setting will make them invisible.', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'cpt_advanced_switch',
					'type' => 'EQ',
					'value' => true
				),
                'is_dangerous' => true
			),

			/**
			 * Search global
			 */
			array(
				'id' => 'search_global_tab',
				'type' => 'tab',
				'label' => __( 'Search (global)', 'minerva-kb' ),
				'icon' => 'fa-search'
			),
			// search global title
			array(
				'id' => 'search_global_title',
				'type' => 'title',
				'label' => __( 'Global search settings', 'minerva-kb' ),
				'description' => __( 'Configure search results page and other search options here', 'minerva-kb' )
			),
            array(
                'id' => 'search_exclude_kb_from_global_search',
                'type' => 'checkbox',
                'label' => __( 'Exclude KB articles from global search results?', 'minerva-kb' ),
                'default' => false,
                'description' => __( 'When enabled, KB articles will not appear on theme search page', 'minerva-kb' ),
            ),
			array(
				'id' => 'search_mode',
				'type' => 'select',
				'label' => __( 'Which search mode to use?', 'minerva-kb' ),
				'options' => array(
					'blocking' => __( 'Blocking', 'minerva-kb' ),
					'nonblocking' => __( 'Non-blocking (default)', 'minerva-kb' )
				),
				'default' => 'nonblocking',
				'description' => __( 'Blocking mode does not send any requests to server until user finishes typing, can be useful for reducing load on server.', 'minerva-kb' ),
			),
			array(
				'id' => 'search_request_fe_cache',
				'type' => 'checkbox',
				'label' => __( 'Enable search requests caching on client side?', 'minerva-kb' ),
				'default' => true,
				'description' => __( 'When enabled, already received search results won\'t be send again to the server until user refreshes the page', 'minerva-kb' ),
			),
            array(
                'id' => 'search_result_groups_title',
                'type' => 'title',
                'label' => __( 'Live Search additional result groups', 'minerva-kb' ),
                'description' => __( 'You can display additional search results, such as FAQ or Glossary matches', 'minerva-kb' )
            ),
            array(
                'id' => 'search_result_groups_section_info',
                'type' => 'info',
                'label' => 'Note: additional search result groups work only in live search dropdown, search results page currently shows only KB results',
            ),
            array(
                'id' => 'search_result_groups',
                'type' => 'layout_select',
                'label' => __( 'Select active Live Search additional result groups', 'minerva-kb' ),
                'default' => 'kb',
                'options' => array(
                    array(
                        'key' => 'kb',
                        'label' => __('Knowledge Base', 'minerva-kb')
                    ),
                    array(
                        'key' => 'topics',
                        'label' => __('KB Topics', 'minerva-kb')
                    ),
                    array(
                        'key' => 'faq',
                        'label' => __('FAQ', 'minerva-kb')
                    ),
                    array(
                        'key' => 'glossary',
                        'label' => __('Glossary', 'minerva-kb')
                    )
                )
            ),
            array(
                'id' => 'search_result_groups_external_info',
                'type' => 'info',
                'label' => __('Note: In order to display FAQ/Glossary in search you need to either have these items on the same page with search or have FAQ/Glossary standalone pages enabled in options', 'minerva-kb'),
            ),
            array(
                'id' => 'search_group_kb_label',
                'type' => 'input',
                'label' => __( 'Search group KB label', 'minerva-kb' ),
                'default' => __('Knowledge Base', 'minerva-kb'),
                'description' => __( 'Search group label is displayed above the results group', 'minerva-kb' )
            ),
            array(
                'id' => 'search_group_kb_limit',
                'type' => 'input',
                'label' => __( 'Search group KB limit', 'minerva-kb' ),
                'default' => -1,
                'description' => __( 'Use -1 to show all', 'minerva-kb' )
            ),
            array(
                'id' => 'search_group_kb_topics_label',
                'type' => 'input',
                'label' => __( 'Search group KB Topics label', 'minerva-kb' ),
                'default' => __('Topics', 'minerva-kb'),
                'description' => __( 'Search group label is displayed above the results group', 'minerva-kb' )
            ),
            array(
                'id' => 'search_group_kb_topics_limit',
                'type' => 'input',
                'label' => __( 'Search group KB Topics limit', 'minerva-kb' ),
                'default' => 5,
                'description' => __( 'Use -1 to show all', 'minerva-kb' )
            ),
            array(
                'id' => 'search_group_faq_label',
                'type' => 'input',
                'label' => __( 'Search group FAQ label', 'minerva-kb' ),
                'default' => __('FAQ', 'minerva-kb'),
                'description' => __( 'Search group label is displayed above the results group', 'minerva-kb' )
            ),
            array(
                'id' => 'search_group_faq_limit',
                'type' => 'input',
                'label' => __( 'Search group FAQ limit', 'minerva-kb' ),
                'default' => 5,
                'description' => __( 'Use -1 to show all', 'minerva-kb' )
            ),
            array(
                'id' => 'search_group_glossary_label',
                'type' => 'input',
                'label' => __( 'Search group Glossary label', 'minerva-kb' ),
                'default' => __('Glossary', 'minerva-kb'),
                'description' => __( 'Search group label is displayed above the results group', 'minerva-kb' )
            ),
            array(
                'id' => 'search_group_glossary_limit',
                'type' => 'input',
                'label' => __( 'Search group Glossary limit', 'minerva-kb' ),
                'default' => 5,
                'description' => __( 'Use -1 to show all', 'minerva-kb' )
            ),
			array(
				'id' => 'search_request_icon',
				'type' => 'icon_select',
				'label' => __( 'Search request icon', 'minerva-kb' ),
				'default' => 'fa-circle-o-notch',
			),
			array(
				'id' => 'search_request_icon_color',
				'type' => 'color',
				'label' => __( 'Search request icon color', 'minerva-kb' ),
				'default' => '#2ab77b'
			),
			array(
				'id' => 'search_include_tag_matches',
				'type' => 'checkbox',
				'label' => __( 'Include tag matches in search results?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'Only exact matches are added, for ex. search for install will match articles with tag install, not installation', 'minerva-kb' ),
			),
			array(
				'id' => 'search_delay',
				'type' => 'input',
				'label' => __( 'Live Search delay/throttle (ms)', 'minerva-kb' ),
				'default' => 1000,
				'description' => __( 'Delay before search after the moment user stops typing query, in milliseconds. For non-blocking mode - minimum interval between requests', 'minerva-kb' )
			),
			array(
				'id' => 'search_product_prefix',
				'type' => 'input_text',
				'label' => __( 'Text prefix when showing results for product in multi-product mode', 'minerva-kb' ),
				'default' => __('Showing results for', 'minerva-kb'),
				'description' => __( 'This will be displayed before search results together with current product name', 'minerva-kb' )
			),
			array(
				'id' => 'search_needle_length',
				'type' => 'input',
				'label' => __( 'Number of characters to trigger search', 'minerva-kb' ),
				'default' => 3,
				'description' => __( 'Search will not run until user types at least this amount of characters', 'minerva-kb' )
			),
			array(
				'id' => 'live_search_show_excerpt',
				'type' => 'checkbox',
				'label' => __( 'Show excerpt in live search results?', 'minerva-kb' ),
				'default' => false,
			),
			array(
				'id' => 'live_search_excerpt_length',
				'type' => 'input',
				'label' => __( 'Live search results excerpt length (in characters)', 'minerva-kb' ),
				'default' => 140
			),
			array(
				'id' => 'live_search_disable_mobile',
				'type' => 'checkbox',
				'label' => __( 'Disable live search on mobile?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'When disabled, search page will be shown instead', 'minerva-kb' ),
			),
			array(
				'id' => 'live_search_disable_tablet',
				'type' => 'checkbox',
				'label' => __( 'Disable live search on tablet?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'When disabled, search page will be shown instead', 'minerva-kb' ),
			),
			array(
				'id' => 'live_search_disable_desktop',
				'type' => 'checkbox',
				'label' => __( 'Disable live search on desktop?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'When disabled, search page will be shown instead', 'minerva-kb' ),
			),
			array(
				'id' => 'live_search_use_post',
				'type' => 'checkbox',
				'label' => __( 'Use POST http method for search requests?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'Can be useful if you have conflicts with caching plugins', 'minerva-kb' ),
			),
			/**
			 * Search results page
			 */
			array(
				'id' => 'search_results_title',
				'type' => 'title',
				'label' => __( 'Search results page settings', 'minerva-kb' ),
				'description' => __( 'Configure appearance and display mode of search results page', 'minerva-kb' )
			),
			array(
				'id' => 'search_results_top_padding',
				'type' => 'css_size',
				'label' => __( 'Search results page top padding', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "0"),
				'description' => __( 'Distance between header and search results page content', 'minerva-kb' )
			),
			array(
				'id' => 'search_results_bottom_padding',
				'type' => 'css_size',
				'label' => __( 'Search results  page bottom padding', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "0"),
				'description' => __( 'Distance between search results page content and footer', 'minerva-kb' )
			),
			array(
				'id' => 'search_sidebar',
				'type' => 'image_select',
				'label' => __( 'Search results page sidebar position', 'minerva-kb' ),
				'options' => array(
					'none' => array(
						'label' => __( 'None', 'minerva-kb' ),
						'img' => MINERVA_KB_IMG_URL . 'no-sidebar.png'
					),
					'left' => array(
						'label' => __( 'Left', 'minerva-kb' ),
						'img' => MINERVA_KB_IMG_URL . 'left-sidebar.png'
					),
					'right' => array(
						'label' => __( 'Right', 'minerva-kb' ),
						'img' => MINERVA_KB_IMG_URL . 'right-sidebar.png'
					),
				),
				'default' => 'right',
				'description' => __( 'You can add widgets to sidebars under Appearance - Widgets', 'minerva-kb' )
			),
			array(
				'id' => 'search_results_per_page',
				'type' => 'input',
				'label' => __( 'Number of search results per page. Use -1 to show all', 'minerva-kb' ),
				'default' => __( '10', 'minerva-kb' )
			),
			array(
				'id' => 'show_search_page_search',
				'type' => 'checkbox',
				'label' => __( 'Show search box on results page?', 'minerva-kb' ),
				'default' => true,
				'description' => __( 'Search settings from topic search will be used', 'minerva-kb' ),
			),
			array(
				'id' => 'show_breadcrumbs_search',
				'type' => 'checkbox',
				'label' => __( 'Show breadcrumbs on search results page?', 'minerva-kb' ),
				'default' => true,
				'description' => __( 'Enable/disable breadcrumbs for search results page', 'minerva-kb' ),
			),
			array(
				'id' => 'search_results_breadcrumbs_label',
				'type' => 'input_text',
				'label' => __( 'Search breadcrumbs label', 'minerva-kb' ),
				'default' => __( 'Search results for %s', 'minerva-kb' ),
				'description' => __( '%s will be replaced with search term', 'minerva-kb' ),
			),
			array(
				'id' => 'search_results_page_title',
				'type' => 'input_text',
				'label' => __( 'Search page title', 'minerva-kb' ),
				'default' => __( 'Found %s results for: %s', 'minerva-kb' ),
				'description' => __( '%s will be replaced with number of results and search term', 'minerva-kb' ),
			),
			array(
				'id' => 'search_results_layout',
				'type' => 'select',
				'label' => __( 'Which search results page layout to use?', 'minerva-kb' ),
				'options' => array(
					'simple' => __( 'Simple', 'minerva-kb' ),
					'detailed' => __( 'Detailed (with excerpt)', 'minerva-kb' )
				),
				'default' => 'detailed'
			),
			array(
				'id' => 'search_results_detailed_title',
				'type' => 'title',
				'label' => __( 'Search results detailed layout settings', 'minerva-kb' ),
				'description' => __( 'Configure settings of detailed mode for search results', 'minerva-kb' )
			),

			array(
				'id' => 'search_results_match_color',
				'type' => 'color',
				'label' => __( 'Search match in excerpt color', 'minerva-kb' ),
				'default' => '#000'
			),
			array(
				'id' => 'search_results_match_bg',
				'type' => 'color',
				'label' => __( 'Search match in excerpt background color', 'minerva-kb' ),
				'default' => 'rgba(255,255,255,0)'
			),
			array(
				'id' => 'show_search_page_topic',
				'type' => 'checkbox',
				'label' => __( 'Show article topic on results page?', 'minerva-kb' ),
				'default' => true
			),
			array(
				'id' => 'search_results_settings_info',
				'type' => 'info',
				'label' => 'Please note, that more Detailed view settings can be found in "Topics" section of settings.',
			),
			array(
				'id' => 'search_no_results_title',
				'type' => 'input_text',
				'label' => __( 'Search no results page title', 'minerva-kb' ),
				'default' => __( 'Nothing Found', 'minerva-kb' )
			),
			array(
				'id' => 'search_no_results_subtitle',
				'type' => 'input_text',
				'label' => __( 'Search no results page subtitle', 'minerva-kb' ),
				'default' => __( 'Sorry, but nothing matched your search terms. Please try again with some different keywords.', 'minerva-kb' )
			),

			/**
			 * Article
			 */
			array(
				'id' => 'single_tab',
				'type' => 'tab',
				'label' => __( 'Article', 'minerva-kb' ),
				'icon' => 'fa-file-text-o'
			),
            // article layout
            array(
                'id' => 'article_layout_title',
                'type' => 'title',
                'label' => __( 'Article layout', 'minerva-kb' ),
                'description' => __( 'Configure appearance of single template', 'minerva-kb' )
            ),
			array(
				'id' => 'single_template',
				'type' => 'select',
				'label' => __( 'Which template to use?', 'minerva-kb' ),
				'options' => array(
					'theme' => __( 'Theme single template', 'minerva-kb' ),
					'plugin' => __( 'Plugin article template', 'minerva-kb' )
				),
				'default' => 'plugin',
				'experimental' => __( 'This is experimental feature and depends a lot on theme styles and layout', 'minerva-kb' ),
				'description' => __( 'Note, that you can override plugin templates in your theme. See documentation for details', 'minerva-kb' )
			),
			array(
				'id' => 'single_top_padding',
				'type' => 'css_size',
				'label' => __( 'Article page top padding', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "3"),
				'description' => __( 'Distance between header and article content', 'minerva-kb' )
			),
			array(
				'id' => 'single_bottom_padding',
				'type' => 'css_size',
				'label' => __( 'Article page bottom padding', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "3"),
				'description' => __( 'Distance between article content and footer', 'minerva-kb' )
			),
            // article header
            array(
                'id' => 'article_header_title',
                'type' => 'title',
                'label' => __( 'Article header items', 'minerva-kb' ),
                'description' => __( 'Configure appearance of article header', 'minerva-kb' )
            ),
            array(
                'id' => 'show_article_title',
                'type' => 'checkbox',
                'label' => __( 'Show article title?', 'minerva-kb' ),
                'default' => true,
                'description' => __( 'You may remove article title in case theme already displays it', 'minerva-kb' )
            ),
			array(
				'id' => 'show_last_modified_date',
				'type' => 'checkbox',
				'label' => __( 'Show last modified date?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'last_modified_date_text',
				'type' => 'input_text',
				'label' => __( 'Last modified date label', 'minerva-kb' ),
				'default' => __( 'Last modified:', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'show_last_modified_date',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'show_article_versions',
				'type' => 'checkbox',
				'label' => __( 'Show article versions?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'article_versions_text',
				'type' => 'input_text',
				'label' => __( 'Article versions label', 'minerva-kb' ),
				'default' => __( 'For versions:', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'show_article_versions',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'enable_versions_links',
				'type' => 'checkbox',
				'label' => __( 'Enable links to versions archive (version archives must be enabled)?', 'minerva-kb' ),
				'default' => false,
				'dependency' => array(
					'target' => 'show_article_versions',
					'type' => 'EQ',
					'value' => true
				)
			),
            // article estimated reading time
            array(
                'id' => 'article_estimated_time_title',
                'type' => 'title',
                'label' => __( 'Estimated reading time', 'minerva-kb' ),
                'description' => __( 'Configure appearance of article reading time', 'minerva-kb' )
            ),
			array(
				'id' => 'show_reading_estimate',
				'type' => 'checkbox',
				'label' => __( 'Show estimated reading time?', 'minerva-kb' ),
				'default' => true
			),
			array(
				'id' => 'estimated_time_text',
				'type' => 'input_text',
				'label' => __( 'Estimated reading time text', 'minerva-kb' ),
				'default' => __( 'Estimated reading time:', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'show_reading_estimate',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'estimated_time_less_than_min',
				'type' => 'input_text',
				'label' => __( 'Estimated reading less than 1 minute text', 'minerva-kb' ),
				'default' => __( '< 1 min', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'show_reading_estimate',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'estimated_time_min',
				'type' => 'input_text',
				'label' => __( 'Estimated reading minute text', 'minerva-kb' ),
				'default' => __( 'min', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'show_reading_estimate',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'estimated_time_icon',
				'type' => 'icon_select',
				'label' => __( 'Estimated time icon', 'minerva-kb' ),
				'default' => 'fa-clock-o',
				'dependency' => array(
					'target' => 'show_reading_estimate',
					'type' => 'EQ',
					'value' => true
				)
			),
            // article sidebar
            array(
                'id' => 'article_sidebar_title',
                'type' => 'title',
                'label' => __( 'Article sidebar settings', 'minerva-kb' ),
                'description' => __( 'Configure appearance of article sidebar', 'minerva-kb' )
            ),
            array(
                'id' => 'article_sidebar',
                'type' => 'image_select',
                'label' => __( 'Article sidebar position', 'minerva-kb' ),
                'options' => array(
                    'none' => array(
                        'label' => __( 'None', 'minerva-kb' ),
                        'img' => MINERVA_KB_IMG_URL . 'no-sidebar.png'
                    ),
                    'left' => array(
                        'label' => __( 'Left', 'minerva-kb' ),
                        'img' => MINERVA_KB_IMG_URL . 'left-sidebar.png'
                    ),
                    'right' => array(
                        'label' => __( 'Right', 'minerva-kb' ),
                        'img' => MINERVA_KB_IMG_URL . 'right-sidebar.png'
                    ),
                ),
                'default' => 'right',
                'description' => __( 'You can add widgets to sidebars under Appearance - Widgets', 'minerva-kb' )
            ),
            array(
                'id' => 'article_sidebar_sticky',
                'type' => 'checkbox',
                'label' => __( 'Make article sidebar sticky?', 'minerva-kb' ),
                'default' => false,
                'description' => __( 'You can make sidebar stick to top of the window on scroll', 'minerva-kb' ),
                'dependency' => array(
                    'target' => 'article_sidebar',
                    'type' => 'NEQ',
                    'value' => 'none'
                )
            ),
            array(
                'id' => 'article_sidebar_sticky_top',
                'type' => 'css_size',
                'label' => __( 'Sticky sidebar top position', 'minerva-kb' ),
                'default' => array("unit" => 'em', "size" => "3"),
                'description' => __( 'Distance between top of page and sidebar when in sticky mode', 'minerva-kb' ),
                'dependency' => array(
                    'target' => 'article_sidebar',
                    'type' => 'NEQ',
                    'value' => 'none'
                )
            ),
            array(
                'id' => 'article_sidebar_sticky_min_width',
                'type' => 'css_size',
                'label' => __( 'Disable sticky sidebar when screen width less than', 'minerva-kb' ),
                'default' => array("unit" => 'px', "size" => "1025"),
                'units' => array('px'),
                'description' => __( 'You can set the minimum required browser width for sticky sidebar', 'minerva-kb' ),
                'dependency' => array(
                    'target' => 'article_sidebar',
                    'type' => 'NEQ',
                    'value' => 'none'
                )
            ),
            // article footer
            array(
                'id' => 'article_footer_title',
                'type' => 'title',
                'label' => __( 'Article footer items', 'minerva-kb' ),
                'description' => __( 'Configure appearance of article footer', 'minerva-kb' )
            ),
			array(
				'id' => 'show_pageviews',
				'type' => 'checkbox',
				'label' => __( 'Show pageviews count?', 'minerva-kb' ),
				'default' => true
			),
			array(
				'id' => 'pageviews_label',
				'type' => 'input_text',
				'label' => __( 'Views label', 'minerva-kb' ),
				'default' => __( 'Views:', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'show_pageviews',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'enable_comments',
				'type' => 'checkbox',
				'label' => __( 'Enable comments?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'comments_position',
				'type' => 'select',
				'label' => __( 'Comments position', 'minerva-kb' ),
				'options' => array(
					'after_content' => __( 'After article content', 'minerva-kb' ),
					'inside_container' => __( 'Inside container', 'minerva-kb' ),
					'after_container' => __( 'After container', 'minerva-kb' )
				),
				'default' => 'after_container',
				'experimental' => __( 'This is experimental feature and depends a lot on theme styles and layout', 'minerva-kb' ),
				'description' => __( 'You can choose where to display comments box: right after article content, inside the container element or after the container element', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'enable_comments',
					'type' => 'NEQ',
					'value' => 'none'
				)
			),
			array(
				'id' => 'article_pagination_label',
				'type' => 'input_text',
				'label' => __( 'Article pagination label', 'minerva-kb' ),
				'default' => __( 'Pages:', 'minerva-kb' )
			),
			array(
				'id' => 'add_article_html',
				'type' => 'checkbox',
				'label' => __( 'Add custom HTML at the bottom of each article?', 'minerva-kb' ),
				'default' => false
			),
            array(
                'id' => 'article_html',
                'type' => 'textarea_text',
                'label' => __( 'Article custom HTML', 'minerva-kb' ),
                'default' => '',
                'description' => __( 'This HTML will be displayed after each article content. You can use it to display additional support contacts or info.', 'minerva-kb' ),
                'dependency' => array(
                    'target' => 'add_article_html',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
			array(
				'id' => 'show_related_articles',
				'type' => 'checkbox',
				'label' => __( 'Show related articles?', 'minerva-kb' ),
				'default' => true
			),
			array(
				'id' => 'related_articles_label',
				'type' => 'input_text',
				'label' => __( 'Related articles title', 'minerva-kb' ),
				'default' => __( 'Related articles', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'show_related_articles',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'show_article_author',
				'type' => 'checkbox',
				'label' => __( 'Show article author?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'article_author_text',
				'type' => 'input_text',
				'label' => __( 'Article author text', 'minerva-kb' ),
				'default' => __( 'Written by:', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'show_article_author',
					'type' => 'EQ',
					'value' => true
				)
			),
            // article next/previous
            array(
                'id' => 'article_navigation_title',
                'type' => 'title',
                'label' => __( 'Article navigation', 'minerva-kb' ),
                'description' => __( 'Configure appearance of article next / previous links', 'minerva-kb' )
            ),
            array(
                'id' => 'show_article_navigation',
                'type' => 'checkbox',
                'label' => __( 'Show article navigation (next/previous links)?', 'minerva-kb' ),
                'default' => false
            ),
            array(
                'id' => 'show_navigation_heading',
                'type' => 'checkbox',
                'label' => __( 'Show navigation heading?', 'minerva-kb' ),
                'default' => false,
                'dependency' => array(
                    'target' => 'show_article_navigation',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
            array(
                'id' => 'article_navigation_label',
                'type' => 'input_text',
                'label' => __( 'Navigation heading', 'minerva-kb' ),
                'default' => __( 'Continue reading', 'minerva-kb' ),
                'dependency' => array(
                    'target' => 'show_article_navigation',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
            array(
                'id' => 'article_navigation_prev_label',
                'type' => 'input_text',
                'label' => __( 'Previous label', 'minerva-kb' ),
                'default' => __( 'Previous: ', 'minerva-kb' ),
                'dependency' => array(
                    'target' => 'show_article_navigation',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
            array(
                'id' => 'article_navigation_next_label',
                'type' => 'input_text',
                'label' => __( 'Next label', 'minerva-kb' ),
                'default' => __( 'Next: ', 'minerva-kb' ),
                'dependency' => array(
                    'target' => 'show_article_navigation',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
            array(
                'id' => 'use_same_topic_navigation',
                'type' => 'checkbox',
                'label' => __( 'Show articles from same term (topic) only?', 'minerva-kb' ),
                'default' => true,
                'dependency' => array(
                    'target' => 'show_article_navigation',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
            // article tags settings
            array(
                'id' => 'article_tags_title',
                'type' => 'title',
                'label' => __( 'Article tags', 'minerva-kb' ),
                'description' => __( 'Configure article tags display settings', 'minerva-kb' )
            ),
			array(
				'id' => 'show_article_tags',
				'type' => 'checkbox',
				'label' => __( 'Show article tags?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'show_article_tags_icon',
				'type' => 'checkbox',
				'label' => __( 'Show article tags icon?', 'minerva-kb' ),
				'default' => true,
				'dependency' => array(
					'target' => 'show_article_tags',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'article_tags_icon',
				'type' => 'icon_select',
				'label' => __( 'Article tags icon', 'minerva-kb' ),
				'default' => 'fa-tag',
				'dependency' => array(
					'target' => 'show_article_tags',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'article_tags_label',
				'type' => 'input_text',
				'label' => __( 'Tags label', 'minerva-kb' ),
				'default' => __( 'Tags:', 'minerva-kb' ),
				'description' => __( 'Set this field empty to remove text label', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'show_article_tags',
					'type' => 'EQ',
					'value' => true
				)
			),
            // other article settings
            array(
                'id' => 'article_other_title',
                'type' => 'title',
                'label' => __( 'Misc. article settings', 'minerva-kb' ),
                'description' => __( 'Advanced article settings', 'minerva-kb' )
            ),
            array(
                'id' => 'article_disable_block_editor',
                'type' => 'checkbox',
                'label' => __( 'Disable block editor for Articles? (WordPress v5.0+)', 'minerva-kb' ),
                'default' => false
            ),
            array(
                'id' => 'article_fancybox',
                'type' => 'checkbox',
                'label' => __( 'Add fancybox to article images?', 'minerva-kb' ),
                'default' => true,
                'description' => __( 'NOTE: To enable fancybox for image, you need to set <b>Link To</b> option to <b>Media file</b> when adding media to article', 'minerva-kb' ),
            ),
            array(
                'id' => 'article_include_base_html',
                'type' => 'checkbox',
                'label' => __( 'Include base HTML styles in article?', 'minerva-kb' ),
                'description' => __( 'Compatibility option for themes that remove basic HTML styles.', 'minerva-kb' ),
                'default' => false
            ),
            array(
                'id' => 'article_no_content_filter',
                'type' => 'checkbox',
                'label' => __( 'Do not use content filter for article in Theme template', 'minerva-kb' ),
                'default' => false,
                'description' => __( 'You may enable this option if you want to build custom layout for KB article pages via external page builder. You will need to use article content shortcode to display KB article elements', 'minerva-kb' )
            ),
			/**
			 * Attachments
			 */
			array(
				'id' => 'global_attachments_tab',
				'type' => 'tab',
				'label' => __( 'Attachments', 'minerva-kb' ),
				'icon' => 'fa-paperclip'
			),
			array(
				'id' => 'global_attachments_title',
				'type' => 'title',
				'label' => __( 'Attachments settings', 'minerva-kb' ),
				'description' => __( 'Configure appearance and display mode of attachments', 'minerva-kb' )
			),
			array(
				'id' => 'article_attach_label',
				'type' => 'input_text',
				'label' => __( 'Article attachments label', 'minerva-kb' ),
				'default' => __( 'Attachments', 'minerva-kb' ),
				'description' => __( 'Set this field empty to remove text label', 'minerva-kb' )
			),
			array(
				'id' => 'attach_archive_file_label',
				'type' => 'select',
				'label' => __( 'Article attachments file label', 'minerva-kb' ),
				'options' => array(
					'title' => __( 'Attachment title', 'minerva-kb' ),
					'filename' => __( 'Attachment filename', 'minerva-kb' ),
				),
				'default' => 'title',
				'description' => __( 'You can use filename with extension or attachment title', 'minerva-kb' )
			),
			array(
				'id' => 'show_attach_size',
				'type' => 'checkbox',
				'label' => __( 'Show attachment size?', 'minerva-kb' ),
				'default' => true
			),
			array(
				'id' => 'attach_icons_off',
				'type' => 'checkbox',
				'label' => __( 'Disable file icons?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'attach_archive_icon',
				'type' => 'icon_select',
				'label' => __( 'Archive file icon', 'minerva-kb' ),
				'default' => 'fa-file-archive-o'
			),
			array(
				'id' => 'attach_archive_color',
				'type' => 'color',
				'label' => __( 'Archive file color', 'minerva-kb' ),
				'default' => '#555759'
			),
			array(
				'id' => 'attach_pdf_icon',
				'type' => 'icon_select',
				'label' => __( 'PDF file icon', 'minerva-kb' ),
				'default' => 'fa-file-pdf-o'
			),
			array(
				'id' => 'attach_pdf_color',
				'type' => 'color',
				'label' => __( 'Pdf file color', 'minerva-kb' ),
				'default' => '#f02f13'
			),
			array(
				'id' => 'attach_text_icon',
				'type' => 'icon_select',
				'label' => __( 'Text file icon', 'minerva-kb' ),
				'default' => 'fa-file-text-o'
			),
			array(
				'id' => 'attach_text_color',
				'type' => 'color',
				'label' => __( 'Text file color', 'minerva-kb' ),
				'default' => '#555759'
			),
			array(
				'id' => 'attach_image_icon',
				'type' => 'icon_select',
				'label' => __( 'Image file icon', 'minerva-kb' ),
				'default' => 'fa-file-image-o'
			),
			array(
				'id' => 'attach_image_color',
				'type' => 'color',
				'label' => __( 'Image file color', 'minerva-kb' ),
				'default' => '#df0000'
			),
			array(
				'id' => 'attach_excel_icon',
				'type' => 'icon_select',
				'label' => __( 'Spreadsheet file icon', 'minerva-kb' ),
				'default' => 'fa-file-excel-o'
			),
			array(
				'id' => 'attach_excel_color',
				'type' => 'color',
				'label' => __( 'Spreadsheet file color', 'minerva-kb' ),
				'default' => '#24724B'
			),
			array(
				'id' => 'attach_word_icon',
				'type' => 'icon_select',
				'label' => __( 'Word file icon', 'minerva-kb' ),
				'default' => 'fa-file-word-o'
			),
			array(
				'id' => 'attach_word_color',
				'type' => 'color',
				'label' => __( 'Word file color', 'minerva-kb' ),
				'default' => '#295698'
			),
			array(
				'id' => 'attach_video_icon',
				'type' => 'icon_select',
				'label' => __( 'Video file icon', 'minerva-kb' ),
				'default' => 'fa-file-video-o'
			),
			array(
				'id' => 'attach_video_color',
				'type' => 'color',
				'label' => __( 'Video file color', 'minerva-kb' ),
				'default' => '#19b7ea'
			),
			array(
				'id' => 'attach_audio_icon',
				'type' => 'icon_select',
				'label' => __( 'Audio file icon', 'minerva-kb' ),
				'default' => 'fa-file-audio-o'
			),
			array(
				'id' => 'attach_audio_color',
				'type' => 'color',
				'label' => __( 'Audio file color', 'minerva-kb' ),
				'default' => '#faa703'
			),
			array(
				'id' => 'attach_default_icon',
				'type' => 'icon_select',
				'label' => __( 'Default file icon', 'minerva-kb' ),
				'default' => 'fa-file-o'
			),
			array(
				'id' => 'attach_default_color',
				'type' => 'color',
				'label' => __( 'Default file color', 'minerva-kb' ),
				'default' => '#555759'
			),

			/**
			 * Article versions
			 */
			array(
				'id' => 'article_versions_tab',
				'type' => 'tab',
				'label' => __( 'Article versions', 'minerva-kb' ),
				'icon' => 'fa-flag'
			),
			array(
				'id' => 'article_versions_title',
				'type' => 'title',
				'label' => __( 'Article versions', 'minerva-kb' ),
				'description' => __( 'You can use versions if your main product is software and you need to indicate for which software versions article is written', 'minerva-kb' )
			),
			array(
				'id' => 'add_article_versions',
				'type' => 'checkbox',
				'label' => __( 'Enable versions tag for articles? (you will need to refresh the page after changing this)', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'enable_versions_archive',
				'type' => 'checkbox',
				'label' => __( 'Enable versions archive (displays all articles for given version)?', 'minerva-kb' ),
				'default' => false,
				'dependency' => array(
					'target' => 'add_article_versions',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'versions_slug',
				'type' => 'input',
				'label' => __( 'Versions URL sluq (must be unique and not used by posts or pages)', 'minerva-kb' ),
				'default' => __( 'kbversion', 'minerva-kb' ),
				'description' => __( 'NOTE: this setting affects WordPress rewrite rules. After changing it you need to go to Settings - Permalinks and press Save to update rewrite rules.', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'add_article_versions',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'version_label_bg',
				'type' => 'color',
				'label' => __( 'Version label background color', 'minerva-kb' ),
				'default' => '#00a0d2',
				'dependency' => array(
					'target' => 'add_article_versions',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'version_label_text_color',
				'type' => 'color',
				'label' => __( 'Version label text color', 'minerva-kb' ),
				'default' => '#fff',
				'dependency' => array(
					'target' => 'add_article_versions',
					'type' => 'EQ',
					'value' => true
				)
			),

			/**
			 * Article search
			 */
			array(
				'id' => 'article_search_tab',
				'type' => 'tab',
				'label' => __( 'Article search', 'minerva-kb' ),
				'icon' => 'fa-search'
			),
			array(
				'id' => 'add_article_search',
				'type' => 'checkbox',
				'label' => __( 'Add search in articles?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'article_search_title',
				'type' => 'input_text',
				'label' => __( 'Article search title', 'minerva-kb' ),
				'default' => __( '', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'add_article_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'article_search_title_color',
				'type' => 'color',
				'label' => __( 'Search title color', 'minerva-kb' ),
				'default' => '#333333',
				'dependency' => array(
					'target' => 'add_article_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'article_search_title_size',
				'type' => 'input',
				'label' => __( 'Search title font size', 'minerva-kb' ),
				'default' => __( '1.2em', 'minerva-kb' ),
				'description' => 'Use any CSS value, for ex. 3em or 20px',
				'dependency' => array(
					'target' => 'add_article_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'article_search_theme',
				'type' => 'select',
				'label' => __( 'Which search input theme to use?', 'minerva-kb' ),
				'options' => array(
					'minerva' => __( 'Minerva', 'minerva-kb' ),
					'clean' => __( 'Clean', 'minerva-kb' ),
					'mini' => __( 'Mini', 'minerva-kb' ),
					'bold' => __( 'Bold', 'minerva-kb' ),
					'invisible' => __( 'Invisible', 'minerva-kb' ),
					'thick' => __( 'Thick', 'minerva-kb' ),
					'3d' => __( '3d', 'minerva-kb' ),
				),
				'default' => 'mini',
				'description' => __( 'Use predefined styles for search bar', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'add_article_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'article_search_border_color',
				'type' => 'color',
				'label' => __( 'Search wrap border color (not in all themes)', 'minerva-kb' ),
				'default' => '#ffffff',
				'dependency' => array(
					'target' => 'add_article_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'article_search_min_width',
				'type' => 'input',
				'label' => __( 'Search input minimum width', 'minerva-kb' ),
				'default' => __( '100%', 'minerva-kb' ),
				'description' => 'Use any CSS value, for ex. 40em or 300px. em are better for mobile devices',
				'dependency' => array(
					'target' => 'add_article_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'article_search_container_padding_top',
				'type' => 'input',
				'label' => __( 'Search container top padding', 'minerva-kb' ),
				'default' => __( '0', 'minerva-kb' ),
				'description' => 'Use any CSS value, for ex. 3em or 50px',
				'dependency' => array(
					'target' => 'add_article_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'article_search_container_padding_bottom',
				'type' => 'input',
				'label' => __( 'Search container bottom padding', 'minerva-kb' ),
				'default' => __( '0', 'minerva-kb' ),
				'description' => 'Use any CSS value, for ex. 3em or 50px',
				'dependency' => array(
					'target' => 'add_article_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'article_search_placeholder',
				'type' => 'input_text',
				'label' => __( 'Article search placeholder', 'minerva-kb' ),
				'default' => __( 'ex.: Installation', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'add_article_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'article_disable_autofocus',
				'type' => 'checkbox',
				'label' => __( 'Disable search field autofocus?', 'minerva-kb' ),
				'default' => true,
				'dependency' => array(
					'target' => 'add_article_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'article_show_search_tip',
				'type' => 'checkbox',
				'label' => __( 'Show search tip?', 'minerva-kb' ),
				'default' => false,
				'dependency' => array(
					'target' => 'add_article_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'article_search_tip',
				'type' => 'input_text',
				'label' => __( 'Article search tip (under the input)', 'minerva-kb' ),
				'default' => __( 'Tip: Use arrows to navigate results, ESC to focus search input', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'add_article_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'article_search_tip_color',
				'type' => 'color',
				'label' => __( 'Search tip color', 'minerva-kb' ),
				'default' => '#cccccc',
				'dependency' => array(
					'target' => 'add_article_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'article_search_container_bg',
				'type' => 'color',
				'label' => __( 'Search container background color', 'minerva-kb' ),
				'default' => '#ffffff',
				'dependency' => array(
					'target' => 'add_article_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'article_search_container_image_bg',
				'type' => 'media',
				'label' => __( 'Search container background image URL (optional)', 'minerva-kb' ),
				'default' => '',
				'dependency' => array(
					'target' => 'add_article_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'article_add_gradient_overlay',
				'type' => 'checkbox',
				'label' => __( 'Add gradient overlay?', 'minerva-kb' ),
				'default' => false,
				'dependency' => array(
					'target' => 'add_article_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'article_search_container_gradient_from',
				'type' => 'color',
				'label' => __( 'Search container gradient from', 'minerva-kb' ),
				'default' => '#00c1b6',
				'dependency' => array(
					'target' => 'add_article_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'article_search_container_gradient_to',
				'type' => 'color',
				'label' => __( 'Search container gradient to', 'minerva-kb' ),
				'default' => '#136eb5',
				'dependency' => array(
					'target' => 'add_article_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'article_search_container_gradient_opacity',
				'type' => 'input',
				'label' => __( 'Search container background gradient opacity', 'minerva-kb' ),
				'default' => 1,
				'description' => __( 'Use any CSS opacity value, for example 1 or 0.7', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'add_article_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'article_add_pattern_overlay',
				'type' => 'checkbox',
				'label' => __( 'Add pattern overlay?', 'minerva-kb' ),
				'default' => false,
				'dependency' => array(
					'target' => 'add_article_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'article_search_container_image_pattern',
				'type' => 'media',
				'label' => __( 'Search container background pattern image URL (optional)', 'minerva-kb' ),
				'default' => '',
				'dependency' => array(
					'target' => 'add_article_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'article_search_container_image_pattern_opacity',
				'type' => 'input',
				'label' => __( 'Search container background pattern opacity', 'minerva-kb' ),
				'default' => 1,
				'description' => __( 'Use any CSS opacity value, for example 1 or 0.7. You can also use transparent .png and set opacity to 1', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'add_article_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'article_show_topic_in_results',
				'type' => 'checkbox',
				'label' => __( 'Show topic in results?', 'minerva-kb' ),
				'default' => true,
				'dependency' => array(
					'target' => 'add_article_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			/**
			 * Guest posting
			 */
			array(
				'id' => 'submission_tab',
				'type' => 'tab',
				'label' => __( 'Guest posting', 'minerva-kb' ),
				'icon' => 'fa-paper-plane-o'
			),
			array(
				'id' => 'submit_settings_title',
				'type' => 'title',
				'label' => __( 'Guest posting settings', 'minerva-kb' ),
				'description' => __( 'You can allow users or guests to submit KB content without giving them access to Dashboard. To do so you need to insert Submission form shortcode on any page. Submitted articles will be saved as new Drafts. NOTE, you can insert only one form per page.', 'minerva-kb' )
			),
			array(
				'id' => 'submit_usage',
				'type' => 'code',
				'label' => __( 'Submit form shortcode example', 'minerva-kb' ),
				'default' => '[mkb-guestpost]'
			),
			array(
				'id' => 'submit_disable',
				'type' => 'checkbox',
				'label' => __( 'Disable submission forms?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'submit_disable_message',
				'type' => 'input_text',
				'label' => __( 'Submit disabled message (optional)', 'minerva-kb' ),
				'default' => __( 'Content submission is currently disabled.', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'submit_disable',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'antispam_quiz_enable',
				'type' => 'checkbox',
				'label' => __( 'Enable anti-spam question?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'antispam_quiz_question',
				'type' => 'input_text',
				'label' => __( 'Anti-spam question', 'minerva-kb' ),
				'default' => __( '3 + 5 = ?', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'antispam_quiz_enable',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'antispam_quiz_answer',
				'type' => 'input_text',
				'label' => __( 'Anti-spam answer', 'minerva-kb' ),
				'default' => __( '8', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'antispam_quiz_enable',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'antispam_failed_message',
				'type' => 'input_text',
				'label' => __( 'Anti-spam answer error message', 'minerva-kb' ),
				'default' => __( 'Wrong security question answer, try again.', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'antispam_quiz_enable',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'submit_restrict_enable',
				'type' => 'checkbox',
				'label' => __( 'Enable submission restriction by user role?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'submit_restrict_role',
				'type' => 'roles_select',
				'label' => __( 'Who can submit articles?', 'minerva-kb' ),
				'default' => 'none',
				'flush' => false,
				'view_log' => false,
				'description' => __( 'Select roles, that have access to articles submission on client side. By default, anyone can submit', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'submit_restrict_enable',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'submit_restriction_failed_message',
				'type' => 'input_text',
				'label' => __( 'Submit restriction failed message (optional)', 'minerva-kb' ),
				'default' => __( 'You are not allowed to submit content, please register or sign in.', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'submit_restrict_enable',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'submit_form_heading_label',
				'type' => 'input_text',
				'label' => __( 'Submit form heading label', 'minerva-kb' ),
				'default' => __( 'Submit your article', 'minerva-kb' )
			),
			array(
				'id' => 'submit_form_subheading_label',
				'type' => 'input_text',
				'label' => __( 'Submit form subheading label', 'minerva-kb' ),
				'default' => __( 'Article will be submitted and published after review.', 'minerva-kb' )
			),
			array(
				'id' => 'submit_article_title_label',
				'type' => 'input_text',
				'label' => __( 'Submit article title label', 'minerva-kb' ),
				'default' => __( 'Article title:', 'minerva-kb' )
			),
			array(
				'id' => 'submit_unique_titles',
				'type' => 'checkbox',
				'label' => __( 'Require unique titles?', 'minerva-kb' ),
				'default' => true
			),
			array(
				'id' => 'submit_unique_titles_error_message',
				'type' => 'input_text',
				'label' => __( 'Non-unique title error message', 'minerva-kb' ),
				'default' => __( 'Article title already exists, please select unique one', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'submit_unique_titles',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'submit_content_label',
				'type' => 'input_text',
				'label' => __( 'Submit content label', 'minerva-kb' ),
				'default' => __( 'Article content:', 'minerva-kb' )
			),
			array(
				'id' => 'submit_content_editor_skin',
				'type' => 'select',
				'label' => __( 'Content editor style', 'minerva-kb' ),
				'options' => array(
					'snow' => __( 'Fixed toolbar', 'minerva-kb' ),
					'bubble' => __( 'Floating toolbar', 'minerva-kb' )
				),
				'default' => 'snow'
			),
			array(
				'id' => 'submit_content_default_text',
				'type' => 'input_text',
				'label' => __( 'Submit content initial value', 'minerva-kb' ),
				'default' => __( 'Start writing your article here...', 'minerva-kb' )
			),
			array(
				'id' => 'submit_allow_topics_select',
				'type' => 'checkbox',
				'label' => __( 'Allow users to select topics?', 'minerva-kb' ),
				'default' => true
			),
			array(
				'id' => 'submit_topic_select_label',
				'type' => 'input_text',
				'label' => __( 'Submit topic select label', 'minerva-kb' ),
				'default' => __( 'Select topic:', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'submit_allow_topics_select',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'submit_send_button_label',
				'type' => 'input_text',
				'label' => __( 'Submit button label', 'minerva-kb' ),
				'default' => __( 'Submit article', 'minerva-kb' )
			),
			array(
				'id' => 'submit_send_button_bg',
				'type' => 'color',
				'label' => __( 'Submit button background color', 'minerva-kb' ),
				'default' => '#4a90e2',
			),
			array(
				'id' => 'submit_send_button_color',
				'type' => 'color',
				'label' => __( 'Submit button text color', 'minerva-kb' ),
				'default' => '#ffffff',
			),
			array(
				'id' => 'submit_success_message',
				'type' => 'input_text',
				'label' => __( 'Submit success message', 'minerva-kb' ),
				'default' => __( 'Your content has been submitted, thank you!', 'minerva-kb' )
			),

			/**
			 * Topics
			 */
			array(
				'id' => 'topic_tab',
				'type' => 'tab',
				'label' => __( 'Topics', 'minerva-kb' ),
				'icon' => 'fa-address-book-o'
			),
			array(
				'id' => 'topic_template',
				'type' => 'select',
				'label' => __( 'Which topic template to use?', 'minerva-kb' ),
				'options' => array(
					'theme' => __( 'Theme archive template', 'minerva-kb' ),
					'plugin' => __( 'Plugin topic template', 'minerva-kb' )
				),
				'default' => 'plugin',
				'experimental' => __( 'This is experimental feature and depends a lot on theme styles and layout', 'minerva-kb' ),
				'description' => __( 'Note, that you can override plugin templates in your theme. See documentation for details', 'minerva-kb' )
			),
            array(
                'id' => 'topic_page_elements_mode',
                'type' => 'select',
                'label' => __( 'Select topic page elements', 'minerva-kb' ),
                'options' => array(
                    'default' => __( 'Default', 'minerva-kb' ),
                    'reorder' => __( 'Custom order', 'minerva-kb' ),
                    'page' => __( 'Use page content', 'minerva-kb' )
                ),
                'default' => 'default',
                'description' => __( 'You can create draft page and use it as topic template. It will be used for all topics', 'minerva-kb' )
            ),
            array(
                'id' => 'topic_page_elements_order',
                'type' => 'layout_select',
                'label' => __( 'Select topic page elements order (plugin template only)', 'minerva-kb' ),
                'default' => 'title,search,breadcrumbs,children,articles,pagination',
                'options' => array(
                    array(
                        'key' => 'title',
                        'label' => __('Title & Description', 'minerva-kb')
                    ),
                    array(
                        'key' => 'search',
                        'label' => __('Search', 'minerva-kb')
                    ),
                    array(
                        'key' => 'breadcrumbs',
                        'label' => __('Breadcrumbs', 'minerva-kb')
                    ),
                    array(
                        'key' => 'children',
                        'label' => __('Child Topics', 'minerva-kb')
                    ),
                    array(
                        'key' => 'articles',
                        'label' => __('Articles List', 'minerva-kb')
                    ),
                    array(
                        'key' => 'pagination',
                        'label' => __('Pagination', 'minerva-kb')
                    )
                ),
                'dependency' => array(
                    'target' => 'topic_page_elements_mode',
                    'type' => 'EQ',
                    'value' => 'reorder'
                )
            ),
            array(
                'id' => 'topic_page_elements_page',
                'type' => 'page_select',
                'label' => __( 'Select page to use as KB Topic template', 'minerva-kb' ),
                'options' => self::get_pages_options(false),
                'default' => '',
                'description' => __( 'Note: topic elements are not rendered in preview. You can open any topic from menu to preview template changes', 'minerva-kb' ),
                'dependency' => array(
                    'target' => 'topic_page_elements_mode',
                    'type' => 'EQ',
                    'value' => 'page'
                )
            ),
            array(
                'id' => 'topic_page_elements_page_info',
                'type' => 'info',
                'label' => 'You can use these text shortcodes inside page to display standard topic template elements: <ul>
                    <li>Title: <strong>[mkb-tmpl-topic-title]</strong></li>
                    <li>Description: <strong>[mkb-tmpl-topic-description]</strong></li>
                    <li>Breadcrumbs: <strong>[mkb-tmpl-topic-breadcrumbs]</strong></li>
                    <li>Search: <strong>[mkb-tmpl-topic-search]</strong></li>
                    <li>Child Topics: <strong>[mkb-tmpl-topic-children]</strong></li>
                    <li>Articles: <strong>[mkb-tmpl-topic-loop]</strong></li>
                    <li>Pagination: <strong>[mkb-tmpl-topic-pagination]</strong></li>
                </ul>',
                'dependency' => array(
                    'target' => 'topic_page_elements_mode',
                    'type' => 'EQ',
                    'value' => 'page'
                )
            ),
			array(
				'id' => 'topic_template_view',
				'type' => 'select',
				'label' => __( 'Which topic article list layout to use?', 'minerva-kb' ),
				'options' => array(
					'simple' => __( 'Simple (default)', 'minerva-kb' ),
					'detailed' => __( 'Detailed', 'minerva-kb' )
				),
				'default' => 'simple',
				'description' => __( 'Detailed view provides extra information about articles', 'minerva-kb' )
			),
            array(
                'id' => 'show_topic_read_more',
                'type' => 'checkbox',
                'label' => __( 'Show read more link (detailed view only)?', 'minerva-kb' ),
                'default' => false,
                'dependency' => array(
                    'target' => 'topic_template_view',
                    'type' => 'EQ',
                    'value' => 'detailed'
                )
            ),
            array(
                'id' => 'topic_read_more_label',
                'type' => 'input_text',
                'label' => __( 'Detailed view Read more link text', 'minerva-kb' ),
                'default' => __( 'Read more', 'minerva-kb' ),
                'dependency' => array(
                    'target' => 'topic_template_view',
                    'type' => 'EQ',
                    'value' => 'detailed'
                )
            ),
            array(
                'id' => 'topic_read_more_view',
                'type' => 'select',
                'label' => __( 'Which read more style to use?', 'minerva-kb' ),
                'options' => array(
                    'link' => __( 'Link (default)', 'minerva-kb' ),
                    'theme_btn' => __( 'Theme button (theme form button styles used)', 'minerva-kb' )
                ),
                'default' => 'link',
                'dependency' => array(
                    'target' => 'topic_template_view',
                    'type' => 'EQ',
                    'value' => 'detailed'
                )
            ),
			array(
				'id' => 'show_topic_title',
				'type' => 'checkbox',
				'label' => __( 'Show topic title?', 'minerva-kb' ),
				'default' => true,
				'description' => __( 'You can remove topic title if theme already shows it', 'minerva-kb' )
			),
			array(
				'id' => 'show_topic_description',
				'type' => 'checkbox',
				'label' => __( 'Show topic description?', 'minerva-kb' ),
				'default' => true,
				'description' => __( 'You can remove topic description if theme already shows it', 'minerva-kb' )
			),
            array(
                'id' => 'topic_item_bg',
                'type' => 'color',
                'label' => __( 'Simple view background color', 'minerva-kb' ),
                'default' => '#f7f7f7',
            ),
            array(
                'id' => 'show_topic_list_icons',
                'type' => 'checkbox',
                'label' => __( 'Simple view: Show article icons?', 'minerva-kb' ),
                'default' => true,
                'description' => __( 'You can remove article icons in topic list', 'minerva-kb' )
            ),
            array(
                'id' => 'topic_item_top_padding',
                'type' => 'css_size',
                'label' => __( 'Simple view: item top/bottom padding', 'minerva-kb' ),
                'default' => array("unit" => 'em', "size" => "1")
            ),
            array(
                'id' => 'topic_item_left_padding',
                'type' => 'css_size',
                'label' => __( 'Simple view: item left/right padding', 'minerva-kb' ),
                'default' => array("unit" => 'em', "size" => "1")
            ),
			array(
				'id' => 'search_results_excerpt_length',
				'type' => 'input',
				'label' => __( 'Detailed view excerpt length (characters)', 'minerva-kb' ),
				'default' => __( '300', 'minerva-kb' )
			),
			array(
				'id' => 'show_search_page_views',
				'type' => 'checkbox',
				'label' => __( 'Detailed view: Show article views count?', 'minerva-kb' ),
				'default' => true,
				'description' => __( 'Views will be displayed only when > 0', 'minerva-kb' )
			),
			array(
				'id' => 'show_search_page_likes',
				'type' => 'checkbox',
				'label' => __( 'Detailed view: Show article likes count?', 'minerva-kb' ),
				'default' => true,
				'description' => __( 'Likes will be displayed only when > 0', 'minerva-kb' )
			),
			array(
				'id' => 'show_search_page_dislikes',
				'type' => 'checkbox',
				'label' => __( 'Detailed view: Show article dislikes count?', 'minerva-kb' ),
				'default' => true,
				'description' => __( 'Dislikes will be displayed only when > 0', 'minerva-kb' )
			),
			array(
				'id' => 'show_search_page_last_edit',
				'type' => 'checkbox',
				'label' => __( 'Detailed view: Show article last modified date?', 'minerva-kb' ),
				'default' => true
			),
			array(
				'id' => 'topic_articles_per_page',
				'type' => 'input',
				'label' => __( 'Number of articles per page. Use -1 to show all', 'minerva-kb' ),
				'default' => __( '10', 'minerva-kb' )
			),
			array(
				'id' => 'topic_top_padding',
				'type' => 'css_size',
				'label' => __( 'Topic page top padding', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "3"),
				'description' => __( 'Distance between header and topic page content', 'minerva-kb' )
			),
			array(
				'id' => 'topic_bottom_padding',
				'type' => 'css_size',
				'label' => __( 'Topic page bottom padding', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "3"),
				'description' => __( 'Distance between topic page content and footer', 'minerva-kb' )
			),
			array(
				'id' => 'topic_list_layout',
				'type' => 'image_select',
				'label' => __( 'Topic list layout', 'minerva-kb' ),
				'options' => array(
					'1col' => array(
						'label' => __( '1 column', 'minerva-kb' ),
						'img' => MINERVA_KB_IMG_URL . 'col-1.png'
					),
					'2col' => array(
						'label' => __( '2 columns', 'minerva-kb' ),
						'img' => MINERVA_KB_IMG_URL . 'col-2.png'
					),
					'3col' => array(
						'label' => __( '3 columns', 'minerva-kb' ),
						'img' => MINERVA_KB_IMG_URL . 'col-3.png'
					)
				),
				'default' => '1col'
			),
			array(
				'id' => 'topic_sidebar',
				'type' => 'image_select',
				'label' => __( 'Topic sidebar position', 'minerva-kb' ),
				'options' => array(
					'none' => array(
						'label' => __( 'None', 'minerva-kb' ),
						'img' => MINERVA_KB_IMG_URL . 'no-sidebar.png'
					),
					'left' => array(
						'label' => __( 'Left', 'minerva-kb' ),
						'img' => MINERVA_KB_IMG_URL . 'left-sidebar.png'
					),
					'right' => array(
						'label' => __( 'Right', 'minerva-kb' ),
						'img' => MINERVA_KB_IMG_URL . 'right-sidebar.png'
					),
				),
				'default' => 'right',
				'description' => __( 'You can add widgets to sidebars under Appearance - Widgets', 'minerva-kb' )
			),
			array(
				'id' => 'topic_children_layout',
				'type' => 'image_select',
				'label' => __( 'Sub-topics', 'minerva-kb' ),
				'options' => array(
					'2col' => array(
						'label' => __( '2 columns', 'minerva-kb' ),
						'img' => MINERVA_KB_IMG_URL . 'col-2.png'
					),
					'3col' => array(
						'label' => __( '3 columns', 'minerva-kb' ),
						'img' => MINERVA_KB_IMG_URL . 'col-3.png'
					),
					'4col' => array(
						'label' => __( '4 columns', 'minerva-kb' ),
						'img' => MINERVA_KB_IMG_URL . 'col-4.png'
					),
				),
				'default' => '2col'
			),
			array(
				'id' => 'topic_children_view',
				'type' => 'image_select',
				'label' => __( 'Sub-topics view', 'minerva-kb' ),
				'options' => array(
					'list' => array(
						'label' => __( 'List view', 'minerva-kb' ),
						'img' => MINERVA_KB_IMG_URL . 'list-view.png'
					),
					'box' => array(
						'label' => __( 'Box view', 'minerva-kb' ),
						'img' => MINERVA_KB_IMG_URL . 'box-view.png'
					)
				),
				'default' => 'box'
			),
			array(
				'id' => 'home_topics_stretch',
				'type' => 'checkbox',
				'label' => __( 'Make home page topic boxes equal height (modern browsers only)?', 'minerva-kb' ),
				'default' => true,
				'description' => __( 'Sometimes if topics have different content, it is a good idea to stretch smaller columns to bigger ones', 'minerva-kb' ),
			),
			array(
				'id' => 'topic_children_include_articles',
				'type' => 'checkbox',
				'label' => __( 'Include articles from child topics?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'When enabled, articles from nested categories will be included in current topic page', 'minerva-kb' ),
			),
			array(
				'id' => 'raw_topic_description_switch',
				'type' => 'checkbox',
				'label' => __( 'Allow HTML output in topic description?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'Some plugins replace topic description editor with visual editor. This option allows to output HTML in topic description on client side.', 'minerva-kb' ),
			),
			array(
				'id' => 'topic_customize_title',
				'type' => 'checkbox',
				'label' => __( 'Customize topic titles?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'By default, standard WordPress category title format is used', 'minerva-kb' ),
			),
			array(
				'id' => 'topic_custom_title_prefix',
				'type' => 'input_text',
				'label' => __( 'Custom topic title prefix', 'minerva-kb' ),
				'default' => __( 'Topic: ', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'topic_customize_title',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'topic_box_single_item_article_link',
				'type' => 'checkbox',
				'label' => __( 'Box view: link directly to article when only one article in topic?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'You can enable this to direct customers to article if there are no other articles in topic', 'minerva-kb' )
			),
			array(
				'id' => 'topic_show_child_topics_list',
				'type' => 'checkbox',
				'label' => __( 'List view: show child topics list before articles list?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'topic_child_topic_list_icon',
				'type' => 'icon_select',
				'label' => __( 'Child topic icon', 'minerva-kb' ),
				'default' => 'fa-folder',
				'dependency' => array(
					'target' => 'topic_show_child_topics_list',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'enable_articles_reorder',
				'type' => 'checkbox',
				'label' => __( 'Enable articles Drag n Drop custom order?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'When enabled, you will be able to reorder articles using Drag n Drop. By default, they\'re shown by date', 'minerva-kb' ),
			),
			array(
				'id' => 'articles_orderby',
				'type' => 'select',
				'label' => __( 'Articles order parameter', 'minerva-kb' ),
				'options' => array(
					'date' => __( 'Date', 'minerva-kb' ),
					'modified' => __( 'Last modified', 'minerva-kb' ),
					'title' => __( 'Title', 'minerva-kb' ),
					'ID' => __( 'ID', 'minerva-kb' ),
					'name' => __( 'Slug', 'minerva-kb' ),
					'comment_count' => __( 'Comments count', 'minerva-kb' ),
				),
				'default' => 'date',
				'dependency' => array(
					'target' => 'enable_articles_reorder',
					'type' => 'NEQ',
					'value' => true
				)
			),
			array(
				'id' => 'articles_order',
				'type' => 'select',
				'label' => __( 'Articles order', 'minerva-kb' ),
				'options' => array(
					'ASC' => __( 'Ascending', 'minerva-kb' ),
					'DESC' => __( 'Descending', 'minerva-kb' )
				),
				'default' => 'DESC',
				'dependency' => array(
					'target' => 'enable_articles_reorder',
					'type' => 'NEQ',
					'value' => true
				)
			),
			array(
				'id' => 'topic_no_results_subtitle',
				'type' => 'input_text',
				'label' => __( 'Text to display for empty archives', 'minerva-kb' ),
				'default' => __( 'We can&rsquo;t find what you&rsquo;re looking for. Try searching maybe.', 'minerva-kb' )
			),
			/**
			 * Dynamic topics
			 */
			array(
				'id' => 'dynamic_topic_tab',
				'type' => 'tab',
				'label' => __( 'Dynamic Topics', 'minerva-kb' ),
				'icon' => 'fa-address-book'
			),
			array(
				'id' => 'recent_topic_label',
				'type' => 'input_text',
				'label' => __( 'Recent label', 'minerva-kb' ),
				'default' => __( 'Recent', 'minerva-kb' )
			),
			array(
				'id' => 'recent_topic_description',
				'type' => 'input_text',
				'label' => __( 'Recent description', 'minerva-kb' ),
				'default' => __( 'Recently added articles', 'minerva-kb' )
			),
			array(
				'id' => 'updated_topic_label',
				'type' => 'input_text',
				'label' => __( 'Recently updated label', 'minerva-kb' ),
				'default' => __( 'Recently updated', 'minerva-kb' )
			),
			array(
				'id' => 'updated_topic_description',
				'type' => 'input_text',
				'label' => __( 'Updated description', 'minerva-kb' ),
				'default' => __( 'Recently updated articles', 'minerva-kb' )
			),
			array(
				'id' => 'most_viewed_topic_label',
				'type' => 'input_text',
				'label' => __( 'Most viewed label', 'minerva-kb' ),
				'default' => __( 'Most viewed', 'minerva-kb' )
			),
			array(
				'id' => 'most_viewed_topic_description',
				'type' => 'input_text',
				'label' => __( 'Most viewed description', 'minerva-kb' ),
				'default' => __( 'Articles with most pageviews', 'minerva-kb' )
			),
			array(
				'id' => 'most_liked_topic_label',
				'type' => 'input_text',
				'label' => __( 'Most liked label', 'minerva-kb' ),
				'default' => __( 'Most liked', 'minerva-kb' )
			),
			array(
				'id' => 'most_liked_topic_description',
				'type' => 'input_text',
				'label' => __( 'Most liked description', 'minerva-kb' ),
				'default' => __( 'Most useful articles, calculated by article likes', 'minerva-kb' )
			),
			/**
			 * Topic search
			 */
			array(
				'id' => 'topic_search_tab',
				'type' => 'tab',
				'label' => __( 'Topic search', 'minerva-kb' ),
				'icon' => 'fa-search'
			),
			array(
				'id' => 'add_topic_search',
				'type' => 'checkbox',
				'label' => __( 'Add search in topics?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'topic_search_title',
				'type' => 'input_text',
				'label' => __( 'Topic search title', 'minerva-kb' ),
				'default' => __( '', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'add_topic_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'topic_search_title_color',
				'type' => 'color',
				'label' => __( 'Search title color', 'minerva-kb' ),
				'default' => '#333333',
				'dependency' => array(
					'target' => 'add_topic_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'topic_search_title_size',
				'type' => 'input',
				'label' => __( 'Search title font size', 'minerva-kb' ),
				'default' => __( '1.2em', 'minerva-kb' ),
				'description' => 'Use any CSS value, for ex. 3em or 20px',
				'dependency' => array(
					'target' => 'add_topic_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'topic_search_theme',
				'type' => 'select',
				'label' => __( 'Which search input theme to use?', 'minerva-kb' ),
				'options' => array(
					'minerva' => __( 'Minerva', 'minerva-kb' ),
					'clean' => __( 'Clean', 'minerva-kb' ),
					'mini' => __( 'Mini', 'minerva-kb' ),
					'bold' => __( 'Bold', 'minerva-kb' ),
					'invisible' => __( 'Invisible', 'minerva-kb' ),
					'thick' => __( 'Thick', 'minerva-kb' ),
					'3d' => __( '3d', 'minerva-kb' ),
				),
				'default' => 'mini',
				'description' => __( 'Use predefined styles for search bar', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'add_topic_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'topic_search_border_color',
				'type' => 'color',
				'label' => __( 'Search wrap border color (not in all themes)', 'minerva-kb' ),
				'default' => '#ffffff',
				'dependency' => array(
					'target' => 'add_topic_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'topic_search_min_width',
				'type' => 'input',
				'label' => __( 'Search input minimum width', 'minerva-kb' ),
				'default' => __( '100%', 'minerva-kb' ),
				'description' => 'Use any CSS value, for ex. 40em or 300px. em are better for mobile devices',
				'dependency' => array(
					'target' => 'add_topic_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'topic_search_container_padding_top',
				'type' => 'input',
				'label' => __( 'Search container top padding', 'minerva-kb' ),
				'default' => __( '0', 'minerva-kb' ),
				'description' => 'Use any CSS value, for ex. 3em or 50px',
				'dependency' => array(
					'target' => 'add_topic_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'topic_search_container_padding_bottom',
				'type' => 'input',
				'label' => __( 'Search container bottom padding', 'minerva-kb' ),
				'default' => __( '0', 'minerva-kb' ),
				'description' => 'Use any CSS value, for ex. 3em or 50px',
				'dependency' => array(
					'target' => 'add_topic_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'topic_search_placeholder',
				'type' => 'input_text',
				'label' => __( 'Topic search placeholder', 'minerva-kb' ),
				'default' => __( 'ex.: Installation', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'add_topic_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'topic_disable_autofocus',
				'type' => 'checkbox',
				'label' => __( 'Disable search field autofocus?', 'minerva-kb' ),
				'default' => true,
				'dependency' => array(
					'target' => 'add_topic_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'topic_show_search_tip',
				'type' => 'checkbox',
				'label' => __( 'Show search tip?', 'minerva-kb' ),
				'default' => false,
				'dependency' => array(
					'target' => 'add_topic_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'topic_search_tip',
				'type' => 'input_text',
				'label' => __( 'Topic search tip (under the input)', 'minerva-kb' ),
				'default' => __( 'Tip: Use arrows to navigate results, ESC to focus search input', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'add_topic_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'topic_search_tip_color',
				'type' => 'color',
				'label' => __( 'Search tip color', 'minerva-kb' ),
				'default' => '#cccccc',
				'dependency' => array(
					'target' => 'add_topic_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'topic_search_container_bg',
				'type' => 'color',
				'label' => __( 'Search container background color', 'minerva-kb' ),
				'default' => '#ffffff',
				'dependency' => array(
					'target' => 'add_topic_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'topic_search_container_image_bg',
				'type' => 'media',
				'label' => __( 'Search container background image URL (optional)', 'minerva-kb' ),
				'default' => '',
				'dependency' => array(
					'target' => 'add_topic_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'topic_add_gradient_overlay',
				'type' => 'checkbox',
				'label' => __( 'Add gradient overlay?', 'minerva-kb' ),
				'default' => false,
				'dependency' => array(
					'target' => 'add_topic_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'topic_search_container_gradient_from',
				'type' => 'color',
				'label' => __( 'Search container gradient from', 'minerva-kb' ),
				'default' => '#00c1b6',
				'dependency' => array(
					'target' => 'add_topic_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'topic_search_container_gradient_to',
				'type' => 'color',
				'label' => __( 'Search container gradient to', 'minerva-kb' ),
				'default' => '#136eb5',
				'dependency' => array(
					'target' => 'add_topic_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'topic_search_container_gradient_opacity',
				'type' => 'input',
				'label' => __( 'Search container background gradient opacity', 'minerva-kb' ),
				'default' => 1,
				'description' => __( 'Use any CSS opacity value, for example 1 or 0.7', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'add_topic_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'topic_add_pattern_overlay',
				'type' => 'checkbox',
				'label' => __( 'Add pattern overlay?', 'minerva-kb' ),
				'default' => false,
				'dependency' => array(
					'target' => 'add_topic_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'topic_search_container_image_pattern',
				'type' => 'media',
				'label' => __( 'Search container background pattern image URL (optional)', 'minerva-kb' ),
				'default' => '',
				'dependency' => array(
					'target' => 'add_topic_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'topic_search_container_image_pattern_opacity',
				'type' => 'input',
				'label' => __( 'Search container background pattern opacity', 'minerva-kb' ),
				'default' => 1,
				'description' => __( 'Use any CSS opacity value, for example 1 or 0.7. You can also use transparent .png and set opacity to 1', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'add_topic_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'topic_show_topic_in_results',
				'type' => 'checkbox',
				'label' => __( 'Show topic in results?', 'minerva-kb' ),
				'default' => true,
				'dependency' => array(
					'target' => 'add_topic_search',
					'type' => 'EQ',
					'value' => true
				)
			),
			/**
			 * Tags
			 */
			array(
				'id' => 'tags_tab',
				'type' => 'tab',
				'label' => __( 'Tags', 'minerva-kb' ),
				'icon' => 'fa-tags'
			),
			array(
				'id' => 'tags_disable',
				'type' => 'checkbox',
				'label' => __( 'Disable tags archive?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'You can use tags for description purposes, but remove tags archive and tag links from articles', 'minerva-kb' ),
			),
			array(
				'id' => 'tag_template',
				'type' => 'select',
				'label' => __( 'Which tag template to use?', 'minerva-kb' ),
				'options' => array(
					'theme' => __( 'Theme archive template', 'minerva-kb' ),
					'plugin' => __( 'Plugin tag template', 'minerva-kb' )
				),
				'default' => 'plugin',
				'experimental' => __( 'This is experimental feature and depends a lot on theme styles and layout', 'minerva-kb' ),
				'description' => __( 'Note, that you can override plugin templates in your theme. See documentation for details', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'tags_disable',
					'type' => 'NEQ',
					'value' => true
				)
			),
			array(
				'id' => 'tag_articles_per_page',
				'type' => 'input',
				'label' => __( 'Number of articles per tag page. Use -1 to show all', 'minerva-kb' ),
				'default' => __( '10', 'minerva-kb' )
			),
			array(
				'id' => 'tag_sidebar',
				'type' => 'image_select',
				'label' => __( 'Tag sidebar position', 'minerva-kb' ),
				'options' => array(
					'none' => array(
						'label' => __( 'None', 'minerva-kb' ),
						'img' => MINERVA_KB_IMG_URL . 'no-sidebar.png'
					),
					'left' => array(
						'label' => __( 'Left', 'minerva-kb' ),
						'img' => MINERVA_KB_IMG_URL . 'left-sidebar.png'
					),
					'right' => array(
						'label' => __( 'Right', 'minerva-kb' ),
						'img' => MINERVA_KB_IMG_URL . 'right-sidebar.png'
					),
				),
				'default' => 'right',
				'description' => __( 'You can add widgets to sidebars under Appearance - Widgets', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'tags_disable',
					'type' => 'NEQ',
					'value' => true
				)
			),
			/**
			 * Breadcrumbs
			 */
			array(
				'id' => 'breadcrumbs_tab',
				'type' => 'tab',
				'label' => __( 'Breadcrumbs', 'minerva-kb' ),
				'icon' => 'fa-ellipsis-h'
			),
			array(
				'id' => 'breadcrumbs_home_label',
				'type' => 'input_text',
				'label' => __( 'Breadcrumbs home page label', 'minerva-kb' ),
				'default' => __( 'KB Home', 'minerva-kb' )
			),
			array(
				'id' => 'breadcrumbs_custom_home_switch',
				'type' => 'checkbox',
				'label' => __( 'Set custom home page link?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'This can be useful if you are building KB home page with shortcodes', 'minerva-kb' )
			),
			array(
				'id' => 'breadcrumbs_custom_home_page',
				'type' => 'select',
				'label' => __( 'Breadcrumbs custom home page', 'minerva-kb' ),
				'options' => self::get_pages_options(),
				'default' => '',
				'description' => __( 'Select breadcrumbs custom home page', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'breadcrumbs_custom_home_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'breadcrumbs_label',
				'type' => 'input_text',
				'label' => __( 'Breadcrumbs label', 'minerva-kb' ),
				'default' => __( 'You are here:', 'minerva-kb' )
			),
			array(
				'id' => 'breadcrumbs_separator_icon',
				'type' => 'icon_select',
				'label' => __( 'Breadcrumbs separator', 'minerva-kb' ),
				'default' => 'fa-caret-right'
			),
			array(
				'id' => 'breadcrumbs_font_size',
				'type' => 'css_size',
				'label' => __( 'Breadcrumbs font size', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "1"),
				'description' => __( 'Breadcrumbs font size', 'minerva-kb' )
			),
			array(
				'id' => 'breadcrumbs_align',
				'type' => 'select',
				'label' => __( 'Breadcrumbs text align', 'minerva-kb' ),
				'options' => array(
					'left' => __( 'Left', 'minerva-kb' ),
					'center' => __( 'Center', 'minerva-kb' ),
					'right' => __( 'Right', 'minerva-kb' )
				),
				'default' => 'left',
				'description' => __( 'Select text align for breadrumbs', 'minerva-kb' )
			),
			array(
				'id' => 'breadcrumbs_top_padding',
				'type' => 'css_size',
				'label' => __( 'Breadcrumbs top padding', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "0"),
				'description' => __( 'Breadcrumbs container top padding', 'minerva-kb' )
			),
			array(
				'id' => 'breadcrumbs_bottom_padding',
				'type' => 'css_size',
				'label' => __( 'Breadcrumbs bottom padding', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "0"),
				'description' => __( 'Breadcrumbs container bottom padding', 'minerva-kb' )
			),
			array(
				'id' => 'breadcrumbs_left_padding',
				'type' => 'css_size',
				'label' => __( 'Breadcrumbs left padding', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "0"),
				'description' => __( 'Breadcrumbs container left padding', 'minerva-kb' )
			),
			array(
				'id' => 'breadcrumbs_right_padding',
				'type' => 'css_size',
				'label' => __( 'Breadcrumbs right padding', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "0"),
				'description' => __( 'Breadcrumbs container right padding', 'minerva-kb' )
			),
			array(
				'id' => 'breadcrumbs_bg_color',
				'type' => 'color',
				'label' => __( 'Breadcrumbs container background color (transparent by default)', 'minerva-kb' ),
				'default' => 'rgba(255,255,255,0)'
			),
			array(
				'id' => 'breadcrumbs_image_bg',
				'type' => 'media',
				'label' => __( 'Breadcrumbs background image URL (optional)', 'minerva-kb' ),
				'default' => ''
			),
			array(
				'id' => 'breadcrumbs_add_gradient',
				'type' => 'checkbox',
				'label' => __( 'Add gradient overlay?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'breadcrumbs_gradient_from',
				'type' => 'color',
				'label' => __( 'Breadcrumbs gradient from', 'minerva-kb' ),
				'default' => '#00c1b6',
				'dependency' => array(
					'target' => 'breadcrumbs_add_gradient',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'breadcrumbs_gradient_to',
				'type' => 'color',
				'label' => __( 'Breadcrumbs gradient to', 'minerva-kb' ),
				'default' => '#136eb5',
				'dependency' => array(
					'target' => 'breadcrumbs_add_gradient',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'breadcrumbs_gradient_opacity',
				'type' => 'input',
				'label' => __( 'Breadcrumbs background gradient opacity', 'minerva-kb' ),
				'default' => 1,
				'description' => __( 'Use any CSS opacity value, for example 1 or 0.7', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'breadcrumbs_add_gradient',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'breadcrumbs_add_pattern',
				'type' => 'checkbox',
				'label' => __( 'Add pattern overlay?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'breadcrumbs_image_pattern',
				'type' => 'media',
				'label' => __( 'Breadcrumbs background pattern image URL (optional)', 'minerva-kb' ),
				'default' => '',
				'dependency' => array(
					'target' => 'breadcrumbs_add_pattern',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'breadcrumbs_image_pattern_opacity',
				'type' => 'input',
				'label' => __( 'Breadcrumbs background pattern opacity', 'minerva-kb' ),
				'default' => 1,
				'description' => __( 'Use any CSS opacity value, for example 1 or 0.7. You can also use transparent .png and set opacity to 1', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'breadcrumbs_add_pattern',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'breadcrumbs_text_color',
				'type' => 'color',
				'label' => __( 'Breadcrumbs text color', 'minerva-kb' ),
				'default' => '#888'
			),
			array(
				'id' => 'breadcrumbs_link_color',
				'type' => 'color',
				'label' => __( 'Breadcrumbs link color', 'minerva-kb' ),
				'default' => '#888'
			),
			array(
				'id' => 'breadcrumbs_add_shadow',
				'type' => 'checkbox',
				'label' => __( 'Add shadow to breadcrumbs container?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'breadcrumbs_inset_shadow',
				'type' => 'checkbox',
				'label' => __( 'Inner shadow?', 'minerva-kb' ),
				'default' => false,
				'dependency' => array(
					'target' => 'breadcrumbs_add_shadow',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'show_breadcrumbs_category',
				'type' => 'checkbox',
				'label' => __( 'Show breadcrumbs in category?', 'minerva-kb' ),
				'default' => true
			),
			array(
				'id' => 'show_breadcrumbs_single',
				'type' => 'checkbox',
				'label' => __( 'Show breadcrumbs in article?', 'minerva-kb' ),
				'default' => true
			),
			array(
				'id' => 'show_breadcrumbs_current_title',
				'type' => 'checkbox',
				'label' => __( 'Show current article title in breadcrumbs?', 'minerva-kb' ),
				'default' => true
			),
			/**
			 * Rating
			 */
			array(
				'id' => 'rating_tab',
				'type' => 'tab',
				'label' => __( 'Rating', 'minerva-kb' ),
				'icon' => 'fa-star-o'
			),
			array(
				'id' => 'rating_block_label',
				'type' => 'input_text',
				'label' => __( 'Rating block label', 'minerva-kb' ),
				'default' => __( 'Was this article helpful?', 'minerva-kb' )
			),
            array(
                'id' => 'rating_prevent_multiple',
                'type' => 'title',
                'label' => __( 'Multiple votes settings', 'minerva-kb' ),
                'description' => __( 'Configure multiple votes settings', 'minerva-kb' )
            ),
            array(
                'id' => 'rating_prevent_multiple',
                'type' => 'checkbox',
                'label' => __( 'Prevent multiple votes for same article?', 'minerva-kb' ),
                'default' => true
            ),
            array(
                'id' => 'rating_prevent_multiple_interval',
                'type' => 'input',
                'label' => __( 'Minimum interval between votes (hours)', 'minerva-kb' ),
                'default' => 24,
                'dependency' => array(
                    'target' => 'rating_prevent_multiple',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
            array(
                'id' => 'rating_already_voted_message',
                'type' => 'input_text',
                'label' => __( 'Message to display when user already voted', 'minerva-kb' ),
                'default' => __( 'You have already rated this article', 'minerva-kb' ),
                'dependency' => array(
                    'target' => 'rating_prevent_multiple',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
			array(
				'id' => 'likes_title',
				'type' => 'title',
				'label' => __( 'Likes settings', 'minerva-kb' ),
				'description' => __( 'Configure rating likes', 'minerva-kb' )
			),
			array(
				'id' => 'show_likes_button',
				'type' => 'checkbox',
				'label' => __( 'Show likes button?', 'minerva-kb' ),
				'default' => true
			),
			array(
				'id' => 'like_label',
				'type' => 'input_text',
				'label' => __( 'Like label', 'minerva-kb' ),
				'default' => __( 'Like', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'show_likes_button',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'show_likes_icon',
				'type' => 'checkbox',
				'label' => __( 'Show likes icon?', 'minerva-kb' ),
				'default' => true,
				'dependency' => array(
					'target' => 'show_likes_button',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'like_icon',
				'type' => 'icon_select',
				'label' => __( 'Like icon', 'minerva-kb' ),
				'default' => 'fa-smile-o',
				'dependency' => array(
					'target' => 'show_likes_button',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'like_color',
				'type' => 'color',
				'label' => __( 'Like button color (used also for messages and feedback form button)', 'minerva-kb' ),
				'default' => '#4BB651',
				'dependency' => array(
					'target' => 'show_likes_button',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'show_likes_count',
				'type' => 'checkbox',
				'label' => __( 'Show likes count?', 'minerva-kb' ),
				'default' => true,
				'dependency' => array(
					'target' => 'show_likes_button',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'show_like_message',
				'type' => 'checkbox',
				'label' => __( 'Show message after like?', 'minerva-kb' ),
				'default' => false,
				'dependency' => array(
					'target' => 'show_likes_button',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'like_message_text',
				'type' => 'textarea_text',
				'label' => __( 'Like message text', 'minerva-kb' ),
				'default' => __( '<i class="fa fa-smile-o"></i> Great!<br/><strong>Thank you</strong> for your vote!', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'show_likes_button',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'dislikes_title',
				'type' => 'title',
				'label' => __( 'Dislikes settings', 'minerva-kb' ),
				'description' => __( 'Configure rating dislikes', 'minerva-kb' )
			),
			array(
				'id' => 'show_dislikes_button',
				'type' => 'checkbox',
				'label' => __( 'Show dislikes button?', 'minerva-kb' ),
				'default' => true
			),
			array(
				'id' => 'dislike_label',
				'type' => 'input_text',
				'label' => __( 'Dislike label', 'minerva-kb' ),
				'default' => __( 'Dislike', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'show_dislikes_button',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'show_dislikes_icon',
				'type' => 'checkbox',
				'label' => __( 'Show dislikes icon?', 'minerva-kb' ),
				'default' => true,
				'dependency' => array(
					'target' => 'show_dislikes_button',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'dislike_icon',
				'type' => 'icon_select',
				'label' => __( 'Dislike icon', 'minerva-kb' ),
				'default' => 'fa-frown-o',
				'dependency' => array(
					'target' => 'show_dislikes_button',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'dislike_color',
				'type' => 'color',
				'label' => __( 'Dislike button color', 'minerva-kb' ),
				'default' => '#C85C5E',
				'dependency' => array(
					'target' => 'show_dislikes_button',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'show_dislikes_count',
				'type' => 'checkbox',
				'label' => __( 'Show dislikes count?', 'minerva-kb' ),
				'default' => true,
				'dependency' => array(
					'target' => 'show_dislikes_button',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'show_dislike_message',
				'type' => 'checkbox',
				'label' => __( 'Show message after dislike?', 'minerva-kb' ),
				'default' => false,
				'dependency' => array(
					'target' => 'show_dislikes_button',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'dislike_message_text',
				'type' => 'textarea_text',
				'label' => __( 'Dislike message text', 'minerva-kb' ),
				'default' => __( 'Thank you for your vote!', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'show_dislikes_button',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'rating_message_bg',
				'type' => 'color',
				'label' => __( 'Like / dislike message background color', 'minerva-kb' ),
				'default' => '#f7f7f7'
			),
			array(
				'id' => 'rating_message_color',
				'type' => 'color',
				'label' => __( 'Like / dislike message text color', 'minerva-kb' ),
				'default' => '#888'
			),
			array(
				'id' => 'rating_message_border_color',
				'type' => 'color',
				'label' => __( 'Like / dislike message border color', 'minerva-kb' ),
				'default' => '#eee'
			),
			array(
				'id' => 'show_rating_total',
				'type' => 'checkbox',
				'label' => __( 'Show rating total?', 'minerva-kb' ),
				'default' => false,
				'description' => 'A line of text, like: 3 of 10 found this article helpful'
			),
			array(
				'id' => 'rating_total_text',
				'type' => 'input_text',
				'label' => __( 'Rating total text', 'minerva-kb' ),
				'default' => __( '%d of %d found this article helpful.', 'minerva-kb' ),
				'description' => 'First %d is replaced with likes, second - with total sum of votes',
				'dependency' => array(
					'target' => 'show_rating_total',
					'type' => 'EQ',
					'value' => true
				)
			),
			/**
			 * Feedback
			 */
			array(
				'id' => 'feedback_tab',
				'type' => 'tab',
				'label' => __( 'Feedback', 'minerva-kb' ),
				'icon' => 'fa-bullhorn'
			),
			array(
				'id' => 'enable_feedback',
				'type' => 'checkbox',
				'label' => __( 'Enable article feedback?', 'minerva-kb' ),
				'default' => false,
				'description' => 'Allow users to leave feedback on articles'
			),
			array(
				'id' => 'feedback_mode',
				'type' => 'select',
				'label' => __( 'Feedback form display mode?', 'minerva-kb' ),
				'options' => array(
					'dislike' => __( 'Show after dislike', 'minerva-kb' ),
					'like' => __( 'Show after like', 'minerva-kb' ),
					'any' => __( 'Show after like or dislike', 'minerva-kb' ),
					'always' => __( 'Always present', 'minerva-kb' )
				),
				'default' => 'dislike',
				'description' => __( 'Select when to display feedback form', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'enable_feedback',
					'type' => 'EQ',
					'value' => true
				)
			),
            array(
                'id' => 'feedback_email_on',
                'type' => 'checkbox',
                'label' => __( 'Enable feedback email field?', 'minerva-kb' ),
                'default' => false,
                'dependency' => array(
                    'target' => 'enable_feedback',
                    'type' => 'EQ',
                    'value' => true
                ),
                'description' => 'Allow users to leave email for contact'
            ),
            array(
                'id' => 'feedback_email_label',
                'type' => 'input_text',
                'label' => __( 'Set feedback email form label', 'minerva-kb' ),
                'default' => __( 'Your email (optional):', 'minerva-kb' ),
                'dependency' => array(
                    'target' => 'enable_feedback',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
			array(
				'id' => 'feedback_label',
				'type' => 'input_text',
				'label' => __( 'Set feedback form label', 'minerva-kb' ),
				'default' => __( 'You can leave feedback:', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'enable_feedback',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'feedback_submit_label',
				'type' => 'input_text',
				'label' => __( 'Set feedback submit button label', 'minerva-kb' ),
				'default' => __( 'Send feedback', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'enable_feedback',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'feedback_submit_request_label',
				'type' => 'input_text',
				'label' => __( 'Set feedback submit button label to show when request in progress', 'minerva-kb' ),
				'default' => __( 'Sending...', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'enable_feedback',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'feedback_submit_bg',
				'type' => 'color',
				'label' => __( 'Feedback submit button background color', 'minerva-kb' ),
				'default' => '#4a90e2',
				'dependency' => array(
					'target' => 'enable_feedback',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'feedback_submit_color',
				'type' => 'color',
				'label' => __( 'Feedback submit button text color', 'minerva-kb' ),
				'default' => '#ffffff',
				'dependency' => array(
					'target' => 'enable_feedback',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'feedback_info_text',
				'type' => 'textarea_text',
				'label' => __( 'You can add extra description to feedback form', 'minerva-kb' ),
				'default' => __( 'We will use your feedback to improve this article', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'enable_feedback',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'feedback_sent_text',
				'type' => 'textarea_text',
				'label' => __( 'Text to display after feedback sent. You can use HTML', 'minerva-kb' ),
				'default' => __( 'Thank you for your feedback, we will do our best to fix this soon', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'enable_feedback',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'feedback_message_bg',
				'type' => 'color',
				'label' => __( 'Feedback message background color', 'minerva-kb' ),
				'default' => '#f7f7f7',
				'dependency' => array(
					'target' => 'enable_feedback',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'feedback_message_color',
				'type' => 'color',
				'label' => __( 'Feedback message text color', 'minerva-kb' ),
				'default' => '#888',
				'dependency' => array(
					'target' => 'enable_feedback',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'feedback_message_border_color',
				'type' => 'color',
				'label' => __( 'Feedback message border color', 'minerva-kb' ),
				'default' => '#eee',
				'dependency' => array(
					'target' => 'enable_feedback',
					'type' => 'EQ',
					'value' => true
				)
			),
			/**
			 * Shortcodes
			 */
			array(
				'id' => 'shortcodes_tab',
				'type' => 'tab',
				'label' => __( 'Shortcodes', 'minerva-kb' ),
				'icon' => 'fa-code'
			),
			array(
				'id' => 'info_title',
				'type' => 'title',
				'label' => __( 'Info shortcode', 'minerva-kb' ),
				'description' => __( 'Highlight interesting information using this shortcode', 'minerva-kb' ),
				'preview_image' => MINERVA_KB_IMG_URL . 'info-shortcode.png'
			),
			array(
				'id' => 'info_usage',
				'type' => 'code',
				'label' => __( 'Info use example', 'minerva-kb' ),
				'default' => '[mkb-info]Lorem Ipsum is simply dummy text of the printing and typesetting industry.[/mkb-info]'
			),
			array(
				'id' => 'info_icon',
				'type' => 'icon_select',
				'label' => __( 'Info icon', 'minerva-kb' ),
				'default' => 'fa-info-circle'
			),
			array(
				'id' => 'info_bg',
				'type' => 'color',
				'label' => __( 'Info background', 'minerva-kb' ),
				'default' => '#d9edf7'
			),			
			array(
				'id' => 'info_border',
				'type' => 'color',
				'label' => __( 'Info border color', 'minerva-kb' ),
				'default' => '#bce8f1'
			),
			array(
				'id' => 'info_icon_color',
				'type' => 'color',
				'label' => __( 'Info icon color', 'minerva-kb' ),
				'default' => '#31708f'
			),
			array(
				'id' => 'info_color',
				'type' => 'color',
				'label' => __( 'Info text color', 'minerva-kb' ),
				'default' => '#333333'
			),
			array(
				'id' => 'tip_title',
				'type' => 'title',
				'label' => __( 'Tip shortcode', 'minerva-kb' ),
				'description' => __( 'Highlight interesting information using this shortcode', 'minerva-kb' ),
				'preview_image' => MINERVA_KB_IMG_URL . 'tip-shortcode.png'
			),
			array(
				'id' => 'tip_usage',
				'type' => 'code',
				'label' => __( 'Tip use example', 'minerva-kb' ),
				'default' => '[mkb-tip]Lorem Ipsum is simply dummy text of the printing and typesetting industry.[/mkb-tip]'
			),
			array(
				'id' => 'tip_icon',
				'type' => 'icon_select',
				'label' => __( 'Tip icon', 'minerva-kb' ),
				'default' => 'fa-lightbulb-o'
			),
			array(
				'id' => 'tip_bg',
				'type' => 'color',
				'label' => __( 'Tip background', 'minerva-kb' ),
				'default' => '#fcf8e3'
			),
			array(
				'id' => 'tip_border',
				'type' => 'color',
				'label' => __( 'Tip border color', 'minerva-kb' ),
				'default' => '#faebcc'
			),
			array(
				'id' => 'tip_icon_color',
				'type' => 'color',
				'label' => __( 'Tip icon color', 'minerva-kb' ),
				'default' => '#8a6d3b'
			),
			array(
				'id' => 'tip_color',
				'type' => 'color',
				'label' => __( 'Tip text color', 'minerva-kb' ),
				'default' => '#333333'
			),
			array(
				'id' => 'warning_title',
				'type' => 'title',
				'label' => __( 'Warning shortcode', 'minerva-kb' ),
				'description' => __( 'Highlight important information using this shortcode', 'minerva-kb' ),
				'preview_image' => MINERVA_KB_IMG_URL . 'warning-shortcode.png'
			),
			array(
				'id' => 'warning_usage',
				'type' => 'code',
				'label' => __( 'Warning use example', 'minerva-kb' ),
				'default' => '[mkb-warning]Lorem Ipsum is simply dummy text of the printing and typesetting industry.[/mkb-warning]'
			),
			array(
				'id' => 'warning_icon',
				'type' => 'icon_select',
				'label' => __( 'Warning icon', 'minerva-kb' ),
				'default' => 'fa-exclamation-triangle'
			),
			array(
				'id' => 'warning_bg',
				'type' => 'color',
				'label' => __( 'Warning background', 'minerva-kb' ),
				'default' => '#f2dede'
			),
			array(
				'id' => 'warning_border',
				'type' => 'color',
				'label' => __( 'Warning border color', 'minerva-kb' ),
				'default' => '#ebccd1'
			),
			array(
				'id' => 'warning_icon_color',
				'type' => 'color',
				'label' => __( 'Warning icon color', 'minerva-kb' ),
				'default' => '#a94442'
			),
			array(
				'id' => 'warning_color',
				'type' => 'color',
				'label' => __( 'Warning text color', 'minerva-kb' ),
				'default' => '#333333'
			),
			// Related content
			array(
				'id' => 'related_content_title',
				'type' => 'title',
				'label' => __( 'Related content shortcode', 'minerva-kb' ),
				'description' => __( 'Show links to related content with this shortcode', 'minerva-kb' ),
				'preview_image' => MINERVA_KB_IMG_URL . 'related-content-shortcode.png'
			),
			array(
				'id' => 'related_content_usage',
				'type' => 'code',
				'label' => __( 'Related use example. Add list of ids', 'minerva-kb' ),
				'default' => '[mkb-related ids="7,8,19"][/mkb-related]'
			),
			array(
				'id' => 'related_content_label',
				'type' => 'input_text',
				'label' => __( 'Related content shortcode label', 'minerva-kb' ),
				'default' => __( 'See also:', 'minerva-kb' )
			),
			array(
				'id' => 'related_content_bg',
				'type' => 'color',
				'label' => __( 'Related content background', 'minerva-kb' ),
				'default' => '#e8f9f2'
			),
			array(
				'id' => 'related_content_border',
				'type' => 'color',
				'label' => __( 'Related content border color', 'minerva-kb' ),
				'default' => '#2ab77b'
			),
			array(
				'id' => 'related_content_links_color',
				'type' => 'color',
				'label' => __( 'Related content links color', 'minerva-kb' ),
				'default' => '#007acc'
			),
			array(
				'id' => 'related_content_label_color',
				'type' => 'color',
				'label' => __( 'Related content label color', 'minerva-kb' ),
				'default' => '#333333'
			),

			/**
			 * TOC
			 */
			array(
				'id' => 'toc_tab',
				'type' => 'tab',
				'label' => __( 'Table of contents', 'minerva-kb' ),
				'icon' => 'fa-list-ol'
			),
			array(
				'id' => 'toc_title',
				'type' => 'title',
				'label' => __( 'Table of contents', 'minerva-kb' ),
				'description' => __( 'Build dynamic table of contents using heading tags or anchor shortcode', 'minerva-kb' ),
				'preview_image' => MINERVA_KB_IMG_URL . 'toc-shortcode.png',
				'width' => 200
			),
			array(
				'id' => 'toc_global_info',
				'type' => 'info',
				'label' => 'Table of contents is built dynamically from h1-h6 heading tags inside article. ' .
				           'To use table of contents as sidebar widget, you need to disable it in article body, using option below.'.
				           'You can also build table of contents manually, using mkb-anchor shortcode.',
			),
			array(
				'id' => 'toc_dynamic_enable',
				'type' => 'checkbox',
				'label' => __( 'Enable dynamic table of contents?', 'minerva-kb' ),
				'default' => true,
				'description' => __( 'Dynamic table of contents is built from headings found in article. NOTE: if [mkb-anchor] shortcodes are found in article, dynamic TOC will switch to shortcode (manual) mode', 'minerva-kb' ),
			),
			array(
				'id' => 'toc_hierarchical_enable',
				'type' => 'checkbox',
				'label' => __( 'Hierarchical table of contents?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'When enabled, will build hierarchical tree, h1 - top level, h6 - bottom level. NOTE: lower level headings without parents will be treated as root entries', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'toc_dynamic_enable',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'toc_max_width',
				'type' => 'css_size',
				'label' => __( 'Width of TOC in article', 'minerva-kb' ),
				'default' => array("unit" => '%', "size" => "30"),
				'description' => __( 'Width of table of contents in article body', 'minerva-kb' )
			),
			array(
				'id' => 'toc_max_width_h',
				'type' => 'css_size',
				'label' => __( 'Width of hierarchical TOC in article', 'minerva-kb' ),
				'default' => array("unit" => '%', "size" => "40"),
				'description' => __( 'Width of hierarchical table of contents in article body', 'minerva-kb' )
			),
			array(
				'id' => 'toc_numbers_enable',
				'type' => 'checkbox',
				'label' => __( 'Add numbers to table of contents items?', 'minerva-kb' ),
				'default' => true,
				'description' => __( 'You can disable this to remove numbers before items.', 'minerva-kb' ),
			),
			array(
				'id' => 'toc_headings_exclude',
				'type' => 'input',
				'label' => __( 'Exclude specific headings from table of contents', 'minerva-kb' ),
				'default' => __( '', 'minerva-kb' ),
				'description' => __( 'Comma-separated list of headings you want to exclude from dynamic table of contents. Example value: "h1,h3,h5"', 'minerva-kb' ),
			),
			array(
				'id' => 'toc_content_parse',
				'type' => 'checkbox',
				'label' => __( 'Parse article content (shortcodes) before generating table of contents?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'Some plugins, like VC, add their own heading shortcodes. Turn this on if you need those headings in table of contents.', 'minerva-kb' ),
			),
			array(
				'id' => 'toc_url_update',
				'type' => 'checkbox',
				'label' => __( 'Update page url on section select?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'You can use this option to update URL on each section navigation, so that link is always actual.', 'minerva-kb' ),
			),
			array(
				'id' => 'toc_scroll_offset',
				'type' => 'css_size',
				'label' => __( 'Table of contents scroll offset', 'minerva-kb' ),
				'default' => array("unit" => 'px', "size" => "0"),
				'units' => array("px"),
				'description' => __( 'Can be useful if you have sticky header that overlaps content. You can use negative values here as well.', 'minerva-kb' )
			),

			array(
				'id' => 'toc_label',
				'type' => 'input_text',
				'label' => __( 'Table of contents label', 'minerva-kb' ),
				'default' => __( 'In this article', 'minerva-kb' )
			),
			// back to top
			array(
				'id' => 'toc_back_to_top_title',
				'type' => 'title',
				'label' => __( 'Back to top', 'minerva-kb' ),
				'description' => __( 'Configure Back to top links for TOC', 'minerva-kb' )
			),
			array(
				'id' => 'show_back_to_top',
				'type' => 'checkbox',
				'label' => __( 'Show back to top link in anchors?', 'minerva-kb' ),
				'default' => true
			),
			array(
				'id' => 'back_to_site_top',
				'type' => 'checkbox',
				'label' => __( 'Scroll back to site top?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'By default, back to top scrolls to article text top', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'show_back_to_top',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'back_to_top_text',
				'type' => 'input_text',
				'label' => __( 'Back to top text', 'minerva-kb' ),
				'default' => __( 'To top', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'show_back_to_top',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'show_back_to_top_icon',
				'type' => 'checkbox',
				'label' => __( 'Add back to top icon?', 'minerva-kb' ),
				'default' => true,
				'dependency' => array(
					'target' => 'show_back_to_top',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'back_to_top_icon',
				'type' => 'icon_select',
				'label' => __( 'Back to top icon', 'minerva-kb' ),
				'default' => 'fa-long-arrow-up',
				'dependency' => array(
					'target' => 'show_back_to_top',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'back_to_top_position',
				'type' => 'select',
				'label' => __( 'Where to display back to top?', 'minerva-kb' ),
				'options' => array(
					'inline' => __( 'Inline with section title', 'minerva-kb' ),
					'under' => __( 'Under section title', 'minerva-kb' )
				),
				'default' => 'inline',
				'dependency' => array(
					'target' => 'show_back_to_top',
					'type' => 'EQ',
					'value' => true
				)
			),
			// scrollspy
			array(
				'id' => 'scrollspy_title',
				'type' => 'title',
				'label' => __( 'Table of contents Widget / ScrollSpy settings', 'minerva-kb' ),
				'description' => __( 'Configure TOC widget', 'minerva-kb' )
			),
			array(
				'id' => 'toc_in_content_disable',
				'type' => 'checkbox',
				'label' => __( 'Remove table of contents from article body?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'This must be on if you plan to use table of contents widget in article sidebar.', 'minerva-kb' ),
			),
			array(
				'id' => 'toc_sidebar_desktop_only',
				'type' => 'checkbox',
				'label' => __( 'Always show TOC in article body on mobile/tablets?', 'minerva-kb' ),
				'default' => true,
				'description' => __( 'On mobile devices sidebar is displayed under the content, so table of contents works better in article body.', 'minerva-kb' ),
			),
			array(
				'id' => 'scrollspy_switch',
				'type' => 'checkbox',
				'label' => __( 'Enable ScrollSpy?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'scrollspy_bg',
				'type' => 'color',
				'label' => __( 'Active link background color', 'minerva-kb' ),
				'default' => '#00aae8',
				'dependency' => array(
					'target' => 'scrollspy_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'scrollspy_color',
				'type' => 'color',
				'label' => __( 'Active link text color', 'minerva-kb' ),
				'default' => '#fff',
				'dependency' => array(
					'target' => 'scrollspy_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
			// manual
			array(
				'id' => 'toc_manual_title',
				'type' => 'title',
				'label' => __( 'Table of contents manual mode', 'minerva-kb' ),
			),
			array(
				'id' => 'toc_manual_info',
				'type' => 'info',
				'label' => 'Table of contents can be build using shortcodes instead of headings, in case you can not use h1-h6 tags in article text. ' .
				           'To use table of contents in manual mode you need to disable dynamic table of contents above and use mkb-anchor shortcodes, see example below.',
			),
			array(
				'id' => 'toc_manual_usage',
				'type' => 'code',
				'label' => __( 'Table of contents manual mode (shortcode) use example', 'minerva-kb' ),
				'default' => '[mkb-anchor]Section name[/mkb-anchor]'
			),
			/**
			 * Restrict content
			 */
			array(
				'id' => 'restrict_tab',
				'type' => 'tab',
				'label' => __( 'Restrict Access', 'minerva-kb' ),
				'icon' => 'fa-lock'
			),
			array(
				'id' => 'restrict_title',
				'type' => 'title',
				'label' => __( 'Content restriction settings', 'minerva-kb' ),
				'description' => __( 'You can customize who can see the knowledge base content here', 'minerva-kb' )
			),
			array(
				'id' => 'restrict_on',
				'type' => 'checkbox',
				'label' => __( 'Enable content restriction?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'By default, we disable restrict functionality, since you might use external plugin for this', 'minerva-kb' ),
			),
			array(
				'id' => 'restrict_article_role',
				'type' => 'roles_select',
				'label' => __( 'Global restriction: who can view articles?', 'minerva-kb' ),
				'default' => 'none',
				'flush' => true,
				'view_log' => true,
				'description' => __( 'Select roles, that have access to articles on client side.<br/> If you want to restrict specific articles or topics, you do so on article and topic pages', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_remove_from_archives',
				'type' => 'checkbox',
				'label' => __( 'Remove restricted articles & topics from home page and archives?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'You can display or remove restricted articles from topics', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
            array(
                'id' => 'restrict_apply_topics_filter_globally',
                'type' => 'checkbox',
                'label' => __( 'Apply topics restriction filter globally?', 'minerva-kb' ),
                'default' => false,
                'experimental' => __( 'This is experimental feature and depends a lot on theme and other plugins', 'minerva-kb' ),
                'description' => __( 'When enabled, we\'ll attempt to apply restriction logic for all Wordpress taxonomy queries', 'minerva-kb' ),
                'dependency' => array(
                    'target' => 'restrict_on',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
			array(
				'id' => 'restrict_remove_from_search',
				'type' => 'checkbox',
				'label' => __( 'Remove restricted articles from search results?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'You can display or remove restricted articles from search results', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
            array(
				'id' => 'restrict_apply_search_filter_globally',
				'type' => 'checkbox',
				'label' => __( 'Apply search restriction filter globally?', 'minerva-kb' ),
				'default' => false,
				'experimental' => __( 'This is experimental feature and depends a lot on theme and other plugins', 'minerva-kb' ),
				'description' => __( 'When enabled, we\'ll attempt to apply restriction logic for all Wordpress search queries', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_remove_search_for_restricted',
				'type' => 'checkbox',
				'label' => __( 'Remove search sections when user has no access to knowledge base?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'You can remove search modules completely for users who do not have access to content.', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_article_message',
				'type' => 'textarea_text',
				'label' => __( 'Restricted article message', 'minerva-kb' ),
				'description' => __( 'Message to display when unauthorized user is trying to access restricted article. You can use HTML here', 'minerva-kb' ),
				'default' => __( 'The content you are trying to access is for members only. Please login to view it.', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_message_icon',
				'type' => 'icon_select',
				'label' => __( 'Restrict message icon', 'minerva-kb' ),
				'default' => 'fa-lock',
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_message_bg',
				'type' => 'color',
				'label' => __( 'Restrict message background', 'minerva-kb' ),
				'default' => '#fcf8e3',
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_message_border',
				'type' => 'color',
				'label' => __( 'Restrict message border color', 'minerva-kb' ),
				'default' => '#faebcc',
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_message_icon_color',
				'type' => 'color',
				'label' => __( 'Restrict message icon color', 'minerva-kb' ),
				'default' => '#8a6d3b',
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_message_color',
				'type' => 'color',
				'label' => __( 'Restrict message text color', 'minerva-kb' ),
				'default' => '#333333',
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_show_article_breadcrumbs',
				'type' => 'checkbox',
				'label' => __( 'Show breadcrumbs on restricted articles?', 'minerva-kb' ),
				'default' => true,
				'description' => __( 'Control the visibility of breadcrumbs on restricted articles', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_show_article_search',
				'type' => 'checkbox',
				'label' => __( 'Show articles search section on restricted articles?', 'minerva-kb' ),
				'default' => true,
				'description' => __( 'Control the visibility of search on restricted articles', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_show_article_related',
				'type' => 'checkbox',
				'label' => __( 'Show related articles section on restricted articles?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'Control the visibility of related articles section on restricted articles', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_show_article_excerpt',
				'type' => 'checkbox',
				'label' => __( 'Show excerpt for restricted articles?', 'minerva-kb' ),
				'default' => true,
				'description' => __( 'Control the teaser/excerpt for restricted articles. NOTE, the text added to excerpt box displayed, not dynamically generated', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_show_article_toc',
				'type' => 'checkbox',
				'label' => __( 'Show table of contents widget for restricted articles?', 'minerva-kb' ),
				'default' => true,
				'description' => __( 'Control the TOC display for restricted articles.', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_show_excerpt_gradient',
				'type' => 'checkbox',
				'label' => __( 'Show excerpt gradient overlay?', 'minerva-kb' ),
				'default' => true,
				'description' => __( 'A semi-transparent gradient, that hides the ending of the excerpt', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_article_excerpt_gradient_start',
				'type' => 'color',
				'label' => __( 'Start color for overlay gradient', 'minerva-kb' ),
				'default' => '#fff',
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_message_before_html',
				'type' => 'textarea_text',
				'label' => __( 'Restricted article additional HTML (before login form)', 'minerva-kb' ),
				'description' => __( 'Use this field if you need to display any extra HTML content before login form', 'minerva-kb' ),
				'default' => __( '', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_message_after_html',
				'type' => 'textarea_text',
				'label' => __( 'Restricted article additional HTML (after login form)', 'minerva-kb' ),
				'description' => __( 'Use this field if you need to display any extra HTML content after messages and login form', 'minerva-kb' ),
				'default' => __( '', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_login_title',
				'type' => 'title',
				'label' => __( 'Restricted content login form', 'minerva-kb' ),
				'description' => __( 'Configure the appearance for the login form', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_show_login_form',
				'type' => 'checkbox',
				'label' => __( 'Show login form after restricted content message?', 'minerva-kb' ),
				'default' => true,
				'description' => __( 'Control the login form display for restricted articles', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_login_form_width',
				'type' => 'css_size',
				'label' => __( 'Login form width', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "26"),
				'description' => __( 'Minimum width for login form', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_login_form_align',
				'type' => 'select',
				'label' => __( 'Login form align in container', 'minerva-kb' ),
				'options' => array(
					'left' => __( 'Left', 'minerva-kb' ),
					'center' => __( 'Center', 'minerva-kb' ),
					'right' => __( 'Right', 'minerva-kb' ),
				),
				'default' => 'center',
				'description' => __( 'Select login form align', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_login_bg',
				'type' => 'color',
				'label' => __( 'Login form background', 'minerva-kb' ),
				'default' => '#f7f7f7',
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_login_label_color',
				'type' => 'color',
				'label' => __( 'Login form label color', 'minerva-kb' ),
				'default' => '#999',
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_login_input_bg',
				'type' => 'color',
				'label' => __( 'Login form input background', 'minerva-kb' ),
				'default' => '#ffffff',
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_login_input_text_color',
				'type' => 'color',
				'label' => __( 'Login form input text color', 'minerva-kb' ),
				'default' => '#333',
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_login_username_label_text',
				'type' => 'input_text',
				'label' => __( 'Login form username/email label text', 'minerva-kb' ),
				'default' => __( 'Username or Email Address', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_login_password_label_text',
				'type' => 'input_text',
				'label' => __( 'Login form password label text', 'minerva-kb' ),
				'default' => __( 'Password', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_login_remember_label_text',
				'type' => 'input_text',
				'label' => __( 'Login form Remember me label text', 'minerva-kb' ),
				'default' => __( 'Remember Me', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_login_text',
				'type' => 'input_text',
				'label' => __( 'Login button text', 'minerva-kb' ),
				'default' => __( 'Log in', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_login_btn_bg',
				'type' => 'color',
				'label' => __( 'Login button background', 'minerva-kb' ),
				'default' => '#F7931E',
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_login_btn_shadow',
				'type' => 'color',
				'label' => __( 'Login button shadow', 'minerva-kb' ),
				'default' => '#e46d19',
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_login_btn_color',
				'type' => 'color',
				'label' => __( 'Login button text color', 'minerva-kb' ),
				'default' => '#ffffff',
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_show_register_link',
				'type' => 'checkbox',
				'label' => __( 'Show register button inside login form?', 'minerva-kb' ),
				'default' => true,
				'description' => __( 'Control the register button display in login form', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_show_or',
				'type' => 'checkbox',
				'label' => __( 'Also show separator label between login and register?', 'minerva-kb' ),
				'default' => true,
				'description' => __( 'Text between login and register buttons', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_or_text',
				'type' => 'input_text',
				'label' => __( 'Separator label text', 'minerva-kb' ),
				'default' => __( 'Or', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_register_text',
				'type' => 'input_text',
				'label' => __( 'Register button text', 'minerva-kb' ),
				'default' => __( 'Register', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_register_btn_bg',
				'type' => 'color',
				'label' => __( 'Login register button background', 'minerva-kb' ),
				'default' => '#29ABE2',
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_register_btn_shadow',
				'type' => 'color',
				'label' => __( 'Register button shadow', 'minerva-kb' ),
				'default' => '#287eb1',
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_register_btn_color',
				'type' => 'color',
				'label' => __( 'Register button text color', 'minerva-kb' ),
				'default' => '#ffffff',
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'restrict_disable_form_styles',
				'type' => 'checkbox',
				'label' => __( 'Disable custom form and styles?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'Default theme login form and style will apply', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'restrict_on',
					'type' => 'EQ',
					'value' => true
				)
			),

			/**
			 * Floating Helper
			 */
			array(
				'id' => 'floating_helper_tab',
				'type' => 'tab',
				'label' => __( 'Floating helper', 'minerva-kb' ),
				'icon' => 'fa-sticky-note'
			),
			array(
				'id' => 'floating_helper_switch',
				'type' => 'checkbox',
				'label' => __( 'Enable floating helper?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'Turn this on to enable floating helper globally', 'minerva-kb' ),
			),
            array(
                'id' => 'fh_display_mode',
                'type' => 'select',
                'label' => __( 'Helper display mode', 'minerva-kb' ),
                'options' => array(
                    'auto' => __( 'Auto (with delay)', 'minerva-kb' ),
                    'js_click' => __( 'Click on element', 'minerva-kb' )
                ),
                'default' => 'auto',
                'description' => __( 'You can show helper automatically or via button click (must have CSS class ".js-mkb-helper-open")', 'minerva-kb' ),
                'dependency' => array(
                    'target' => 'floating_helper_switch',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
            array(
                'id' => 'fh_show_delay',
                'type' => 'input',
                'label' => __( 'Delay before showing helper button (ms)', 'minerva-kb' ),
                'default' => 3000,
                'description' => __( 'You can specify a delay before helper icon is shown', 'minerva-kb' ),
                'dependency' => array(
                    'target' => 'floating_helper_switch',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
			array(
				'id' => 'fh_display_title',
				'type' => 'title',
				'label' => __( 'Display options', 'minerva-kb' ),
				'description' => __( 'Configure where to display helper', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'floating_helper_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'fh_hide_on_kb',
				'type' => 'checkbox',
				'label' => __( 'Do not display on KB pages?', 'minerva-kb' ),
				'default' => true,
				'description' => __( 'Turn this on if you don\'t need helper on all KB pages', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'floating_helper_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'fh_hide_on_pages',
				'type' => 'checkbox',
				'label' => __( 'Do not display on regular pages?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'Turn this on if you don\'t need helper on regular pages', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'floating_helper_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'fh_hide_on_pages_ids',
				'type' => 'input',
				'label' => __( 'List of page IDs to exclude (optional)', 'minerva-kb' ),
				'default' => '',
				'description' => __( 'You can specify a comma-separated list of page IDs where helper should not appear', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'floating_helper_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
            array(
                'id' => 'fh_show_on_pages_ids',
                'type' => 'input',
                'label' => __( 'List of page IDs to display on (optional)', 'minerva-kb' ),
                'default' => '',
                'description' => __( 'You can specify a comma-separated list of page IDs where helper should appear. It will not appear on other pages.', 'minerva-kb' ),
                'dependency' => array(
                    'target' => 'floating_helper_switch',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
			array(
				'id' => 'fh_hide_on_blog',
				'type' => 'checkbox',
				'label' => __( 'Do not display on blog pages?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'Turn this on if you don\'t need helper on blog posts, categories, etc', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'floating_helper_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'fh_hide_on_mobile',
				'type' => 'checkbox',
				'label' => __( 'Do not display on mobile?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'Turn this on if you don\'t need helper on mobile devices', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'floating_helper_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'fh_hide_on_tablet',
				'type' => 'checkbox',
				'label' => __( 'Do not display on tablet?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'Turn this on if you don\'t need helper on tablet devices', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'floating_helper_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'fh_hide_on_desktop',
				'type' => 'checkbox',
				'label' => __( 'Do not display on desktop?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'Turn this on if you don\'t need helper on desktop devices', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'floating_helper_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
            array(
                'id' => 'fh_hide_for_restricted',
                'type' => 'checkbox',
                'label' => __( 'Do not display for globally restricted users?', 'minerva-kb' ),
                'default' => true,
                'description' => __( 'Turn this on if you don\'t need helper displayed for users who cannot access articles', 'minerva-kb' ),
                'dependency' => array(
                    'target' => 'floating_helper_switch',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
			array(
				'id' => 'fh_label_text',
				'type' => 'input_text',
				'label' => __( 'Helper label text', 'minerva-kb' ),
				'default' => __( 'Have questions? Search our knowledgebase.', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'floating_helper_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'fh_search_placeholder_text',
				'type' => 'input_text',
				'label' => __( 'Helper search placeholder text', 'minerva-kb' ),
				'default' => __( 'Search knowledge base', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'floating_helper_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'fh_style_title',
				'type' => 'title',
				'label' => __( 'Style options', 'minerva-kb' ),
				'description' => __( 'Configure helper style', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'floating_helper_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'fh_content_search_max_height',
				'type' => 'css_size',
				'label' => __( 'Helper search results height limit', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "20"),
				'units' => array('em', 'rem', 'px'),
				'description' => __( 'You can change this if you want helper to stay within some height limit', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'floating_helper_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'fh_content_width',
				'type' => 'css_size',
				'label' => __( 'Helper content width', 'minerva-kb' ),
				'default' => array("unit" => 'em', "size" => "36"),
				'units' => array('em', 'rem', 'px'),
				'dependency' => array(
					'target' => 'floating_helper_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
            array(
                'id' => 'fh_bottom_offset',
                'type' => 'css_size',
                'label' => __( 'Helper bottom offset', 'minerva-kb' ),
                'default' => array("unit" => 'em', "size" => "2"),
                'units' => array('em', 'rem', 'px'),
                'dependency' => array(
                    'target' => 'floating_helper_switch',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
            array(
                'id' => 'fh_side_offset',
                'type' => 'css_size',
                'label' => __( 'Helper side (left/right) offset', 'minerva-kb' ),
                'default' => array("unit" => 'em', "size" => "2"),
                'units' => array('em', 'rem', 'px'),
                'dependency' => array(
                    'target' => 'floating_helper_switch',
                    'type' => 'EQ',
                    'value' => true
                )
            ),
			array(
				'id' => 'fh_content_bg',
				'type' => 'color',
				'label' => __( 'Helper background color', 'minerva-kb' ),
				'default' => '#4a90e2',
				'dependency' => array(
					'target' => 'floating_helper_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'fh_content_color',
				'type' => 'color',
				'label' => __( 'Helper text color', 'minerva-kb' ),
				'default' => '#ffffff',
				'dependency' => array(
					'target' => 'floating_helper_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'fh_btn_icon',
				'type' => 'icon_select',
				'label' => __( 'Helper button icon', 'minerva-kb' ),
				'default' => 'fa-info',
				'dependency' => array(
					'target' => 'floating_helper_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'fh_btn_bg',
				'type' => 'color',
				'label' => __( 'Helper button background color', 'minerva-kb' ),
				'default' => '#4a90e2',
				'dependency' => array(
					'target' => 'floating_helper_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'fh_btn_color',
				'type' => 'color',
				'label' => __( 'Helper button text / icon color', 'minerva-kb' ),
				'default' => '#ffffff',
				'dependency' => array(
					'target' => 'floating_helper_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'fh_btn_size',
				'type' => 'css_size',
				'label' => __( 'Helper button height', 'minerva-kb' ),
				'default' => array("unit" => 'px', "size" => "78"),
				'units' => array('px'),
				'description' => __( 'Floating button height', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'floating_helper_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'fh_btn_icon_size',
				'type' => 'css_size',
				'label' => __( 'Helper button icon size', 'minerva-kb' ),
				'default' => array("unit" => 'px', "size" => "38"),
				'units' => array('px'),
				'description' => __( 'Floating button icon size', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'floating_helper_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'fh_display_position',
				'type' => 'select',
				'label' => __( 'Helper display position', 'minerva-kb' ),
				'options' => array(
					'btm_right' => __( 'Bottom right', 'minerva-kb' ),
					'btm_left' => __( 'Bottom left', 'minerva-kb' )
				),
				'default' => 'btm_right',
				'dependency' => array(
					'target' => 'floating_helper_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'fh_bottom_html',
				'type' => 'textarea',
				'label' => __( 'HTML to add after helper search box', 'minerva-kb' ),
				'height' => 20,
				'width' => 80,
				'default' => __( '', 'minerva-kb' )
			),
			/**
			 * Auto Updates
			 */
			array(
				'id' => 'auto_updates_tab',
				'type' => 'tab',
				'label' => __( 'Registration / Updates', 'minerva-kb' ),
				'icon' => 'fa-refresh'
			),
			array(
				'id' => 'auto_updates_title',
				'type' => 'title',
				'label' => __( 'Registration & Auto-Updates configuration', 'minerva-kb' ),
				'description' => __( 'To activate automatic updates you will need your purchase code from Envato', 'minerva-kb' )
			),
			array(
				'id' => 'auto_updates_switch',
				'type' => 'checkbox',
				'label' => __( 'Enable automatic check for updates?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'Plugin will check for updates periodically, you will be able to run update when it is available via Plugins menu page', 'minerva-kb' ),
			),
			array(
				'id' => 'auto_updates_verification',
				'type' => 'envato_verify',
				'label' => __( 'Please, enter your Purchase Code', 'minerva-kb' ),
				'default' => '',
				'description' => __( 'Purchase code can be downloaded at Envato dashboard / Downloads / MinervaKB / Download > License certificate & purchase code.', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'auto_updates_switch',
					'type' => 'EQ',
					'value' => true
				)
			),
			/**
			 * Google Analytics
			 */
			array(
				'id' => 'ga_tab',
				'type' => 'tab',
				'label' => __( 'Google Analytics', 'minerva-kb' ),
				'icon' => 'fa-line-chart'
			),
			array(
				'id' => 'ga_title',
				'type' => 'title',
				'label' => __( 'Google Analytics custom events integration', 'minerva-kb' ),
				'description' => __( 'Please note: MinervaKB does not add Google Analytics tracking code, this is usually done in theme templates. Please follow the instructions on Google Analytics tracking code page.', 'minerva-kb' )
			),
			// ok search
			array(
				'id' => 'track_search_with_results',
				'type' => 'checkbox',
				'label' => __( 'Track search with results?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'Search keyword will be used as Event Label', 'minerva-kb' ),
			),
			array(
				'id' => 'ga_good_search_category',
				'type' => 'input',
				'label' => __( 'Successful search: Event category', 'minerva-kb' ),
				'default' => __( 'Knowledge Base', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'track_search_with_results',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'ga_good_search_action',
				'type' => 'input',
				'label' => __( 'Successful search: Event action', 'minerva-kb' ),
				'default' => __( 'Search success', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'track_search_with_results',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'ga_good_search_value',
				'type' => 'input',
				'label' => __( 'Successful search: Event value (integer, optional)', 'minerva-kb' ),
				'default' => __( '', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'track_search_with_results',
					'type' => 'EQ',
					'value' => true
				)
			),
			// failed search
			array(
				'id' => 'track_search_without_results',
				'type' => 'checkbox',
				'label' => __( 'Track search without results?', 'minerva-kb' ),
				'default' => false,
				'description' => __( 'Search keyword will be used as Event Label', 'minerva-kb' ),
			),
			array(
				'id' => 'ga_bad_search_category',
				'type' => 'input',
				'label' => __( 'Failed search: Event category', 'minerva-kb' ),
				'default' => __( 'Knowledge Base', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'track_search_without_results',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'ga_bad_search_action',
				'type' => 'input',
				'label' => __( 'Failed search: Event action', 'minerva-kb' ),
				'default' => __( 'Search fail', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'track_search_without_results',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'ga_bad_search_value',
				'type' => 'input',
				'label' => __( 'Failed search: Event value (integer, optional)', 'minerva-kb' ),
				'default' => __( '', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'track_search_without_results',
					'type' => 'EQ',
					'value' => true
				)
			),
			//likes
			array(
				'id' => 'track_article_likes',
				'type' => 'checkbox',
				'label' => __( 'Track article likes?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'ga_like_category',
				'type' => 'input',
				'label' => __( 'Like: Event category', 'minerva-kb' ),
				'default' => __( 'Knowledge Base', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'track_article_likes',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'ga_like_action',
				'type' => 'input',
				'label' => __( 'Like: Event action', 'minerva-kb' ),
				'default' => __( 'Article like', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'track_article_likes',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'ga_like_label',
				'type' => 'select',
				'label' => __( 'Like: Event Label', 'minerva-kb' ),
				'options' => array(
					'article_id' => __( 'Article ID', 'minerva-kb' ),
					'article_title' => __( 'Article title', 'minerva-kb' )
				),
				'default' => 'article_id',
				'dependency' => array(
					'target' => 'track_article_likes',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'ga_like_value',
				'type' => 'input',
				'label' => __( 'Like: Event value (integer, optional)', 'minerva-kb' ),
				'default' => __( '', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'track_article_likes',
					'type' => 'EQ',
					'value' => true
				)
			),
			// dislikes
			array(
				'id' => 'track_article_dislikes',
				'type' => 'checkbox',
				'label' => __( 'Track article dislikes?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'ga_dislike_category',
				'type' => 'input',
				'label' => __( 'Dislike: Event category', 'minerva-kb' ),
				'default' => __( 'Knowledge Base', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'track_article_dislikes',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'ga_dislike_action',
				'type' => 'input',
				'label' => __( 'Dislike: Event action', 'minerva-kb' ),
				'default' => __( 'Article dislike', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'track_article_dislikes',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'ga_dislike_label',
				'type' => 'select',
				'label' => __( 'Dislike: Event Label', 'minerva-kb' ),
				'options' => array(
					'article_id' => __( 'Article ID', 'minerva-kb' ),
					'article_title' => __( 'Article title', 'minerva-kb' )
				),
				'default' => 'article_id',
				'dependency' => array(
					'target' => 'track_article_dislikes',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'ga_dislike_value',
				'type' => 'input',
				'label' => __( 'Dislike: Event value (integer, optional)', 'minerva-kb' ),
				'default' => __( '', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'track_article_dislikes',
					'type' => 'EQ',
					'value' => true
				)
			),
			// feedback
			array(
				'id' => 'track_article_feedback',
				'type' => 'checkbox',
				'label' => __( 'Track article feedback?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'ga_feedback_category',
				'type' => 'input',
				'label' => __( 'Feedback: Event category', 'minerva-kb' ),
				'default' => __( 'Knowledge Base', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'track_article_feedback',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'ga_feedback_action',
				'type' => 'input',
				'label' => __( 'Feedback: Event action', 'minerva-kb' ),
				'default' => __( 'Article feedback', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'track_article_feedback',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'ga_feedback_label',
				'type' => 'select',
				'label' => __( 'Feedback: Event Label', 'minerva-kb' ),
				'options' => array(
					'article_id' => __( 'Article ID', 'minerva-kb' ),
					'article_title' => __( 'Article title', 'minerva-kb' )
				),
				'default' => 'article_id',
				'dependency' => array(
					'target' => 'track_article_feedback',
					'type' => 'EQ',
					'value' => true
				)
			),
			array(
				'id' => 'ga_feedback_value',
				'type' => 'input',
				'label' => __( 'Feedback: Event value (integer, optional)', 'minerva-kb' ),
				'default' => __( '', 'minerva-kb' ),
				'dependency' => array(
					'target' => 'track_article_feedback',
					'type' => 'EQ',
					'value' => true
				)
			),
			/**
			 * Localization
			 */
			array(
				'id' => 'localization_tab',
				'type' => 'tab',
				'label' => __( 'Localization', 'minerva-kb' ),
				'icon' => 'fa-language'
			),
			array(
				'id' => 'localization_title',
				'type' => 'title',
				'label' => __( 'Plugin localization', 'minerva-kb' ),
				'description' => __( 'Here will be general text strings used in plugin. Section specific texts are found in appropriate sections. Alternative you can use WPML or other plugin to translate KB text fields', 'minerva-kb' )
			),

			array(
				'id' => 'articles_text',
				'type' => 'input_text',
				'label' => __( 'Article plural text', 'minerva-kb' ),
				'default' => __( 'articles', 'minerva-kb' )
			),
			array(
				'id' => 'article_text',
				'type' => 'input_text',
				'label' => __( 'Article singular text', 'minerva-kb' ),
				'default' => __( 'article', 'minerva-kb' )
			),
			array(
				'id' => 'questions_text',
				'type' => 'input_text',
				'label' => __( 'Question plural text', 'minerva-kb' ),
				'default' => __( 'questions', 'minerva-kb' )
			),
			array(
				'id' => 'question_text',
				'type' => 'input_text',
				'label' => __( 'Question singular text', 'minerva-kb' ),
				'default' => __( 'question', 'minerva-kb' )
			),
			array(
				'id' => 'cpt_labels_title',
				'type' => 'title',
				'label' => __( 'Post type labels', 'minerva-kb' ),
				'description' => __( 'Change post type labels text', 'minerva-kb' )
			),
			array(
				'id' => 'cpt_label_name',
				'type' => 'input_text',
				'label' => __( 'Post type name', 'minerva-kb' ),
				'default' => __( 'KB Articles', 'minerva-kb' ),
			),
			array(
				'id' => 'cpt_label_singular_name',
				'type' => 'input',
				'label' => __( 'Post type singular name', 'minerva-kb' ),
				'default' => __( 'KB Article', 'minerva-kb' )
			),
			array(
				'id' => 'cpt_label_menu_name',
				'type' => 'input',
				'label' => __( 'Post type menu name', 'minerva-kb' ),
				'default' => __( 'Knowledge Base', 'minerva-kb' )
			),
			array(
				'id' => 'cpt_label_all_articles',
				'type' => 'input',
				'label' => __( 'Post type: All articles', 'minerva-kb' ),
				'default' => __( 'All Articles', 'minerva-kb' )
			),
			array(
				'id' => 'cpt_label_view_item',
				'type' => 'input',
				'label' => __( 'Post type: View item', 'minerva-kb' ),
				'default' => __( 'View Article', 'minerva-kb' )
			),
			array(
				'id' => 'cpt_label_add_new_item',
				'type' => 'input',
				'label' => __( 'Post type: Add new item', 'minerva-kb' ),
				'default' => __( 'Add New Article', 'minerva-kb' )
			),
			array(
				'id' => 'cpt_label_add_new',
				'type' => 'input',
				'label' => __( 'Post type: Add new', 'minerva-kb' ),
				'default' => __( 'Add New Article', 'minerva-kb' )
			),
			array(
				'id' => 'cpt_label_edit_item',
				'type' => 'input',
				'label' => __( 'Post type: Edit item', 'minerva-kb' ),
				'default' => __( 'Edit Article', 'minerva-kb' )
			),
			array(
				'id' => 'cpt_label_update_item',
				'type' => 'input',
				'label' => __( 'Post type: Update item', 'minerva-kb' ),
				'default' => __( 'Update Article', 'minerva-kb' )
			),
			array(
				'id' => 'cpt_label_search_items',
				'type' => 'input',
				'label' => __( 'Post type: Search items', 'minerva-kb' ),
				'default' => __( 'Search Articles', 'minerva-kb' )
			),
			array(
				'id' => 'cpt_label_not_found',
				'type' => 'input',
				'label' => __( 'Post type: Not found', 'minerva-kb' ),
				'default' => __( 'Not Found', 'minerva-kb' )
			),
			array(
				'id' => 'cpt_label_not_found_in_trash',
				'type' => 'input',
				'label' => __( 'Post type: Not found in trash', 'minerva-kb' ),
				'default' => __( 'Not Found In Trash', 'minerva-kb' )
			),
			array(
				'id' => 'cpt_topic_labels_title',
				'type' => 'title',
				'label' => __( 'Post type category labels', 'minerva-kb' ),
				'description' => __( 'Change post type category labels text', 'minerva-kb' )
			),
			array(
				'id' => 'cpt_topic_label_name',
				'type' => 'input_text',
				'label' => __( 'Post type category name', 'minerva-kb' ),
				'default' => __( 'Topics', 'minerva-kb' ),
			),
			array(
				'id' => 'cpt_topic_label_add_new',
				'type' => 'input',
				'label' => __( 'Post type category: Add new', 'minerva-kb' ),
				'default' => __( 'Add New Topic', 'minerva-kb' ),
			),
			array(
				'id' => 'cpt_topic_label_new_item_name',
				'type' => 'input',
				'label' => __( 'Post type category: New item name', 'minerva-kb' ),
				'default' => __( 'New Topic', 'minerva-kb' ),
			),
			array(
				'id' => 'cpt_tag_labels_title',
				'type' => 'title',
				'label' => __( 'Post type tag labels', 'minerva-kb' ),
				'description' => __( 'Change post type tag labels text', 'minerva-kb' )
			),
			array(
				'id' => 'cpt_tag_label_name',
				'type' => 'input',
				'label' => __( 'Post type tag name', 'minerva-kb' ),
				'default' => __( 'KB Tags', 'minerva-kb' ),
			),
			array(
				'id' => 'cpt_tag_label_add_new',
				'type' => 'input',
				'label' => __( 'Post type tag: Add new', 'minerva-kb' ),
				'default' => __( 'Add New Tag', 'minerva-kb' ),
			),
			array(
				'id' => 'cpt_tag_label_new_item_name',
				'type' => 'input',
				'label' => __( 'Post type tag: New item name', 'minerva-kb' ),
				'default' => __( 'New Tag', 'minerva-kb' ),
			),
			array(
				'id' => 'localization_search_title',
				'type' => 'title',
				'label' => __( 'Search labels', 'minerva-kb' )
			),
			array(
				'id' => 'search_results_text',
				'type' => 'input_text',
				'label' => __( 'Search multiple results text', 'minerva-kb' ),
				'default' => __( 'results', 'minerva-kb' )
			),
			array(
				'id' => 'search_result_text',
				'type' => 'input_text',
				'label' => __( 'Search single result text', 'minerva-kb' ),
				'default' => __( 'result', 'minerva-kb' )
			),
			array(
				'id' => 'search_no_results_text',
				'type' => 'input_text',
				'label' => __( 'Search no results text', 'minerva-kb' ),
				'default' => __( 'No results', 'minerva-kb' )
			),
			array(
				'id' => 'search_clear_icon_tooltip',
				'type' => 'input_text',
				'label' => __( 'Clear icon tooltip', 'minerva-kb' ),
				'default' => __( 'Clear search', 'minerva-kb' )
			),
			array(
				'id' => 'localization_pagination_title',
				'type' => 'title',
				'label' => __( 'Pagination labels', 'minerva-kb' )
			),
			array(
				'id' => 'pagination_prev_text',
				'type' => 'input_text',
				'label' => __( 'Previous page link text', 'minerva-kb' ),
				'default' => __( 'Previous', 'minerva-kb' )
			),
			array(
				'id' => 'pagination_next_text',
				'type' => 'input_text',
				'label' => __( 'Next page link text', 'minerva-kb' ),
				'default' => __( 'Next', 'minerva-kb' )
			),
			/**
			 * Theme compatibility
			 */
			array(
				'id' => 'compatibility_tab',
				'type' => 'tab',
				'label' => __( 'Theme options', 'minerva-kb' ),
				'icon' => 'fa-handshake-o'
			),
			array(
				'id' => 'compatibility_title',
				'type' => 'title',
				'label' => __( 'Theme compatibility tools', 'minerva-kb' ),
				'description' => __( 'MinervaKB tries to play well with most themes, but some themes need extra steps. Do not edit these settings unless you experience issues with theme templates', 'minerva-kb' )
			),
			array(
				'id' => 'font_awesome_theme_title',
				'type' => 'title',
				'label' => __( 'Font loading settings', 'minerva-kb' ),
				'description' => __( 'In case your theme loads Font Awesome, you can disable loading it from plugin', 'minerva-kb' )
			),
			array(
				'id' => 'no_font_awesome',
				'type' => 'checkbox',
				'label' => __( 'Do not load Font Awesome assets?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'compatibility_headers_title',
				'type' => 'title',
				'label' => __( 'Template headers and footers', 'minerva-kb' ),
				'description' => __( 'Most often single / category templates are used as standalone pages. But sometimes themes load them from inside other templates. In this scenario we do not need to load header and footer', 'minerva-kb' )
			),
			array(
				'id' => 'no_article_header',
				'type' => 'checkbox',
				'label' => __( 'Do not load header in article template?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'no_article_footer',
				'type' => 'checkbox',
				'label' => __( 'Do not load footer in article template?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'no_topic_header',
				'type' => 'checkbox',
				'label' => __( 'Do not load header in topic template?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'no_topic_footer',
				'type' => 'checkbox',
				'label' => __( 'Do not load footer in topic template?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'no_page_header',
				'type' => 'checkbox',
				'label' => __( 'Do not load header in page template?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'no_page_footer',
				'type' => 'checkbox',
				'label' => __( 'Do not load footer in page template?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'no_tag_header',
				'type' => 'checkbox',
				'label' => __( 'Do not load header in tag template?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'no_tag_footer',
				'type' => 'checkbox',
				'label' => __( 'Do not load footer in tag template?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'no_archive_header',
				'type' => 'checkbox',
				'label' => __( 'Do not load header in archive template?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'no_archive_footer',
				'type' => 'checkbox',
				'label' => __( 'Do not load footer in archive template?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'no_search_header',
				'type' => 'checkbox',
				'label' => __( 'Do not load header in search results template?', 'minerva-kb' ),
				'default' => false
			),
			array(
				'id' => 'no_search_footer',
				'type' => 'checkbox',
				'label' => __( 'Do not load footer in search results template?', 'minerva-kb' ),
				'default' => false
			),
			/**
			 * Demo import
			 */
			array(
				'id' => 'demo_import_tab',
				'type' => 'tab',
				'label' => __( 'Demo import', 'minerva-kb' ),
				'icon' => 'fa-gift'
			),
			array(
				'id' => 'demo_import',
				'type' => 'demo_import',
				'label' => __( 'One-click Demo Import', 'minerva-kb' ),
				'default' => '',
				'description' => __( 'You can import dummy articles, topics and pages for quick testing. Press Skip if you don\'t want this tab to open by default (you will still be able to use import later)', 'minerva-kb' ),
			),
			/**
			 * Import / Export
			 */
			array(
				'id' => 'export_import_tab',
				'type' => 'tab',
				'label' => __( 'Import / Export', 'minerva-kb' ),
				'icon' => 'fa-cloud-download'
			),
			array(
				'id' => 'settings_export',
				'type' => 'export',
				'label' => __( 'Settings export. You can copy and save this content:', 'minerva-kb' ),
				'default' => '',
				'description' => __( 'NOTE: Only saved settings are exported, if you have unsaved changes you need to save them before exporting.', 'minerva-kb' ),
			),
			array(
				'id' => 'settings_import',
				'type' => 'import',
				'label' => __( 'Settings import. Paste saved settings here:', 'minerva-kb' ),
				'default' => ''
			),
		);
	}

	protected static function get_pages_options($published_only = true) {
		$result = array("" => __('Please select page', 'minerva-kb'));

		$pages_args = array(
			'sort_order' => 'asc',
			'sort_column' => 'post_title',
			'hierarchical' => 1,
			'exclude' => '',
			'include' => '',
			'meta_key' => '',
			'meta_value' => '',
			'authors' => '',
			'child_of' => 0,
			'parent' => -1,
			'exclude_tree' => '',
			'number' => '',
			'offset' => 0,
			'post_type' => 'page',
			'post_status' => $published_only ? 'publish' : 'publish,private,draft'
		);

		$pages = get_pages($pages_args);

		if ($pages) {
			$result = array_reduce($pages, function($all, $page) {
				$all[$page->ID] = $page->post_title;

				return $all;
			}, $result);
		}

		return $result;
	}

	protected static function get_home_layout_options() {
		return array(
			array(
				'key' => 'search',
				'label' => __('Search', 'minerva-kb'),
				'icon' => 'fa-eye'
			),
			array(
				'key' => 'topics',
				'label' => __('Topics', 'minerva-kb'),
				'icon' => 'fa-eye'
			),
			array(
				'key' => 'tagcloud',
				'label' => __('Tag cloud', 'minerva-kb'),
				'icon' => 'fa-eye'
			),
			array(
				'key' => 'top_articles',
				'label' => __('Top articles', 'minerva-kb'),
				'icon' => 'fa-eye'
			)
		);
	}

	protected static function get_user_roles_options() {
		return array(
			'none' => __('Not restricted', 'minerva-kb'),
			'administrator' => __('Administrator', 'minerva-kb'),
			'editor' => __('Editor', 'minerva-kb'),
			'author' => __('Author', 'minerva-kb'),
			'contributor' => __('Contributor', 'minerva-kb'),
			'subscriber' => __('Subscriber', 'minerva-kb'),
		);
	}

	public static function get_topics_options() {
		$saved = self::get_saved_values();
		$category = isset($saved['article_cpt_category']) ?
			$saved['article_cpt_category'] :
			'topic'; // TODO: use separate defaults

		$options = array(
			array(
				'key' => 'recent',
				'label' => __('Recent', 'minerva-kb')
			),
			array(
				'key' => 'updated',
				'label' => __('Recently updated', 'minerva-kb')
			),
			array(
				'key' => 'top_views',
				'label' => __('Most viewed', 'minerva-kb')
			),
			array(
				'key' => 'top_likes',
				'label' => __('Most liked', 'minerva-kb')
			)
		);

		$topics = get_terms( $category, array(
			'hide_empty' => false,
		) );

		if (isset($topics) && !is_wp_error($topics) && !empty($topics)) {
			foreach ( $topics as $item ):
				array_push($options, array(
					'key' => $item->term_id,
					'label' => $item->name,
				));
			endforeach;
		}

		return $options;
	}
	
	public static function get_faq_categories_options() {
		$options = array();

		$categories = get_terms( 'mkb_faq_category', array(
			'hide_empty' => false,
		) );

		if (isset($categories) && !is_wp_error($categories) && !empty($categories)) {
			foreach ( $categories as $item ):
				array_push($options, array(
					'key' => $item->term_id,
					'label' => $item->name,
				));
			endforeach;
		}

		return $options;
	}

	public static function get_search_topics_options() {
		$saved = self::get_saved_values();
		$category = isset($saved['article_cpt_category']) ?
			$saved['article_cpt_category'] :
			'topic'; // TODO: use separate defaults

		$options = array();

		$topics = get_terms( $category, array(
			'hide_empty' => false,
		) );

		if (isset($topics) && !is_wp_error($topics) && !empty($topics)) {
			foreach ( $topics as $item ):
				array_push($options, array(
					'key' => $item->term_id,
					'label' => $item->name,
				));
			endforeach;
		}

		return $options;
	}

    /**
     * Get options from tax terms list
     * @param $taxonomy
     * @return array
     */
    public static function get_tax_term_options($taxonomy) {
        $options = array(
            '' => __('Not set', 'minerva-kb')
        );

        $terms = get_terms(array(
            'taxonomy' => $taxonomy,
            'hide_empty' => false
        ));

        if (isset($terms) && !is_wp_error($terms) && !empty($terms)) {
            foreach ($terms as $term):
                $options[$term->term_id] = $term->name;
            endforeach;
        }

        return $options;
    }

    /**
     * @param $roles
     * @param $caps
     * @return array
     */
    public static function get_user_options($roles = array(), $caps = array()) {
        $options = array(
            '' => __('Not set', 'minerva-kb')
        );

        $args = array('role__in' => $roles); // TODO: order in code, if necessary
        $users = get_users($args);

        if (isset($users) && !is_wp_error($users) && !empty($users)) {
            foreach ($users as $user):
                $options[$user->ID] = $user->display_name;
            endforeach;
        }

        return $options;
    }

	/**
	 * To be used inside options method
	 * @param $key
	 */
	protected static function get_saved_option($key, $default = null) {
		$saved = self::get_saved_values();
		return isset($saved[$key]) ? $saved[$key] : $default;
	}

	/**
	 * @return array
	 */
	public static function get_home_sections_options() {
		$saved = self::get_saved_values();
		$faq_disable = isset($saved['disable_faq']) ? $saved['disable_faq'] : false;

		$options = array(
			array(
				'key' => 'search',
				'label' => __('Search', 'minerva-kb')
			),
			array(
				'key' => 'topics',
				'label' => __('Topics', 'minerva-kb')
			)
		);

		if (!$faq_disable) {
			array_push($options, array(
				'key' => 'faq',
				'label' => __('FAQ', 'minerva-kb')
			));
		}

		return $options;
	}

	public static function get_non_ui_options($options) {
		return array_filter($options, function($option) {
			return !in_array($option['type'], array(
				'tab',
				'title',
				'description',
				'code',
				'info',
				'warning',
				'demo_import',
				'export',
				'import'
			));
		});
	}

	public static function save($options) {
		self::add_wpml_string_options($options);

		$result = update_option(self::OPTION_KEY, json_encode($options));

		// invalidate options cache
		global $minerva_kb_options_cache;
		$minerva_kb_options_cache = null;

		global $minerva_kb;
		$minerva_kb->restrict->invalidate_restriction_cache();

		return $result;
	}

	/**
	 * Imports previously saved settings
	 * @param $import_data
	 *
	 * @return bool
	 */
	public static function import($import_data) {
		$parse_data = null;

		try {

			$parse_data = json_decode(stripslashes_deep($import_data), true);

			if (empty($parse_data)) {
				return false;
			}

			$all_options = self::get();

			foreach($all_options as $key => $value) {
				if (isset($parse_data[$key])) {
					$all_options[$key] = $parse_data[$key];
				}
			}

			self::save($all_options);

		} catch (Exception $e) {
			return false;
		}

		return true;
	}

	/**
	 * Registers options that require translations
	 * @param $options
	 */
	private static function add_wpml_string_options($options) {

		if (!function_exists ( 'icl_register_string' )) { return; }

		$all_options = self::get_options_by_id();

		foreach($options as $id => $value) {
			if (!isset($all_options[$id]) ||
			    ($all_options[$id]['type'] !== 'input_text' && $all_options[$id]['type'] !== 'textarea_text')) {
				continue;
			}

			icl_register_string(self::WPML_DOMAIN, $all_options[$id]['label'], $value);
		}
	}

	/**
	 * Translates saved values
	 * @param $options
	 *
	 * @return mixed
	 */
	private static function translate_values($options) {

		if (!function_exists( 'icl_register_string' )) {
			return $options;
		}

		$all_options = self::get_options_by_id();

		foreach($options as $id => $value) {
			if (!isset($all_options[$id]) ||
			    ($all_options[$id]['type'] !== 'input_text' && $all_options[$id]['type'] !== 'textarea_text')) {
				continue;
			}

			$options[$id] = apply_filters('wpml_translate_single_string', $value, self::WPML_DOMAIN, $all_options[$id]['label']);
		}

		return $options;
	}

	public static function save_option($key, $value) {
		$all_options = self::get();
		$all_options[$key] = $value;

		self::save($all_options);
	}

    public static function save_options($changed_options) {
        $all_options = self::get();

        foreach($changed_options as $key => $value) {
            $all_options[$key] = $value;
        }

        self::save($all_options);
    }

	public static function reset() {
		update_option(self::OPTION_KEY, json_encode(self::get_options_defaults()));
	}

	public static function get() {
		global $minerva_kb_options_cache;

		if (!$minerva_kb_options_cache) {
			$minerva_kb_options_cache = self::translate_values(
				wp_parse_args(self::get_saved_values(), self::get_options_defaults())
			);
		}

		return $minerva_kb_options_cache;
	}

	public static function get_saved_values() {
		$options = json_decode(get_option(self::OPTION_KEY), true);

		$options = !empty($options) ? $options : array();

		return self::normalize_values(stripslashes_deep($options));
	}

	public static function normalize_values($settings) {
		return array_map(function($value) {
			if ($value === 'true') {
				return true;
			} else if ($value === 'false') {
				return false;
			} else {
				return $value;
			}
		}, $settings);
	}

	public static function option($key) {
		$all_options = self::get();

		return isset($all_options[$key]) ? $all_options[$key] : null;
	}

	/**
	 * Detects if flush rules was called for current set of CPT slugs
	 * @return bool
	 */
	public static function need_to_flush_rules() {
		$flushed_cpt = get_option('_mkb_flushed_rewrite_cpt');
		$flushed_topic = get_option('_mkb_flushed_rewrite_topic');
		$flushed_tag = get_option('_mkb_flushed_rewrite_tag');

		$cpt_slug = self::option('cpt_slug_switch') ? self::option('article_slug') : self::option('article_cpt');
		$cpt_category_slug = self::option('cpt_category_slug_switch') ? self::option('category_slug') : self::option('article_cpt_category');
		$cpt_tag_slug = self::option('cpt_tag_slug_switch') ? self::option('tag_slug') : self::option('article_cpt_tag');

		return $cpt_slug != $flushed_cpt ||
		       $cpt_category_slug != $flushed_topic ||
		       $cpt_tag_slug != $flushed_tag;
	}

	/**
	 * Sets flush flags not to flush on every load
	 */
	public static function update_flush_flags() {
		$cpt_slug = self::option('cpt_slug_switch') ? self::option('article_slug') : self::option('article_cpt');
		$cpt_category_slug = self::option('cpt_category_slug_switch') ? self::option('category_slug') : self::option('article_cpt_category');
		$cpt_tag_slug = self::option('cpt_tag_slug_switch') ? self::option('tag_slug') : self::option('article_cpt_tag');

		update_option('_mkb_flushed_rewrite_cpt', $cpt_slug);
		update_option('_mkb_flushed_rewrite_topic', $cpt_category_slug);
		update_option('_mkb_flushed_rewrite_tag', $cpt_tag_slug);
	}

	/**
	 * Removes flags on uninstall
	 */
	public static function remove_flush_flags() {
		delete_option('_mkb_flushed_rewrite_cpt');
		delete_option('_mkb_flushed_rewrite_topic');
		delete_option('_mkb_flushed_rewrite_tag');
	}

	/**
	 * Removes all plugin data from options table
	 */
	public static function remove_data() {
		delete_option(self::OPTION_KEY);
	}
}

global $minerva_kb_options;

$minerva_kb_options = new MKB_Options();