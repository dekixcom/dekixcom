<?php
/**
 * Project: MinervaKB.
 * Copyright: 2015-2017 @KonstruktStudio
 */
class MKB_Users {

    const AGENT_ROLE = 'mkb_support_agent';

    const MANAGER_ROLE = 'mkb_support_manager';

    const CUSTOMER_ROLE = 'mkb_support_user';

    const GUEST_USER_NAME = 'minerva_support_guest_user';

    const PENDING_USER_META_KEY = '_mkb_waiting_for_admin_approval';

    private $user = null;

    public $is_guest = false;

    private $is_customer = false;

    private $is_agent = false;

    private $is_manager = false;

    private $is_admin = false;

    private $agents = array();

    private $managers = array();

    private $customers = array();

    private $current_user_allowed_ticket_filetypes = null;

    private $current_user_ticket_file_limits = null;

    private static $instance = null;

    /**
     * MKB_Users constructor.
     */
    public function __construct() {
        add_action('init', array($this, 'parse_user_info'));

        // permissions-related redirects and other actions
        add_action('current_screen', array($this, 'restrict_admin_screen_access'));
        add_action('current_screen', array($this, 'process_user_actions'));
        add_filter('admin_body_class', array($this, 'admin_body_class'), 999);

        add_action('init', array($this, 'remove_admin_bar'), 100);

        // woo
        add_filter( 'woocommerce_prevent_admin_access', array($this, 'manage_woo_admin_access'));
        add_filter( 'woocommerce_disable_admin_bar', array($this, 'manage_woo_admin_access'));

        // pending users check
        add_filter( 'authenticate', array($this, 'check_for_not_approved_users'), 100, 2);
        add_filter( 'authenticate', array($this, 'check_for_not_allowed_users'), 100, 2);

        // notices
        add_action( 'admin_notices', array($this, 'admin_notices'));
    }

    /**
     * @return MKB_Users|null
     */
    public static function instance() {
        if (self::$instance == null) {
            self::$instance = new MKB_Users();
        }

        return self::$instance;
    }

    /**
     * Stores general user-related data
     */
    public function parse_user_info() {
        $current_user = wp_get_current_user();

        // agents
        $agents_by_role = get_users(array(
            'role'    => self::AGENT_ROLE,
            'orderby' => 'user_nicename',
            'order'   => 'ASC'
        ));
        $agents_by_cap = self::get_users_by_cap('mkb_ticket_assignee');

        if (current_user_can('administrator')) {
            $agents_by_role  = array_merge($agents_by_role, array($current_user));
        }

        $this->agents = array_unique(array_merge($agents_by_role, $agents_by_cap), SORT_REGULAR);

        // managers
        $this->managers = get_users(array(
            'role'    => self::MANAGER_ROLE,
            'orderby' => 'user_nicename',
            'order'   => 'ASC'
        ));

        // customers
        $this->customers = get_users(array(
            'role'    => self::CUSTOMER_ROLE,
            'orderby' => 'user_nicename',
            'order'   => 'ASC'
        ));

        if (is_user_logged_in()) {
            // user
            $this->user = wp_get_current_user();

            $this->is_admin = current_user_can('administrator');
            $this->is_agent = in_array($this->user, $this->agents);
            $this->is_manager = in_array($this->user, $this->managers);
            $this->is_customer = in_array($this->user, $this->customers); // TODO: maybe use actual user settings, not only customer role
        } else {
            // guest
            $this->is_guest = true;
        }
    }

    /**
     * Wrapper for user capability check
     * @param $user
     * @param $cap
     * @return bool
     */
    public static function user_can($user, $cap) {
        if (!$user || !is_object($user) || is_wp_error($user)) {
            return false;
        }

        return $user->has_cap($cap);
    }

    /**
     * @param $cap
     * @return array
     */
    public static function get_users_by_cap($cap) {
        return get_users(array(
                'meta_key' => 'wp_capabilities',
                'meta_value' => $cap,
                'meta_compare' => 'LIKE'
            )
        );
    }

    /**
     *
     */
    public function get_agents() {
        return $this->agents;
    }

    /**
     * @return bool|WP_User
     */
    public static function get_guest_support_user() {
        return get_user_by('login', self::GUEST_USER_NAME);
    }

    public static function is_minerva_support_user() {
        return self::instance()->is_customer;
    }

    /**
     * @return bool|null
     */
    public function can_user_attach_files() {
        if ($this->is_admin || $this->is_manager || $this->is_agent) {
            return true;
        }

        if ($this->is_guest) {
            return MKB_Options::option('tickets_allow_guest_attachments');
        } else if (MKB_Tickets::user_can_create_tickets()) { // TODO: note, this is confusing logic
            return MKB_Options::option('tickets_allow_user_attachments');
        }

        return false;
    }

    /**
     * @return array
     */
    public function get_current_user_allowed_ticket_filetypes() {
        if (isset($this->current_user_allowed_ticket_filetypes)) {
            return $this->current_user_allowed_ticket_filetypes;
        }

        $this->current_user_allowed_ticket_filetypes = array();

        $system_allowed_extensions = array_keys(get_allowed_mime_types());
        $system_allowed_extensions = explode('|', implode('|', $system_allowed_extensions)); // removes |
        $user_category_allowed_extensions_str = '';
        $merge_system_extensions = false;

        if ($this->is_guest) {
            if (MKB_Options::option('tickets_allow_guest_attachments')) {
                $user_category_allowed_extensions_str = MKB_Options::option('tickets_guest_allowed_filetypes');
                $merge_system_extensions = MKB_Options::option('tickets_guest_include_system_filetypes');
            }
        } else {
            // admins & managers
            if ($this->is_admin || $this->is_manager) {
                $user_category_allowed_extensions_str = MKB_Options::option('tickets_admin_allowed_filetypes');
                $merge_system_extensions = true;
            } else if ($this->is_agent) {
                // agents
                $user_category_allowed_extensions_str = MKB_Options::option('tickets_admin_allowed_filetypes');
                $merge_system_extensions = MKB_Options::option('tickets_admin_include_system_filetypes');
            } else if (MKB_Tickets::user_can_create_tickets()) { // TODO: note, this is confusing logic
                // support users & custom roles
                if (MKB_Options::option('tickets_allow_user_attachments')) {
                    $user_category_allowed_extensions_str = MKB_Options::option('tickets_user_allowed_filetypes');
                    $merge_system_extensions = MKB_Options::option('tickets_user_include_system_filetypes');
                }
            }
        }

        $allowed_extensions = array_filter(array_map(function($item) {
            return str_replace('.', '', trim($item));
        }, explode(',', $user_category_allowed_extensions_str)));

        if ($merge_system_extensions) {
            $allowed_extensions = array_merge($allowed_extensions, $system_allowed_extensions);
        }

        $allowed_extensions = array_unique($allowed_extensions);
        sort($allowed_extensions);

        $this->current_user_allowed_ticket_filetypes = $allowed_extensions;

        return $this->current_user_allowed_ticket_filetypes;
    }

    /**
     * @return array|null
     */
    public function get_current_user_ticket_file_limits() {
        if (isset($this->current_user_ticket_file_limits)) {
            return $this->current_user_ticket_file_limits;
        }

        $max_files = 2;
        $max_file_size = 2;
        $system_max_file_size = wp_max_upload_size() / 1024 / 1024;

        if ($this->is_guest) {
            $max_files = MKB_Options::option('tickets_guest_max_files');
            $max_file_size = MKB_Options::option('tickets_guest_max_file_size');
        } else {
            if ($this->is_admin || $this->is_manager) {
                $max_files = MKB_Options::option('tickets_agent_max_files'); // TODO: maybe max system number (if any)
                $max_file_size = $system_max_file_size;
            } else if ($this->is_agent) {
                $max_files = MKB_Options::option('tickets_agent_max_files');
                $max_file_size = MKB_Options::option('tickets_agent_max_file_size');
            } else if ($this->is_customer) {
                $max_files = MKB_Options::option('tickets_user_max_files');
                $max_file_size = MKB_Options::option('tickets_user_max_file_size');
            }
        }

        $this->current_user_ticket_file_limits = array(
            'max_files' => $max_files,
            'max_file_size' => $max_file_size,
        );

        return $this->current_user_ticket_file_limits;
    }

    /**
     * Redirects based on current screen and user permissions
     * TODO: investigate in detail the order (maybe use text file log)
     */
    public function restrict_admin_screen_access() {
        if (defined('DOING_AJAX') && DOING_AJAX) {
            return;
        }

        global $pagenow;
        global $current_screen;

        // support user redirect from dashboard
        if ($this->is_customer && MKB_Options::option('tickets_redirect_support_user_from_admin')) {
            $user_redirect = get_site_url(); // default redirect to /

            $redirect_to_page = MKB_Options::option('tickets_redirect_support_user_from_admin_page');

            if ($redirect_to_page) {
                $user_redirect = get_the_permalink($redirect_to_page);
            }

            wp_redirect($user_redirect);

            return;
        }

        $admin_tickets_list_url = admin_url('edit.php?post_type=mkb_ticket&page=minerva-mkb_ticket-submenu-tickets-list');

        // users redirect from new ticket screen, if they are not allowed
        if (in_array($pagenow, array('post-new.php')) &&
            isset($_GET['post_type']) && $_GET['post_type'] === 'mkb_ticket' &&
            !current_user_can('mkb_create_tickets') &&
            !current_user_can('administrator')) {

            wp_redirect($admin_tickets_list_url);
        }

        // ticket edit screen
        if (in_array($pagenow, array('post.php')) &&
            isset($current_screen->post_type) &&
            $current_screen->post_type === 'mkb_ticket' &&
            !current_user_can('administrator')) {

            $ticket_id = (int)$_REQUEST['post'];

            if (MKB_Tickets::is_ticket_assigned($ticket_id)) {
                // 1. ticket has assignee

                $is_ticket_assigned_to_current_user = MKB_Tickets::is_ticket_assigned_to_user($ticket_id, $this->user->ID);

                // 1.1 agents redirect from tickets assigned to other agents
                if (!$is_ticket_assigned_to_current_user && !current_user_can('mkb_view_others_tickets')) {
                    wp_redirect($admin_tickets_list_url);
                }
            } else {
                // 2. unassigned ticket

                // 2.1 redirect if agent is not allowed to view unassigned tickets
                if (!current_user_can('mkb_view_unassigned_tickets')) {
                    wp_redirect($admin_tickets_list_url);
                }
            }
        }

        // redirect to new tickets list in admin
        if (in_array($pagenow, array('edit.php')) &&
            (isset($_GET['post_type']) && $_GET['post_type'] === 'mkb_ticket') &&
            !isset($_GET['post']) &&
            !isset($_GET['page'])) {

            wp_redirect($admin_tickets_list_url);
            exit(); // needed here, but not on other redirects
        }
    }

    public function process_user_actions() {
        if (!is_admin() || !isset($_REQUEST['mkb_nonce']) || !isset($_REQUEST['mkb_action']) || !current_user_can('administrator')) {
            return;
        }

        if (!wp_verify_nonce($_REQUEST['mkb_nonce'], 'mkb_user_edit_nonce')) {
            return;
        }

        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;

        $support_user_id = isset($_REQUEST['user_id']) ? $_REQUEST['user_id'] : null;

        if (!$support_user_id) {
            return;
        }

        $support_user = get_user_by('ID', $support_user_id);

        if (!$support_user) {
            return;
        }

        $action = $_REQUEST['mkb_action'];

        $user_action_notices = array();

        switch ($action) {
            case 'approve-support-user':
                delete_user_meta($support_user_id, MKB_Users::PENDING_USER_META_KEY);

                $support_user->add_role(MKB_Users::CUSTOMER_ROLE);

                if (MKB_Options::option('email_user_registration_approved_switch')) {
                    $email_template_context = array(
                        'user_firstname' => $support_user->first_name
                    );

                    MKB_Emails::instance()->send(
                        $support_user->user_email,
                        MKB_Emails::EMAIL_TYPE_USER_REGISTRATION_APPROVED,
                        $email_template_context
                    );
                }

                array_push($user_action_notices, array(
                    'message' => __('User registration has been approved.', 'minerva-kb'),
                    'type' => 'success'
                ));
                break;

            case 'deny-support-user':

                if (MKB_Options::option('email_user_registration_denied_switch')) {
                    $email_template_context = array(
                        'user_firstname' => $support_user->first_name
                    );

                    MKB_Emails::instance()->send(
                        $support_user->user_email,
                        MKB_Emails::EMAIL_TYPE_USER_REGISTRATION_DENIED,
                        $email_template_context
                    );
                }

                $user_id = $support_user->ID;

                wp_delete_user($support_user->ID);
                wpmu_delete_user($user_id);

                array_push($user_action_notices, array(
                    'message' => __('User registration has been denied. Account has been deleted.', 'minerva-kb'),
                    'type' => 'success'
                ));
                break;

            default:
                break;
        }

        set_transient("_mkb_user_actions_notices_for_user_{$user_id}", $user_action_notices);

        wp_redirect(admin_url('users.php'));
    }

    /**
     * TODO: move admin notices to separate module (users & ticket admin page)
     */
    public function admin_notices() {
        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;
        $transient_name = "_mkb_user_actions_notices_for_user_{$user_id}";

        if ($save_notices = get_transient($transient_name)) {
            if (sizeof($save_notices)) {
                foreach($save_notices as $notice) {
                    MKB_TemplateHelper::render_admin_notice($notice['message'], $notice['type']);
                }
            }

            delete_transient($transient_name);
        }
    }

    /**
     *
     */
    public function remove_admin_bar() {
        if ($this->is_minerva_support_user() && MKB_Options::option('tickets_hide_admin_bar_for_support_user')) {
            show_admin_bar(false);
        }
    }

    /**
     * @param $user
     * @param $username
     * @return WP_Error
     */
    public function check_for_not_approved_users($user, $username) {
        if ($user && !is_wp_error($user) && isset($user->data) && self::is_user_pending_admin_approval($user->data->ID)) {
            return new WP_Error( 'support_account_not_yet_approved','Your registration request is waiting for admin approval');
        }

        return $user;
    }

    /**
     * @param $user
     * @param $username
     * @return WP_Error
     */
    public function check_for_not_allowed_users($user, $username) {
        if ($username === self::GUEST_USER_NAME) {
            return new WP_Error( 'guest_user_login_attempt','This user cannot login');
        }

        return $user;
    }

    /**
     * @param $email_or_login
     * @return bool|WP_User
     */
    public static function get_user_by_email_or_login($email_or_login) {
        $user = get_user_by('email', $email_or_login);

        if (!$user) {
            $user = get_user_by('login', $email_or_login);
        }

        return $user;
    }

    /**
     * @param $user_id
     * @return mixed
     */
    public static function is_user_pending_admin_approval($user_id) {
        return get_user_meta($user_id,self::PENDING_USER_META_KEY,true);
    }

    /**
     * @param $classes
     * @return string
     */
    public function admin_body_class($classes) {
        if (!current_user_can('mkb_create_tickets')) {
            $classes = $classes . ' mkb-no-create-ticket';
        }

        return $classes;
    }

    public function manage_woo_admin_access($prevent) {
        if ($this->is_agent || $this->is_manager) {
            return false;
        }

        return $prevent;
    }
    
    public static function get_known_capabilities() {
        $caps = array(
            // general WordPress capabilities
            'wp' => array(
                'read' => __('Allows access to Administration Screens options: Dashboard, Users > Your Profile', 'minerva-kb'),
                'upload_files' => __('Allows access to Administration Screens options: Media, Media > Add New', 'minerva-kb')
            ),

            // Knowledge Base access
            'kb' => array(
                'mkb_edit_articles' => __('General access to Knowledge Base admin menu. Allows to create new articles and submit them for review.', 'minerva-kb'),
                'mkb_edit_published_articles' => __('Allows to edit own published KB articles.', 'minerva-kb'),
                'mkb_edit_others_articles' => __('Allows to edit KB articles created by other authors.', 'minerva-kb'),
                'mkb_edit_private_articles' => __('Allows to edit private KB articles created by other authors.', 'minerva-kb'),

                'mkb_publish_articles' => __('Allows to publish KB articles.', 'minerva-kb'),

                'mkb_read_private_articles' => __('Allows access to KB articles marked as private', 'minerva-kb'),

                'mkb_manage_sorting' => __('Allows to manage KB Sorting', 'minerva-kb'),

                'mkb_delete_articles' => __('General permission to delete own, not published KB articles.', 'minerva-kb'),
                'mkb_delete_published_articles' => __('Allows to delete published KB articles.', 'minerva-kb'),
                'mkb_delete_others_articles' => __('Allows to delete KB articles created by other authors.', 'minerva-kb'),
                'mkb_delete_private_articles' => __('Allows to delete private KB articles created by other authors.', 'minerva-kb'),

                'mkb_assign_kb_topics' => __('Allows to assign KB topics to articles.', 'minerva-kb'),
                'mkb_assign_kb_tags' => __('Allows to assign KB tags to articles.', 'minerva-kb'),
                'mkb_assign_kb_versions' => __('Allows to assign versions to articles.', 'minerva-kb'),

                'mkb_manage_kb_topics' => __('Allows to manage (create, edit, delete) KB topics.', 'minerva-kb'),
                'mkb_manage_kb_tags' => __('Allows to manage (create, edit, delete) KB tags.', 'minerva-kb'),
                'mkb_manage_kb_versions' => __('Allows to manage (create, edit, delete) KB versions.', 'minerva-kb'),
            ),

            // FAQ access
            'faq' => array(
                'mkb_edit_faqs' => __('General access to FAQ admin menu. Allows to create new FAQ items and submit them for review.', 'minerva-kb'),
                'mkb_edit_published_faqs' => __('Allows to edit own published FAQ answers.', 'minerva-kb'),
                'mkb_edit_others_faqs' => __('Allows to edit FAQ answers created by other authors.', 'minerva-kb'),
                'mkb_edit_private_faqs' => __('Allows to edit private FAQ answers created by other authors.', 'minerva-kb'),

                'mkb_publish_faqs' => __('Allows to publish FAQ answers.', 'minerva-kb'),

                'mkb_read_private_faqs' => __('Allows access to FAQ answers marked as private', 'minerva-kb'),

                'mkb_manage_faq_sorting' => __('Allows to manage FAQ Sorting', 'minerva-kb'),

                'mkb_assign_faq_categories' => __('Allows to assign FAQ categories to answers.', 'minerva-kb'),
                'mkb_manage_faq_categories' => __('Allows to manage (create, edit, delete) FAQ categories.', 'minerva-kb'),

                'mkb_delete_faqs' => __('General permission to delete own, not published FAQ answers.', 'minerva-kb'),
                'mkb_delete_published_faqs' => __('Allows to delete published FAQ answers.', 'minerva-kb'),
                'mkb_delete_others_faqs' => __('Allows to delete FAQ answers created by other authors.', 'minerva-kb'),
                'mkb_delete_private_faqs' => __('Allows to delete private FAQ answers created by other authors.', 'minerva-kb'),
            ),

            // Glossary access
            'glossary' => array(
                'mkb_edit_glossary_terms' => __('General access to Glossary admin menu. Allows to create new Glossary items and submit them for review.', 'minerva-kb'),

                'mkb_edit_published_glossary_terms' => __('Allows to edit own Glossary terms that are already published.', 'minerva-kb'),
                'mkb_edit_others_glossary_terms' => __('Allows to edit Glossary terms created by other authors.', 'minerva-kb'),
                'mkb_edit_private_glossary_terms' => __('Allows to edit private Glossary terms created by other authors.', 'minerva-kb'),
                'mkb_publish_glossary_terms' => __('Allows to publish Glossary terms.', 'minerva-kb'),

                'mkb_read_private_glossary_terms' => __('Allows to view Glossary terms marked as private on the client-side', 'minerva-kb'),

                'mkb_delete_glossary_terms' => __('General permission to delete own, not published Glossary terms.', 'minerva-kb'),
                'mkb_delete_published_glossary_terms' => __('Allows to delete published Glossary terms.', 'minerva-kb'),
                'mkb_delete_others_glossary_terms' => __('Allows to delete Glossary terms created by other authors.', 'minerva-kb'),
                'mkb_delete_private_glossary_terms' => __('Allows to delete private Glossary terms created by other authors.', 'minerva-kb'),
            ),
        );

        if (!MKB_Options::option('tickets_disable_tickets')) {
            $caps = array_merge($caps, array(
                // Tickets access
                'tickets' => array(
                    // used as alias for WP edit_posts cap (access to menu, list & edit)
                    'mkb_view_tickets' => __('General access to tickets in admin. Displays only tickets assigned to agent. Required for any other permission related to ticket processing.', 'minerva-kb'), // + required for admin menu, general cap for all ticket modification, can still reply

                    // tickets list visibility & count
                    'mkb_view_unassigned_tickets' => __('Allows user to view tickets that are not yet assigned.', 'minerva-kb'),

                    // assignment caps
                    'mkb_assign_unassigned_tickets_to_self' => __('Allows user to assign unassigned tickets to self.', 'minerva-kb'), // + allows agents to assign unassigned tickets to self, not necessary if can assign tickets

                    // replies
                    'mkb_reply_to_tickets' => __('Allows user to reply to assigned tickets.', 'minerva-kb'),

                    // custom ticket cap
//                'mkb_create_tickets' => __('TODO.', 'minerva-kb'), // TODO: add with create ticket in admin + Open ticket link permission, Note: this is NOT a WP post cap. Hides Open Ticket links and redirects users without permission from new ticket page

                    // edit tickets (taxonomies, etc.)
                    'mkb_view_others_tickets' => __('Allows user to view tickets assigned to other agents.', 'minerva-kb'), // + agents, controls if agent can see tickets assigned to other agents (including ticket count / ticket list / edit screen)
                    'mkb_modify_others_tickets' => __('Allows user to modify fields/taxonomies of tickets, assigned to other agents. Also requires capabilities to edit each field/taxonomy (type, priority, etc.)', 'minerva-kb'), // + custom, differs from WP post cap. allows to modify other agent tickets (for managers / admins)
                    'mkb_reply_to_others_tickets' => __('Allows user to reply to any open ticket.', 'minerva-kb'),
                    'mkb_assign_tickets' => __('Allows to change ticket assignee field.', 'minerva-kb'), // + allows to change assignee

                    'mkb_delete_tickets' => __('Allows to delete tickets. Any ticket visible to agent can be deleted.', 'minerva-kb'),

                    'mkb_view_tickets_dashboard' => __('Allows to access Tickets Dashboard admin page.', 'minerva-kb'), // -

                    // assignment src / target
                    'mkb_ticket_assignee' => __('Specifies if tickets can be assigned to user.', 'minerva-kb'),
                    'mkb_ticket_author' => __('Specifies if user can be selected as ticket author.', 'minerva-kb'),
                ),

                // ticket taxonomies
                'tickets_tax' => array(
                    'mkb_assign_ticket_types' => __('Allows to assign Types to tickets.', 'minerva-kb'),
                    'mkb_assign_ticket_priorities' => __('Allows to assign Priorities to tickets.', 'minerva-kb'),
                    'mkb_assign_ticket_departments' => __('Allows to assign Departments to tickets.', 'minerva-kb'),
                    'mkb_assign_ticket_products' => __('Allows to assign Products to tickets.', 'minerva-kb'),
                    'mkb_assign_ticket_tags' => __('Allows to assign Tags to tickets.', 'minerva-kb'),

                    'mkb_manage_ticket_types' => __('Allows to manage (create, edit, delete) Ticket Types.', 'minerva-kb'),
                    'mkb_manage_ticket_priorities' => __('Allows to manage (create, edit, delete) Ticket Priorities.', 'minerva-kb'),
                    'mkb_manage_ticket_departments' => __('Allows to manage (create, edit, delete) Ticket Departments.', 'minerva-kb'),
                    'mkb_manage_ticket_products' => __('Allows to manage (create, edit, delete) Ticket Products.', 'minerva-kb'),
                    'mkb_manage_ticket_tags' => __('Allows to manage (create, edit, delete) Ticket Tags.', 'minerva-kb'),

                    // TODO: ticket channels - investigate
                    // 'mkb_assign_ticket_statuses' => __('Allows to assign Statuses to tickets.', 'minerva-kb'),
                    // 'mkb_manage_ticket_statuses' => __('Allows to manage (create, edit, delete) Ticket Statuses.', 'minerva-kb'),
                    // 'mkb_assign_ticket_channels' => __('TODO.', 'minerva-kb'), // ???
                    // 'mkb_manage_ticket_channels' => __('TODO.', 'minerva-kb'),  // agent / manager only
                ),

                // canned responses
                'tickets_canned' => array(
                    'mkb_edit_canned_responses' => __('General access to Canned Response edit screen.', 'minerva-kb'),
                    'mkb_edit_published_canned_responses' => __('Allows to edit own published Canned Responses.', 'minerva-kb'),
                    'mkb_edit_others_canned_responses' => __('Allows to edit Canned Responses created by other agents.', 'minerva-kb'),
                    'mkb_edit_private_canned_responses' => __('Allows to edit private Canned Responses created by other agents.', 'minerva-kb'),

                    'mkb_publish_canned_responses' => __('Allows to publish Canned Responses.', 'minerva-kb'),

                    'mkb_delete_canned_responses' => __('General permission to delete own, not published Canned Responses.', 'minerva-kb'),
                    'mkb_delete_published_canned_responses' => __('Allows to delete published Canned Responses.', 'minerva-kb'),
                    'mkb_delete_others_canned_responses' => __('Allows to delete Canned Responses created by other agents.', 'minerva-kb'),
                    'mkb_delete_private_canned_responses' => __('Allows to delete private Canned Responses created by other agents.', 'minerva-kb'),

                    'mkb_assign_canned_response_categories' => __('Allows to assign Canned Response Categories.', 'minerva-kb'),

                    'mkb_manage_canned_response_categories' => __('Allows to manage (create, edit, delete) Canned Response Categories.', 'minerva-kb'),
                )
            ));
        }

        return $caps;
    }

    public static function get_default_caps_preset_for_role($role) {
        $preset = null;

        switch($role) {
            case 'contributor':
                $preset = array(
                    // KB
                    'mkb_edit_articles',
                    'mkb_delete_articles',

                    // FAQ
                    'mkb_edit_faqs',
                    'mkb_delete_faqs',

                    // Glossary
                    'mkb_edit_glossary_terms',
                    'mkb_delete_glossary_terms',
                );
                break;

            case 'author':
                $preset = array_merge(
                    self::get_default_caps_preset_for_role('contributor'),
                    array(
                        // KB
                        'mkb_edit_published_articles',

                        'mkb_publish_articles',

                        'mkb_delete_published_articles',

                        'mkb_assign_kb_topics',
                        'mkb_assign_kb_tags',
                        'mkb_assign_kb_versions',

                        // FAQ
                        'mkb_edit_published_faqs',

                        'mkb_publish_faqs',

                        'mkb_delete_published_faqs',

                        'mkb_assign_faq_categories',

                        // Glossary
                        'mkb_edit_published_glossary_terms',

                        'mkb_publish_glossary_terms',

                        'mkb_delete_published_glossary_terms',
                    )
                );
                break;

            case 'editor':
                $preset = array_merge(
                    self::get_default_caps_preset_for_role('author'),
                    array(
                        // KB
                        'mkb_read_private_articles',

                        'mkb_edit_others_articles',
                        'mkb_edit_private_articles',

                        'mkb_delete_others_articles',
                        'mkb_delete_private_articles',

                        'mkb_manage_kb_topics',
                        'mkb_manage_kb_tags',
                        'mkb_manage_kb_versions',

                        // FAQ
                        'mkb_read_private_faqs',

                        'mkb_edit_others_faqs',
                        'mkb_edit_private_faqs',

                        'mkb_delete_others_faqs',
                        'mkb_delete_private_faqs',

                        'mkb_manage_faq_categories',

                        // Glossary
                        'mkb_read_private_glossary_terms',

                        'mkb_edit_others_glossary_terms',
                        'mkb_edit_private_glossary_terms',

                        'mkb_delete_others_glossary_terms',
                        'mkb_delete_private_glossary_terms',
                    )
                );
                break;

            /**
             * Support User
             */
            case 'mkb_support_user':
                $preset = array(
                    'read',
                    'mkb_ticket_author',
                );
                break;

            /**
             * Support Agent
             */
            case 'mkb_support_agent':
                $preset = array_merge(
                    self::get_default_caps_preset_for_role('contributor'),
                    array(
                        // wp
                        'read',
                        'upload_files',

                        // Tickets
                        'mkb_view_tickets',
                        'mkb_view_unassigned_tickets',

                        'mkb_assign_unassigned_tickets_to_self',

                        'mkb_ticket_assignee',

                        // replies
                        'mkb_reply_to_tickets',

                        // canned
                        'mkb_edit_canned_responses',
                        'mkb_edit_others_canned_responses',
                        'mkb_edit_published_canned_responses',

                        'mkb_publish_canned_responses',

                        'mkb_delete_canned_responses',
                        'mkb_delete_published_canned_responses',

                        'mkb_assign_canned_response_categories',

                        // tax
                        'mkb_assign_ticket_types',
                        'mkb_assign_ticket_priorities',
                        'mkb_assign_ticket_departments',
                        'mkb_assign_ticket_products',
                        'mkb_assign_ticket_tags',
                    )
                );
                break;

            /**
             * Support Agent
             */
            case 'mkb_support_manager':
                $preset = array_merge(
                    self::get_default_caps_preset_for_role('mkb_support_agent'),
                    array(
                        'mkb_view_others_tickets',

                        'mkb_modify_others_tickets',

                        'mkb_delete_tickets',

                        'mkb_view_tickets_dashboard',

                        'mkb_assign_tickets',

                        'mkb_reply_to_others_tickets',

                        // tax
                        'mkb_manage_ticket_types',
                        'mkb_manage_ticket_priorities',
                        'mkb_manage_ticket_departments',
                        'mkb_manage_ticket_products',
                        'mkb_manage_ticket_tags',

                        // canned
                        'mkb_edit_private_canned_responses',

                        'mkb_delete_others_canned_responses',
                        'mkb_delete_private_canned_responses',

                        // canned response categories
                        'mkb_manage_canned_response_categories',
                    )
                );
                break;

            default:
                break;
        }

        return $preset;
    }

    /**
     * @param $role
     * @param $caps
     * @return bool
     */
    public static function update_role_permissions($role, $caps) {
        $edited_role = get_role($role);

        if (!$edited_role) {
            return false;
        }

        foreach($caps as $cap => $is_granted) {
            $is_granted = filter_var($is_granted, FILTER_VALIDATE_BOOLEAN);

            if ($is_granted) {
                $edited_role->add_cap($cap);
            } else {
                $edited_role->remove_cap($cap);
            }
        }

        return true;
    }

    /**
     * Creates required roles and caps on plugin activation
     */
    public static function create_users_and_caps() {
        $shared_wp_caps = array(
            'read',
            'upload_files',
        );

        /**
         * Knowledge Base caps
         */
        $kb_contributor_caps = array(
            'mkb_read_article',
            'mkb_edit_article',
            'mkb_edit_articles',
            'mkb_delete_article',
            'mkb_delete_articles',
        );

        $kb_author_caps = array(
            'mkb_edit_published_articles',
            'mkb_publish_articles',
            'mkb_delete_published_articles',

            'mkb_assign_kb_topics',
            'mkb_assign_kb_tags',
            'mkb_assign_kb_versions',
        );

        $kb_editor_caps = array(
            'mkb_edit_others_articles',
            'mkb_read_private_articles',
            'mkb_edit_private_articles',
            'mkb_delete_private_articles',
            'mkb_delete_others_articles',

            'mkb_manage_kb_topics',
            'mkb_manage_kb_tags',
            'mkb_manage_kb_versions',
        );

        $kb_caps = array_merge(
            $kb_contributor_caps,
            $kb_author_caps,
            $kb_editor_caps
        );

        /**
         * FAQ caps
         */
        $faq_contributor_caps = array(
            'mkb_read_faq',
            'mkb_edit_faq',
            'mkb_edit_faqs',
            'mkb_delete_faq',
            'mkb_delete_faqs',
        );

        $faq_author_caps = array(
            'mkb_edit_published_faqs',
            'mkb_publish_faqs',
            'mkb_delete_published_faqs',

            'mkb_assign_faq_categories',
        );

        $faq_editor_caps = array(
            'mkb_edit_others_faqs',
            'mkb_read_private_faqs',
            'mkb_edit_private_faqs',
            'mkb_delete_private_faqs',
            'mkb_delete_others_faqs',

            'mkb_manage_faq_categories',
        );

        $faq_caps = array_merge(
            $faq_contributor_caps,
            $faq_author_caps,
            $faq_editor_caps
        );

        /**
         * Glossary caps
         */
        $glossary_contributor_caps = array(
            'mkb_read_glossary_term',
            'mkb_edit_glossary_term',
            'mkb_edit_glossary_terms',
            'mkb_delete_glossary_term',
            'mkb_delete_glossary_terms',
        );

        $glossary_author_caps = array(
            'mkb_edit_published_glossary_terms',
            'mkb_publish_glossary_terms',
            'mkb_delete_published_glossary_terms',
        );

        $glossary_editor_caps = array(
            'mkb_edit_others_glossary_terms',
            'mkb_read_private_glossary_terms',
            'mkb_edit_private_glossary_terms',
            'mkb_delete_private_glossary_terms',
            'mkb_delete_others_glossary_terms',
        );

        $glossary_caps = array_merge(
            $glossary_contributor_caps,
            $glossary_author_caps,
            $glossary_editor_caps
        );

        $ticket_caps = array(
            // WP post-based caps
            'mkb_view_tickets', // + wp general cap for tickets admin menu, needed for agents / users
//            'mkb_read_private_tickets', // for future use
//            'mkb_edit_private_tickets', // for future use
//            'mkb_delete_private_tickets', // for future use
            'mkb_delete_ticket',
            'mkb_delete_tickets',

            'mkb_view_tickets_dashboard', // -

            // custom ticket caps
            'mkb_create_tickets', // + Open ticket link permission, Note: this is NOT a WP post cap. Hides Open Ticket links and redirects users without permission from new ticket page

            // tickets list visibility & count
            'mkb_view_unassigned_tickets', // + agents, controls if agent can see unassigned tickets in admin
            'mkb_view_others_tickets', // + agents, controls if agent can see tickets assigned to other agents (including ticket count / ticket list / edit screen)

            // edit tickets (taxonomies, etc.)
            'mkb_modify_others_tickets', // + custom, differs from WP post cap. allows to modify other agent tickets (for managers / admins)

            // assignment src / target
            'mkb_ticket_assignee',
            'mkb_ticket_author',

            // assignment caps
            'mkb_assign_tickets', // + allows to change assignee
            'mkb_assign_unassigned_tickets_to_self', // + allows agents to assign unassigned tickets to self, not necessary if can assign tickets

            // replies
            'mkb_reply_to_tickets', // + allows agent to reply to assigned tickets
            'mkb_reply_to_others_tickets', // + allows agent to reply to other agent tickets

            // + canned responses
            'mkb_read_canned_response',
            'mkb_edit_canned_response',
            'mkb_edit_canned_responses',
            'mkb_edit_others_canned_responses',
            'mkb_edit_published_canned_responses',
            'mkb_publish_canned_responses',
            'mkb_read_private_canned_responses',
            'mkb_edit_private_canned_responses',
            'mkb_delete_private_canned_responses',
            'mkb_delete_canned_response',
            'mkb_delete_canned_responses',
            'mkb_delete_published_canned_responses',
            'mkb_delete_others_canned_responses',

            // canned response categories
            'mkb_manage_canned_response_categories', // agent / manager only
            'mkb_assign_canned_response_categories', // agent only

            // statuses
            'mkb_manage_ticket_statuses', // agent only, for future use, currently statuses are built-in
            'mkb_assign_ticket_statuses', // - agent / user, change 'open', 'closed', etc.

            // types
            'mkb_manage_ticket_types', // agent / manager only
            'mkb_assign_ticket_types',

            // channels
            'mkb_manage_ticket_channels',  // agent / manager only
            'mkb_assign_ticket_channels',

            // priorities
            'mkb_manage_ticket_priorities',  // agent / manager only
            'mkb_assign_ticket_priorities',

            // departments
            'mkb_manage_ticket_departments',  // agent / manager only
            'mkb_assign_ticket_departments',

            // products
            'mkb_manage_ticket_products',  // agent / manager only
            'mkb_assign_ticket_products',

            // tags
            'mkb_manage_ticket_tags',
            'mkb_assign_ticket_tags'
        );

        /**
         * Agent caps
         */
        $agent_capabilities = array_merge(
            $shared_wp_caps,
            $kb_contributor_caps,
            $faq_contributor_caps,
            $glossary_contributor_caps,
            array(
                'mkb_view_tickets',  // required for admin menu

//                'mkb_create_tickets',

                'mkb_view_unassigned_tickets',

//                'mkb_ticket_author',
                'mkb_ticket_assignee', // can be assigned tickets to
                'mkb_assign_unassigned_tickets_to_self',

                // replies
                'mkb_reply_to_tickets',

                // canned
//                'mkb_read_canned_response',
//                'mkb_edit_canned_response',
                'mkb_edit_canned_responses',
                'mkb_edit_others_canned_responses',
                'mkb_edit_published_canned_responses',
                'mkb_publish_canned_responses',
                'mkb_read_private_canned_responses',
                'mkb_edit_private_canned_responses',
                'mkb_delete_private_canned_responses',
//                'mkb_delete_canned_response',
                'mkb_delete_canned_responses',
                'mkb_delete_published_canned_responses',
                'mkb_delete_others_canned_responses',

                // tax
//                'mkb_assign_ticket_statuses',
                'mkb_assign_ticket_types',
                'mkb_assign_ticket_channels',
                'mkb_assign_ticket_priorities',
                'mkb_assign_ticket_departments',
                'mkb_assign_ticket_products',
                'mkb_assign_ticket_tags'
            )
        );

        $manager_caps = array_merge(
            $shared_wp_caps,

            $kb_contributor_caps,
            $faq_contributor_caps,
            $glossary_contributor_caps,

            $ticket_caps
        );

        /**
         * Support User caps
         */
        $support_user_capabilities = array(
            'read',
            'mkb_ticket_author',
        );

        $base_minerva_caps = array_merge(
            $shared_wp_caps,
            $kb_caps,
            $faq_caps,
            $glossary_caps
        );

        // editor
        $editor = get_role('editor');

        if ($editor) {
            foreach ($base_minerva_caps as $cap) {
                $editor->add_cap($cap);
            }
        }

        // contributor
        $contributor = get_role('contributor');

        if ($contributor) {
            // edits his own stuff, no publish
            $contributor_caps = array_merge(
                $kb_contributor_caps,
                $faq_contributor_caps,
                $glossary_contributor_caps
            );

            foreach ($contributor_caps as $cap) {
                $contributor->add_cap($cap);
            }
        }

        // author
        $author = get_role('author');

        if ($author) {
            // edits and publishes his own stuff
            $author_caps = array_merge(
                $kb_contributor_caps,
                $kb_author_caps,
                $faq_contributor_caps,
                $faq_author_caps,
                $glossary_contributor_caps,
                $glossary_author_caps
            );

            foreach ($author_caps as $cap) {
                $author->add_cap($cap);
            }
        }

        // users
        $support_user_role = get_role(self::CUSTOMER_ROLE);

        if (!$support_user_role) {
            $support_user_role = add_role(self::CUSTOMER_ROLE, __('Minerva Support User', 'minerva-kb'), array('read' => true));
        }

        if ($support_user_role) {
            foreach ($ticket_caps as $cap) {
                $support_user_role->remove_cap($cap);
            }

            foreach($support_user_capabilities as $cap) {
                $support_user_role->add_cap($cap);
            }
        }

        // agents
        $support_agent_role = get_role(self::AGENT_ROLE);

        if (!$support_agent_role) {
            $support_agent_role = add_role(self::AGENT_ROLE, __('Minerva Support Agent', 'minerva-kb'), array('read' => true));
        }

        if ($support_agent_role) {
            foreach ($ticket_caps as $cap) {
                $support_agent_role->remove_cap($cap);
            }

            foreach($agent_capabilities as $cap) {
                $support_agent_role->add_cap($cap);
            }
        }

        // managers
        $support_manager_role = get_role(self::MANAGER_ROLE);

        if (!$support_manager_role) {
            $support_manager_role = add_role(self::MANAGER_ROLE, __('Minerva Support Manager', 'minerva-kb'), array('read' => true));
        }

        if ($support_manager_role) {
            foreach ($base_minerva_caps as $cap) {
                $support_manager_role->remove_cap($cap);
            }

            foreach($manager_caps as $cap) {
                $support_manager_role->add_cap($cap);
            }
        }

        // admins
        $admin_role = get_role( 'administrator');

        if ($admin_role) {
            foreach($base_minerva_caps as $cap) {
                $admin_role->add_cap($cap);
            }

            foreach($ticket_caps as $cap) {
                $admin_role->add_cap($cap);
            }
        }

        if (!username_exists(self::GUEST_USER_NAME)) {
            remove_action('register_new_user', 'wp_send_new_user_notifications');
            $guest_support_user_id = register_new_user(self::GUEST_USER_NAME, 'dummy@email.com');
            add_action('register_new_user', 'wp_send_new_user_notifications');
        }
    }

    // TODO: reset caps to default set via admin screen
    // TODO: caps management page (with user-friendly naming, descriptions and only minerva caps)

}
