<?php
/**
 * Project: MinervaKB.
 * Copyright: 2015-2017 @KonstruktStudio
 */

require_once(MINERVA_KB_PLUGIN_DIR . 'lib/vendor/Mobile_Detect.php');

/**
 * Class MinervaKB_Info
 * Holds and caches all needed info for currently rendered entity
 */
class MinervaKB_Info {

	// const
	const ENT_OTHER = -1;

	const ENT_BLOG = 0;
	const ENT_POST = 1;

	const ENT_KB_ARCHIVE = 3;
	const ENT_KB_TOPIC = 4;
	const ENT_KB_TAG = 5;
	const ENT_KB_ARTICLE = 6;

	const ENT_SEARCH = 7;

	private $rendered_entity;

	private $is_ajax;

	private $is_tag;

	private $is_topic;

	private $is_version_tag;

	private $is_article_archive;

	private $is_archive;

	private $is_single;

	private $is_ticket;

	private $is_support_client;

	private $is_post;

	private $is_page;

	private $is_search;

	private $is_home;
	
	private $is_builder_home;
	
	private $is_settings_home;

	private $is_minerva_page_template;

	private $is_block_editor_ready;

	private $is_kb_page;

	private $is_support_account_page;

	private $is_create_ticket_page;

	private $is_blog;

	private $is_blog_page;

	private $is_admin;

	private $is_client;
	
	private $is_desktop;
	
	private $is_tablet;
	
	private $is_mobile;

	private $is_demo_imported;

	private $is_demo_skipped;

	private $is_wpml;

	private $is_woocommerce_active;

	private $is_rtl;

	private $current_product;

	private $product_topics;

    private $user_tickets_count_info;

	private $user_replies_count_info;

	private $user_replies_count_for_ticket_info;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->get_initial_info();
	}

	/**
	 * Gets all non-lazy properties
	 */
	private function get_initial_info() {
		$this->get_device_info();
	}

	/**
	 * Gets device info
	 */
	private function get_device_info() {
		$detect = new KST_Mobile_Detect();

		$this->is_desktop = false;
		$this->is_tablet = false;
		$this->is_mobile = false;

		if ( $detect->isTablet() ) {
			$this->is_tablet = true;
		} else if($detect->isMobile() ) {
			$this->is_mobile = true;
		} else {
			$this->is_desktop = true;
		}

		$detect = null;
	}

	public function is_ajax() {
		if (isset($this->is_ajax)) {
			return $this->is_ajax;
		}

		$this->is_ajax = (defined('DOING_AJAX') && DOING_AJAX);

		return $this->is_ajax;
	}

	/**
	 * Detects KB home page built with plugin settings
	 * @return bool
	 */
	public function is_single() {
		if (isset($this->is_single)) {
			return $this->is_single;
		}

		global $post;

		$this->is_single = is_single() && $post->post_type == MKB_Options::option( 'article_cpt' );

		return $this->is_single;
	}

    /**
     * Synonym for is_single
     * @return bool
     */
	public function is_article() {
	    return $this->is_single();
    }

	/**
	 * Detects any KB archive page
	 * @return bool
	 */
	public function is_archive() {
		if (isset($this->is_archive)) {
			return $this->is_archive;
		}

		$this->is_archive = $this->is_topic() || $this->is_article_archive() || $this->is_tag() || $this->is_version_tag();

		return $this->is_archive;
	}

	/**
	 * Detects topic loop
	 * @return bool
	 */
	public function is_topic() {
		if (isset($this->is_topic)) {
			return $this->is_topic;
		}

		$this->is_topic = is_tax( MKB_Options::option( 'article_cpt_category' ) );

		return $this->is_topic;
	}

	/**
	 * Detects article archive loop
	 * @return bool
	 */
	public function is_article_archive() {
		if (isset($this->is_article_archive)) {
			return $this->is_article_archive;
		}

		$this->is_article_archive = is_post_type_archive( MKB_Options::option( 'article_cpt' ) );

		return $this->is_article_archive;
	}

	/**
	 * Detects tag loop
	 * @return bool
	 */
	public function is_tag() {
		if (isset($this->is_tag)) {
			return $this->is_tag;
		}

		$this->is_tag = is_tax( MKB_Options::option( 'article_cpt_tag' ));

		return $this->is_tag;
	}

	/**
	 * Detects version tag loop
	 * @return bool
	 */
	public function is_version_tag() {
		if (isset($this->is_version_tag)) {
			return $this->is_version_tag;
		}

		$this->is_version_tag = is_tax( 'mkb_version' );

		return $this->is_version_tag;
	}

	/**
	 * Detects search results loop
	 * @return bool
	 */
	public function is_search() {
		if (isset($this->is_search)) {
			return $this->is_search;
		}

		global $wp_query;

		$this->is_search = $wp_query->is_search;

		return $this->is_search;
	}

	/**
	 * Detects any KB home page
	 * @return mixed
	 */
	public function is_home() {
		if (isset($this->is_home)) {
			return $this->is_home;
		}

		$this->is_home = $this->is_settings_home() || $this->is_builder_home();

		return $this->is_home;
	}

	/**
	 * Detects KB home page built with page builder
	 * @return bool
	 */
	public function is_builder_home() {
		if (isset($this->is_builder_home)) {
			return $this->is_builder_home;
		}

		$this->is_builder_home = get_post_type() === 'page' && MKB_PageOptions::is_builder_page();

		return $this->is_builder_home;
	}

	/**
	 * Detects KB home page built with plugin settings
	 * @return bool
	 */
	public function is_settings_home() {
		if (isset($this->is_settings_home)) {
			return $this->is_settings_home;
		}

		global $post;

		$this->is_settings_home = get_post_type() === 'page' &&
		       MKB_Options::option( 'kb_page' ) &&
		       $post->ID === (int) MKB_Options::option( 'kb_page' );

		return $this->is_settings_home;
	}

	/**
	 * Detects KB home page built with plugin settings
	 * @return bool
	 */
	public function is_minerva_page_template() {
		if (isset($this->is_minerva_page_template)) {
			return $this->is_minerva_page_template;
		}

		$this->is_minerva_page_template = MinervaKB_PageTemplates::is_minerva_page_template();

		return $this->is_minerva_page_template;
	}

	/**
	 * Detects is WP5 block editor ready
	 * @return bool
	 */
	public function is_block_editor_ready() {
		if (isset($this->is_block_editor_ready)) {
			return $this->is_block_editor_ready;
		}

		$this->is_block_editor_ready = version_compare(
		    floatval(get_bloginfo( 'version')),
            '5.0',
            '>='
        );

		return $this->is_block_editor_ready;
	}

	/**
	 * Checks if we're on any KB-related page
	 * @return mixed
	 */
	public function is_kb_page() {
		if (isset($this->is_kb_page)) {
			return $this->is_kb_page;
		}

		$this->is_kb_page = $this->is_archive() || $this->is_single() || $this->is_home() || $this->is_search();

		return $this->is_kb_page;
	}

    /**
     * Checks if we're on support account
     * @return mixed
     */
    public function is_support_account_page() {
        if (isset($this->is_support_account_page)) {
            return $this->is_support_account_page;
        }

        $support_account_page = MKB_Options::option('tickets_support_account_page');

        $this->is_support_account_page = is_page() && $support_account_page && get_the_ID() === (int)$support_account_page;

        return $this->is_support_account_page;
    }

    /**
     * Checks if we're on create ticket page
     * @return mixed
     */
    public function is_create_ticket_page() {
        if (isset($this->is_create_ticket_page)) {
            return $this->is_create_ticket_page;
        }

        $create_ticket_page = MKB_Options::option('tickets_create_page');

        $this->is_create_ticket_page = is_page() && $create_ticket_page && get_the_ID() === (int)$create_ticket_page;

        return $this->is_create_ticket_page;
    }

    /**
     * Detects ticket single page
     * @return bool
     */
    public function is_ticket() {
        if (isset($this->is_ticket)) {
            return $this->is_ticket;
        }

        global $post;

        $this->is_ticket = is_single() && $post->post_type == 'mkb_ticket';

        return $this->is_ticket;
    }

    /**
     * Detects support client user
     * @return bool
     */
    public function is_support_client() {
        if (isset($this->is_support_client)) {
            return $this->is_support_client;
        }

        $user = wp_get_current_user();

        $this->is_support_client = false;

        if ( isset( $user->roles ) && is_array( $user->roles ) ) {
            if (in_array( 'support-client', $user->roles )) {
                $this->is_support_client = true;
            }
        }

        return $this->is_support_client;
    }

	/**
	 * Detects post
	 * @return bool
	 */
	public function is_post() {
		if (isset($this->is_post)) {
			return $this->is_post;
		}

		$this->is_post = 'post' === get_post_type() && is_singular();

		return $this->is_post;
	}

    /**
     * Detects page
     * @return bool
     */
    public function is_page() {
        if (isset($this->is_page)) {
            return $this->is_page;
        }

        $this->is_page = is_page();

        return $this->is_page;
    }

	/**
	 * Detects posts archive
	 * @return bool
	 */
	public function is_blog() {
		if (isset($this->is_blog)) {
			return $this->is_blog;
		}

		$this->is_blog = 'post' === get_post_type() && !is_singular();

		return $this->is_blog;
	}

	/**
	 * Checks if we're on any blog-related page
	 * @return mixed
	 */
	public function is_blog_page() {
		if (isset($this->is_blog_page)) {
			return $this->is_blog_page;
		}

		$this->is_blog_page = ( is_author() || is_category() || is_tag() || is_date() || is_home() || is_single() ) &&
		                      'post' == get_post_type();

		return $this->is_blog_page;
	}

	/**
	 * Detects currently rendered entity
	 * @return mixed
	 */
	public function rendered_entity() {
		if (isset($this->rendered_entity)) {
			return $this->rendered_entity;
		}

		if (is_search()) {
			$this->rendered_entity = self::ENT_SEARCH;
		} else if ($this->is_post()) {
			$this->rendered_entity = self::ENT_POST;
		} else if ($this->is_blog()) {
			$this->rendered_entity = self::ENT_BLOG;
		} else if ($this->is_single()) {
			$this->rendered_entity = self::ENT_KB_ARTICLE;
		} else if ($this->is_topic()) {
			$this->rendered_entity = self::ENT_KB_TOPIC;
		} else if ($this->is_tag()) {
			$this->rendered_entity = self::ENT_KB_TAG;
		} else if ($this->is_article_archive()) {
			$this->rendered_entity = self::ENT_KB_ARCHIVE;
		} else {
			$this->rendered_entity = self::ENT_OTHER;
		}

		return $this->rendered_entity;
	}

	/**
	 * Detects current product when in multi-product KB mode
	 * @return null
	 */
	public function current_product() {
		if (isset($this->current_product)) {
			return $this->current_product;
		}

		$this->current_product = null;

		if (isset($_REQUEST['kb_id']) && (int)$_REQUEST['kb_id'] > 0) {
			$this->current_product = (int)$_REQUEST['kb_id'];
		} else {
			switch ($this->rendered_entity()) {
				case self::ENT_KB_ARTICLE:
					$this->current_product = $this->get_product_for_article();
					break;

				case self::ENT_KB_TOPIC:
					$this->current_product = $this->get_product_for_topic();
					break;

				default:
					break;

			}
		}

		return $this->current_product;
	}

	/**
	 * Get product for current topic
	 * @return string
	 */
	public function get_product_for_topic() {
		$wp_the_query = $GLOBALS['wp_the_query'];
		$term = $this->get_root_term(get_term($wp_the_query->get_queried_object()));

		return MinervaKB::topic_option($term, 'topic_product_switch') ? $term->term_id : null; // TODO: term id
	}

	/**
	 * Gets product for article
	 * @return string
	 */
	public function get_product_for_article($return_term = false) {
		$terms = wp_get_post_terms( get_the_ID(), MKB_Options::option( 'article_cpt_category' ));
		$term = null;

		if ($terms && !empty($terms) && isset($terms[0])) {
			$term = $terms[0];
		}

		$term = $this->get_root_term($term);

		return MinervaKB::topic_option($term, 'topic_product_switch') ? ($return_term ? $term : $term->term_id) : null; // TODO: term id
	}

	private function get_root_term($term) {
		if (!$term) {
			return null;
		}

		if ($term->parent != '0') { // child
			$ancestors = get_ancestors( $term->term_id, MKB_Options::option( 'article_cpt_category' ), 'taxonomy' );

			if (!empty($ancestors)) {
				$term = get_term_by( 'id', $ancestors[sizeof($ancestors) - 1], MKB_Options::option( 'article_cpt_category' ) );
			}
		}

		return $term;
	}

	/**
	 * Gets topics for current product topic
	 * @return array|null|WP_Error
	 */
	public function product_topics() {
		if (isset($this->product_topics)) {
			return $this->product_topics;
		}

		$product_id = $this->current_product();

		if (!$product_id) {
			$this->product_topics = null;
			return $this->product_topics;
		}

		$this->product_topics = array($product_id);
		$this->product_topics = array_merge($this->product_topics, get_term_children( $product_id, MKB_Options::option( 'article_cpt_category' ) ));

		return $this->product_topics;
	}

    /**
     * Gets user tickets count, for admin only
     * @param $force_update
     * @return array
     */
    public function get_user_tickets_count($force_update = false) {
        if (isset($this->user_tickets_count_info)) {
            return $this->user_tickets_count_info;
        }

        $tickets_count = array(
            'active' => 0,
            'closed' => 0,
            'trash' => 0,
            // TODO: total, assigned to user, awaiting agent reply
        );

        if (!is_admin()) {
            return $tickets_count;
        }

        // TODO: count
        // TODO: save transient

        $this->user_tickets_count_info = MKB_Tickets::calculate_tickets_count();

        return $this->user_tickets_count_info;
    }

    /**
     * Gets user total reply count for given render
     * @param $user_id
     * @return int|mixed
     */
	public function get_user_replies_count($user_id) {
        if (!$this->user_replies_count_info) {
            $this->user_replies_count_info = array();
        }

        if (!$user_id) {
            return 0;
        }

        if (!isset($this->user_replies_count_info[$user_id])) {
            $query_args = array(
                'post_type' => 'mkb_ticket_reply',
                'posts_per_page' => -1,
                'ignore_sticky_posts' => 1,
                'author' => $user_id
            );

            $replies = get_posts($query_args);

            $this->user_replies_count_info[$user_id] = sizeof($replies);
        }

        return $this->user_replies_count_info[$user_id];
    }

    /**
     * Gets user total reply count for given render
     * @param $user_id
     * @return int|mixed
     */
    public function get_user_replies_count_for_ticket($user_id, $ticket_id) {
        if (!$this->user_replies_count_for_ticket_info) {
            $this->user_replies_count_for_ticket_info = array();
        }

        if (!$user_id || !$ticket_id) {
            return 0;
        }

        if (!isset($this->user_replies_count_per_ticket_info[$user_id])) {
            $this->user_replies_count_for_ticket_info[$user_id] = array();
        }

        if (!isset($this->user_replies_count_for_ticket_info[$user_id][$ticket_id])) {
            $query_args = array(
                'post_type' => 'mkb_ticket_reply',
                'posts_per_page' => -1,
                'ignore_sticky_posts' => 1,
                'post_parent' => $ticket_id,
                'author' => $user_id
            );

            $replies = get_posts( $query_args );

            $this->user_replies_count_for_ticket_info[$user_id][$ticket_id] = sizeof($replies);
        }

        return $this->user_replies_count_for_ticket_info[$user_id][$ticket_id];
    }

	/**
	 * Detects admin side
	 * @return bool
	 */
	public function is_admin() {
		if (isset($this->is_admin)) {
			return $this->is_admin;
		}

		$this->is_admin = is_admin();

		return $this->is_admin;
	}

	/**
	 * Detects client side
	 * @return bool
	 */
	public function is_client() {
		if (isset($this->is_client)) {
			return $this->is_client;
		}

		$this->is_client = !$this->is_admin();

		return $this->is_client;
	}

	/**
	 * Flag for desktop devices
	 * @return mixed
	 */
	public function is_desktop () {
		return $this->is_desktop;
	}

	/**
	 * Flag for desktop devices
	 * @return mixed
	 */
	public function is_tablet () {
		return $this->is_tablet;
	}

	/**
	 * Flag for desktop devices
	 * @return mixed
	 */
	public function is_mobile () {
		return $this->is_mobile;
	}

	/**
	 * Flag for dummy data imported
	 * @return bool
	 */
	public function is_demo_imported () {
		if (isset($this->is_demo_imported)) {
			return $this->is_demo_imported;
		}

		$this->is_demo_imported = MinervaKB_DemoImporter::is_imported();

		return $this->is_demo_imported;
	}

	/**
	 * Flag for dummy data skipped
	 * @return bool
	 */
	public function is_demo_skipped () {
		if (isset($this->is_demo_skipped)) {
			return $this->is_demo_skipped;
		}

		$this->is_demo_skipped = MinervaKB_DemoImporter::is_skipped();

		return $this->is_demo_skipped;
	}

	/**
	 * Returns platform string
	 * @return string
	 */
	public function platform() {
		if ($this->is_mobile()) {
			return 'mobile';
		} else if ($this->is_tablet()) {
			return 'tablet';
		} else {
			return 'desktop';
		}
	}

	/**
	 * Detects WPML
	 * @return bool
	 */
	public function is_wpml() {
        if (isset($this->is_wpml)) {
            return $this->is_wpml;
        }

		$this->is_wpml = defined('ICL_LANGUAGE_CODE');

		return $this->is_wpml;
	}

	/**
	 * Detects Woo
	 * @return bool
	 */
	public function is_woocommerce_active() {
        if (isset($this->is_woocommerce_active)) {
            return $this->is_woocommerce_active;
        }

		$this->is_woocommerce_active = defined('WC_VERSION');

		return $this->is_woocommerce_active;
	}

	/**
	 * Detects RTL
	 * @return bool
	 */
	public function is_rtl() {
		if (!isset($this->is_rtl)) {
			$this->is_rtl = function_exists('is_rtl') && is_rtl();
		}

		return $this->is_rtl;
	}
}