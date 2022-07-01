<?php
/**
 * Project: MinervaKB.
 * Copyright: 2015-2017 @KonstruktStudio
 */

// legacy WordPress support
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/polyfill.php');

// utils
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/utils.php');

// abstract
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/abstract/admin-edit-screen.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/abstract/admin-menu-page.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/abstract/admin-submenu-page.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/abstract/shortcode.php');

// importer
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/import/import.php');

// global modules
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/options.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/page-options.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/info.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/users.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/emails.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/attachments.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/tickets.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/woocommerce.php');

// helpers
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/helpers/settings-builder.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/helpers/template-helper.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/helpers/forms-builder.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/helpers/analytics.php');

// modules
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/api.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/cpt.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/restrict.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/content.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/actions.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/assets.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/styles.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/ajax.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/widgets.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/floating-helper.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/updates.php');

// shortcodes
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/shortcodes/search.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/shortcodes/topics.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/shortcodes/topic.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/shortcodes/tip.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/shortcodes/info.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/shortcodes/warning.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/shortcodes/anchor.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/shortcodes/related.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/shortcodes/faq.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/shortcodes/guestpost.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/shortcodes/article-content.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/shortcodes/faq-content.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/shortcodes/glossary.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/shortcodes/recently-viewed-articles.php');
// template parts shortcodes
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/shortcodes/topic-tmpl.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/shortcodes/user-tickets-list.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/shortcodes/create-ticket.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/shortcodes/create-ticket-link.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/shortcodes/login-register-form.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/shortcodes/logout.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/shortcodes.php');

// elementor
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/elementor/elementor.php');

// block editor files
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/blocks/index.php');

// admin menu pages and edit screens
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/pages/admin.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/pages/settings.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/pages/dashboard.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/pages/dashboard-tickets.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/pages/sorting.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/pages/faq-sorting.php'); // TODO: separate terms tree from sorting pages
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/pages/uninstall.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/pages/page.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/pages/article.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/pages/user.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/pages/glossary.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/pages/ticket.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/pages/ticket-taxonomies.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/pages/tickets-list.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/pages/form-editor.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/pages/permissions.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/pages/welcome.php');

// page templates
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/page-templates.php');

/**
 * Class MinervaKB_App
 * Main App Controller,
 * creates all module instances and passes down dependencies
 */
class MinervaKB_App {
    private static $instance = null;

    /**
     * @return MinervaKB_App|null
     * @deprecated 2.0.0 Use instance() instead
     */
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new MinervaKB_App();
        }

        return self::$instance;
    }

    /**
     * @return MinervaKB_App|null
     */
    public static function instance() {
        if (self::$instance == null) {
            self::$instance = new MinervaKB_App();
        }

        return self::$instance;
    }

	// holds current render info
	public $info;

    // users management API
    public $users;

    // emails management
    public $emails;

    // Woo integrations
    public $woocommerce;

	// custom post types controller
	private $cpt;

	// restriction module
	public $restrict;

	// manages content rendering
	public $content;

	// manages content parts rendering via actions
	public $actions;

	// inline styles manager
	public $inline_styles;

	// assets manager
	private $assets;

	// ajax manager
	private $ajax;

	// sidebars and widgets manager
	private $widgets;

	// shortcodes manager
	public $shortcodes;

	// analytics manager
	private $analytics;

	// floating helper
	public $floating_helper;

	// auto updates
	public $updates;

	// admin menu controller
	private $admin_page;

	// form editor
	private $form_editor_page;

	// permissions pge
	private $permissions_page;

	// settings menu page controller
	private $settings_page;

	// dashboard menu page controller
	private $dashboard_page;

	// tickets dashboard page controller
	private $dashboard_tickets_page;

	// welcome menu page controller
	private $welcome_page;

	// sorting menu page controller
	private $sorting_page;

	// FAQ sorting menu page controller
	private $faq_sorting_page;

	// uninstall menu page controller
	private $uninstall_page;

	// page edit screen controller
	private $page_edit;

	// article edit screen controller
	private $article_edit;

	// glossary edit screen controller
	private $glossary_edit;

	// ticket edit screen controller
	private $ticket_edit;

	// ticket tax edit screen controller
	private $ticket_taxonomies_edit;

	// tickets list page
	private $tickets_list;

	// user edit screen controller
	private $user_edit;

    // custom page templates controller
    private $page_templates;

	/**
	 * App entry
	 */
	public function __construct() {

		// global info model
		$this->info = new MinervaKB_Info();

		$this->users = MKB_Users::instance();

		$this->emails = MKB_Emails::instance();

		$this->woocommerce = new MKB_WooCommerce();

		// restrict access functionality
		$this->restrict = new MinervaKB_Restrict( array(
			'info' => $this->info
		) );

		// custom post types
		$this->cpt = new MinervaKB_CPT(array(
			'info' => $this->info,
			'restrict' => $this->restrict
		));

        // inline styles module
        $this->inline_styles = new MinervaKB_DynamicStyles(array(
            'info' => $this->info
        ));

		// client or ajax
		if ($this->info->is_client() || $this->info->is_ajax()) {
			$this->content = new MinervaKB_Content(array(
				'info' => $this->info,
				'restrict' => $this->restrict
			));
		}

		if ($this->info->is_client()) {
			// content hooks
			$this->actions = new MinervaKB_ContentHooks(array(
				'info' => $this->info,
				'restrict' => $this->restrict
			));

			// floating helper
			$this->floating_helper = new MinervaKB_FloatingHelper(array(
				'info' => $this->info
			));
		}

		if ($this->info->is_admin()) {
			$this->analytics = new MinervaKB_Analytics();
		}

		// ajax manager
		$this->ajax = new MinervaKB_Ajax(array(
			'info' => $this->info,
			'analytics' => $this->analytics,
			'restrict' => $this->restrict
		));

		// assets manager
		$this->assets = new MinervaKB_Assets(array(
			'info' => $this->info,
			'inline_styles' => $this->inline_styles,
			'ajax' => $this->ajax
		));

		// shortcodes manager
		$this->shortcodes = new MinervaKB_Shortcodes();

		// widgets manager
		$this->widgets = new MinervaKB_Widgets();

		// page templates
		$this->page_templates = new MinervaKB_PageTemplates();

		/**
		 * Admin menu pages
		 */
		if ($this->info->is_admin()) {

			// admin welcome menu page
			$this->welcome_page = new MinervaKB_WelcomePage( array(
				'info' => $this->info,
				'ajax' => $this->ajax
			) );

			// admin sorting menu page
			$this->sorting_page = new MinervaKB_SortingPage( array(
				'info' => $this->info,
				'ajax' => $this->ajax
			) );

			// admin FAQ sorting menu page
			$this->faq_sorting_page = new MinervaKB_FAQSortingPage( array(
				'info' => $this->info,
				'ajax' => $this->ajax
			) );

			// admin settings menu page
			$this->settings_page = new MinervaKB_SettingsPage( array(
				'info' => $this->info,
				'ajax' => $this->ajax
			) );

			// admin dashboard menu page
			$this->dashboard_page = new MinervaKB_DashboardPage( array(
				'analytics' => $this->analytics
			) );

            if (!MKB_Options::option('tickets_disable_tickets')) {
                // admin tickets dashboard menu page
                $this->dashboard_tickets_page = new MinervaKB_TicketsDashboardPage(array());
            }

            if (!MKB_Options::option('tickets_disable_tickets')) {
                // form editor page
                $this->form_editor_page = new MinervaKB_FormEditorPage();
            }

			// permissions page
			$this->permissions_page = new MinervaKB_PermissionsPage();

			// admin uninstall menu page
			$this->uninstall_page = new MinervaKB_UninstallPage( array(
				'info' => $this->info,
				'ajax' => $this->ajax
			) );

			/**
			 * Edit screens
			 */
			// page edit screen
			$this->page_edit = new MinervaKB_PageEdit();

			// article edit screen
			$this->article_edit = new MinervaKB_ArticleEdit(array(
				'restrict' => $this->restrict
			));

			// glossary edit screen
			$this->glossary_edit = new MinervaKB_GlossaryEdit(array());

            if (!MKB_Options::option('tickets_disable_tickets')) {
                // ticket edit
                $this->tickets_list = new MinervaKB_TicketsListPage(array());
                $this->ticket_edit = new MinervaKB_TicketEdit(array());
                $this->ticket_taxonomies_edit = new MinervaKB_TicketTaxonomyPages(array());
            }

			// user edit screen
			$this->user_edit = new MinervaKB_UserEdit(array());

			// auto updates
			$this->updates = new MinervaKB_AutoUpdate(array(
				'info' => $this->info
			));
		}

		// maybe run migrations
		new MKB_Migrations();
	}
}