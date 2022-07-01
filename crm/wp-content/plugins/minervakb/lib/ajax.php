<?php
/**
 * Project: Minerva KB
 * Copyright: 2015-2016 @KonstruktStudio
 */

require_once( MINERVA_KB_PLUGIN_DIR . 'lib/helpers/settings-builder.php' );
require_once( MINERVA_KB_PLUGIN_DIR . 'lib/db.php' );

class MinervaKB_Ajax {

	private $analytics;

	private $restrict;

	private $info;

	const NONCE = 'minerva_kb_nonce';
	const NONCE_KEY = 'minerva_kb_ajax_nonce';

	public function __construct($deps) {

		$this->setup_dependencies( $deps );

		$this->register();
	}

	/**
	 * Sets up dependencies
	 * @param $deps
	 */
	private function setup_dependencies($deps) {
		if (isset($deps['analytics'])) {
			$this->analytics = $deps['analytics'];
		}

		if (isset($deps['restrict'])) {
			$this->restrict = $deps['restrict'];
		}

		if (isset($deps['info'])) {
			$this->info = $deps['info'];
		}
	}

	/**
	 * Registers actions handlers
	 */
	public function register() {

		// save settings
		add_action( 'wp_ajax_mkb_save_settings', array( $this, 'save_settings' ) );

		// reset settings
		add_action( 'wp_ajax_mkb_reset_settings', array( $this, 'reset_settings' ) );

		// import settings
		add_action( 'wp_ajax_mkb_import_settings', array( $this, 'import_settings' ) );

		// update permissions
        add_action( 'wp_ajax_mkb_update_permissions', array( $this, 'update_permissions' ) );

		// save sorting
		add_action( 'wp_ajax_mkb_save_sorting', array( $this, 'save_sorting' ) );

		// save terms sorting
		add_action( 'wp_ajax_mkb_save_terms_sorting', array( $this, 'save_terms_sorting' ) );

		// verify purchase
		add_action( 'wp_ajax_mkb_verify_purchase', array( $this, 'verify_purchase' ) );

		// reset ticket IDs
        add_action( 'wp_ajax_mkb_reset_ticket_ids', array( $this, 'reset_ticket_ids' ) );

		// uninstall settings
		add_action( 'wp_ajax_mkb_uninstall_plugin', array( $this, 'uninstall_plugin' ) );

		// import dummy data
		add_action( 'wp_ajax_mkb_demo_import', array( $this, 'demo_import' ) );

		// skip dummy data import
		add_action( 'wp_ajax_mkb_skip_demo_import', array( $this, 'skip_demo_import' ) );

		// remove selected import entities
		add_action( 'wp_ajax_mkb_remove_import_entities', array( $this, 'remove_import_entities' ) );

		// remove all import entities
		add_action( 'wp_ajax_mkb_remove_all_import_entities', array( $this, 'remove_all_import_entities' ) );

		// resets stats
		add_action( 'wp_ajax_mkb_reset_stats', array( $this, 'reset_stats' ) );

		// restriction rules flush
		add_action( 'wp_ajax_mkb_flush_restriction', array( $this, 'flush_restriction' ) );

		// restriction views log
		add_action( 'wp_ajax_mkb_view_restriction_log', array( $this, 'view_restriction_log' ) );

		// restriction views log clear
		add_action( 'wp_ajax_mkb_clear_restriction_log', array( $this, 'clear_restriction_log' ) );

		// home builder section html
		add_action( 'wp_ajax_mkb_get_section_html', array( $this, 'get_section_html' ) );

		// test email
		add_action( 'wp_ajax_mkb_send_test_email', array( $this, 'send_test_email' ) );

		// analytics
		add_action( 'wp_ajax_mkb_get_month_analytics', array( $this, 'get_month_analytics' ) );
		add_action( 'wp_ajax_mkb_get_hit_results', array( $this, 'get_hit_results' ) );
		add_action( 'wp_ajax_mkb_get_ordered_search_stats', array( $this, 'get_ordered_search_stats' ) );
		add_action( 'wp_ajax_mkb_get_search_stats_page', array( $this, 'get_search_stats_page' ) );
		add_action( 'wp_ajax_mkb_get_articles_list', array( $this, 'get_articles_list' ) );
		add_action( 'wp_ajax_mkb_get_week_analytics', array( $this, 'get_week_analytics' ) );

		// search
		add_action( 'wp_ajax_mkb_kb_search', array( $this, 'ajax_kb_search' ) );
		add_action( 'wp_ajax_nopriv_mkb_kb_search', array( $this, 'ajax_kb_search' ) );

		// pageview tracking
		add_action( 'wp_ajax_mkb_article_pageview', array( $this, 'article_pageview' ) );
		add_action( 'wp_ajax_nopriv_mkb_article_pageview', array( $this, 'article_pageview' ) );

		// article like
		add_action( 'wp_ajax_mkb_article_like', array( $this, 'article_like' ) );
		add_action( 'wp_ajax_nopriv_mkb_article_like', array( $this, 'article_like' ) );

		// article dislike
		add_action( 'wp_ajax_mkb_article_dislike', array( $this, 'article_dislike' ) );
		add_action( 'wp_ajax_nopriv_mkb_article_dislike', array( $this, 'article_dislike' ) );

		// article feedback
		add_action( 'wp_ajax_mkb_article_feedback', array( $this, 'article_feedback' ) );
		add_action( 'wp_ajax_nopriv_mkb_article_feedback', array( $this, 'article_feedback' ) );
		add_action( 'wp_ajax_mkb_remove_feedback', array( $this, 'remove_feedback' ) );

		// get shortcodes options HTML
		add_action( 'wp_ajax_mkb_get_shortcode_options', array( $this, 'get_shortcode_options' ) );

		// receive frontend submission
		add_action( 'wp_ajax_mkb_save_client_submission', array( $this, 'save_client_submission' ) );
		add_action( 'wp_ajax_nopriv_mkb_save_client_submission', array( $this, 'save_client_submission' ) );

		// track attachment downloads
		add_action( 'wp_ajax_mkb_track_attachment_download', array( $this, 'track_attachment_download' ) );
		add_action( 'wp_ajax_nopriv_mkb_track_attachment_download', array( $this, 'track_attachment_download' ) );

		// glossary term content
		add_action( 'wp_ajax_mkb_get_glossary_term_content', array( $this, 'get_glossary_term_content' ) );
		add_action( 'wp_ajax_nopriv_mkb_get_glossary_term_content', array( $this, 'get_glossary_term_content' ) );

        // save reply as
        add_action( 'wp_ajax_mkb_save_reply_as', array( $this, 'save_reply_as' ) );

        // tickets and support
        add_action( 'wp_ajax_nopriv_mkb_create_support_account', array( $this, 'create_support_account' ) );

        // tickets add ticket
        add_action('wp_ajax_mkb_create_support_ticket', array($this, 'create_support_ticket'));
        add_action('wp_ajax_nopriv_mkb_create_support_ticket', array($this, 'create_support_ticket'));
        // tickets add guest ticket
        // TODO: maybe remove, used in js to detect guest vs user
        add_action( 'wp_ajax_mkb_create_guest_support_ticket', array($this, 'create_support_ticket'));
        add_action( 'wp_ajax_nopriv_mkb_create_guest_support_ticket', array( $this, 'create_support_ticket'));

        // ticket list
        add_action( 'wp_ajax_mkb_get_tickets_list', array( $this, 'get_tickets_list' ) );
        add_action( 'wp_ajax_mkb_get_tickets_list_ui_preferences', array( $this, 'get_tickets_list_ui_preferences' ) );
        add_action( 'wp_ajax_mkb_save_tickets_list_ui_preferences', array( $this, 'save_tickets_list_ui_preferences' ) );
        add_action( 'wp_ajax_mkb_get_tickets_lock_info', array( $this, 'get_tickets_lock_info' ) );
        add_action( 'wp_ajax_mkb_tickets_bulk_trash', array( $this, 'tickets_bulk_trash' ) );
        add_action( 'wp_ajax_mkb_tickets_bulk_restore', array( $this, 'tickets_bulk_restore' ) );
        add_action( 'wp_ajax_mkb_tickets_bulk_permanently_delete', array( $this, 'tickets_bulk_permanently_delete' ) );

        // form editor
        add_action( 'wp_ajax_mkb_save_form_config', array( $this, 'save_form_config' ) );
        add_action( 'wp_ajax_mkb_reset_form_config', array( $this, 'reset_form_config' ) );
        add_action( 'wp_ajax_mkb_get_form_field_html', array( $this, 'get_form_field_html' ) );

        // reply to ticket (user & guest)
        add_action( 'wp_ajax_mkb_reply_to_ticket', array( $this, 'reply_to_ticket' ) );
        add_action( 'wp_ajax_nopriv_mkb_reply_to_ticket', array( $this, 'reply_to_ticket_guest' ) );

        // reply edit
        add_action( 'wp_ajax_mkb_get_ticket_reply_for_edit', array( $this, 'get_ticket_reply_for_edit' ) );
        add_action( 'wp_ajax_mkb_edit_ticket_reply', array( $this, 'edit_ticket_reply' ) );

        // reply delete / restore
        add_action( 'wp_ajax_mkb_delete_ticket_reply', array( $this, 'delete_ticket_reply' ) );
        add_action( 'wp_ajax_mkb_restore_ticket_reply', array( $this, 'restore_ticket_reply' ) );

        // ticket credentials
        add_action( 'wp_ajax_mkb_provide_ticket_credentials', array( $this, 'provide_ticket_credentials' ) );
        add_action( 'wp_ajax_nopriv_mkb_provide_ticket_credentials', array( $this, 'provide_ticket_credentials_guest' ) );
        add_action( 'wp_ajax_mkb_delete_ticket_credentials', array( $this, 'delete_ticket_credentials' ) );
        add_action( 'wp_ajax_nopriv_mkb_delete_ticket_credentials', array( $this, 'delete_ticket_credentials_guest' ) );

        // support account login / register
        add_action( 'wp_ajax_nopriv_mkb_account_login', array( $this, 'account_login' ) );

        // ticket viewed by customer
        add_action( 'wp_ajax_nopriv_mkb_ticket_viewed_by_customer', array( $this, 'ticket_viewed_by_customer' ) );
        add_action( 'wp_ajax_mkb_ticket_viewed_by_customer', array( $this, 'ticket_viewed_by_customer' ) );

        // reopen ticket
        add_action( 'wp_ajax_mkb_reopen_ticket', array( $this, 'reopen_ticket' ) );
        add_action( 'wp_ajax_nopriv_mkb_reopen_ticket', array( $this, 'reopen_ticket_guest' ) );
	}

	public static function get_nonce() {
		return self::NONCE;
	}

	public static function get_nonce_key() {
		return self::NONCE_KEY;
	}

	private function send_nonce_error() {
        $this->send_error_message_json(
            4001,
            __( 'Timeout error. Sorry, you cannot currently perform this action. Try to refresh the page or login.', 'minerva-kb' )
        );
	}

    private function send_access_error() {
        $this->send_error_message_json(
            4002,
            __( 'Access error. Sorry, only administrators can perform this action. Try to refresh the page or login with a different user', 'minerva-kb' )
        );
    }

    /**
     * @param int $code
     * @param string $message
     */
    private function send_error_message_json($code, $message) {
        echo json_encode(array(
            'status' => 1,
            'errors' => array(
                'global' => array(
                    array(
                        'code' => $code,
                        'message' => $message
                    )
                )
            )
        ));

        wp_die();
    }

	/**
	 * Checks user and checks if he is admin
	 */
	protected function check_admin_user() {
		if (!current_user_can('manage_options')) {
			$this->send_access_error();
		}

		$this->check_nonce();
	}

	/**
	 * Checks if user is really user
	 */
	protected function check_nonce() {
		if (!check_ajax_referer( self::get_nonce(), 'nonce_value', false)) {
			$this->send_nonce_error();
		}
	}

	/**
	 * Live search handler
	 */
	public function ajax_kb_search() {
		global $post;
		global $minerva_kb;

		$search = trim( $_REQUEST['search'] );
		$search = filter_var($search, FILTER_SANITIZE_STRING);
		$track_results = isset( $_REQUEST['trackResults'] );
		$search_mode = $_REQUEST['mode'];
		$search_results = array();
		$faq_results = array();
		$glossary_results = array();
		$topics_results = array();
		$is_specific_topics = isset( $_REQUEST['topics'] ) && $_REQUEST['topics'] != '';
		$product_id = isset( $_REQUEST['kb_id'] ) && (int)$_REQUEST['kb_id'] > 0 ? (int)$_REQUEST['kb_id'] : null;
		$specific_topics_query = array();

        $active_search_groups = explode(',', MKB_Options::option('search_result_groups'));

		// 1. KB search by content
		$query_args = array(
			'post_type' => MKB_Options::option( 'article_cpt' ),
			'post_status' => 'publish',
			'ignore_sticky_posts' => 1,
			's' => $search,
			'order_by' => 'relevance',
			'posts_per_page' => MKB_Options::option('search_group_kb_limit')
		);

		if ( $is_specific_topics ) {
			$specific_topics_query = array(
				array(
					'taxonomy' => MKB_Options::option( 'article_cpt_category' ),
					'field' => 'term_id',
					'terms' => array_map( function ( $string_id ) {
						return (int) $string_id;
					}, explode( ',', $_REQUEST['topics'] ) ),
					'operator' => 'IN',
				),
			);

			$query_args['tax_query'] = $specific_topics_query;
		}

		$search_loop = new WP_Query( $query_args );

		if ( $search_loop->have_posts() ) :
			while ( $search_loop->have_posts() ) : $search_loop->the_post();
				$topics_list = wp_get_post_terms( $post->ID, MKB_Options::option( 'article_cpt_category' ), array( "fields" => "all" ) );
				$topics_info = array();

				if (!empty($topics_list)) {
					foreach($topics_list as $topic) {
						array_push($topics_info, array(
							'id' => $topic->term_id,
							'name' => $topic->name,
							'color' => MKB_TemplateHelper::get_topic_color_by_id($topic->term_id)
						));
					}
				}

				$excerpt = strip_tags(preg_replace('#\[[^\]]+\]#', '', $post->post_content));

				$article_product = $this->info->get_product_for_article(true);
				$article_product_name = null;

				if ($article_product) {
					$article_product_name = $article_product->name;
				}

				array_push( $search_results, array(
					"id" => $post->ID,
					"title" => get_the_title(),
					"link" => get_the_permalink(),
					"topics" => $topics_info,
					"product" => $article_product_name,
					"excerpt" => MKB_Options::option( 'live_search_show_excerpt' ) ?
						mb_substr($excerpt, 0, MKB_Options::option( 'live_search_excerpt_length' )) :
						''
				) );
			endwhile;
		endif;
		wp_reset_postdata();

		if ($search_mode === 'blocking' || $track_results) {
			ob_start();
			try {
				MKB_DbModel::register_hit( MKB_DbModel::HIT_TYPE_SEARCH, array(
					"keyword" => $search,
					"results_count" => sizeof( $search_results ),
					"results_ids" => sizeof( $search_results ) ?
						array_map( function ( $result ) {
							return $result["id"];
						}, $search_results ) :
						null
				) );
			} catch (Exception $e) {}
			ob_clean();
		}

        // 2. FAQ search
        if (!MKB_Options::option('disable_faq') && in_array('faq', $active_search_groups)) {
            $faq_query_args = array(
                'post_type' => 'mkb_faq',
                'post_status' => 'publish',
                'ignore_sticky_posts' => 1,
                's' => $search,
                'order_by' => 'relevance',
                'posts_per_page' => MKB_Options::option('search_group_faq_limit')
            );

            $faq_search_loop = new WP_Query( $faq_query_args );

            if ( $faq_search_loop->have_posts() ) :
                while ( $faq_search_loop->have_posts() ) : $faq_search_loop->the_post();
                    array_push( $faq_results, array(
                        "id" => $post->ID,
                        "title" => get_the_title(),
                        "link" => get_the_permalink()
                    ) );
                endwhile;
            endif;
            wp_reset_postdata();
        }

        // 3. Glossary search
        if (!MKB_Options::option('disable_glossary') && in_array('glossary', $active_search_groups)) {
            $glossary_query_args = array(
                'post_type' => 'mkb_glossary',
                'post_status' => 'publish',
                'ignore_sticky_posts' => 1,
                's' => $search,
                'order_by' => 'relevance',
                'posts_per_page' => MKB_Options::option('search_group_glossary_limit')
            );

            $glossary_search_loop = new WP_Query($glossary_query_args);

            if ($glossary_search_loop->have_posts()) :
                while ($glossary_search_loop->have_posts()) : $glossary_search_loop->the_post();
                    array_push($glossary_results, array(
                        "id" => $post->ID,
                        "title" => get_the_title(),
                        "link" => get_the_permalink()
                    ));
                endwhile;
            endif;
            wp_reset_postdata();
        }

        $is_restrict_on = MKB_Options::option('restrict_on');
        $is_topic_restrict_on = $is_restrict_on && MKB_Options::option('restrict_remove_from_archives');
        $is_topic_global_restrict_on = $is_topic_restrict_on && $minerva_kb->restrict->is_user_globally_restricted();

        // 4. Topics search
        if (in_array('topics', $active_search_groups) && !$is_topic_global_restrict_on) {
            $topics = get_terms( array(
                'taxonomy' => MKB_Options::option('article_cpt_category'),
                'hide_empty' => true,
                'search' => $search,
                'posts_per_page' => MKB_Options::option('search_group_kb_topics_limit')
            ) );

            if (!is_wp_error($topics) && sizeof($topics)) {
                foreach($topics as $topic) {
                    // skip all restricted topics
                    if ($is_topic_restrict_on &&
                        isset($topic->term_id) && !$minerva_kb->restrict->is_topic_allowed($topic)) {

                        continue;
                    }

                    array_push($topics_results, array(
                        "id" => $topic->term_id,
                        "title" => $topic->name,
                        "link" => get_term_link( $topic )
                    ));
                }
            }
        }

        // send results
		$status = 0;

		$res = array(
			'search' => $search,
			'result' => $search_results,
			'extraResults' => array(
                'faq' => $faq_results,
                'glossary' => $glossary_results,
                'topics' => $topics_results
            ),
			'status' => $status
		);

		if ($product_id) {
			$product = get_term_by('id', $product_id, MKB_Options::option( 'article_cpt_category' ));

			ob_start();
			?>
			<li class="kb-search__results-info">
				<?php echo esc_html(MKB_Options::option('search_product_prefix')); ?>
				&nbsp;<strong><?php echo esc_html($product->name); ?></strong>
			</li>
			<?php
			$results_info_html = ob_get_clean();

			$res['results_info'] = $results_info_html;
		}

		echo json_encode( $res );

		wp_die();
	}

	/**
	 *
	 * @param $post_id
	 * @param $key
	 */
	protected function update_count_meta( $post_id, $key ) {
		$now = time();
		$begin_of_day = strtotime( "midnight", $now );

		$current_count_meta_raw = get_post_meta( $post_id, $key, true );
		$current_count_meta = array();

		if ( $current_count_meta_raw ) {
			$current_count_meta = json_decode( $current_count_meta_raw, true );
		}

		if ( ! array_key_exists( $begin_of_day, $current_count_meta ) ) {
			$current_count_meta[ $begin_of_day ] = 0;
		}

		$current_day_count                   = (int) $current_count_meta[ $begin_of_day ];
		$current_count_meta[ $begin_of_day ] = ++ $current_day_count;

		update_post_meta( $post_id, $key, json_encode( $current_count_meta ) );
	}

	/**
	 * Article pageview
	 */
	public function article_pageview() {
		$article_id = (int) $_POST['id'];
		$article    = get_post( $article_id );

		if ( $article === null ) {
			wp_die();
		}

		$current_views = (int) get_post_meta( $article_id, '_mkb_views', true );
		update_post_meta( $article_id, '_mkb_views', ++ $current_views );

		$this->update_count_meta( $article_id, '_mkb_views_meta' );

		// recently viewed articles
		if (is_user_logged_in()) {
		    $current_user = wp_get_current_user();

		    $recently_viewed_articles = get_user_meta($current_user->ID, '_mkb_recently_viewed_articles', true);

		    if (!$recently_viewed_articles) {
                $recently_viewed_articles = array();
            } else {
                $recently_viewed_articles = json_decode($recently_viewed_articles);
            }

            if (($key = array_search($article_id, $recently_viewed_articles)) !== false) {
                unset($recently_viewed_articles[$key]);
            }

            $recently_viewed_articles[]= $article_id;

            update_user_meta($current_user->ID, '_mkb_recently_viewed_articles', json_encode(array_slice($recently_viewed_articles, 0, 10)));
        }

		$status = 0;

		echo json_encode( array(
			'status' => $status
		) );

		wp_die();
	}

	/**
	 * Article like handler
	 */
	public function article_like() {
		$this->check_nonce();

		$article_id = (int) $_POST['id'];
		$article    = get_post( $article_id );

		if ( $article === null ) {
			wp_die();
		}

		$current_views = (int) get_post_meta( $article_id, '_mkb_likes', true );
		update_post_meta( $article_id, '_mkb_likes', ++ $current_views );

		$this->update_count_meta( $article_id, '_mkb_likes_meta' );
		$this->set_rating_cookie($article_id);

		$status = 0;

		echo json_encode(array(
			'status' => $status
		));

		wp_die();
	}

	/**
	 * Article dislike
	 */
	public function article_dislike() {
		$this->check_nonce();

		$article_id = (int) $_POST['id'];
		$article    = get_post( $article_id );

		if ( $article === null ) {
			wp_die();
		}

		$current_views = (int) get_post_meta( $article_id, '_mkb_dislikes', true );
		update_post_meta( $article_id, '_mkb_dislikes', ++ $current_views );

		$this->update_count_meta( $article_id, '_mkb_dislikes_meta' );
        $this->set_rating_cookie($article_id);

		$status = 0;

		echo json_encode( array(
			'status' => $status
		) );

		wp_die();
	}

    /**
     * Saves article rating cookie
     * @param $article_id
     */
	private function set_rating_cookie($article_id) {
	    if (!MKB_Options::option('rating_prevent_multiple')) {
	        return;
        }

        setcookie('mkb_article_rated_' . $article_id, 1, time() + (int)MKB_Options::option('rating_prevent_multiple_interval') * 3600, COOKIEPATH, COOKIE_DOMAIN);
    }

    /**
     * Send test email
     */
    public function send_test_email() {
        $this->check_admin_user();

        $to = MKB_Options::option('email_notify_default_email');
        $subject = 'Test email';

        ob_start(); ?><p>This is a test email</p><?php

        $body = ob_get_clean();
        $headers = array('Content-Type: text/html; charset=UTF-8');

        $result = false;

        if (!defined('MINERVA_DEMO_MODE')) {
            $result = wp_mail($to, $subject, $body, $headers);
        }

        echo json_encode(array(
            'status' => $result === true ? 0 : 1
        ));

        wp_die();
    }

	/**
	 * Article feedback
	 */
	public function article_feedback() {
		$this->check_nonce();

		$article_id     = (int) $_POST['id'];
		$feedback_count = wp_count_posts( 'mkb_feedback' )->publish;
		$feedback_content = wp_strip_all_tags( $_POST['content'] );
        $feedback_email = $_POST['email'];

		$feedback_post = array(
			'post_title' => wp_strip_all_tags(
				__( 'Article feedback' .
				    ( $feedback_count > 0 ?
					    ' #' . ( $feedback_count + 1 ) :
					    '' ), 'minerva-kb' ) ),
			'post_content' => $feedback_content,
			'post_status' => 'publish',
			'post_type' => 'mkb_feedback'
		);
		$feedback_post_id = wp_insert_post( $feedback_post );

		if ($feedback_email) {
            add_post_meta($feedback_post_id, 'feedback_email', $feedback_email);
        }

		// older WP versions
		add_post_meta($feedback_post_id, 'feedback_article_id', $article_id);

		if (MKB_Options::option('email_notify_feedback_switch')) {
            $to = MKB_Options::option('email_notify_default_email');

            $email_template_context = array(
                'article_title' => get_the_title($article_id),
                'action_url' => MKB_Utils::get_post_edit_admin_url($article_id),
                'message_text' => stripslashes($feedback_content)
            );

            $result = MKB_Emails::instance()->send(
                $to,
                MKB_Emails::EMAIL_TYPE_ADMIN_NEW_ARTICLE_FEEDBACK,
                $email_template_context
            );
        }

		$status = 0;

		echo json_encode( array(
			'status' => $status,
            'result' => $result
		) );

		wp_die();
	}

	/**
	 * Saves plugin settings
	 */
	public function save_settings() {
		$this->check_admin_user();

		$settings = $_POST['settings'];

		if (!$settings || empty($settings)) {
			wp_die();
		}

        $settings = json_decode(stripslashes($settings), true);

		if (!$settings || !sizeof($settings)) {
            wp_die();
        }

		$update_result = MKB_Options::save($settings);

		$status = 0;

		echo json_encode(array(
			'status' => $status,
			'settings' => MKB_Options::get(),
            'updateResult' => $update_result
		));

		wp_die();
	}

	/**
	 * Resets plugin settings
	 */
	public function reset_settings() {
		$this->check_admin_user();

		MKB_Options::reset();

		$status = 0;

		echo json_encode( array(
			'status' => $status
		) );

		wp_die();
	}

	/**
	 * Import plugin settings
	 */
	public function import_settings() {
		$this->check_admin_user();

		$role = $_POST['mkb_edited_role'];

		if ( ! isset($import_data) ) {
			wp_die();
		}

		$status = MKB_Options::import( $import_data ) ? 0 : 1;

		echo json_encode( array(
			'status' => $status
		) );

		wp_die();
	}

    /**
     * Permissions update
     */
	public function update_permissions() {
        $this->check_admin_user();

        $role = isset($_POST['role']) ? esc_html($_POST['role']) : null;
        $caps = isset($_POST['caps']) ? $_POST['caps'] : array();

        if (!$caps || empty($caps) || !$role) {
            wp_die();
        }

        MKB_Users::update_role_permissions($role, $caps);

        $status = 0;

        echo json_encode( array(
            'status' => $status
        ));

        wp_die();
    }

	/**
	 * uninstall plugin data
	 */
	public function uninstall_plugin() {
		$this->check_admin_user();

		$status = 0;

		MKB_Options::remove_data();
		MinervaKB_Analytics::delete_all_feedback();
		MinervaKB_DemoImporter::remove_data();
		MKB_DbModel::delete_schema();

		echo json_encode( array(
			'status' => $status
		) );

		wp_die();
	}

	/**
	 * Imports dummy data
	 */
	public function demo_import() {
		$this->check_admin_user();

		$status = 0;

		$set_home_page = $_POST['setHomePage'] === 'true';
		$use_block_editor = isset($_POST['useBlockEditorData']) && $_POST['useBlockEditorData'] === 'true';

		ob_start();
        $entries = MinervaKB_DemoImporter::run_import(array(
            'set_home_page' => $set_home_page,
            'use_block_editor' => $use_block_editor
        ));
		$output = ob_get_clean();

		echo json_encode( array(
			'status' => $status,
			'output' => $output,
			'entities_html' => MinervaKB_DemoImporter::get_entities_html($entries)
		) );

		wp_die();
	}

	/**
	 * Skips dummy data import
	 */
	public function skip_demo_import() {
		$this->check_admin_user();

		$status = 0;

		MinervaKB_DemoImporter::skip_import();

		echo json_encode( array(
			'status' => $status
		) );

		wp_die();
	}

	/**
	 * Removes selected imported entities
	 */
	public function remove_import_entities() {
		$this->check_admin_user();

		$status = 0;

		$ids = $_POST['ids'];
		$type = $_POST['type'];

		$status = MinervaKB_DemoImporter::remove_import_entities($type, $ids);

		echo json_encode( array(
			'status' => $status
		) );

		wp_die();
	}

	/**
	 * Removes all imported entities
	 */
	public function remove_all_import_entities() {
		$this->check_admin_user();

		$status = 0;

		$status = MinervaKB_DemoImporter::remove_all_import_entities();

		echo json_encode( array(
			'status' => $status
		) );

		wp_die();
	}

	/**
	 * Gets month analytics
	 */
	public function get_month_analytics() {
		$this->check_admin_user();

		$status = 0;

		echo json_encode( array(
			'status' => $status,
			'graphDates' => $this->analytics->get_recent_month_dates(),
			'graphViews' => $this->analytics->get_recent_month_views(),
			'graphLikes' => $this->analytics->get_recent_month_likes(),
			'graphDislikes' => $this->analytics->get_recent_month_dislikes(),
		) );

		wp_die();
	}

	/**
	 * Gets week analytics
	 */
	public function get_week_analytics() {
		$this->check_admin_user();

		$status = 0;

		echo json_encode( array(
			'status' => $status,
			'graphDates' => $this->analytics->get_recent_week_dates(),
			'graphViews' => $this->analytics->get_recent_week_views(),
			'graphLikes' => $this->analytics->get_recent_week_likes(),
			'graphDislikes' => $this->analytics->get_recent_week_dislikes(),
		) );

		wp_die();
	}

	/**
	 * Gets home page builder section html
	 */
	public function get_section_html() {
		$settings_builder = new MKB_SettingsBuilder();
		$layout_editor    = new MKB_LayoutEditor( $settings_builder );

		$this->check_admin_user();

		$status = 0;

		echo json_encode( array(
			'status' => $status,
			'html' => $layout_editor->get_section_html( $_POST['section_type'], $_POST['position'] )
		) );

		wp_die();
	}

	/**
	 * Gets article list
	 */
	public function get_articles_list() {
		$query_args = array(
			'post_type' => MKB_Options::option( 'article_cpt' ),
			'post__not_in' => array( (int) $_POST['currentId'] ),
			'posts_per_page' => - 1
		);

		$articles_loop = new WP_Query( $query_args );
		$articles_list = array();

		if ( $articles_loop->have_posts() ) :
			while ( $articles_loop->have_posts() ) : $articles_loop->the_post();
				array_push( $articles_list, array(
					"title" => get_the_title(),
					"id" => get_the_ID()
				) );
			endwhile;
		endif;
		wp_reset_postdata();

		$status = 0;

		echo json_encode( array(
			'articles' => $articles_list,
			'status' => $status
		) );

		wp_die();
	}

	/**
	 * Saves posts sorting
	 */
	public function save_sorting() {
        $this->check_nonce();

		$sorting = $_POST['sorting'];
		$tax = $_POST['taxonomy'];

		if ( ! $sorting || empty( $sorting ) || !$tax ) {
			wp_die();
		}

		// TODO; maybe add permissions check here as well

		foreach($sorting as $term_id => $posts):
			foreach($posts as $index => $id):
                update_post_meta( (int)$id, 'mkb_tax_order_' . $term_id, (int)$index );
			endforeach;
		endforeach;

		$status = 0;

		echo json_encode( array(
			'status' => $status,
			'sorting' => $sorting,
			'tax' => $tax
		) );

		wp_die();
	}


	/**
	 * Saves terms sorting
	 */
	public function save_terms_sorting() {
        $this->check_nonce();

		$sorting = $_POST['sorting'];
		$tax = $_POST['taxonomy'];

		if ( ! $sorting || empty( $sorting ) || !$tax ) {
			wp_die();
		}

		foreach($sorting as $term_id => $order):
			MKB_TemplateHelper::set_topic_option($term_id, 'topic_order', $order);
		endforeach;

		$status = 0;

		echo json_encode( array(
			'status' => $status,
			'sorting' => $sorting,
			'tax' => $tax
		) );

		wp_die();
	}

	/**
	 * Removes feedback entry
	 */
	public function remove_feedback() {
		$this->check_admin_user();

		$status = 0;

		if ( isset( $_POST['feedback_id'] ) ) {
			wp_trash_post( (int) $_POST['feedback_id'] );
		}

		echo json_encode( array(
			'status' => $status
		) );

		wp_die();
	}

	/**
	 * Return keyword article recent matches
	 */
	public function get_hit_results() {
		$this->check_admin_user();

		$status = 0;

		$results = MKB_DbModel::get_search_hit_results( $_POST["hit_id"] );

		if ($results && sizeof($results)) {
			$results = array_map(function($result_id) {
				return array(
					'id' => $result_id,
					'title' => get_the_title($result_id),
					'link' => get_the_permalink($result_id)
				);
			}, $results);
		}

		echo json_encode( array(
			'status' => $status,
			'articles' => $results
		) );

		wp_die();
	}

	/**
	 * Gets search stats
	 */
	public function get_search_stats_page() {
		$this->check_admin_user();

		$status = 0;

		$page = (int) $_POST['page'];
		$field = $_POST['field'];
		$order = $_POST['order'];
		$items_per_page = 20;

		$results = $this->analytics->get_keywords(array(
			'field' => $field,
			'order' => $order,
            'offset' => $page * $items_per_page
		));

		echo json_encode( array(
			'status' => $status,
			'stats' => $results
		) );

		wp_die();
	}

	/**
	 * Gets search stats ordered
	 */
	public function get_ordered_search_stats() {
		$this->check_admin_user();

		$status = 0;

		$page = (int) $_POST['page'];
		$field = $_POST['field'];
		$order = $_POST['order'];

		$items_per_page = 20;

		$results = array_slice($this->analytics->get_keywords(array(
			"field" => $field,
			"order" => $order
		)), $page * $items_per_page, $items_per_page);

		echo json_encode( array(
			'status' => $status,
			'stats' => $results
		) );

		wp_die();
	}

	/**
	 * Resets stats on user request
	 */
	public function reset_stats() {
		$this->check_admin_user();

		$status = 0;

		$config = $_POST['resetConfig'];

		$articleId = isset($_POST['articleId']) ? $_POST['articleId'] : null;

		if ($articleId !== null) {
			$this->reset_article_stats((int)$articleId, $config);
		} else {
			$query_args = array(
				'post_type' => MKB_Options::option( 'article_cpt' ),
				'posts_per_page' => - 1
			);

			$articles_loop = new WP_Query( $query_args );

			if ( $articles_loop->have_posts() ) :
				while ( $articles_loop->have_posts() ) : $articles_loop->the_post();
					$id = get_the_ID();

					$this->reset_article_stats($id, $config);
				endwhile;
			endif;
			wp_reset_postdata();
		}

		if ($articleId === null && isset($config['search']) && $config['search'] === "true") {
			// reset search
			MKB_DbModel::reset_search_data();
		}

		echo json_encode( array(
			'status' => $status,
			'config' => $config
		) );

		wp_die();
	}

	/**
	 * Helper to reset single article stats
	 * @param $id
	 * @param $config
	 */
	private function reset_article_stats($id, $config) {
		if (isset($config['dislikes']) && $config['dislikes'] === "true") {
			// reset dislikes
			delete_post_meta($id, '_mkb_dislikes');
			delete_post_meta($id, '_mkb_dislikes_meta');
		}

		if (isset($config['likes']) && $config['likes'] === "true") {
			// reset likes
			delete_post_meta($id, '_mkb_likes');
			delete_post_meta($id, '_mkb_likes_meta');
		}

		if (isset($config['views']) && $config['views'] === "true") {
			// reset views
			delete_post_meta($id, '_mkb_views');
			delete_post_meta($id, '_mkb_views_meta');
		}
	}

	/**
	 * Flush restriction cache
	 */
	public function flush_restriction() {
		$this->check_admin_user();

		$status = 0;

		global $minerva_kb;
		$minerva_kb->restrict->invalidate_restriction_cache();

		echo json_encode( array(
			'status' => $status
		) );

		wp_die();
	}

	/**
	 * View restriction log
	 */
	public function view_restriction_log() {
		$this->check_admin_user();

		$status = 0;

		global $minerva_kb;
		$log = $minerva_kb->restrict->get_recent_visitors_log();

		echo json_encode( array(
			'status' => $status,
			'log' => $log
		) );

		wp_die();
	}

	/**
	 * Clear restriction log
	 */
	public function clear_restriction_log() {
		$this->check_admin_user();

		$status = 0;

		global $minerva_kb;
		$minerva_kb->restrict->clear_recent_visitors_log();

		echo json_encode( array(
			'status' => $status
		) );

		wp_die();
	}

	/**
	 * Gets options html for shortcode
	 */
	public function get_shortcode_options() {
		$status = 0;

		$shortcode = isset($_POST['shortcode']) ? $_POST['shortcode'] : '';
		$values = isset($_POST['values']) ? $_POST['values'] : array();

		global $minerva_kb;

		$options = $minerva_kb->shortcodes->get_options_for($shortcode);

		ob_start();
		$settings_helper = new MKB_SettingsBuilder();
		?><div class="mkb-shortcode-options">
			<?php
			if (!empty($options)):
				foreach ( $options as $option ):
					$settings_helper->render_option(
						$option["type"],
						isset($values[$option['id']]) ? $values[$option['id']] : $option["default"],
						$option
					);
				endforeach;
			else:
				?><div class="mkb-shortcode-no-options"><?php
				_e( 'This shortcode has no options', 'minerva-kb' );
				?></div><?php
			endif;
		?></div>
		<?php
		$html = ob_get_clean();

		echo json_encode( array(
			'status' => $status,
			'count' => sizeof($options),
			'html' => $html
		) );

		wp_die();
	}

	/**
	 * Receives and saves client submission
	 */
	public function save_client_submission() {
		$status = 0;
		$error = '';

		$title = isset($_POST['title']) ? trim($_POST['title']) : '';
		$topic = isset($_POST['topic']) ? $_POST['topic'] : '';
		$content = isset($_POST['content']) ? trim($_POST['content']) : '';
		$antispam = isset($_POST['antispam']) ? trim($_POST['antispam']) : '';

		// filter to block submission by user parameters (IP, location, etc.)
		if (!apply_filters('minerva_guestpost_allow_post', true)) {
			$status = 1;
			$error = __( 'Submission disabled by server rules', 'minerva-kb' );
		}

		// content and title must not be empty
		if (!$title || !$content) {
			$status = 1;
			$error = __( 'Title and content are required', 'minerva-kb' );
		}

		// unique titles check
		if (MKB_Options::option('submit_unique_titles') && post_exists( $title )) {
			$status = 1;
			$error = MKB_Options::option('submit_unique_titles_error_message');
		}

		// antispam
		if (MKB_Options::option('antispam_quiz_enable') && $antispam != MKB_Options::option('antispam_quiz_answer')) {
			$status = 1;
			$error = MKB_Options::option('antispam_failed_message');
		}

		if ($status === 0) {
			$client_article = array(
				'post_title' => wp_strip_all_tags( apply_filters('minerva_guestpost_title', $title) ),
				'post_content' => wpautop( wp_kses_post( apply_filters('minerva_guestpost_content', $content) ) ),
				'post_status' => apply_filters('minerva_guestpost_post_status', 'draft'),
				'post_type' => MKB_Options::option('article_cpt')
			);

			$create_result = $article_id = wp_insert_post( $client_article );

			if ($create_result == 0) {
				$status = 1;
				$error = __( 'Article not created, some unknown error happened', 'minerva-kb' );
			} else {
				if (MKB_Options::option('submit_allow_topics_select') && $topic) {
					wp_set_object_terms( $create_result, array( (int)$topic ), MKB_Options::option('article_cpt_category') );
				}

				// email notification
                if (MKB_Options::option('email_admin_new_guest_article_switch')) {
                    $to = MKB_Options::option('email_notify_default_email');

                    $email_template_context = array(
                        'article_title' => get_the_title($article_id),
                        'action_url' => MKB_Utils::get_post_edit_admin_url($article_id)
                    );

                    MKB_Emails::instance()->send(
                        $to,
                        MKB_Emails::EMAIL_TYPE_ADMIN_NEW_GUEST_ARTICLE,
                        $email_template_context
                    );
                }
			}
		}

		echo json_encode( array(
			'status' => $status,
			'error' => $error
		) );

		wp_die();
	}

	/**
	 * Purchase verification via purchase code
	 */
	public function verify_purchase() {
		$status = 0;

		$purchase_code = isset($_REQUEST['code']) ? trim($_REQUEST['code']) : '';
		$check_result = false;

		if (!$purchase_code) {
			$status = 1;
		} else {
			MKB_Options::save_option('auto_updates_verification', $purchase_code);

			try {
				$check_result = MinervaKB_AutoUpdate::verify_purchase(true);
			} catch (Exception $e) {
				$check_result = false;
			}
		}

		echo json_encode( array(
			'status' => $status,
			'check_result' => $check_result
		) );

		wp_die();
	}

	public function reset_ticket_ids() {
        $status = 0;

        $first_id = isset($_REQUEST['firstId']) ? (int)$_REQUEST['firstId'] : null;

        if ($first_id !== null) {
            $custom_ticket_id = $first_id;

            $query_args = array(
                'post_type' => 'mkb_ticket',
                'ignore_sticky_posts' => 1,
                'posts_per_page' => -1,
                'post_status' => 'publish,pending,auto-draft,future,private,draft,trash',
                'orderby' => 'ID',
                'order' => 'ASC'
            );

            $tickets_loop = new WP_Query( $query_args );

            if ($tickets_loop->have_posts()):
                while($tickets_loop->have_posts()) : $tickets_loop->the_post();
                    $ticket_id = get_the_ID();
                    update_post_meta($ticket_id, MKB_Tickets::TICKET_CUSTOM_ID_META_KEY, $custom_ticket_id++);
                endwhile;
            endif;

            MKB_Tickets::update_next_ticket_custom_id($custom_ticket_id);

            wp_reset_postdata();

        } else {
            $status = 1;
        }

        echo json_encode( array(
            'status' => $status
        ));

        wp_die();
    }

	public function track_attachment_download() {
		$status = 0;

		$attachment_id = isset($_POST['id']) ? $_POST['id'] : '';

		if ($attachment_id) {
			MinervaKB_ArticleEdit::track_attachment_download($attachment_id);
		}

		echo json_encode( array(
			'status' => $status,
		) );

		wp_die();
	}

    /**
     * Gets glossary term
     */
    public function get_glossary_term_content() {
        $term_id = (int) $_REQUEST['id'];

        $glossary_term = get_post( $term_id );
        $thumbnail = get_the_post_thumbnail_url( $term_id, 'full' );

        $status = 0;

        ob_start();

        if ($thumbnail) {
            ?><div class="mkb-glossary-tooltip__featured">
                <img src="<?php echo esc_url($thumbnail); ?>" alt="Glossary Term: <?php echo esc_attr($glossary_term->post_title); ?>">
            </div><?php
        }

        ?><div class="mkb-glossary-tooltip__content">
            <div class="mkb-glossary-tooltip__title">
                <?php esc_html_e($glossary_term->post_title ); ?>
            </div><?php
            echo apply_filters( 'the_content', $glossary_term->post_content );
        ?></div><?php

        $html = ob_get_clean();

        echo json_encode( array(
            'html' => $html,
            'status' => $status
        ) );

        wp_die();
    }

    /**
     * Saves reply as
     */
    public function save_reply_as() {
        $reply_id = (int) $_REQUEST['id'];
        $title = $_REQUEST['title'];
        $target = $_REQUEST['target'];

        $status = 0;

        $post_type = 'mkb_canned_response';
        $edit_link = '';

        switch($target) {
            case 'faq':
                $post_type = 'mkb_faq';
                break;

            case 'kb':
                $post_type = MKB_Options::option('article_cpt');
                break;

            default:
                // canned response
                break;
        }

        $reply = get_post($reply_id);

        $new_post = array(
            'post_title' => wp_strip_all_tags(apply_filters('minerva_save_reply_as_title', $title)),
            'post_content' => $reply->post_content,
            'post_status' => apply_filters('minerva_save_reply_as_post_status', 'draft'),
            'post_type' => $post_type
        );

        $new_post_id = wp_insert_post( $new_post );

        if ($new_post_id && !is_wp_error($new_post_id)) {
            $edit_link = admin_url( 'post.php?post=' . $new_post_id) . '&action=edit';
        } else {
            $status = 1;
            // TODO: errors
        }

        echo json_encode(array(
            'url' => $edit_link,
            'status' => $status
        ));

        wp_die();
    }

    /**
     * Create support ticket
     */
    public function create_support_ticket() {
        $this->check_nonce();

        $status = 0;
        $this->errors = array();

        // vars
        $user_id = 0;
        $is_guest = !is_user_logged_in();

        if ($is_guest) {
            $guest_user = MKB_Users::get_guest_support_user();

            $user_id = $guest_user ? $guest_user->ID : 0;
        } else {
            $user_id = get_current_user_id();
        }

        if (
            !$user_id || // unknown user or guest user deleted
            $is_guest && !MKB_Options::option('tickets_allow_guest_tickets') // guest tickets are disabled
        ) {
            echo json_encode(array(
                'status' => 1
            ));

            wp_die();

            return;
        }

        // form data
        // common fields
        $form_title = isset($_REQUEST['title']) ? wp_strip_all_tags($_REQUEST['title']) : '';

        $form_message = isset($_REQUEST['message']) ? wp_kses_post($_REQUEST['message']) : '';

        // guest fields
        $form_firstname = isset($_REQUEST['firstname']) ? wp_strip_all_tags($_REQUEST['firstname']) : '';
        $form_lastname = isset($_REQUEST['lastname']) ? wp_strip_all_tags($_REQUEST['lastname']) : '';
        $form_email = isset($_REQUEST['email']) ? wp_strip_all_tags($_REQUEST['email']) : '';

        // technical data
        $referrer_type = isset($_REQUEST['referrer_type']) ? wp_strip_all_tags($_REQUEST['referrer_type']) : '';
        $referrer_meta = isset($_REQUEST['referrer_meta']) ? wp_strip_all_tags($_REQUEST['referrer_meta']) : '';

        $ticket_url = '';

        // Create post object
        $ticket_args = array(
            'post_type'     => 'mkb_ticket',
            'post_title'    => $form_title,
            'post_content'  => $form_message,
            'post_status'   => 'publish',
            'post_author'   => $user_id,
            // NOTE: tax_input does not work for quest users
        );

        $ticket_id = wp_insert_post($ticket_args);

        $ticket_url = get_permalink($ticket_id);

        if ($is_guest) {
            $access_token = self::get_uid(30);
            $ticket_url .= '?ticket_access_token=' . $access_token;
        }

        $email_sent = false;

        if (!is_wp_error($ticket_id)) {
            // Default taxonomies
            self::set_ticket_taxonomy_from_request($ticket_id, 'mkb_ticket_type');
            self::set_ticket_taxonomy_from_request($ticket_id, 'mkb_ticket_product');
            self::set_ticket_taxonomy_from_request($ticket_id, 'mkb_ticket_department');
            self::set_ticket_taxonomy_from_request($ticket_id, 'mkb_ticket_priority');

            // custom ticket ID number
            MKB_Tickets::maybe_set_custom_ticket_id($ticket_id);

            // Assignee
            if (MKB_Options::option('tickets_assignment_mode') === 'user' && MKB_Options::option('tickets_default_assignee')) {
                $assignee = get_user_by('ID', MKB_Options::option('tickets_default_assignee'));

                if ($assignee) {
                    update_post_meta($ticket_id, '_mkb_ticket_assignee', $assignee->ID);

                    if (MKB_Options::option('email_agent_ticket_assigned_switch')) {
                        MKB_Emails::instance()->send(
                            $assignee->user_email,
                            MKB_Emails::EMAIL_TYPE_AGENT_TICKET_ASSIGNED,
                            array(
                                'agent_firstname' => $assignee->first_name,
                                'ticket_id' => $ticket_id,
                                'ticket_title' => $form_title,
                                'message_text' => $form_message,
                                'action_url' => MKB_Utils::get_post_edit_admin_url($ticket_id)
                            )
                        );
                    }
                }
            }

            // base
            add_post_meta($ticket_id, '_mkb_ticket_status', 'open', true);

            // referrer
            add_post_meta($ticket_id, '_mkb_referrer_type', $referrer_type);
            add_post_meta($ticket_id, '_mkb_referrer_meta', $referrer_meta);

            // guest meta
            if ($is_guest) {
                add_post_meta($ticket_id, MKB_Tickets::TICKET_GUEST_TICKET_META_KEY, true);

                add_post_meta($ticket_id, '_mkb_guest_ticket_email', $form_email);
                add_post_meta($ticket_id, '_mkb_guest_ticket_firstname', $form_firstname);
                add_post_meta($ticket_id, '_mkb_guest_ticket_lastname', $form_lastname);

                add_post_meta($ticket_id, '_mkb_guest_ticket_access_token', $access_token);
            }

            // custom fields
            MKB_FormsBuilder::save_form_extra_fields($ticket_id, $is_guest ? 'guestTicketForm' : 'userTicketForm');

            // system meta
            MKB_Tickets::set_ticket_channel_form($ticket_id);
            MKB_Tickets::set_awaiting_agent_reply_flag($ticket_id);

            // attachments
            $uploader = new MKB_Attachments(
                'mkb_ticket_create_files',
                'ticket' . $ticket_id,
                $ticket_id
            );

            // TODO: display any internal attachment errors not caught on FE
            $attachments_errors = $uploader->process_files();

            $email_template_context = array(
                // client tags
                'guest_firstname' => $form_firstname,

                // ticket tags
                'ticket_title' => $form_title,
                'ticket_id' => $ticket_id,
                'action_url' => $ticket_url,
                'message_text' => $form_message,
            );

            // email notification
            // TODO: refactor all email $template_context maybe
            if ($is_guest && MKB_Options::option('email_guest_ticket_created_switch') && $form_email) {
                // guest ticket created message
                $email_template_context['guest_firstname'] = $form_firstname;

                $email_sent = MKB_Emails::instance()->send(
                    $form_email,
                    MKB_Emails::EMAIL_TYPE_GUEST_TICKET_CREATED,
                    $email_template_context
                );
            } else if (!$is_guest && MKB_Options::option('email_user_ticket_created_switch')) { // user
                $user = wp_get_current_user();

                $email_template_context['user_firstname'] = $user->first_name;

                $email_sent = MKB_Emails::instance()->send(
                    $user->user_email,
                    MKB_Emails::EMAIL_TYPE_USER_TICKET_CREATED,
                    $email_template_context
                );
            }

        } else {
            // TODO: errors
            $status = 1;
        }

        echo json_encode(array(
            'status' => $status,
            'ticketUrl' => $ticket_url,
            'emailSent' => $email_sent
        ));

        wp_die();
    }

    /**
     * @param $ticket_id
     * @param $taxonomy
     */
    private static function set_ticket_taxonomy_from_request($ticket_id, $taxonomy) {
        $tax_defaults_map = array(
            'mkb_ticket_type' => 'tickets_default_type',
            'mkb_ticket_product' => 'tickets_default_product',
            'mkb_ticket_department' => 'tickets_default_department',
            'mkb_ticket_priority' => 'tickets_default_priority',
        );

        $tax_default_option = MKB_Options::option($tax_defaults_map[$taxonomy]);

        $selected_taxonomy_id = null;

        if (isset($_REQUEST[$taxonomy])) {
            $selected_taxonomy_id = wp_strip_all_tags($_REQUEST[$taxonomy]);
        } else if ($tax_default_option) {
            $selected_taxonomy_id = $tax_default_option;
        }

        if (!$selected_taxonomy_id) {
            return;
        }

        $selected_taxonomy_term = get_term_by('id', $selected_taxonomy_id, $taxonomy);

        if ($selected_taxonomy_term) {
            wp_set_object_terms($ticket_id, $selected_taxonomy_term->term_id, $taxonomy);
        }
    }

    /**
     * @param int $length
     * @return false|string
     */
    private static function get_uid($length = 20) {
        if (function_exists('random_bytes')) {
            // TODO: test on php 7+
            return substr(bin2hex(random_bytes($length)), 0, $length);
        } else {
            return substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'), 0, $length);
        }
    }

    /**
     * Reply to support ticket
     */
    public function reply_to_ticket() {
        $this->check_nonce();

        MKB_Tickets::process_user_new_reply_request();
    }

    /**
     * Reply to support ticket from guest
     */
    public function reply_to_ticket_guest() {
        $this->check_nonce();

        $errors = MKB_Tickets::process_user_new_reply_request(true);

        echo json_encode(array(
            'status' => sizeof($errors) ? 1 : 0,
            'errors' =>$errors
        ));

        wp_die();
    }

    /**
     * Ticket reopen by user
     */
    public function reopen_ticket() {
        $this->check_nonce();

        $errors = MKB_Tickets::process_user_ticket_reopen_request();

        echo json_encode(array(
            'status' => sizeof($errors) ? 1 : 0,
            'errors' =>$errors
        ));

        wp_die();
    }

    /**
     * Ticket reopen by guest
     */
    public function reopen_ticket_guest() {
        $errors = MKB_Tickets::process_user_ticket_reopen_request(true);

        echo json_encode(array(
            'status' => sizeof($errors) ? 1 : 0,
            'errors' =>$errors
        ));

        wp_die();
    }

    /**
     *
     */
    public function provide_ticket_credentials() {
        $this->check_nonce();

        $status = 0;

        $ticket_id = $_REQUEST['ticket_id'];
        $credentials = isset($_REQUEST['ticket_credentials']) ? wp_kses_post($_REQUEST['ticket_credentials']) : '';

        if ($credentials && MKB_Tickets::is_ticket($ticket_id)) {
            // TODO: check user === ticket author
            MKB_Tickets::set_ticket_credentials($ticket_id, $credentials);
        } else {
            $status = 1;
        }

        echo json_encode(array(
            'status' => $status
        ));

        wp_die();
    }

    /**
     *
     */
    public function delete_ticket_credentials() {
        $this->check_nonce();

        $status = 0;

        $ticket_id = $_REQUEST['ticket_id'];

        if (MKB_Tickets::is_ticket($ticket_id)) {
            // TODO: check user === ticket author
            MKB_Tickets::delete_ticket_credentials($ticket_id);
        } else {
            $status = 1;
        }

        echo json_encode(array(
            'status' => $status
        ));

        wp_die();
    }

    /**
     *
     */
    public function provide_ticket_credentials_guest() {
        $this->check_nonce();

        $status = 0;

        $ticket_id = $_REQUEST['ticket_id'];
        $credentials = isset($_REQUEST['ticket_credentials']) ? wp_kses_post($_REQUEST['ticket_credentials']) : '';

        if ($credentials && MKB_Tickets::is_ticket($ticket_id) && MKB_Tickets::verify_guest_ticket_access_token($ticket_id)) {
            MKB_Tickets::set_ticket_credentials($ticket_id, $credentials);
        } else {
            $status = 1;
        }

        echo json_encode(array(
            'status' => $status
        ));

        wp_die();
    }

    /**
     *
     */
    public function delete_ticket_credentials_guest() {
        $this->check_nonce();

        $status = 0;

        $ticket_id = $_REQUEST['ticket_id'];

        if (MKB_Tickets::is_ticket($ticket_id) && MKB_Tickets::verify_guest_ticket_access_token($ticket_id)) {
            MKB_Tickets::delete_ticket_credentials($ticket_id);
        } else {
            $status = 1;
        }

        echo json_encode(array(
            'status' => $status
        ));

        wp_die();
    }

    /**
     * Gets ticket reply HTML for edit
     */
    public function get_ticket_reply_for_edit() {
        $this->check_nonce();

        $status = 0;

        $reply_id = $_REQUEST['reply_id'];

        $reply = get_post($reply_id);

        echo json_encode(array(
            'status' => $status,
            'html' => $reply->post_content
        ));

        wp_die();
    }

    /**
     * Updates ticket reply
     */
    public function edit_ticket_reply() {
        $this->check_nonce();

        $status = 0;

        $reply_id = $_REQUEST['reply_id'];
        $edited_reply = $_REQUEST['edited_reply'];

        $reply = array(
            'ID' => $reply_id,
            'post_content' => $edited_reply
        );

        wp_update_post($reply);

        update_post_meta($reply_id, '_mkb_reply_edited', true);

        echo json_encode(array(
            'status' => $status
        ));

        wp_die();
    }

    /**
     * Delete ticket reply
     */
    public function delete_ticket_reply() {
        $this->check_nonce();

        $status = 0;

        $reply_id = $_REQUEST['reply_id'];

        $reply = array(
            'ID' => $reply_id,
            'post_status' => 'trash'
        );

        wp_update_post($reply);

        $user_id = get_current_user_id();
        update_post_meta($reply_id, '_mkb_status_last_modified_by', $user_id);

        echo json_encode(array(
            'status' => $status
        ));

        wp_die();
    }

    /**
     * Restore ticket reply
     */
    public function restore_ticket_reply() {
        $this->check_nonce();

        $status = 0;

        $reply_id = $_REQUEST['reply_id'];

        $reply = array(
            'ID' => $reply_id,
            'post_status' => 'publish'
        );

        wp_update_post($reply);

        $user_id = get_current_user_id();
        update_post_meta($reply_id, '_mkb_status_last_modified_by', $user_id);

        echo json_encode(array(
            'status' => $status
        ));

        wp_die();
    }

    /**
     * Create support user account
     * TODO: move to tickets or separate class
     */
    public function create_support_account() {
        $this->check_nonce();

        if (defined('MINERVA_DEMO_MODE')) {
            echo json_encode( array(
                'status' => 1,
                'username' => '',
                'loggedIn' => '',
                'errors' => 'Registration is not allowed on demo site'
            ));

            wp_die();

            return;
        }

        if (!MKB_Options::option('tickets_allow_users_registration')) {
            echo json_encode( array(
                'status' => 1,
                'username' => '',
                'loggedIn' => '',
                'errors' => 'Registration is not allowed'
            ));

            wp_die();

            return;
        }

        $status = 0;
        $errors = array();
        $username = '';
        $logged_in = false;

        $message = '';

        $min_username_length = apply_filters('minerva_register_account_username_min_length', 5);

        $form_first_name = isset($_REQUEST['mkb_account_firstname']) ? wp_strip_all_tags($_REQUEST['mkb_account_firstname']) : '' ;
        $form_last_name = isset($_REQUEST['mkb_account_lastname']) ? wp_strip_all_tags($_REQUEST['mkb_account_lastname']) : '';
        $form_email = isset($_REQUEST['mkb_account_email']) ? wp_strip_all_tags($_REQUEST['mkb_account_email']) : '';
        $form_password = isset($_REQUEST['mkb_account_password']) ? wp_strip_all_tags($_REQUEST['mkb_account_password']) : '';
        $form_privacy = isset($_REQUEST['mkb_reg_privacy_accept']) ? (bool)$_REQUEST['mkb_reg_privacy_accept'] : false;

        if ($form_privacy) {
            // 1. try first + last name
            $username = sanitize_user(
                apply_filters(
                'minerva_register_account_username_from_name',
                    strtolower($form_first_name . '.' . $form_last_name)
                ),
            true);

            $postfix = '';
            $postfix_step = 1;

            // 2. try email login
            if (mb_strlen($username) < $min_username_length) {
                $email_parts = explode('@', $form_email);

                $username = sanitize_user(
                    apply_filters(
                    'minerva_register_account_username_from_email',
                        // TODO: mb_compatibility wrapper or utils
                        strtolower($email_parts[0])
                    ),
                true);
            }

            // 3. try full email
            if (mb_strlen($username) < $min_username_length) {
                $username = sanitize_user(
                    apply_filters(
                    'minerva_register_account_username_from_full_email',
                        strtolower($form_email)
                    ),
                true);
            }

            // 4. add hash to whatever we have
            if (mb_strlen($username) < $min_username_length) {
                $username = apply_filters(
                    'minerva_register_account_username_with_hash',
                    mb_substr(uniqid($username, true), 0, $min_username_length - mb_strlen($username))
                );
            }

            while(username_exists($username . $postfix) !== false) { $postfix = $postfix_step++; }

            $username = $username . $postfix;

            $user_data = apply_filters('minerva_register_account_insert_user_args', array(
                'user_login'            => $username,
                'user_pass'             => $form_password,
                'user_email'            => $form_email,
                'first_name'            => $form_first_name,
                'last_name'             => $form_last_name,
                'role'                  => MKB_Options::option('tickets_require_admin_approve_for_new_users') ?
                    '' :
                    'mkb_support_user'
            ));

            $user_id = wp_insert_user($user_data);

            if (!is_wp_error($user_id)) {
                // fine, move on
                $user = get_user_by('ID', $user_id);

                if (MKB_Options::option('tickets_require_admin_approve_for_new_users')) {
                    update_user_meta($user_id, MKB_Users::PENDING_USER_META_KEY, true);

                    $message = 'We have received your registration request. Our administrator will process it soon and you will receive an email.';

                    // user notification
                    if (MKB_Options::option('email_user_registration_received_switch')) {
                        MKB_Emails::instance()->send(
                            $form_email,
                            MKB_Emails::EMAIL_TYPE_USER_REGISTRATION_RECEIVED,
                            array(
                                'user_firstname' => $form_first_name
                            )
                        );
                    }

                    // admin notification
                    if (MKB_Options::option('email_admin_new_registration_request_switch')) {
                        $to = MKB_Options::option('email_notify_default_email');

                        MKB_Emails::instance()->send(
                            $to,
                            MKB_Emails::EMAIL_TYPE_ADMIN_NEW_REGISTRATION_REQUEST,
                            array(
                                'user_firstname' => $form_first_name,
                                'user_lastname' => $form_last_name,
                                'user_email' => $form_email,
                                'action_url' => admin_url('user-edit.php?user_id=' . $user_id)
                            )
                        );
                    }
                } else {
                    if (!is_user_logged_in()) {
                        // proceed with login
                        wp_set_current_user($user_id, $user->user_email);
                        wp_set_auth_cookie($user_id);

                        $logged_in = true;

                        $message = 'You have been registered, reloading page...';
                    }
                }
            } else {
                $status = 1;
                $errors = $user_id->get_error_messages();
            }
        } else {
            $status = 1;
        }

        echo json_encode( array(
            'status' => $status,
            'username' => $username,
            'loggedIn' => $logged_in,
            'message' => $message,
            'errors' => $errors
        ));

        wp_die();
    }

    /**
     * Support account login
     */
    public function account_login() {
        $status = 0;

        if (defined('MINERVA_DEMO_MODE')) {
            echo json_encode( array(
                'status' => 1,
                'errors' => 'Login is not allowed on demo site'
            ));

            wp_die();

            return;
        }

        $errors = null;

        $form_login = isset($_REQUEST['mkb_account_login']) ? $_REQUEST['mkb_account_login'] : '';
        $form_password = isset($_REQUEST['mkb_account_password']) ? $_REQUEST['mkb_account_password'] : '';
        $form_remember_me = isset($_REQUEST['mkb_remember_me']) ? $_REQUEST['mkb_remember_me'] : false;

        $user = MKB_Users::get_user_by_email_or_login($form_login);

        if ($user && MKB_Users::is_user_pending_admin_approval($user->ID)) {
            $status = 1;

            echo json_encode(array(
                'status' => $status,
                'errors' => array('Your account is not yet approved by site administrator. You will receive an email once it is processed.')
            ));

            wp_die();
        }

        // TODO: redirects
        $login_args = array(
            'user_login' => $form_login,
            'user_password' => $form_password,
            'remember' => (bool)$form_remember_me
        );

        $login_result = wp_signon($login_args);

        if (!is_wp_error($login_result)) {
            //wp_safe_redirect($redirect_url);
        } else {
            $status = 1;

            // TODO: errors
        }

        echo json_encode(array(
            'status' => $status,
            'errors' => $errors
        ));

        wp_die();
    }

    /**
     *
     */
    public function ticket_viewed_by_customer() {
        $this->check_nonce();

        $status = 0;

        MKB_Tickets::delete_unread_agent_replies_count_flag($_REQUEST['ticketId']);

        echo json_encode(array(
            'status' => $status
        ));

        wp_die();
    }

    /**
     * Save form config
     */
    public function save_form_config() {
        $this->check_admin_user();

        $status = 0;

        $form_id = isset($_REQUEST['formId']) ? $_REQUEST['formId'] : null;
        $form_config = isset($_REQUEST['formConfig']) ? $_REQUEST['formConfig'] : null;

        if ($form_id && $form_config) {
            MKB_FormsBuilder::save_form_config($form_id, $form_config); // TODO: errors
        } else {
            $status = 1;
        }

        echo json_encode(array(
            'status' => $status
        ));

        wp_die();
    }

    /**
     * Loads tickets list
     */
    public function get_tickets_list() {
        $this->check_nonce();

        $status = 0;

        $group = isset($_REQUEST['group']) ? $_REQUEST['group'] : 'active';

        $tickets_count = MinervaKB_App::instance()->info->get_user_tickets_count();

        $tickets = MKB_Tickets::get_tickets(array('group' => $group));
        $users = MKB_Tickets::get_ticket_list_users($tickets);

        echo json_encode(array(
            'status' => $status,
            'tickets' => $tickets,
            'users' => $users,
            'activeCount' => $tickets_count['active'],
            'closedCount' => $tickets_count['closed'],
            'trashCount' => $tickets_count['trash']
        ));

        wp_die();
    }

    /**
     * Save ticket list UI for user
     */
    public function save_tickets_list_ui_preferences() {
        $this->check_nonce();

        $status = 0;

        $preferences = isset($_POST['preferences']) ? $_POST['preferences'] : null;

        $user = wp_get_current_user();
        update_user_meta( $user->ID, '_mkb_tickets_list_ui_preferences', json_encode($preferences));

        echo json_encode(array(
            'status' => $status
        ));

        wp_die();
    }

    /**
     * Get ticket list UI for user
     */
    public function get_tickets_list_ui_preferences() {
        $this->check_nonce();

        $status = 0;

        $user = wp_get_current_user();
        $preferences = get_user_meta( $user->ID, '_mkb_tickets_list_ui_preferences', true);

        $preferences = $preferences ? json_decode($preferences, true) : null;

        if ($preferences) {
            // int arrays
            $int_array_keys = array(
                'typeFilter',
                'priorityFilter',
                'productFilter',
                'departmentFilter',
                'assigneeFilter',
                'authorFilter',
            );

            foreach($int_array_keys as $key) {
                $preferences[$key] = isset($preferences[$key]) ? $preferences[$key] : array();

                $preferences[$key] = array_map(function($item) {
                    return (int)$item;
                }, $preferences[$key]);
            }

            // int props
            $int_keys = array(
                'ticketsPerPage',
            );

            foreach($int_keys as $key) {
                if (isset($preferences[$key])) {
                    $preferences[$key] = (int)$preferences[$key];
                }
            }
        }

        echo json_encode(array(
            'status' => $status,
            'preferences' => $preferences
        ));

        wp_die();
    }

    /**
     * Checks if any of the tickets are locked by other user
     */
    public function get_tickets_lock_info() {
        $this->check_nonce();

        $status = 0;

        $locked = array();
        $users = array();

        $tickets = isset($_REQUEST['ids']) ? $_REQUEST['ids'] : array();

        if (sizeof($tickets)) {
            foreach($tickets as $id) {
                $lock = wp_check_post_lock($id);

                if ($lock) {
                    $lockingUserId = (int)$lock;
                    $locked[$id] = $lockingUserId;

                    if (!isset($users[$lockingUserId])) {
                        $users[$lockingUserId] = MKB_Tickets::get_ticket_list_user_info($lockingUserId);
                    }
                }
            }
        }

        echo json_encode(array(
            'status' => $status,
            'locked' => empty($locked) ? new stdClass() : $locked,
            'users' => $users
        ));

        wp_die();
    }

    public function tickets_bulk_trash() {
        $this->process_tickets_bulk_action('trash');
    }

    public function tickets_bulk_restore() {
        $this->process_tickets_bulk_action('restore');
    }

    public function tickets_bulk_permanently_delete() {
        $this->process_tickets_bulk_action('delete_permanently');
    }

    private function process_tickets_bulk_action($action) {
        $this->check_nonce();

        $status = 0;

        $tickets = isset($_REQUEST['ticketIds']) ? $_REQUEST['ticketIds'] : array();

        if (sizeof($tickets)) {
            foreach($tickets as $id) {
                switch($action) {
                    case 'trash':
                        wp_trash_post($id);
                        break;

                    case 'restore':
                        wp_untrash_post($id);
                        break;

                    case 'delete_permanently':
                        wp_delete_post($id, true);
                        break;

                    default:
                        break;
                }
            }
        }

        echo json_encode(array(
            'status' => $status
        ));

        wp_die();
    }

    /**
     * Reset form config
     */
    public function reset_form_config() {
        $this->check_admin_user();

        $status = 0;

        $form_id = isset($_REQUEST['formId']) ? $_REQUEST['formId'] : null;

        if ($form_id) {
            MKB_FormsBuilder::reset_form_config($form_id); // TODO: errors
        } else {
            $status = 1;
        }

        echo json_encode(array(
            'status' => $status
        ));

        wp_die();
    }

    /**
     *
     */
    public function get_form_field_html() {
        $this->check_admin_user();

        $status = 0;

        $field_id = isset($_REQUEST['fieldId']) ? $_REQUEST['fieldId'] : null;
        $form_id = isset($_REQUEST['formId']) ? $_REQUEST['formId'] : null;

        $html = '';

        if ($field_id && $form_id) {
            $html = MKB_FormsBuilder::get_field_html($field_id, $form_id); // TODO: errors
        } else {
            $status = 1;
        }

        echo json_encode(array(
            'status' => $status,
            'html' => trim($html)
        ));

        wp_die();
    }
}
