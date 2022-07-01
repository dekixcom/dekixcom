<?php
/**
 * Project: Minerva KB
 * Copyright: 2015-2016 @KonstruktStudio
 */

require_once(MINERVA_KB_PLUGIN_DIR . 'lib/widgets/recent-topics.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/widgets/recent-articles.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/widgets/table-of-contents.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/widgets/search.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/widgets/breadcrumbs.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/widgets/content-tree.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/widgets/account.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/widgets/create-ticket-link.php');

/**
 * Sidebars and widgets
 */
class MinervaKB_Widgets {

	/**
	 * Constructor
	 */
	public function __construct () {
		add_action( 'widgets_init', array($this, 'register_sidebars') );
		add_action( 'widgets_init', array($this, 'register_widgets') );
	}

	/**
	 * Register all plugin sidebars
	 */
	public function register_sidebars() {
	    $heading_tag = MKB_Options::option('widget_heading_type');
	    $before_title = '<' . $heading_tag . ' class="mkb-widget-title">';
        $after_title = '</' . $heading_tag . '>';

		register_sidebar(
			apply_filters('minerva_home_sidebar_options',
				array(
					'name' => __('MinervaKB: Home', 'minerva-kb'),
					'id' => 'sidebar-kb-page',
					'description' => __('Add widgets here to appear in your Knowledge Base home page sidebar.', 'minerva-kb'),
					'before_widget' => '<section id="%1$s" class="widget mkb-widget %2$s">',
					'after_widget' => '</section>',
                    'before_title' => $before_title,
                    'after_title' => $after_title,
				)
			)
		);

        register_sidebar(
			apply_filters('minerva_topic_sidebar_options',
				array(
					'name' => __('MinervaKB: Topic', 'minerva-kb'),
					'id' => 'sidebar-kb-topic',
					'description' => __('Add widgets here to appear in your Knowledge Base Topic sidebar.', 'minerva-kb'),
					'before_widget' => '<section id="%1$s" class="widget mkb-widget %2$s">',
					'after_widget' => '</section>',
					'before_title' => $before_title,
					'after_title' => $after_title,
				)
			)
		);

		if (!MKB_Options::option( 'tags_disable' )) {

			register_sidebar(
				apply_filters('minerva_tag_sidebar_options',
					array(
						'name' => __('MinervaKB: Tag', 'minerva-kb'),
						'id' => 'sidebar-kb-tag',
						'description' => __('Add widgets here to appear in your Knowledge Base Tag sidebar.', 'minerva-kb'),
						'before_widget' => '<section id="%1$s" class="widget mkb-widget %2$s">',
						'after_widget' => '</section>',
                        'before_title' => $before_title,
                        'after_title' => $after_title,
					)
				)
			);

		}

		register_sidebar(
			apply_filters('minerva_article_sidebar_options',
				array(
					'name' => __('MinervaKB: Article', 'minerva-kb'),
					'id' => 'sidebar-kb-article',
					'description' => __('Add widgets here to appear in your Knowledge Base Article sidebar.', 'minerva-kb'),
					'before_widget' => '<section id="%1$s" class="widget mkb-widget %2$s">',
					'after_widget' => '</section>',
                    'before_title' => $before_title,
                    'after_title' => $after_title,
				)
			)
		);

		register_sidebar(
			apply_filters('minerva_search_sidebar_options',
				array(
					'name' => __('MinervaKB: Search Results', 'minerva-kb'),
					'id' => 'sidebar-kb-search',
					'description' => __('Add widgets here to appear in your Knowledge Base Search Results Page sidebar.', 'minerva-kb'),
					'before_widget' => '<section id="%1$s" class="widget mkb-widget %2$s">',
					'after_widget' => '</section>',
                    'before_title' => $before_title,
                    'after_title' => $after_title,
				)
			)
		);

        /**
         * Tickets
         */
        register_sidebar(
            apply_filters('minerva_ticket_sidebar_options',
                array(
                    'name' => __('MinervaKB: Ticket', 'minerva-kb'),
                    'id' => 'sidebar-kb-ticket',
                    'description' => __('Add widgets here to appear in your Support Ticket sidebar.', 'minerva-kb'),
                    'before_widget' => '<section id="%1$s" class="widget mkb-widget %2$s">',
                    'after_widget' => '</section>',
                    'before_title' => $before_title,
                    'after_title' => $after_title,
                )
            )
        );

        register_sidebar(
            apply_filters('minerva_create_ticket_sidebar_options',
                array(
                    'name' => __('MinervaKB: Create Ticket', 'minerva-kb'),
                    'id' => 'sidebar-kb-create_ticket',
                    'description' => __('Add widgets here to appear in your Create Ticket page sidebar.', 'minerva-kb'),
                    'before_widget' => '<section id="%1$s" class="widget mkb-widget %2$s">',
                    'after_widget' => '</section>',
                    'before_title' => $before_title,
                    'after_title' => $after_title,
                )
            )
        );

        register_sidebar(
            apply_filters('minerva_support_account_sidebar_options',
                array(
                    'name' => __('MinervaKB: Support Account', 'minerva-kb'),
                    'id' => 'sidebar-kb-support_account',
                    'description' => __('Add widgets here to appear in your Support Account Page sidebar.', 'minerva-kb'),
                    'before_widget' => '<section id="%1$s" class="widget mkb-widget %2$s">',
                    'after_widget' => '</section>',
                    'before_title' => $before_title,
                    'after_title' => $after_title,
                )
            )
        );
	}

	/**
	 * Registers plugin widgets
	 */
	public function register_widgets() {
		register_widget( 'MKB_Recent_Topics_Widget' );
		register_widget( 'MKB_Recent_Articles_Widget' );
		register_widget( 'MKB_Table_Of_Contents_Widget' );
		register_widget( 'MKB_Search_Widget' );
		register_widget( 'MKB_Breadcrumbs_Widget' );
		register_widget( 'MKB_Content_Tree_Widget' );
		register_widget( 'MKB_Create_Ticket_Link_Widget' );
		register_widget( 'MKB_Account_Widget' );
	}
}
