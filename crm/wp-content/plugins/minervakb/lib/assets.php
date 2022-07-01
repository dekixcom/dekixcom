<?php
/**
 * Project: Minerva KB
 * Copyright: 2015-2016 @KonstruktStudio
 */

require_once(MINERVA_KB_PLUGIN_DIR . 'lib/helpers/fonts.php');

class MinervaKB_Assets {

	private $info;

	private $inline_styles;

	private $ajax;

	/**
	 * Constructor
	 * @param $deps
	 */
	public function __construct($deps) {

		$this->setup_dependencies( $deps );

		add_action( 'wp_enqueue_scripts', array($this, 'client_assets'), 100 );
		add_action( 'admin_enqueue_scripts', array($this, 'admin_assets'), 100 );
		add_action( 'enqueue_block_editor_assets', array($this, 'admin_block_editor_page_assets'), 100 );
	}

	/**
	 * Sets up dependencies
	 * @param $deps
	 */
	private function setup_dependencies($deps) {
		if (isset($deps['info'])) {
			$this->info = $deps['info'];
		}

		if (isset($deps['inline_styles'])) {
			$this->inline_styles = $deps['inline_styles'];
		}

		if (isset($deps['ajax'])) {
			$this->ajax = $deps['ajax'];
		}
	}

	/**
	 * Client-side assets
	 */
	public function client_assets() {
		global $post;

		if (MKB_Options::option( 'typography_on' ) && !MKB_Options::option('dont_load_font')) {
			$all_fonts = mkb_get_all_fonts();
			$google_fonts = $all_fonts['GOOGLE'];
			$google_fonts = $google_fonts["fonts"];
			$selected_family = MKB_Options::option( 'style_font' );
			$selected_weights = MKB_Options::option( 'style_font_gf_weights' );
			$selected_languages = MKB_Options::option( 'style_font_gf_languages' );

			if (isset($google_fonts[$selected_family])) {
				wp_enqueue_style( 'minerva-kb-font/css', mkb_get_google_font_url(
					$selected_family, $selected_weights, $selected_languages
				), false, null );
			}
		}

		wp_enqueue_style( 'minerva-kb/css', MINERVA_KB_PLUGIN_URL . 'assets/css/dist/minerva-kb.css', false, MINERVA_KB_VERSION );

		if (!MKB_Options::option('no_font_awesome')) {
			wp_enqueue_style( 'minerva-kb/fa-css', MINERVA_KB_PLUGIN_URL . 'assets/css/vendor/font-awesome.css', false, null );
		}

		// dynamic styles
		wp_add_inline_style( 'minerva-kb/css', $this->inline_styles->get_css());

		// user custom CSS
		wp_add_inline_style( 'minerva-kb/css', $this->inline_styles->get_custom_css());

		if (is_singular(MKB_Tickets::TICKET_POST_TYPE)) {
            wp_enqueue_script( 'minerva-kb/moment-js', MINERVA_KB_PLUGIN_URL . 'assets/js/vendor/moment/moment-with-locales.js', array(), '2.24.0', true );
        }

		if (MKB_Options::option('article_fancybox') && is_single() && $post->post_type == MKB_Options::option( 'article_cpt' )) {
			wp_enqueue_style( 'minerva-kb/fancybox-css', MINERVA_KB_PLUGIN_URL . 'assets/css/vendor/fancybox/jquery.fancybox-1.3.4.css', false, null );
			wp_enqueue_script( 'minerva-kb/fancybox-easing-js', MINERVA_KB_PLUGIN_URL . 'assets/js/vendor/fancybox/jquery.easing-1.3.pack.js', array( 'jquery' ), null, true );
			wp_enqueue_script( 'minerva-kb/fancybox-js', MINERVA_KB_PLUGIN_URL . 'assets/js/vendor/fancybox/jquery.fancybox-1.3.4.js', array( 'jquery', 'minerva-kb/fancybox-easing-js' ), '1.3.4.1', true );
		}

        wp_enqueue_script( 'minerva-kb/common-ui-js', MINERVA_KB_PLUGIN_URL . 'assets/js/minerva-kb-common-ui.js', array(
            'jquery'
        ), MINERVA_KB_VERSION, true );

		wp_enqueue_script( 'minerva-kb/js', MINERVA_KB_PLUGIN_URL . 'assets/js/minerva-kb.js', array(
            'jquery',
            'minerva-kb/common-ui-js'
        ), MINERVA_KB_VERSION, true );

        wp_localize_script( 'minerva-kb/common-ui-js', 'MinervaKB', $this->get_client_js_data() );
	}

	/**
	 * Gets client side JS data
	 */
	private function get_client_js_data() {
	    $post_id = get_the_ID();

		return array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'siteUrl' => site_url(),
			'platform' => $this->info->platform(),
			'info' => array(
				'isSingle' => $this->info->is_single(),
				'isTicket' => $this->info->is_ticket(),
				'isSupportClient' => $this->info->is_support_client(), // TODO: use MKB_Users maybe
				'isTicketAuthorView' => $this->info->is_ticket() && MKB_Tickets::is_ticket_author_view($post_id),
				'userCanAttachFiles' => MKB_Users::instance()->can_user_attach_files(),
				'isPost' => $this->info->is_post(),
				'isRTL' => $this->info->is_rtl(),
                'postId' => $post_id,
			),
			'nonce' => array(
				'nonce' => wp_create_nonce( $this->ajax->get_nonce() ),
				'nonceKey' =>$this->ajax->get_nonce_key(),
			),
			'settings' => array(
				'show_like_message' => MKB_Options::option( 'show_like_message' ),
				'show_dislike_message' => MKB_Options::option( 'show_dislike_message' ),
				'enable_feedback' => MKB_Options::option( 'enable_feedback' ),
				'single_template' => MKB_Options::option( 'single_template' ),
				'feedback_mode' => MKB_Options::option( 'feedback_mode' ),
				'feedback_email_on' => MKB_Options::option( 'feedback_email_on' ),
				'track_search_with_results' => MKB_Options::option( 'track_search_with_results' ),
				'ga_good_search_category' => MKB_Options::option( 'ga_good_search_category' ),
				'ga_good_search_action' => MKB_Options::option( 'ga_good_search_action' ),
				'ga_good_search_value' => MKB_Options::option( 'ga_good_search_value' ),
				'track_search_without_results' => MKB_Options::option( 'track_search_without_results' ),
				'ga_bad_search_category' => MKB_Options::option( 'ga_bad_search_category' ),
				'ga_bad_search_action' => MKB_Options::option( 'ga_bad_search_action' ),
				'ga_bad_search_value' => MKB_Options::option( 'ga_bad_search_value' ),
				'track_article_likes' => MKB_Options::option( 'track_article_likes' ),
				'ga_like_category' => MKB_Options::option( 'ga_like_category' ),
				'ga_like_action' => MKB_Options::option( 'ga_like_action' ),
				'ga_like_label' => MKB_Options::option( 'ga_like_label' ),
				'ga_like_value' => MKB_Options::option( 'ga_like_value' ),
				'track_article_dislikes' => MKB_Options::option( 'track_article_dislikes' ),
				'ga_dislike_category' => MKB_Options::option( 'ga_dislike_category' ),
				'ga_dislike_action' => MKB_Options::option( 'ga_dislike_action' ),
				'ga_dislike_label' => MKB_Options::option( 'ga_dislike_label' ),
				'ga_dislike_value' => MKB_Options::option( 'ga_dislike_value' ),
				'track_article_feedback' => MKB_Options::option( 'track_article_feedback' ),
				'ga_feedback_category' => MKB_Options::option( 'ga_feedback_category' ),
				'ga_feedback_action' => MKB_Options::option( 'ga_feedback_action' ),
				'ga_feedback_label' => MKB_Options::option( 'ga_feedback_label' ),
				'ga_feedback_value' => MKB_Options::option( 'ga_feedback_value' ),
				'search_delay' => MKB_Options::option( 'search_delay' ),
				'live_search_show_excerpt' => MKB_Options::option( 'live_search_show_excerpt' ),
				'active_search_groups' => explode(',', MKB_Options::option('search_result_groups')),
				'live_search_use_post' => MKB_Options::option( 'live_search_use_post' ),
				'show_back_to_top' => MKB_Options::option( 'show_back_to_top' ),
				'scrollspy_switch' => MKB_Options::option( 'scrollspy_switch' ),
				'toc_in_content_disable' => MKB_Options::option( 'toc_in_content_disable' ),
                'global_scroll_offset' => MKB_Options::option( 'global_scroll_offset' ),
				'article_fancybox' => MKB_Options::option( 'article_fancybox' ),
				'article_sidebar' => MKB_Options::option( 'article_sidebar' ),
				'article_sidebar_sticky' => MKB_Options::option( 'article_sidebar_sticky' ),
				'article_sidebar_sticky_top' => MKB_Options::option( 'article_sidebar_sticky_top' ),
				'article_sidebar_sticky_min_width' => MKB_Options::option( 'article_sidebar_sticky_min_width' ),
				'back_to_top_position' => MKB_Options::option( 'back_to_top_position' ),
				'back_to_top_text' => MKB_Options::option( 'back_to_top_text' ),
				'show_back_to_top_icon' => MKB_Options::option( 'show_back_to_top_icon' ),
				'back_to_top_icon' => MKB_Options::option( 'back_to_top_icon' ),
				'back_to_site_top' => MKB_Options::option( 'back_to_site_top' ),
				'toc_scroll_offset' => MKB_Options::option( 'toc_scroll_offset' ),
				'search_mode' => MKB_Options::option( 'search_mode' ),
				'search_needle_length' => MKB_Options::option( 'search_needle_length' ),
				'search_request_fe_cache' => MKB_Options::option( 'search_request_fe_cache' ),
				'live_search_disable_mobile' => MKB_Options::option( 'live_search_disable_mobile' ),
				'live_search_disable_tablet' => MKB_Options::option( 'live_search_disable_tablet' ),
				'live_search_disable_desktop' => MKB_Options::option( 'live_search_disable_desktop' ),
				'faq_filter_open_single' => MKB_Options::option( 'faq_filter_open_single' ),
				'faq_slow_animation' => MKB_Options::option( 'faq_slow_animation' ),
				'faq_toggle_mode' => MKB_Options::option( 'faq_toggle_mode' ),
				'faq_enable_pages' => MKB_Options::option( 'faq_enable_pages' ),
				'content_tree_widget_open_active_branch' => MKB_Options::option( 'content_tree_widget_open_active_branch' ),
				'toc_url_update' => MKB_Options::option( 'toc_url_update' ),
				'faq_url_update' => MKB_Options::option( 'faq_url_update' ),
				'faq_scroll_offset' => MKB_Options::option( 'faq_scroll_offset' ),
				'toc_headings_exclude' => MKB_Options::option( 'toc_headings_exclude' ),
				'antispam_failed_message' => MKB_Options::option( 'antispam_failed_message' ),
				'submit_success_message' => MKB_Options::option( 'submit_success_message' ),
				'submit_content_editor_skin' => MKB_Options::option( 'submit_content_editor_skin' ),
				'fh_show_delay' => MKB_Options::option( 'fh_show_delay' ),
				'fh_display_mode' => MKB_Options::option( 'fh_display_mode' ),
				'glossary_mobile_mode' => MKB_Options::option( 'glossary_mobile_mode' ),
				'glossary_loader_icon' => MKB_Options::option( 'glossary_loader_icon' ),
				'glossary_enable_pages' => MKB_Options::option( 'glossary_enable_pages' ),
				'glossary_scroll_offset' => MKB_Options::option( 'glossary_scroll_offset' ),
				'glossary_highlight_limit' => MKB_Options::option( 'glossary_highlight_limit' ),
				'enable_posts_glossary_highlight' => MKB_Options::option( 'enable_posts_glossary_highlight' ),
				'blog_posts_glossary_highlight_selector' => MKB_Options::option( 'blog_posts_glossary_highlight_selector' ),
			),
			'i18n' => self::get_i18n(),
            'glossary' => self::get_glossary_data()
		);
	}

	/**
	 * Static i18n strings
	 * @return array
	 */
	public static function get_i18n () {
		return array(
			'no-results' => MKB_Options::option( 'search_no_results_text' ),
			'results' => MKB_Options::option( 'search_results_text' ),
			'result' => MKB_Options::option( 'search_result_text' ),
			'questions' => MKB_Options::option( 'questions_text' ),
			'question' => MKB_Options::option( 'question_text' ),
			'search_group_kb' => MKB_Options::option( 'search_group_kb_label' ),
			'search_group_kb_topics' => MKB_Options::option( 'search_group_kb_topics_label' ),
			'search_group_faq' => MKB_Options::option( 'search_group_faq_label' ),
			'search_group_glossary' => MKB_Options::option( 'search_group_glossary_label' ),
			'like_message_text' => MKB_Options::option( 'like_message_text' ),
			'dislike_message_text' => MKB_Options::option( 'dislike_message_text' ),
			'feedback_label' => MKB_Options::option( 'feedback_label' ),
			'feedback_email_label' => MKB_Options::option( 'feedback_email_label' ),
			'feedback_submit_label' => MKB_Options::option( 'feedback_submit_label' ),
			'feedback_submit_request_label' => MKB_Options::option( 'feedback_submit_request_label' ),
			'feedback_info_text' => MKB_Options::option( 'feedback_info_text' ),
			'feedback_sent_text' => MKB_Options::option( 'feedback_sent_text' ),
			'submission_empty_title' => __('Title must not be empty', 'minerva-kb'),
			'submission_empty_content' => __('Content must not be empty', 'minerva-kb'),
		);
	}

	public static function get_glossary_data() {
	    if (MKB_Options::option('disable_glossary') || !MKB_Options::option('enable_kb_glossary_highlight')) {
	        return array();
        }

        $glossary = array();

        $query_args = array(
            'posts_per_page'   => -1,
            'post_type'        => 'mkb_glossary',
            'post_status'      => 'publish'
        );

        $loop = new WP_Query($query_args);

        if ( $loop->have_posts() ) :
            while ( $loop->have_posts() ) : $loop->the_post();
                $glossary_id = get_the_ID();
                $is_excluded = false;
                $exclude_key = '_mkb_exclude_from_kb';

                if (metadata_exists('post', $glossary_id, $exclude_key)) {
                    $is_excluded = (bool)get_post_meta($glossary_id, $exclude_key, true);
                }

                if ($is_excluded) {
                    continue;
                }

                array_push($glossary, array(
                    'id' => $glossary_id,
                    'title' => get_the_title(),
                    'permalink' => get_the_permalink($glossary_id),
                    'synonyms' => get_post_meta($glossary_id, '_mkb_glossary_synonyms', true)
                ));
            endwhile;
        endif;

        wp_reset_postdata();

        return $glossary;
    }

	/**
	 * Assets required for admin
	 */
	public function admin_assets() {
		$screen = get_current_screen();

		wp_enqueue_media();

		wp_enqueue_style( 'minerva-kb/admin-css', MINERVA_KB_PLUGIN_URL . 'assets/css/dist/minerva-kb-admin.css', false, MINERVA_KB_VERSION );
		wp_enqueue_style( 'minerva-kb/admin-fa-css', MINERVA_KB_PLUGIN_URL . 'assets/css/vendor/font-awesome.css', false, null );

        // dynamic styles
//        wp_add_inline_style( 'minerva-kb/admin-css', $this->inline_styles->get_css());

		// dynamic admin styles
		ob_start();
		?>
		#adminmenu li.menu-icon-<?php esc_attr_e(MKB_Options::option('article_cpt')); ?> .wp-menu-image img,
        #adminmenu li.menu-icon-mkb_faq .wp-menu-image img,
        #adminmenu li.menu-icon-mkb_glossary .wp-menu-image img,
        #adminmenu li.menu-icon-mkb_ticket .wp-menu-image img,
        #adminmenu li.menu-icon-mkb_canned_response .wp-menu-image img {
			width: 20px;
			margin-top: -2px;
			margin-left: -2px;
		}

		#menu-posts-<?php esc_attr_e(MKB_Options::option('article_cpt')); ?> a[href$="minerva-kb-submenu-uninstall"] {
			color: #C85C5E;
		}

		#menu-posts-<?php esc_attr_e(MKB_Options::option('article_cpt')); ?> a[href$="minerva-kb-submenu-uninstall"]:hover {
			color: red;
		}

        .mkb-info {
            background: <?php echo esc_attr(MKB_Options::option( 'info_bg' )); ?>;
            color: <?php echo esc_attr(MKB_Options::option( 'info_color' )); ?>;
            border-color: <?php echo esc_attr(MKB_Options::option( 'info_border' )); ?>;
        }

        .mkb-info__icon {
            color: <?php echo esc_attr(MKB_Options::option( 'info_icon_color' )); ?>;
        }

        .mkb-tip {
            background: <?php echo esc_attr(MKB_Options::option( 'tip_bg' )); ?>;
            color: <?php echo esc_attr(MKB_Options::option( 'tip_color' )); ?>;
            border-color: <?php echo esc_attr(MKB_Options::option( 'tip_border' )); ?>;
        }

        .mkb-tip__icon {
            color: <?php echo esc_attr(MKB_Options::option( 'tip_icon_color' )); ?>;
        }

        .mkb-warning {
            background: <?php echo esc_attr(MKB_Options::option( 'warning_bg' )); ?>;
            color: <?php echo esc_attr(MKB_Options::option( 'warning_color' )); ?>;
            border-color: <?php echo esc_attr(MKB_Options::option( 'warning_border' )); ?>;
        }

        .mkb-warning__icon {
            color: <?php echo esc_attr(MKB_Options::option( 'warning_icon_color' )); ?>;
        }

        .mkb-related-content {
            background: <?php echo esc_attr(MKB_Options::option( 'related_content_bg' )); ?>;
            color: <?php echo esc_attr(MKB_Options::option( 'related_content_label_color' )); ?>;
            border-color: <?php echo esc_attr(MKB_Options::option( 'related_content_border' )); ?>;
        }

        .mkb-related-content a {
            color: <?php echo esc_attr(MKB_Options::option( 'related_content_links_color' )); ?>;
        }

		<?php
		wp_add_inline_style( 'minerva-kb/admin-css', ob_get_clean());

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script('jquery-ui-sortable');
		wp_enqueue_script( 'wp-util' );

		// toastr
		wp_enqueue_style( 'minerva-kb/admin-toastr-css', MINERVA_KB_PLUGIN_URL . 'assets/css/vendor/toastr/toastr.min.css', false, '2.1.3' );
		wp_enqueue_script( 'minerva-kb/admin-toastr-js', MINERVA_KB_PLUGIN_URL . 'assets/js/vendor/toastr/toastr.min.js', array(), '2.1.3', true );

		// cookie
        wp_enqueue_script( 'minerva-kb/admin-cookie-js', MINERVA_KB_PLUGIN_URL . 'assets/js/vendor/js.cookie.js', array(), '2.2.1', true );

		/**
		 * Common & Admin UI
		 */
        wp_enqueue_script( 'minerva-kb/common-ui-js', MINERVA_KB_PLUGIN_URL . 'assets/js/minerva-kb-common-ui.js', array(
            'jquery'
        ), MINERVA_KB_VERSION, true );

		wp_enqueue_script( 'minerva-kb/admin-ui-js', MINERVA_KB_PLUGIN_URL . 'assets/js/minerva-kb-ui.js', array(
			'underscore',
			'jquery',
			'wp-color-picker'
		), MINERVA_KB_VERSION, true );

		wp_localize_script( 'minerva-kb/admin-ui-js', 'MinervaKB', $this->get_admin_js_data() );

		/**
		 * Page builder UI
		 */
		if (isset($screen) && $screen->id == 'page' && $screen->post_type == 'page') {
			wp_enqueue_script( 'minerva-kb/admin-page-js', MINERVA_KB_PLUGIN_URL . 'assets/js/minerva-kb-page.js', array(
				'minerva-kb/common-ui-js',
				'minerva-kb/admin-ui-js',
			), MINERVA_KB_VERSION, true );
		}

		/**
		 * Taxonomy UI
		 */
		if (isset($screen) && ($screen->base == 'term' || $screen->base == 'edit-tags')) {
			wp_enqueue_script( 'minerva-kb/admin-term-js', MINERVA_KB_PLUGIN_URL . 'assets/js/minerva-kb-term.js', array(
				'minerva-kb/common-ui-js',
				'minerva-kb/admin-ui-js',
			), MINERVA_KB_VERSION, true );
		}

        /**
         * User UI
         */
        if (isset($screen) && ($screen->base == 'profile' || $screen->base == 'user-edit')) {
            wp_enqueue_script( 'minerva-kb/admin-user-js', MINERVA_KB_PLUGIN_URL . 'assets/js/minerva-kb-user.js', array(
                'minerva-kb/common-ui-js',
                'minerva-kb/admin-ui-js',
            ), MINERVA_KB_VERSION, true );
        }

        /**
         * Ticket edit UI
         */
        if (isset($screen) && ($screen->base == 'post' || $screen->base == 'edit') && $screen->post_type == 'mkb_ticket') {
            wp_enqueue_script( 'minerva-kb/moment-js', MINERVA_KB_PLUGIN_URL . 'assets/js/vendor/moment/moment-with-locales.js', array(), '2.24.0', true );

            wp_enqueue_script( 'minerva-kb/admin-ticket-js', MINERVA_KB_PLUGIN_URL . 'assets/js/minerva-kb-ticket.js', array(
                'minerva-kb/common-ui-js',
                'minerva-kb/admin-ui-js',
                'minerva-kb/admin-cookie-js',
                'minerva-kb/moment-js',
            ), MINERVA_KB_VERSION, true );
        }

		wp_enqueue_script( 'minerva-kb/admin-articles-js', MINERVA_KB_PLUGIN_URL . 'assets/js/minerva-kb-articles.js', array(
			'jquery',
			'jquery-ui-sortable',
			'minerva-kb/common-ui-js',
			'minerva-kb/admin-ui-js',
		), MINERVA_KB_VERSION, true );
	}

	/**
	 * Data for admin js
	 * @return array
	 */
	private function get_admin_js_data() {
	    $current_user = wp_get_current_user();

		return array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'pluginUrl' => MINERVA_KB_PLUGIN_URL,
			'siteUrl' => site_url(),
			'info' => array(
				'isDemoImported' => $this->info->is_demo_imported(),
				'isDemoSkipped' => $this->info->is_demo_skipped()
			),
			'nonce' => array(
				'nonce' => wp_create_nonce( $this->ajax->get_nonce() ),
				'nonceKey' => $this->ajax->get_nonce_key(),
			),
			'i18n' => array(
				'no-results' => MKB_Options::option('search_no_results_text'),
				'results' => MKB_Options::option('search_results_text'),
				'result' => MKB_Options::option('search_result_text'),
				'no-related' => __('No related articles selected', 'minerva-kb' ),
				'no-attachments' => __('No attachments added for this article', 'minerva-kb' ),
				'loading' => __('Loading...', 'minerva-kb' ),
				'tip' => __('Tip', 'minerva-kb' ),
				'info' => __('Info', 'minerva-kb' ),
				'warning' => __('Warning', 'minerva-kb' ),
				'topic' => __('Topic', 'minerva-kb' ),
				'topics' => __('Topics', 'minerva-kb' ),
				'search' => __('Search', 'minerva-kb' ),
				'anchor' => __('Anchor', 'minerva-kb' ),
				'related' => __('Related', 'minerva-kb' ),
				'submission' => __('Guest Post Form', 'minerva-kb' ),
				'faq' => __('FAQ', 'minerva-kb' ),
				'glossary' => __('Glossary List', 'minerva-kb' ),
				'select-shortcode' => __('Select shortcode', 'minerva-kb' ),
				'loading-options' => __('Loading options...', 'minerva-kb' ),
				'configure-shortcode' => __('Configure shortcode', 'minerva-kb' ),
				'update' => __('Update', 'minerva-kb' ),
				'insert' => __('Insert', 'minerva-kb' ),
				'more-than-one-shortcode' => __('More than 1 shortcode selected, cannot parse', 'minerva-kb' ),
				'minervakb-shortcodes' => __('MinervaKB Shortcodes', 'minerva-kb' ),
				'reset-confirm' => __('Are you sure you want to reset all the settings?', 'minerva-kb' ),
			),
			'optionPrefix' => MINERVA_KB_OPTION_PREFIX,
			'settings' => MKB_Options::get(),
			'user' => $current_user,
			'fontAwesomeIcons' => mkb_icon_options(),
			'articleEdit' => array(
				'attachments' => MinervaKB_ArticleEdit::get_attachments_data(),
				'attachmentsTracking' => MinervaKB_ArticleEdit::get_attachments_tracking_data(),
				'attachmentsIconMap' => MinervaKB_ArticleEdit::get_attachments_icon_map(),
				'attachmentsIconDefault' => MinervaKB_ArticleEdit::get_attachments_icon_default(),
			),
            'ticketEdit' => array(
                'cannedResponses' => self::get_canned_responses()
            )
		);
	}

    /**
     * Assets required for block editor admin
     */
    public function admin_block_editor_page_assets() {
        $screen = get_current_screen();

        /**
         * Page UI
         */
        if (isset($screen) && $screen->id == 'page' && $screen->post_type == 'page') {
            wp_enqueue_script( 'minerva-kb/admin-page-blocks-js', MINERVA_KB_PLUGIN_URL . 'assets/js/minerva-kb-page-gutenberg.js', array(
            ), MINERVA_KB_VERSION, true );
        }
    }

    /**
     * Canned responses for tickets
     * @return array
     */
    public static function get_canned_responses() {
        $screen = get_current_screen();

        $responses = array();

        if (isset($screen) && ($screen->base == 'post' || $screen->base == 'edit') && $screen->post_type == 'mkb_ticket') {
            $response_posts = get_posts(array(
                'post_type' => 'mkb_canned_response',
                'posts_per_page' => -1,
                'ignore_sticky_posts' => 1,
                'status' => 'publish'
            ));

            if (!empty($response_posts)) {
                foreach($response_posts as $response) {
                    $responses[$response->ID] = $response;
                }
            }
        }

        return $responses;
    }
}