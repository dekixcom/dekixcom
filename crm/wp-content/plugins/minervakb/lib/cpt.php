<?php
/**
 * Project: Minerva KB
 * Copyright: 2015-2016 @KonstruktStudio
 */

require_once(MINERVA_KB_PLUGIN_DIR . 'lib/helpers/icon-options.php');

/**
 * Class MinervaKB_CPT
 * Manages custom post type creation and edit pages
 */
class MinervaKB_CPT {

	private $info;

	private $restrict;

	/**
	 * Constructor
	 */
	public function __construct($deps) {

		$this->setup_dependencies($deps);

		$article_cpt = MKB_Options::option('article_cpt');
		$topic_taxonomy = MKB_Options::option('article_cpt_category');

		// post types
		add_action('init', array($this, 'register_post_types'), 10);

		// topic settings
		add_action($topic_taxonomy . '_edit_form_fields', array($this, 'topic_edit_screen_html'), 10, 2);
		add_action('edited_' . $topic_taxonomy, array($this, 'save_topic_meta'), 10, 2);
		add_action('create_' . $topic_taxonomy, array($this, 'save_topic_meta'), 10, 2);
		add_action('delete_' . $topic_taxonomy, array($this, 'delete_topic_meta'), 10, 2);

		// faq settings
		add_action('delete_mkb_faq_category', array($this, 'delete_faq_category_meta'), 10, 2);

		// Drag n Drop articles reorder
		add_action('pre_get_posts', array($this, 'admin_custom_articles_order'), 999);
		add_action('pre_get_posts', array($this, 'custom_articles_order'), 999);

		// extra article list columns
		add_filter('manage_' . $article_cpt . '_posts_columns', array($this, 'set_custom_edit_kb_columns'));
		add_action('manage_' . $article_cpt . '_posts_custom_column' , array($this, 'custom_kb_column'), 0, 2);
		add_filter('manage_edit-' . $article_cpt . '_sortable_columns', array($this, 'sortable_kb_column'));
		add_action('pre_get_posts', array($this, 'kb_list_orderby'));

        if (!MKB_Options::option('tickets_disable_tickets')) {
            // extra ticket list columns
            add_filter('manage_mkb_ticket_posts_columns', array($this, 'set_custom_edit_ticket_columns'));
            add_action('manage_mkb_ticket_posts_custom_column', array($this, 'custom_ticket_column'), 0, 2);

            // TODO: sorting & filters
            add_filter('post_row_actions', array($this, 'remove_ticket_bulk_actions'), 10, 2);
            add_action('pre_get_posts', array($this, 'admin_tickets_query_filters'), 999);
        }

        // post delete
        add_action('before_delete_post', array($this, 'handle_post_delete'));

		// filter topic & tags selects
		add_action( 'restrict_manage_posts', array($this, 'article_list_topic_filter'), 10, 2);
		add_filter( 'parse_query', array($this, 'filter_request_query_topic') , 10);
		add_action( 'restrict_manage_posts', array($this, 'article_list_tag_filter'), 10, 2);
		add_filter( 'parse_query', array($this, 'filter_request_query_tag') , 10);

		// gutenberg editor filters
        add_filter( 'use_block_editor_for_post_type', array($this, 'articles_block_editor_filter'), 10, 2 );
        add_filter( 'use_block_editor_for_post_type', array($this, 'faq_block_editor_filter'), 10, 2 );
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
	 * Registers all configured custom post types
	 */
	public function register_post_types() {

		$this->register_article_cpt();
		$this->register_topic_taxonomy();
		$this->register_tag_taxonomy();

		if (MKB_Options::option('add_article_versions')) {
			$this->register_versions_taxonomy();
		}

		// flush rewrite rules for CPT that have public URLs
		$this->maybe_flush_rules();

		// Feedback
		$this->register_feedback_cpt();

		// FAQ
		if (!MKB_Options::option('disable_faq')) {
			$this->register_faq_cpt();
			$this->register_faq_taxonomy();
		}

		// Glossary
        if (!MKB_Options::option('disable_glossary')) {
            $this->register_glossary_cpt();
        }

        if (!MKB_Options::option('tickets_disable_tickets')) {
            // tickets
            $this->register_ticket_cpt();

            // ticket taxonomies
            $this->register_ticket_type_taxonomy();
            $this->register_ticket_priority_taxonomy();
            $this->register_ticket_tag_taxonomy();
            $this->register_ticket_department_taxonomy();
            $this->register_ticket_product_taxonomy();

            // canned responses
            $this->register_canned_response_cpt();
            $this->register_canned_response_category_taxonomy();

            // replies
            $this->register_ticket_reply_cpt();
        }
    }

	/**
	 * Flush rewrite rules if never flushed
	 */
	private function maybe_flush_rules () {
		// NOTE: needed to make CPT visible after register (force WP rewrite rules flush)
		if (MKB_Options::need_to_flush_rules()) {
			flush_rewrite_rules(false);

			MKB_Options::update_flush_flags();
		}
	}

	/**
	 * Registers KB article custom post type
	 */
	private function register_article_cpt() {
		$labels = array(
			'name' => MKB_Options::option( 'cpt_label_name' ),
			'singular_name' => MKB_Options::option( 'cpt_label_singular_name' ),
			'menu_name' => 'MinervaKB',
			'all_items' => MKB_Options::option( 'cpt_label_menu_name' ),
			'view_item' => MKB_Options::option( 'cpt_label_view_item' ),
			'add_new_item' => MKB_Options::option( 'cpt_label_add_new_item' ),
			'add_new' => MKB_Options::option( 'cpt_label_add_new' ),
			'edit_item' => MKB_Options::option( 'cpt_label_edit_item' ),
			'update_item' => MKB_Options::option( 'cpt_label_update_item' ),
			'search_items' => MKB_Options::option( 'cpt_label_search_items' ),
			'not_found' => MKB_Options::option( 'cpt_label_not_found' ),
			'not_found_in_trash' => MKB_Options::option( 'cpt_label_not_found_in_trash' ),
		);

        $caps = array(
            // we do not define read_, because we don't currently use it. So 'read' is used by default
            'create_posts'	=> 'mkb_edit_articles',
            'edit_post'	=> 'mkb_edit_article', // meta cap
            'edit_posts' => 'mkb_edit_articles',
            'edit_others_posts' => 'mkb_edit_others_articles',
            'edit_published_posts' => 'mkb_edit_published_articles',

            'publish_posts' => 'mkb_publish_articles',

            'read_private_posts' => 'mkb_read_private_articles',
            'edit_private_posts' => 'mkb_edit_private_articles',
            'delete_private_posts' => 'mkb_delete_private_articles',

            'delete_post' => 'mkb_delete_article', // meta cap
            'delete_posts' => 'mkb_delete_articles',
            'delete_published_posts' => 'mkb_delete_published_articles',
            'delete_others_posts' => 'mkb_delete_others_articles',
        );

		$args = array(
			'description' => __( 'KB Articles', 'minerva-kb' ),
			'labels' => $labels,
			'supports' => array(
				'title',
				'editor',
				'excerpt',
				'thumbnail',
				'author',
				'comments',
				'revisions',
				'custom-fields',
			),
			'taxonomies' => array(
				MKB_Options::option( 'article_cpt_category' ),
				MKB_Options::option( 'article_cpt_tag' )
			),
			'hierarchical' => false,
			'public' => true,
			'show_ui' => true,
			'show_in_menu' => true,
			'show_in_nav_menus' => true,
			'show_in_admin_bar' => true,
			'menu_position' => 10,
			'menu_icon' => MINERVA_KB_IMG_URL . 'minerva-icon.png',
			'can_export' => true,
			'has_archive' => (bool) !MKB_Options::option('cpt_archive_disable_switch'),
			'exclude_from_search' => false,
			'publicly_queryable' => true,
            'capability_type' => 'post',
            'map_meta_cap' => true,
            'show_in_rest' => true
		);

		if (!current_user_can('administrator')) {
            $args['capabilities'] = $caps;
        }

		if (MKB_Options::option( 'cpt_slug_switch' )) {
			$args["rewrite"] = array(
				"slug" => MKB_Options::option( 'article_slug' ),
				"with_front" => MKB_Options::option( 'cpt_slug_front_switch' )
			);
		}

		register_post_type( MKB_Options::option( 'article_cpt' ), $args );
	}

	/**
	 * Registers KB topic custom taxonomy
	 */
	private function register_topic_taxonomy() {
		$args = array(
			'labels' => array(
				'name' => MKB_Options::option( 'cpt_topic_label_name' ),
				'add_new_item' => MKB_Options::option( 'cpt_topic_label_add_new' ),
				'new_item_name' => MKB_Options::option( 'cpt_topic_label_new_item_name' )
			),
			'show_ui' => true,
			'show_tagcloud' => false,
			'hierarchical' => true,
            'show_in_rest' => true
		);

        if (!current_user_can('administrator')) {
            $args['capabilities'] = array(
                'manage_terms' => 'mkb_manage_kb_topics',
                'edit_terms' => 'mkb_manage_kb_topics',
                'delete_terms' => 'mkb_manage_kb_topics',
                'assign_terms' => 'mkb_assign_kb_topics'
            );
        }

		if (MKB_Options::option( 'cpt_category_slug_switch' )) {
			$args["rewrite"] = array(
				"slug" => MKB_Options::option( 'category_slug' ),
				"with_front" => MKB_Options::option( 'cpt_category_slug_front_switch' )
			);
		}

		register_taxonomy(
			MKB_Options::option( 'article_cpt_category' ),
			MKB_Options::option( 'article_cpt' ),
			$args
		);
	}

	/**
	 * Registers KB tag custom taxonomy
	 */
	private function register_tag_taxonomy() {
		$args = array(
			'labels' => array(
				'name' => MKB_Options::option( 'cpt_tag_label_name' ),
				'add_new_item' => MKB_Options::option( 'cpt_tag_label_add_new' ),
				'new_item_name' => MKB_Options::option( 'cpt_tag_label_new_item_name' )
			),
			'show_ui' => true,
			'publicly_queryable' => !MKB_Options::option( 'tags_disable' ),
			'show_tagcloud' => true,
			'hierarchical' => false,
            'show_in_rest' => true
		);

        if (!current_user_can('administrator')) {
            $args['capabilities'] = array(
                'manage_terms' => 'mkb_manage_kb_tags',
                'edit_terms' => 'mkb_manage_kb_tags',
                'delete_terms' => 'mkb_manage_kb_tags',
                'assign_terms' => 'mkb_assign_kb_tags'
            );
        }

		if (MKB_Options::option( 'cpt_tag_slug_switch' )) {
			$args["rewrite"] = array(
				"slug" => MKB_Options::option( 'tag_slug' ),
				"with_front" => MKB_Options::option( 'cpt_tag_slug_front_switch' )
			);
		}

		register_taxonomy(
			MKB_Options::option( 'article_cpt_tag' ),
			MKB_Options::option( 'article_cpt' ),
			$args
		);
	}

	/**
	 * Registers KB versions custom taxonomy
	 */
	private function register_versions_taxonomy() {
		$args = array(
			'labels' => array(
				'name' => __( 'Versions', 'minerva-kb' ),
				'add_new_item' => __( 'Add version', 'minerva-kb' ),
				'new_item_name' => __( 'New version', 'minerva-kb' )
			),
			'show_ui' => true,
			'publicly_queryable' => (bool)MKB_Options::option( 'enable_versions_archive' ),
			'show_tagcloud' => true,
			'hierarchical' => false,
            'show_in_rest' => true
		);

        if (!current_user_can('administrator')) {
            $args['capabilities'] = array(
                'manage_terms' => 'mkb_manage_kb_versions',
                'edit_terms' => 'mkb_manage_kb_versions',
                'delete_terms' => 'mkb_manage_kb_versions',
                'assign_terms' => 'mkb_assign_kb_versions'
            );
        }

		if (MKB_Options::option( 'enable_versions_archive' )) {
			$args["rewrite"] = array(
				"slug" => MKB_Options::option('versions_slug'),
				"with_front" => false
			);
		}

		register_taxonomy(
			'mkb_version',
			MKB_Options::option( 'article_cpt' ),
			$args
		);
	}

	/**
	 * Registers feedback custom post type
	 */
	private function register_feedback_cpt() {
		/**
		 * Feedback
		 */
		$labels = array(
			'name' => __('KB Feedback', 'minerva-kb'),
			'singular_name' => __('KB Feedback', 'minerva-kb'),
			'menu_name' => __('KB Feedback', 'minerva-kb'),
			'all_items' => __('All suggestions', 'minerva-kb'),
			'view_item' => __('View suggestion', 'minerva-kb'),
			'add_new_item' => __('Add new suggestion', 'minerva-kb'),
			'add_new' => __('Add new', 'minerva-kb'),
			'edit_item' => __('Edit suggestion', 'minerva-kb'),
			'update_item' => __('Update suggestion', 'minerva-kb'),
			'search_items' => __('Search suggestions', 'minerva-kb'),
			'not_found' => __('Suggestions not found', 'minerva-kb'),
			'not_found_in_trash' => __('Suggestions not found in trash', 'minerva-kb'),
		);

		$args = array(
			'description' => __( 'KB Feedback', 'minerva-kb' ),
			'labels' => $labels,
			'supports' => array(
				'title',
				'editor',
				'author',
				'revisions'
			),
			'hierarchical' => false,
			'public' => false,
			'show_ui' => false,
			'show_in_menu' => false,
			'show_in_nav_menus' => false,
			'show_in_admin_bar' => false,
			'menu_position' => 10,
			'menu_icon' => 'dashicons-welcome-learn-more',
			'can_export' => false,
			'has_archive' => false,
			'exclude_from_search' => true,
			'publicly_queryable' => false,
			'capability_type' => 'post',
            'show_in_rest' => true
		);

		register_post_type( 'mkb_feedback', $args );
	}

	/**
	 * Registers FAQ custom post type
	 */
	private function register_faq_cpt() {
		/**
		 * FAQ
		 */
		$labels = array(
			'name' => 'KB FAQ',
			'singular_name' => 'KB FAQ',
			'menu_name' => 'FAQ',
			'all_items' => 'All questions',
			'view_item' => 'View question',
			'add_new_item' => 'Add new question',
			'add_new' => 'Add new',
			'edit_item' => 'Edit question',
			'update_item' => 'Update question',
			'search_items' => 'Search question',
			'not_found' => 'Questions not found',
			'not_found_in_trash' => 'Questions not found in trash',
		);

        $caps = array(
            // we do not define read_, because we don't currently use it. So 'read' is used by default
            'create_posts'	=> 'mkb_edit_faqs',
            'edit_post'	=> 'mkb_edit_faq', // meta cap
            'edit_posts' => 'mkb_edit_faqs',
            'edit_others_posts' => 'mkb_edit_others_faqs',
            'edit_published_posts' => 'mkb_edit_published_faqs',
            'publish_posts' => 'mkb_publish_faqs',
            'read_private_posts' => 'mkb_read_private_faqs',
            'edit_private_posts' => 'mkb_edit_private_faqs',
            'delete_private_posts' => 'mkb_delete_private_faqs',
            'delete_post' => 'mkb_delete_faq', // met cap
            'delete_posts' => 'mkb_delete_faqs',
            'delete_published_posts' => 'mkb_delete_published_faqs',
            'delete_others_posts' => 'mkb_delete_others_faqs',
        );

        $args = array(
			'description' => __( 'KB FAQ', 'minerva-kb' ),
			'labels' => $labels,
			'supports' => array(
				'title',
				'editor',
				'author',
				'revisions'
			),
			'hierarchical' => false,
			'public' => true,
			'show_ui' => true,
			'show_in_menu' => true,
			'show_in_nav_menus' => false,
			'show_in_admin_bar' => false,
			'menu_position' => 10,
            'menu_icon' => MINERVA_KB_IMG_URL . 'minerva-icon.png',
			'can_export' => true,
			'has_archive' => false,
			// NOTE: exclude_from_search removes results from taxonomy archives, if we have them in the future
			'exclude_from_search' => !(bool) MKB_Options::option('faq_include_in_search'),
			'publicly_queryable' => (bool) MKB_Options::option('faq_enable_pages'),
			'capability_type' => 'post',
            'map_meta_cap' => true,
			'rewrite' => array(
				"slug" => MKB_Options::option('faq_slug'),
				"with_front" => true
			),
            'show_in_rest' => true
		);

        if (!current_user_can('administrator')) {
            $args['capabilities'] = $caps;
        }

		register_post_type( 'mkb_faq', $args );
	}

	/**
	 * Registers KB topic custom taxonomy
	 */
	private function register_faq_taxonomy() {
		$args = array(
			'labels' => array(
				'name' => __( 'Categories', 'minerva-kb' ),
				'add_new_item' => __( 'Add category', 'minerva-kb' ),
				'new_item_name' => __( 'New category', 'minerva-kb' )
			),
			'show_ui' => true,
			'show_tagcloud' => false,
			'hierarchical' => true,
            'show_in_rest' => true
		);

        if (!current_user_can('administrator')) {
            $args['capabilities'] = array(
                'manage_terms' => 'mkb_manage_faq_categories',
                'edit_terms' => 'mkb_manage_faq_categories',
                'delete_terms' => 'mkb_manage_faq_categories',
                'assign_terms' => 'mkb_assign_faq_categories'
            );
        }

		register_taxonomy(
			'mkb_faq_category',
			'mkb_faq',
			$args
		);
	}

    /**
     * Registers Glossary custom post type
     */
    private function register_glossary_cpt() {
        /**
         * Glossary
         */
        $labels = array(
            'name' => 'KB Glossary',
            'singular_name' => 'KB Glossary',
            'menu_name' => 'Glossary',
            'all_items' => 'All terms',
            'view_item' => 'View term',
            'add_new_item' => 'Add new term',
            'add_new' => 'Add new',
            'edit_item' => 'Edit term',
            'update_item' => 'Update term',
            'search_items' => 'Search term',
            'not_found' => 'Terms not found',
            'not_found_in_trash' => 'Terms not found in trash',
        );

        $caps = array(
            // we do not define read_, because we don't currently use it. So 'read' is used by default
            'create_posts'	=> 'mkb_edit_glossary_terms',
            'edit_post'	=> 'mkb_edit_glossary_term', // meta cap
            'edit_posts' => 'mkb_edit_glossary_terms',
            'edit_others_posts' => 'mkb_edit_others_glossary_terms',
            'edit_published_posts' => 'mkb_edit_published_glossary_terms',
            'publish_posts' => 'mkb_publish_glossary_terms',
            'read_private_posts' => 'mkb_read_private_glossary_terms',
            'edit_private_posts' => 'mkb_edit_private_glossary_terms',
            'delete_private_posts' => 'mkb_delete_private_glossary_terms',
            'delete_post' => 'mkb_delete_glossary_term', // meta cap
            'delete_posts' => 'mkb_delete_glossary_terms',
            'delete_published_posts' => 'mkb_delete_published_glossary_terms',
            'delete_others_posts' => 'mkb_delete_others_glossary_terms',
        );

        $args = array(
            'description' => __( 'KB Glossary', 'minerva-kb' ),
            'labels' => $labels,
            'supports' => array(
                'title',
                'editor',
                'thumbnail',
                'author',
                'revisions'
            ),
            'hierarchical' => false,
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_nav_menus' => false,
            'show_in_admin_bar' => false,
            'menu_position' => 10,
            'menu_icon' => MINERVA_KB_IMG_URL . 'minerva-icon.png',
            'can_export' => true,
            'has_archive' => false,
            // NOTE: exclude_from_search removes results from taxonomy archives, if we have them in the future
            'exclude_from_search' => true,
            'publicly_queryable' => (bool) MKB_Options::option('glossary_enable_pages'),
            'capability_type' => 'post',
            'map_meta_cap' => true,
            'rewrite' => array(
                "slug" => MKB_Options::option('glossary_slug'),
                "with_front" => true
            ),
            'show_in_rest' => true
        );

        if (!current_user_can('administrator')) {
            $args['capabilities'] = $caps;
        }

        register_post_type( 'mkb_glossary', $args );
    }

    /**
     * Registers Tickets custom post type
     */
    private function register_ticket_cpt() {
        /**
         * Tickets
         */
        $labels = array(
            'name' => 'Tickets',
            'singular_name' => 'Ticket',
            'menu_name' => 'Tickets %%MKBTicketCount%%',
            'all_items' => 'Support Tickets',
            'view_item' => 'View Ticket',
            'add_new_item' => 'Open New Ticket',
            'add_new' => 'Open New Ticket',
            'edit_item' => 'Support Ticket',
            'update_item' => 'Update Ticket',
            'search_items' => 'Search Tickets',
            'not_found' => 'Tickets not found',
            'not_found_in_trash' => 'Tickets not found in trash',
        );

        $caps = array(
//            'read_post' => 'mkb_view_tickets', // view makes more sense than read for tickets
            // posts ?
            'create_posts'	=> 'mkb_view_tickets', // Note: create_posts does not work without edit_posts in WP, we use create_ticket as custom cap
            'edit_post'	=> 'mkb_view_ticket', // meta
            'edit_posts' => 'mkb_view_tickets',
            'edit_others_posts' => 'mkb_view_tickets', // relates to post author, not assignee
            'edit_published_posts' => 'mkb_view_tickets', // tickets are always published
            'publish_posts' => 'mkb_view_tickets', // tickets are always published
            'read_private_posts' => 'mkb_read_private_tickets', // for future use
            'edit_private_posts' => 'mkb_edit_private_tickets', // for future use
            'delete_private_posts' => 'mkb_delete_private_tickets', // for future use
            'delete_post' => 'mkb_delete_ticket', // meta
            'delete_posts' => 'mkb_delete_tickets', // move ticket to trash, managers only
            'delete_published_posts' => 'mkb_delete_tickets', // tickets are always published
            'delete_others_posts' => 'mkb_delete_tickets', // relates to post author, not assignee
        );

        $args = array(
            'description' => __( 'Tickets', 'minerva-kb' ),
            'labels' => $labels,
            'supports' => array(
                'title',
            ),
            'hierarchical' => false,
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_nav_menus' => false,
            'show_in_admin_bar' => true,
            'menu_position' => 10,
            'menu_icon' => MINERVA_KB_IMG_URL . 'minerva-icon.png',
            'can_export' => true,
            'has_archive' => false,
            'exclude_from_search' => true,
            'publicly_queryable' => true,
            'capability_type' => 'post',
            'map_meta_cap' => true,
            'rewrite' => array(
                "slug" => MKB_Options::option('ticket_slug'),
                "with_front" => true
            ),
            'show_in_rest' => false
        );

        if (!current_user_can('administrator')) {
            $args['capabilities'] = $caps;
        }

        register_post_type( 'mkb_ticket', $args );
    }

    /**
     * Registers Ticket Reply custom post type
     */
    private function register_ticket_reply_cpt() {
        /**
         * Tickets replies
         * TODO: all proper settings
         */
        $labels = array(
            'name' => 'Ticket Reply',
            'singular_name' => 'Ticket Reply',
            'menu_name' => 'Ticket Replies',
            'all_items' => 'All Ticket Replies',
            'view_item' => 'View Ticket Reply',
            'add_new_item' => 'Add new Ticket Reply',
            'add_new' => 'Add new',
            'edit_item' => 'Edit Ticket Reply',
            'update_item' => 'Update Ticket Reply',
            'search_items' => 'Search Ticket Replies',
            'not_found' => 'Ticket Replies not found',
            'not_found_in_trash' => 'Ticket Replies not found in trash',
        );

        $args = array(
            'description' => __( 'Ticket Reply', 'minerva-kb' ),
            'labels' => $labels,
            'supports' => array(
                'title',
                'editor',
                'revisions',
            ),
            'hierarchical' => false,
            'public' => false,
            'show_ui' => false,
            'show_in_menu' => false,
            'show_in_nav_menus' => false,
            'show_in_admin_bar' => false,
            'menu_position' => 10,
            'menu_icon' => 'dashicons-tickets-alt',
            'can_export' => true,
            'has_archive' => false,
            'exclude_from_search' => true,
            'publicly_queryable' => false,
            'capability_type' => 'post',
            'show_in_rest' => false
        );

        register_post_type( 'mkb_ticket_reply', $args );
    }

    /**
     * Registers tickets types custom taxonomy
     */
    private function register_ticket_type_taxonomy() {
        $args = array(
            'labels' => array(
                'name' => __( 'Ticket Types', 'minerva-kb' ),
                'add_new_item' => __( 'Add Ticket Type', 'minerva-kb' ),
                'new_item_name' => __( 'New Ticket Type', 'minerva-kb' )
            ),
            'show_ui' => true,
            'public' => false,
            'show_tagcloud' => false,
            'hierarchical' => true,
            'show_in_rest' => true,
            'meta_box_cb'  => false,
        );

        if (!current_user_can('administrator')) {
            $args['capabilities'] = array(
                'manage_terms' => 'mkb_manage_ticket_types',
                'edit_terms' => 'mkb_manage_ticket_types',
                'delete_terms' => 'mkb_manage_ticket_types',
                'assign_terms' => 'mkb_assign_ticket_types'
            );
        }

        register_taxonomy(
            'mkb_ticket_type',
            'mkb_ticket',
            $args
        );
    }

    /**
     * Registers tickets priorities custom taxonomy
     */
    private function register_ticket_priority_taxonomy() {
        $args = array(
            'labels' => array(
                'name' => __( 'Ticket Priorities', 'minerva-kb' ),
                'add_new_item' => __( 'Add Ticket Priority', 'minerva-kb' ),
                'new_item_name' => __( 'New Ticket Priority', 'minerva-kb' )
            ),
            'show_ui' => true,
            'public' => false,
            'show_tagcloud' => false,
            'hierarchical' => true,
            'show_in_rest' => true,
            'meta_box_cb'  => false,
        );

        if (!current_user_can('administrator')) {
            $args['capabilities'] = array(
                'manage_terms' => 'mkb_manage_ticket_priorities',
                'edit_terms' => 'mkb_manage_ticket_priorities',
                'delete_terms' => 'mkb_manage_ticket_priorities',
                'assign_terms' => 'mkb_assign_ticket_priorities'
            );
        }

        register_taxonomy(
            'mkb_ticket_priority',
            'mkb_ticket',
            $args
        );
    }

    /**
     * Registers tickets tag custom taxonomy
     */
    private function register_ticket_tag_taxonomy() {
        $args = array(
            'labels' => array(
                'name' => __( 'Ticket Tags', 'minerva-kb' ),
                'add_new_item' => __( 'Add Ticket Tag', 'minerva-kb' ),
                'new_item_name' => __( 'New Ticket Tag', 'minerva-kb' )
            ),
            'show_ui' => true,
            'public' => false,
            'show_tagcloud' => false,
            'hierarchical' => false,
            'show_in_rest' => true
        );

        if (!current_user_can('administrator')) {
            $args['capabilities'] = array(
                'manage_terms' => 'mkb_manage_ticket_tags',
                'edit_terms' => 'mkb_manage_ticket_tags',
                'delete_terms' => 'mkb_manage_ticket_tags',
                'assign_terms' => 'mkb_assign_ticket_tags'
            );
        }

        register_taxonomy(
            'mkb_ticket_tag',
            'mkb_ticket',
            $args
        );
    }

    /**
     * Registers tickets departments custom taxonomy
     */
    private function register_ticket_department_taxonomy() {
        $args = array(
            'labels' => array(
                'name' => __( 'Ticket Departments', 'minerva-kb' ),
                'add_new_item' => __( 'Add Ticket Department', 'minerva-kb' ),
                'new_item_name' => __( 'New Ticket Department', 'minerva-kb' )
            ),
            'show_ui' => true,
            'public' => false,
            'show_tagcloud' => false,
            'hierarchical' => true,
            'show_in_rest' => true,
            'meta_box_cb'  => false,
        );

        if (!current_user_can('administrator')) {
            $args['capabilities'] = array(
                'manage_terms' => 'mkb_manage_ticket_departments',
                'edit_terms' => 'mkb_manage_ticket_departments',
                'delete_terms' => 'mkb_manage_ticket_departments',
                'assign_terms' => 'mkb_assign_ticket_departments'
            );
        }

        register_taxonomy(
            'mkb_ticket_department',
            'mkb_ticket',
            $args
        );
    }

    /**
     * Registers tickets products custom taxonomy
     */
    private function register_ticket_product_taxonomy() {
        $args = array(
            'labels' => array(
                'name' => __( 'Ticket Products', 'minerva-kb' ),
                'add_new_item' => __( 'Add Ticket Product', 'minerva-kb' ),
                'new_item_name' => __( 'New Ticket Product', 'minerva-kb' )
            ),
            'show_ui' => true,
            'public' => false,
            'show_tagcloud' => false,
            'hierarchical' => true,
            'show_in_rest' => true,
            'meta_box_cb'  => false,
        );

        if (!current_user_can('administrator')) {
            $args['capabilities'] = array(
                'manage_terms' => 'mkb_manage_ticket_products',
                'edit_terms' => 'mkb_manage_ticket_products',
                'delete_terms' => 'mkb_manage_ticket_products',
                'assign_terms' => 'mkb_assign_ticket_products'
            );
        }

        register_taxonomy(
            'mkb_ticket_product',
            'mkb_ticket',
            $args
        );
    }

    /**
     * Registers canned response custom post type
     */
    private function register_canned_response_cpt() {
        /**
         * Canned responses
         */
        $labels = array(
            'name' => 'Canned Responses',
            'singular_name' => 'Canned Response',
            'menu_name' => 'Canned Responses',
            'all_items' => 'All Responses',
            'view_item' => 'View Canned Response',
            'add_new_item' => 'Add New Canned Response',
            'add_new' => 'Add New',
            'edit_item' => 'Edit Canned Response',
            'update_item' => 'Update Canned Response',
            'search_items' => 'Search Canned Responses',
            'not_found' => 'Canned Response not found',
            'not_found_in_trash' => 'Canned Response not found in trash',
        );

        $caps = array(
            // we do not define read_, because we don't currently use it. So 'read' is used by default
            'create_posts'	=> 'mkb_edit_canned_responses',
            'edit_post'	=> 'mkb_edit_canned_response',
            'edit_posts' => 'mkb_edit_canned_responses',
            'edit_others_posts' => 'mkb_edit_others_canned_responses',
            'edit_published_posts' => 'mkb_edit_published_canned_responses',
            'publish_posts' => 'mkb_publish_canned_responses',
            'read_private_posts' => 'mkb_read_private_canned_responses',
            'edit_private_posts' => 'mkb_edit_private_canned_responses',
            'delete_private_posts' => 'mkb_delete_private_canned_responses',
            'delete_post' => 'mkb_delete_canned_response',
            'delete_posts' => 'mkb_delete_canned_responses',
            'delete_published_posts' => 'mkb_delete_published_canned_responses',
            'delete_others_posts' => 'mkb_delete_others_canned_responses',
        );

        $args = array(
            'description' => __( 'Canned Responses', 'minerva-kb' ),
            'labels' => $labels,
            'supports' => array(
                'title',
                'editor',
                'author',
                'revisions',
            ),
            'hierarchical' => false,
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_nav_menus' => false,
            'show_in_admin_bar' => true,
            'menu_position' => 10,
            'menu_icon' => MINERVA_KB_IMG_URL . 'minerva-icon.png',
            'can_export' => true,
            'has_archive' => false,
            'exclude_from_search' => true,
            'publicly_queryable' => false,
            'capability_type' => 'post',
            'map_meta_cap' => true,
            'show_in_rest' => false
        );

        if (!current_user_can('administrator')) {
            $args['capabilities'] = $caps;
        }

        register_post_type( 'mkb_canned_response', $args );
    }

    /**
     * Canned responses categories
     */
    private function register_canned_response_category_taxonomy() {
        $args = array(
            'labels' => array(
                'name' => __( 'Categories', 'minerva-kb' ),
                'add_new_item' => __( 'Add Response Category', 'minerva-kb' ),
                'new_item_name' => __( 'New Response Category', 'minerva-kb' )
            ),
            'show_ui' => true,
            'public' => false,
            'show_tagcloud' => false,
            'hierarchical' => true,
            'show_in_rest' => true
        );

        if (!current_user_can('administrator')) {
            $args['capabilities'] = array(
                'manage_terms' => 'mkb_manage_canned_response_categories',
                'edit_terms' => 'mkb_manage_canned_response_categories',
                'delete_terms' => 'mkb_manage_canned_response_categories',
                'assign_terms' => 'mkb_assign_canned_response_categories'
            );
        }

        register_taxonomy(
            'mkb_canned_response_category',
            'mkb_canned_response',
            $args
        );
    }

	/**
	 * KB Topic edit screen settings
	 * @param $term
	 */
	public function topic_edit_screen_html($term) {

		$term_id = $term->term_id;
		$term_meta = get_option( "taxonomy_" . MKB_Options::option( 'article_cpt_category' ) . '_' . $term_id );

		$settings_helper = new MKB_SettingsBuilder(array(
			'topic' => true,
			'no_tabs' => true
		));

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
			'parent' => - 1,
			'exclude_tree' => '',
			'number' => '',
			'offset' => 0,
			'post_type' => 'page',
			'post_status' => 'publish,private,draft'
		);

		$pages = get_pages($pages_args);

		$page_options = array(
			'' => __('Please, select page', 'minerva-kb')
		);

		if ($pages) {
			$page_options = array_reduce($pages, function ($all, $page) {
				$all[ $page->ID ] = $page->post_title;
				return $all;
			}, $page_options);
		}

		$options = array(
			array(
				'id' => 'topic_color',
				'type' => 'color',
				'label' => __( 'Topic color', 'minerva-kb' ),
				'default' => '#4a90e2',
				'description' => __( 'Select a color for this topic (optional)', 'minerva-kb' )
			),
			array(
				'id' => 'topic_icon',
				'type' => 'icon_select',
				'label' => __( 'Topic icon', 'minerva-kb' ),
				'default' => 'fa-list-alt',
				'description' => __( 'Select an icon for this topic (optional)', 'minerva-kb' )
			),
			array(
				'id' => 'topic_image',
				'type' => 'media',
				'label' => __( 'Topic image', 'minerva-kb' ),
				'default' => '',
				'description' => __( 'You can use URL or select image from media library', 'minerva-kb' )
			),
			array(
				'id' => 'topic_page_switch',
				'type' => 'checkbox',
				'label' => __('Display page content instead of topic?', 'minerva-kb'),
				'default' => false,
				'description' => __('You can use page content with shortcodes to display more complex KB structures', 'minerva-kb')
			),
			array(
				'id' => 'topic_page',
				'type' => 'page_select',
				'label' => __( 'Select page to use as topic content', 'minerva-kb' ),
				'options' => $page_options,
				'default' => '',
				'description' => __('Page content will be displayed instead of this topic', 'minerva-kb')
			),
			array(
				'id' => 'topic_no_title_switch',
				'type' => 'checkbox',
				'label' => __('Hide title?', 'minerva-kb'),
				'default' => false,
				'description' => __('You can remove topic title from this topic. Useful when you add alternative heading in page content', 'minerva-kb')
			),
			array(
				'id' => 'topic_no_description_switch',
				'type' => 'checkbox',
				'label' => __('Hide description?', 'minerva-kb'),
				'default' => false,
				'description' => __('You can remove topic description from this topic', 'minerva-kb')
			),
			array(
				'id' => 'topic_no_breadcrumbs_switch',
				'type' => 'checkbox',
				'label' => __('Hide breadcrumbs?', 'minerva-kb'),
				'default' => false,
				'description' => __('You can remove breadcrumbs from this topic', 'minerva-kb')
			),
			array(
				'id' => 'topic_no_search_switch',
				'type' => 'checkbox',
				'label' => __('Hide search?', 'minerva-kb'),
				'default' => false,
				'description' => __('You can remove search from this topic', 'minerva-kb')
			),
		);

		/**
		 * Top level options
		 */
		if ($term->parent == '0') {

			$result = array( "" => __('Use default', 'minerva-kb') );

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
				'parent' => - 1,
				'exclude_tree' => '',
				'number' => '',
				'offset' => 0,
				'post_type' => 'page',
				'post_status' => 'publish'
			);

			$pages = get_pages($pages_args);

			if ($pages) {
				$result = array_reduce($pages, function ($all, $page) {
					$all[ $page->ID ] = $page->post_title;

					return $all;
				}, $result);
			}

			$top_level_options = array(
				array(
					'id' => 'topic_parent',
					'type' => 'page_select',
					'label' => __('Topic Knowledge Base Home', 'minerva-kb'),
					'options' => $result,
					'default' => '',
					'description' => __('This is optional. You can select different knowledge base root page for each topic (this affects KB Home link in breadcrumbs)', 'minerva-kb')
				),
				array(
					'id' => 'topic_product_switch',
					'type' => 'checkbox',
					'label' => __('Turn this topic into a product root?', 'minerva-kb'),
					'default' => false,
					'description' => __('If you make this topic a product root, all nested KB elements (like search and widgets) will be scoped to this topic and its children', 'minerva-kb')
				),
				array(
					'id' => 'topic_sidebar_switch',
					'type' => 'checkbox',
					'label' => __('Customize sidebar display for this topic?', 'minerva-kb'),
					'default' => false,
					'description' => __('When you use page content it may be helpful to remove sidebar or change it\'s position', 'minerva-kb')
				),
				array(
					'id' => 'topic_sidebar',
					'type' => 'image_select',
					'label' => __('Topic sidebar position', 'minerva-kb'),
					'options' => array(
						'none' => array(
							'label' => __('None', 'minerva-kb'),
							'img' => MINERVA_KB_IMG_URL . 'no-sidebar.png'
						),
						'left' => array(
							'label' => __('Left', 'minerva-kb'),
							'img' => MINERVA_KB_IMG_URL . 'left-sidebar.png'
						),
						'right' => array(
							'label' => __('Right', 'minerva-kb'),
							'img' => MINERVA_KB_IMG_URL . 'right-sidebar.png'
						),
					),
					'default' => 'right',
					'description' => __('You can add widgets to sidebars under Appearance - Widgets', 'minerva-kb')
				)
			);

			$options = array_merge($options, $top_level_options);
		}

		/**
		 * Restriction
		 */
		if (MKB_Options::option('restrict_on')):

			$restrict_options = array(
				array(
					'id' => 'topic_restrict_role',
					'type' => 'roles_select',
					'label' => __( 'Content restriction: who can view topic?', 'minerva-kb' ),
					'default' => 'none',
					'description' => __('You can restrict access not only for specific articles, but also to topics.', 'minerva-kb')
				),
			);

			$options = array_merge($options, $restrict_options);

		endif;

		?>

		</tbody>
		<tbody class="mkb-term-settings">

		<?php

		foreach ( $options as $option ):

			?>

			<tr class="form-field">
				<th scope="row" valign="top">
					<label for="term_meta[<?php echo esc_attr($option["id"]); ?>]">
						<?php echo esc_html($option["label"]); ?></label>
				</th>
				<td>

					<?php

					$value = isset( $term_meta[$option["id"]] ) ? stripslashes($term_meta[$option["id"]]) : $option['default'];

					$settings_helper->render_option(
						$option["type"],
						$value,
						$option
					);

					?>

					<p class="description"><?php echo esc_html($option["description"]); ?></p>

				</td>
			</tr>

		<?php

		endforeach;

		?>

		<!-- WPML controls box fix begin -->
		<tr class="form-field">
			<th scope="row" valign="top"></th>
			<td></td>
		</tr>
		<!-- WPML controls box fix end -->

		</tbody>
	<?php
	}

	/**
	 * Handle topic settings save
	 * @param $term_id
	 */
	public function save_topic_meta( $term_id ) {
		if ( isset( $_POST['term_meta'] ) ) {

			$term_meta = get_option( "taxonomy_" . MKB_Options::option( 'article_cpt_category' ) . '_' . $term_id );
			$cat_keys = array_keys( $_POST['term_meta'] );

			foreach ( $cat_keys as $key ) {
				if ( isset ( $_POST['term_meta'][$key] ) ) {
					$term_meta[$key] = $_POST['term_meta'][$key];
				}
			}

			$checkboxes = array(
				'topic_page_switch',
				'topic_sidebar_switch',
				'topic_product_switch',
				'topic_no_title_switch',
				'topic_no_description_switch',
				'topic_no_breadcrumbs_switch',
				'topic_no_search_switch',
			);

			foreach($checkboxes as $cb) {
				if (in_array($cb, $cat_keys)) {
					continue;
				}

				$term_meta[ $cb ] = 'off';
			}

			update_option( "taxonomy_" . MKB_Options::option( 'article_cpt_category' ) . '_' . $term_id, $term_meta );
		}

		$this->restrict->invalidate_restriction_cache();
	}

	/**
	 * Handle topic settings delete
	 * @param $term_id
	 */
	public function delete_topic_meta( $term_id ) {
		delete_option( "taxonomy_" . MKB_Options::option( 'article_cpt_category' ) . '_' . $term_id );

        $query_args = array(
            'post_type' => MKB_Options::option('article_cpt'),
            'posts_per_page' => -1,
            'ignore_sticky_posts' => 1,
            'meta_query' => array(
                array('key' => 'mkb_tax_order_' . $term_id, 'compare' => 'EXISTS')
            )
        );

        $loop = new WP_Query($query_args);

        if ($loop->have_posts()):
            while ( $loop->have_posts() ) : $loop->the_post();
                delete_post_meta(get_the_ID(), 'mkb_tax_order_' . $term_id);
            endwhile;
        endif;

        wp_reset_postdata();
	}

    /**
     * Handle FAQ category settings delete
     * @param $term_id
     */
    public function delete_faq_category_meta( $term_id ) {
        $query_args = array(
            'post_type' => 'mkb_faq',
            'posts_per_page' => -1,
            'ignore_sticky_posts' => 1,
            'meta_query' => array(
                array('key' => 'mkb_tax_order_' . $term_id, 'compare' => 'EXISTS')
            )
        );

        $loop = new WP_Query($query_args);

        if ($loop->have_posts()):
            while ( $loop->have_posts() ) : $loop->the_post();
                delete_post_meta(get_the_ID(), 'mkb_tax_order_' . $term_id);
            endwhile;
        endif;

        wp_reset_postdata();
    }

	/**
	 * Custom DnD articles order for admin screens
	 * @param $wp_query
	 */
	public function admin_custom_articles_order($wp_query) {
		if (!$this->info->is_admin() ||  // only for admin screen
		    !MKB_Options::option( 'enable_articles_reorder' ) || // only if reorder enabled
		    !isset($_GET[MKB_Options::option( 'article_cpt_category' )]) // only for articles list on topic edit screen
		) {
			return;
		}

		// NOTE: we cannot use $info->is_topic() here, since wp_query is not yet ready
		if ( isset( $wp_query->query['post_type'] ) &&
		     ! isset( $_GET['orderby'] ) &&
		     $wp_query->query['post_type'] === MKB_Options::option( 'article_cpt' ) ) {

            $tax_obj = $wp_query->get_queried_object();

            if ($tax_obj) { // can be NULL
                $wp_query->set('meta_query', array(
                    'relation' => 'OR',
                    array('key' => 'mkb_tax_order_' . $tax_obj->term_id, 'compare' => 'EXISTS'),
                    array('key' => 'mkb_tax_order_' . $tax_obj->term_id, 'compare' => 'NOT EXISTS'),
                ));
                $wp_query->set('orderby', 'meta_value_num menu_order');
                $wp_query->set('order', 'ASC');
            }
		}
	}

	/**
	 * Client side articles custom order
	 * @param $wp_query
	 */
	public function custom_articles_order($wp_query) {
		if ($this->info->is_admin()) {
			return;
		}

		// NOTE: we cannot use $info->is_topic() here, since wp_query is not yet ready
		if ( isset( $wp_query->tax_query ) &&
		     isset( $wp_query->tax_query->queries ) &&
		     ! empty( $wp_query->tax_query->queries ) &&
		     ! isset( $_GET['orderby'] )
		) {

			foreach ( $wp_query->tax_query->queries as $tax_query ) {
				if ( isset( $tax_query['taxonomy'] ) && $tax_query['taxonomy'] === MKB_Options::option( 'article_cpt_category' ) ) {

					if (MKB_Options::option( 'enable_articles_reorder' )) {
                        $tax_obj = $wp_query->get_queried_object();

                        if (!isset($tax_obj)) {
                            continue;
                        }

                        // NOTE: using meta_key breaks queries if no meta is found (empty results). Need to use query
                        $wp_query->set('meta_query', array(
                            'relation' => 'OR',
                            array('key' => 'mkb_tax_order_' . $tax_obj->term_id, 'compare' => 'EXISTS'),
                            array('key' => 'mkb_tax_order_' . $tax_obj->term_id, 'compare' => 'NOT EXISTS'),
                        ));
                        $wp_query->set('orderby', 'meta_value_num menu_order');
                        $wp_query->set('order', 'ASC');
					} else {
						$wp_query->set('orderby', MKB_Options::option('articles_orderby'));
						$wp_query->set('order', MKB_Options::option('articles_order'));
					}

					break;
				}
			}
		}
	}

	/**
	 * Admin articles list custom columns
	 */
	public function set_custom_edit_kb_columns($columns) {

		unset($columns['author']);
		unset($columns['date']);
		unset($columns['comments']);

		$columns['mkb_topics'] = __( 'Topics', 'minerva-kb' );
		$columns['mkb_tags'] = __( 'Tags', 'minerva-kb' );
		$columns['mkb_views'] = __( '<i class="fa fa-eye" title="Views"></i> <span class="mkb-hidden-column-title">Views</span>', 'minerva-kb' );
		$columns['mkb_likes'] = __( '<i class="fa fa-thumbs-o-up" title="Likes"></i> <span class="mkb-hidden-column-title">Likes</span>', 'minerva-kb' );
		$columns['mkb_dislikes'] = __( '<i class="fa fa-thumbs-o-down" title="Dislikes"></i> <span class="mkb-hidden-column-title">Dislikes</span>', 'minerva-kb' );
		$columns['mkb_feedback'] = __( '<i class="fa fa-bullhorn" title="Feedback"></i> <span class="mkb-hidden-column-title">Feedback</span>', 'minerva-kb' );

		$columns['author'] = __( 'Author', 'minerva-kb' );
		$columns['date'] = __( 'Date', 'minerva-kb' );
		$columns['comments'] = '<span class="vers comment-grey-bubble" title="Comments"><span class="screen-reader-text">Comments</span></span>';

		return $columns;
	}

	public function custom_kb_column( $column, $post_id ) {
		switch ( $column ) {

			case 'mkb_views':
				$views = get_post_meta($post_id, '_mkb_views', true);
				echo esc_html($views > 0 ? $views : 0);
				break;

			case 'mkb_likes':
				$likes = get_post_meta($post_id, '_mkb_likes', true);
				echo esc_html($likes > 0 ? $likes : 0);
				break;

			case 'mkb_dislikes':
				$dislikes = get_post_meta($post_id, '_mkb_dislikes', true);
				echo esc_html($dislikes > 0 ? $dislikes : 0);
				break;

			case 'mkb_topics':
				echo get_the_term_list( $post_id, MKB_Options::option('article_cpt_category'), '', ', ' );
				break;

			case 'mkb_tags':
				echo get_the_term_list( $post_id, MKB_Options::option('article_cpt_tag'), '', ', ' );
				break;

			case 'mkb_feedback':

				$feedback_args = array(
					'posts_per_page'   => - 1,
					'offset'           => 0,
					'category'         => '',
					'category_name'    => '',
					'orderby'          => 'DATE',
					'order'            => 'DESC',
					'include'          => '',
					'exclude'          => '',
					'meta_key'         => 'feedback_article_id',
					'meta_value'       => get_the_ID(),
					'post_type'        => 'mkb_feedback',
					'post_mime_type'   => '',
					'post_parent'      => '',
					'author'           => '',
					'author_name'      => '',
					'post_status'      => 'publish'
				);

				$feedback = get_posts( $feedback_args );

				echo esc_html(count($feedback));

				break;

			default:
				break;
		}
	}

	/**
	 * Make custom columns sortable
	 */
	public function sortable_kb_column( $columns ) {
		$columns['mkb_views'] = 'mkb_views';
		$columns['mkb_likes'] = 'mkb_likes';
		$columns['mkb_dislikes'] = 'mkb_dislikes';

		return $columns;
	}

	/**
	 * Order by custom columns
	 */
	public function kb_list_orderby( $query ) {
		if( !$this->info->is_admin() )
			return;

		$orderby = $query->get( 'orderby');

		if ('mkb_views' == $orderby) {
			$query->set('orderby','meta_value_num title');
			$query->set('meta_query', array(
				'relation' => 'OR',
				array('key' => '_mkb_views', 'compare' => 'EXISTS'),
				array('key' => '_mkb_views', 'compare' => 'NOT EXISTS')
			));
		} else if ('mkb_likes' == $orderby) {
			$query->set('orderby','meta_value_num title');
			$query->set('meta_query', array(
				'relation' => 'OR',
				array('key' => '_mkb_likes', 'compare' => 'EXISTS'),
				array('key' => '_mkb_likes', 'compare' => 'NOT EXISTS')
			));
		} else if ('mkb_dislikes' == $orderby) {
			$query->set('orderby','meta_value_num title');
			$query->set('meta_query', array(
				'relation' => 'OR',
				array('key' => '_mkb_dislikes', 'compare' => 'EXISTS'),
				array('key' => '_mkb_dislikes', 'compare' => 'NOT EXISTS')
			));
		}
	}

    /**
     * Ticket columns
     * @param $columns
     * @return mixed
     */
    public function set_custom_edit_ticket_columns($columns) {

        unset($columns['title']);
        unset($columns['author']);
        unset($columns['date']);
        unset($columns['comments']);

        $columns['mkb_ticket_status'] = __( 'Status', 'minerva-kb' );
        $columns['title'] = __( 'Title', 'minerva-kb' );
        $columns['mkb_ticket_priority'] = __( 'Priority', 'minerva-kb' );
        $columns['mkb_ticket_assignee'] = __( 'Assigned to', 'minerva-kb' );
        $columns['mkb_ticket_product'] = __( 'Product', 'minerva-kb' );
        $columns['mkb_ticket_id'] = __( 'Ticket ID', 'minerva-kb' );
        $columns['mkb_ticket_type'] = __( 'Type', 'minerva-kb' );
        $columns['mkb_ticket_author'] = __( 'Opened by', 'minerva-kb');
        $columns['date'] = __( 'Date', 'minerva-kb' );
        $columns['mkb_activity'] = __( 'Activity', 'minerva-kb' );

        return $columns;
    }

    public function custom_ticket_column( $column, $ticket_id ) {
        $ticket = get_post($ticket_id);
        $author = $ticket->post_author ? get_user_by('ID', $ticket->post_author) : null;
        $ticket_status = MKB_Tickets::get_ticket_status($ticket_id);
        $type = wp_get_post_terms( $ticket_id, array( 'mkb_ticket_type' ) );
        $priority = wp_get_post_terms( $ticket_id, array( 'mkb_ticket_priority' ) );
        $product = wp_get_post_terms( $ticket_id, array( 'mkb_ticket_product' ) );
        $ticket_assignee_id = get_post_meta($ticket_id, '_mkb_ticket_assignee', true);
        $assigned_user = get_user_by('ID', $ticket_assignee_id);
        $is_guest = MKB_Tickets::is_guest_ticket($ticket_id);
        $is_awaiting_agent_reply = MKB_Tickets::is_ticket_awaiting_agent_reply($ticket_id) &&
            $ticket_status['id'] !== MKB_Tickets::TICKET_STATUS_CLOSED;
        $replies = get_posts(array(
            'post_type' => 'mkb_ticket_reply',
            'posts_per_page' => -1,
            'ignore_sticky_posts' => 1,
            'post_parent' => $ticket_id,
            'post_status' => array('publish', 'trash')
        ));
        $replies_count = sizeof($replies);

        switch ( $column ) {
            case 'mkb_ticket_status':
                if ($is_awaiting_agent_reply) {

                    $now = time(); // always in GMT

                    if (sizeof($replies)) {
                        $latest_reply = $replies[0];
                        $reply_timestamp_gmt = get_post_time('U', true, $latest_reply->ID);

                        $elapsed_time_in_seconds = $now - $reply_timestamp_gmt;
                    } else {
                        $ticket_timestamp_gmt = get_post_time('U', true, $ticket_id);

                        $elapsed_time_in_seconds = $now - $ticket_timestamp_gmt;
                    }

                    $elapsed_hours = $elapsed_time_in_seconds / (60 * 60);
                    $freshness_label_color = '#2cbe4e';

                    if ($elapsed_hours > 24) {
                        $freshness_label_color = '#FF8C23';
                    }

                    if ($elapsed_hours > 48) {
                        $freshness_label_color = '#EB2F2D';
                    }

                    // TODO: use latest agent reply time instead, bc customer might submit several replies

                    ?><span class="mkb-ticket-freshness-badge" style="background-color: <?php echo esc_attr($freshness_label_color); ?>;"></span><?php
                }

                ?><span class="mkb-ticket-title-status-badge status--<?php esc_attr_e($ticket_status['id']); ?>">
                    <i class="fa <?php esc_attr_e($ticket_status['icon']); ?>"></i> <?php esc_attr_e($ticket_status['label']); ?>
                </span><?php
                break;

            case 'mkb_ticket_assignee':
                if ($assigned_user):
                    // TODO: it's better to display all tickets assigned to user, instead of profile
                    if ( get_current_user_id() == $ticket_assignee_id )
                        $edit_link = get_edit_profile_url( $ticket_assignee_id );
                    else
                        $edit_link = add_query_arg( 'user_id', $ticket_assignee_id, self_admin_url( 'user-edit.php'));
                    ?><a href="<?php esc_attr_e($edit_link); ?>"><?php esc_html_e($assigned_user->display_name); ?></a><?php
                else:
                    ?><span class="mkb-no-value"><?php _e('Unassigned', 'minerva-kb'); ?></span><?php
                endif;
                break;

            case 'mkb_ticket_type':
                if ($type):
                    $type = $type[0];
                    $color = MKB_TemplateHelper::get_taxonomy_option($type, 'mkb_ticket_type', 'color');
                    ?><span class="mkb-ticket-type-badge mkb-ticket-type-badge--<?php esc_attr_e($type->slug); ?>" style="background: <?php esc_attr_e($color); ?>;">
                        <?php esc_html_e($type->name); ?>
                    </span><?php
                endif;
                break;

            case 'mkb_ticket_priority':
                if ($priority):
                    $priority = $priority[0];
                    $color = MKB_TemplateHelper::get_taxonomy_option($priority, 'mkb_ticket_priority', 'color');
                    ?><span class="mkb-ticket-priority-badge mkb-ticket-priority-badge--<?php esc_attr_e($priority->slug); ?>" style="background: <?php esc_attr_e($color); ?>;">
                    <?php esc_html_e($priority->name); ?>
                    </span><?php
                else:
                    ?><span class="mkb-no-value"><?php _e('No priority', 'minerva-kb'); ?></span><?php
                endif;
                break;

            case 'mkb_ticket_product':
                if ($product):
                    $product = $product[0];
                    esc_html_e($product->name);
                else:
                    ?><span class="mkb-no-value"><?php _e('No product', 'minerva-kb'); ?></span><?php
                endif;
                break;

            case 'mkb_ticket_author':
                if ($is_guest) {
                    $opener_firstname = get_post_meta($ticket_id, '_mkb_guest_ticket_firstname', true);
                    $opener_lastname = get_post_meta($ticket_id, '_mkb_guest_ticket_lastname', true);

                    $label = $opener_firstname || $opener_lastname ?
                        implode(' ', array($opener_firstname, $opener_lastname)) . ' ' . __('(Guest)', 'minerva-kb') :
                        __('Guest', 'minerva-kb');

                    echo esc_html($label);
                } else {
                    ?><a href="<?php esc_attr_e(admin_url('edit.php?post_type=mkb_ticket&author=' . $author->ID)); ?>">
                    <?php esc_html_e(isset($author->display_name) ? $author->display_name : $author->user_login); ?>
                    </a><?php ;
                }
                break;

            case 'mkb_ticket_id':
                echo $ticket_id;
                break;

            case 'mkb_activity':
                ?><strong><?php esc_html_e($replies_count); ?></strong> <?php
                    echo $replies_count === 1 ? __('reply', 'minerva-kb') : __('replies', 'minerva-kb');

                    if ($is_awaiting_agent_reply) {
                        ?>, <?php _e('awaiting agent reply', 'minerva-kb');
                    }

                    if (MKB_Tickets::has_ticket_credentials($ticket_id)) {
                        ?>, <?php _e('credentials provided by customer', 'minerva-kb');
                    }
                break;

            default:
                break;
        }
    }

    public function remove_ticket_bulk_actions( $actions, $post ){
        if ($post->post_type=='mkb_ticket') {
            unset( $actions['inline hide-if-no-js'] );
        }

        return $actions;
    }

    /**
     * Tickets admin list filters
     * @param $wp_query
     */
    public function admin_tickets_query_filters($wp_query) {
        if (!$this->info->is_admin()) {
            return;
        }

        // NOTE: we cannot use $info
        if (!isset($wp_query->query['post_type']) || $wp_query->query['post_type'] !== 'mkb_ticket' || isset($wp_query->query['meta_query'])) {
            return;
        }

        // process tickets list
        $user = wp_get_current_user();

        // check if user can view all tickets
        if (current_user_can('mkb_view_others_tickets') || current_user_can('administrator')) {
            // 1. user an see all tickets, just return
            return;
        } else if (current_user_can('mkb_view_unassigned_tickets')) {
            // 2. user can see unassigned tickets and assigned to self
            $wp_query->set('meta_query', array(
                'relation' => 'OR',
                array('key' => '_mkb_ticket_assignee', 'compare' => 'NOT EXISTS'),
                array(
                    'relation' => 'AND',
                    array('key' => '_mkb_ticket_assignee', 'compare' => 'EXISTS'),
                    array('key' => '_mkb_ticket_assignee', 'value'   => $user->ID, 'compare' => '='),
                )
            ));
        } else {
            // 3. user can only see tickets assigned to self
            $wp_query->set('meta_query', array(
                'relation' => 'AND',
                array('key' => '_mkb_ticket_assignee', 'compare' => 'EXISTS'),
                array('key' => '_mkb_ticket_assignee', 'value'   => $user->ID, 'compare' => '='),
            ));
        }
    }
	
	public function article_list_topic_filter($post_type){
		if (MKB_Options::option('article_cpt') !== $post_type){
			return; //check to make sure this is articles
		}

		$taxonomy_slug = MKB_Options::option('article_cpt_category');
		$taxonomy = get_taxonomy($taxonomy_slug);

		$selected = '';
		$request_attr = 'kbtopic_id'; //this will show up in the url

		if ( isset($_REQUEST[$request_attr] ) ) {
			$selected = $_REQUEST[$request_attr]; //in case the current page is already filtered
		}

		wp_dropdown_categories(array(
			'show_option_all' =>  __("Show All {$taxonomy->label}"),
			'taxonomy'        =>  $taxonomy_slug,
			'name'            =>  $request_attr,
			'orderby'         =>  'name',
			'selected'        =>  $selected,
			'hierarchical'    =>  true,
			'depth'           =>  3,
			'show_count'      =>  true, // Show number of post in parent term
			'hide_empty'      =>  false, // Don't show posts w/o terms
		));
	}

	public function filter_request_query_topic($query){
		//modify the query only if it is admin and main query.
		if ( !(is_admin() AND $query->is_main_query()) ){
			return $query;
		}

		//we want to modify the query for the targeted custom post.
		if ( !isset($query->query['post_type']) || MKB_Options::option('article_cpt') !== $query->query['post_type'] ){
			return $query;
		}

		//type filter
		if ( isset($_REQUEST['kbtopic_id']) &&  0 != $_REQUEST['kbtopic_id']){
			$term =  $_REQUEST['kbtopic_id'];
			$taxonomy_slug = MKB_Options::option('article_cpt_category');
			$query->query_vars['tax_query'] = array(
				array(
					'taxonomy'  => $taxonomy_slug,
					'field'     => 'ID',
					'terms'     => array($term)
				)
			);
		}

		return $query;
	}	
	
	public function article_list_tag_filter($post_type){
		if (MKB_Options::option('article_cpt') !== $post_type){
			return; //check to make sure this is articles
		}

		$taxonomy_slug = MKB_Options::option('article_cpt_tag');
		$taxonomy = get_taxonomy($taxonomy_slug);

		$selected = '';
		$request_attr = 'kbtag_id'; //this will show up in the url
		if ( isset($_REQUEST[$request_attr] ) ) {
			$selected = $_REQUEST[$request_attr]; //in case the current page is already filtered
		}
		wp_dropdown_categories(array(
			'show_option_all' =>  __("Show All {$taxonomy->label}"),
			'taxonomy'        =>  $taxonomy_slug,
			'name'            =>  $request_attr,
			'orderby'         =>  'name',
			'selected'        =>  $selected,
			'hierarchical'    =>  true,
			'depth'           =>  3,
			'show_count'      =>  true, // Show number of post in parent term
			'hide_empty'      =>  false, // Don't show posts w/o terms
		));
	}

	public function filter_request_query_tag($query){
		//modify the query only if it is admin and main query.
		if ( !(is_admin() AND $query->is_main_query()) ){
			return $query;
		}

		//we want to modify the query for the targeted custom post.
		if ( !isset($query->query['post_type']) || MKB_Options::option('article_cpt') !== $query->query['post_type'] ){
			return $query;
		}

		//type filter
		if ( isset($_REQUEST['kbtag_id']) &&  0 != $_REQUEST['kbtag_id']){
			$term =  $_REQUEST['kbtag_id'];
			$taxonomy_slug = MKB_Options::option('article_cpt_tag');
			$query->query_vars['tax_query'] = array(
				array(
					'taxonomy'  => $taxonomy_slug,
					'field'     => 'ID',
					'terms'     => array($term)
				)
			);
		}

		return $query;
	}

    /**
     * @param $post_id
     */
	public function handle_post_delete($post_id) {
        $deleted_post_type = get_post_type($post_id);

        // currently, only for tickets
        if ($deleted_post_type !== MKB_Tickets::TICKET_POST_TYPE) {
            return;
        }

        MKB_History::delete_history_for_ticket_id($post_id);

        $deleted_ticket_replies = get_posts(array(
            'post_type' => 'mkb_ticket_reply',
            'posts_per_page' => -1,
            'ignore_sticky_posts' => 1,
            'post_parent' => $post_id
        ));

        if (sizeof($deleted_ticket_replies)) {
            // delete all replies
            foreach($deleted_ticket_replies as $reply) {

                // delete all reply attachments
                $attachments = get_attached_media( '', $reply->ID);

                foreach ($attachments as $attachment) {
                    wp_delete_attachment($attachment->ID, 'true');
                }

                wp_delete_post($reply->ID, true);
            }
        }

        // delete all ticket attachments
        $attachments = get_attached_media( '', $post_id);

        foreach ($attachments as $attachment) {
            wp_delete_attachment($attachment->ID, 'true');
        }

        // remove empty ticket attachments directory
        $attachments_dir = MKB_Attachments::get_folder_path('ticket' . $post_id);

        if (is_dir($attachments_dir)) {
            rmdir($attachments_dir); // removes directory only when empty
        }
    }

    public function articles_block_editor_filter( $use_block_editor, $post_type ) {
        if ( MKB_Options::option('article_cpt' ) === $post_type && MKB_Options::option('article_disable_block_editor')) {
            return false;
        }

        return $use_block_editor;
    }

    public function faq_block_editor_filter( $use_block_editor, $post_type ) {
        if ( 'mkb_faq' === $post_type && MKB_Options::option('faq_disable_block_editor')) {
            return false;
        }

        return $use_block_editor;
    }
}
