<?php
/**
 * Project: MinervaKB.
 * Copyright: 2015-2017 @KonstruktStudio
 */

define('MINERVA_KB_EMAIL_TMPL_DIR', MINERVA_KB_PLUGIN_DIR . 'lib/templates/email/');

class MKB_Emails {

    // admin emails
    const EMAIL_TYPE_ADMIN_NEW_ARTICLE_FEEDBACK = 'admin-new-article-feedback';
    const EMAIL_TYPE_ADMIN_NEW_GUEST_ARTICLE = 'admin-new-guest-article';
    const EMAIL_TYPE_ADMIN_NEW_REGISTRATION_REQUEST = 'admin-new-registration-request';

    // agent emails
    const EMAIL_TYPE_AGENT_TICKET_ASSIGNED = 'agent-ticket-assigned';
    const EMAIL_TYPE_AGENT_TICKET_REPLY_ADDED = 'agent-ticket-reply-added';
    const EMAIL_TYPE_AGENT_TICKET_CLOSED = 'agent-ticket-closed';
    const EMAIL_TYPE_AGENT_TICKET_REOPENED = 'agent-ticket-reopened';

    // guest emails
    const EMAIL_TYPE_GUEST_TICKET_CREATED = 'guest-ticket-created';
    const EMAIL_TYPE_GUEST_TICKET_REPLY_ADDED = 'guest-ticket-reply-added';
    const EMAIL_TYPE_GUEST_TICKET_CLOSED = 'guest-ticket-closed';

    // user account emails
    const EMAIL_TYPE_USER_REGISTRATION_RECEIVED = 'user-registration-received';
    const EMAIL_TYPE_USER_REGISTRATION_APPROVED = 'user-registration-approved';
    const EMAIL_TYPE_USER_REGISTRATION_DENIED = 'user-registration-denied';

    // user ticket emails
    const EMAIL_TYPE_USER_TICKET_CREATED = 'user-ticket-created';
    const EMAIL_TYPE_USER_TICKET_REPLY_ADDED = 'user-ticket-reply-added';
    const EMAIL_TYPE_USER_TICKET_CLOSED = 'user-ticket-closed';

    private static $instance = null;

    /**
     * constructor
     */
    public function __construct() {
        add_action('minerva_email_header', array($this, 'email_header'), 10);
        add_action('minerva_email_footer', array($this, 'email_footer'), 10);

        if (!MKB_Options::option('email_templates_remove_header')) {
            add_action('minerva_email_content_header', array($this, 'email_content_header'), 10);
        }

        if (!MKB_Options::option('email_templates_remove_footer')) {
            add_action('minerva_email_content_footer', array($this, 'email_content_footer'), 10);
        }

        add_action('minerva_email_content_before', array($this, 'email_content_before'), 10);
        add_action('minerva_email_content_after', array($this, 'email_content_after'), 10);
        add_action('minerva_email_content_body', array($this, 'email_content_body'), 10);
        add_action('minerva_email_content_message', array($this, 'email_content_message'), 10);
        add_action('minerva_email_content_action', array($this, 'email_content_action'), 10);
        add_action('minerva_email_content_action_fallback', array($this, 'email_content_action_fallback'), 10);
    }

    /**
     * @return MKB_Emails
     */
    public static function instance() {
        if (self::$instance == null) {
            self::$instance = new MKB_Emails();
        }

        return self::$instance;
    }

    public static function get_allowed_templates() {
        return array(
            self::EMAIL_TYPE_ADMIN_NEW_ARTICLE_FEEDBACK,
            self::EMAIL_TYPE_ADMIN_NEW_GUEST_ARTICLE,
            self::EMAIL_TYPE_ADMIN_NEW_REGISTRATION_REQUEST,
            self::EMAIL_TYPE_AGENT_TICKET_ASSIGNED,
            self::EMAIL_TYPE_AGENT_TICKET_REPLY_ADDED,
            self::EMAIL_TYPE_AGENT_TICKET_CLOSED,
            self::EMAIL_TYPE_AGENT_TICKET_REOPENED,
            self::EMAIL_TYPE_GUEST_TICKET_CREATED,
            self::EMAIL_TYPE_GUEST_TICKET_REPLY_ADDED,
            self::EMAIL_TYPE_GUEST_TICKET_CLOSED,
            self::EMAIL_TYPE_USER_REGISTRATION_RECEIVED,
            self::EMAIL_TYPE_USER_REGISTRATION_APPROVED,
            self::EMAIL_TYPE_USER_REGISTRATION_DENIED,
            self::EMAIL_TYPE_USER_TICKET_CREATED,
            self::EMAIL_TYPE_USER_TICKET_REPLY_ADDED,
            self::EMAIL_TYPE_USER_TICKET_CLOSED,
        );
    }

    public static function get_email_templates_config() {
        return array(
            // admin emails
            self::EMAIL_TYPE_ADMIN_NEW_ARTICLE_FEEDBACK => array(
                'enabled_option' => 'email_notify_feedback_switch', // legacy option
                'subject_option' => 'email_notify_feedback_subject', // legacy option
                'body_option' => 'email_admin_new_article_feedback_message',
                'action_label_option' => 'email_admin_new_article_feedback_action_label',
            ),
            self::EMAIL_TYPE_ADMIN_NEW_GUEST_ARTICLE => array(
                'enabled_option' => 'email_admin_new_guest_article_switch',
                'subject_option' => 'email_admin_new_guest_article_subject',
                'body_option' => 'email_admin_new_guest_article_message',
                'action_label_option' => 'email_admin_new_guest_article_action_label',
            ),
            self::EMAIL_TYPE_ADMIN_NEW_REGISTRATION_REQUEST => array(
                'enabled_option' => 'email_admin_new_registration_request_switch',
                'subject_option' => 'email_admin_new_registration_request_subject',
                'body_option' => 'email_admin_new_registration_request_message',
                'action_label_option' => 'email_admin_new_registration_request_action_label',
            ),
            // agent emails
            self::EMAIL_TYPE_AGENT_TICKET_ASSIGNED => array(
                'enabled_option' => 'email_agent_ticket_assigned_switch',
                'subject_option' => 'email_agent_ticket_assigned_subject',
                'body_option' => 'email_agent_ticket_assigned_message',
                'action_label_option' => 'email_agent_ticket_assigned_action_label',
            ),
            self::EMAIL_TYPE_AGENT_TICKET_REPLY_ADDED => array(
                'enabled_option' => 'email_agent_ticket_reply_added_switch',
                'subject_option' => 'email_agent_ticket_reply_added_subject',
                'body_option' => 'email_agent_ticket_reply_added_message',
                'action_label_option' => 'email_agent_ticket_reply_added_action_label',
            ),
            self::EMAIL_TYPE_AGENT_TICKET_CLOSED => array(
                'enabled_option' => 'email_agent_ticket_closed_switch',
                'subject_option' => 'email_agent_ticket_closed_subject',
                'body_option' => 'email_agent_ticket_closed_message',
                'action_label_option' => 'email_agent_ticket_closed_action_label',
            ),
            self::EMAIL_TYPE_AGENT_TICKET_REOPENED => array(
                'enabled_option' => 'email_agent_ticket_reopened_switch',
                'subject_option' => 'email_agent_ticket_reopened_subject',
                'body_option' => 'email_agent_ticket_reopened_message',
                'action_label_option' => 'email_agent_ticket_reopened_action_label',
            ),
            // guest emails
            self::EMAIL_TYPE_GUEST_TICKET_CREATED => array(
                'enabled_option' => 'email_guest_ticket_created_switch',
                'subject_option' => 'email_guest_ticket_created_subject',
                'body_option' => 'email_guest_ticket_created_message',
                'action_label_option' => 'email_guest_ticket_created_action_label',
            ),
            self::EMAIL_TYPE_GUEST_TICKET_REPLY_ADDED => array(
                'enabled_option' => 'email_guest_ticket_reply_added_switch',
                'subject_option' => 'email_guest_ticket_reply_added_subject',
                'body_option' => 'email_guest_ticket_reply_added_message',
                'action_label_option' => 'email_guest_ticket_reply_added_action_label',
            ),
            self::EMAIL_TYPE_GUEST_TICKET_CLOSED => array(
                'enabled_option' => 'email_guest_ticket_closed_switch',
                'subject_option' => 'email_guest_ticket_closed_subject',
                'body_option' => 'email_guest_ticket_closed_message',
                'action_label_option' => 'email_guest_ticket_closed_action_label',
            ),
            // user templates
            self::EMAIL_TYPE_USER_TICKET_CREATED => array(
                'enabled_option' => 'email_user_ticket_created_switch',
                'subject_option' => 'email_user_ticket_created_subject',
                'body_option' => 'email_user_ticket_created_message',
                'action_label_option' => 'email_user_ticket_created_action_label',
            ),
            self::EMAIL_TYPE_USER_TICKET_REPLY_ADDED => array(
                'enabled_option' => 'email_user_ticket_reply_added_switch',
                'subject_option' => 'email_user_ticket_reply_added_subject',
                'body_option' => 'email_user_ticket_reply_added_message',
                'action_label_option' => 'email_user_ticket_reply_added_action_label',
            ),
            self::EMAIL_TYPE_USER_TICKET_CLOSED => array(
                'enabled_option' => 'email_user_ticket_closed_switch',
                'subject_option' => 'email_user_ticket_closed_subject',
                'body_option' => 'email_user_ticket_closed_message',
                'action_label_option' => 'email_user_ticket_closed_action_label',
            ),
            self::EMAIL_TYPE_USER_REGISTRATION_RECEIVED => array(
                'enabled_option' => 'email_user_registration_received_switch',
                'subject_option' => 'email_user_registration_received_subject',
                'body_option' => 'email_user_registration_received_message',
            ),
            self::EMAIL_TYPE_USER_REGISTRATION_APPROVED => array(
                'enabled_option' => 'email_user_registration_approved_switch',
                'subject_option' => 'email_user_registration_approved_subject',
                'body_option' => 'email_user_registration_approved_message',
            ),
            self::EMAIL_TYPE_USER_REGISTRATION_DENIED => array(
                'enabled_option' => 'email_user_registration_denied_switch',
                'subject_option' => 'email_user_registration_denied_subject',
                'body_option' => 'email_user_registration_denied_message',
            ),
        );
    }

    public function get_email_context($template_id, $args, $is_preview = false) {
        $templates_config = self::get_email_templates_config();
        $template_config = $templates_config[$template_id];

        $email_context = array(
            'id' => $template_id,

            // globals
            'site_url' => get_site_url(),
            'company_url' => MKB_Options::option('email_company_link'),
            'company_name' => MKB_Options::option('email_company_name'),
            'company_logo' => MKB_SettingsBuilder::media_url(MKB_Options::option('email_company_logo')),
            'footer_copyright' => MKB_Options::option('email_footer_copyright'),
            'footer_text' => MKB_Options::option('email_footer_text'),
            'fallback_caption' => MKB_Options::option('email_action_button_fallback_text')
        );

        $template_vars = $args;

        if ($is_preview) {
            $template_vars = array_merge($template_vars, array(
                'guest_firstname' => 'Guest',
                'agent_firstname' => 'Agent',
                'user_firstname' => 'Client',
                'user_lastname' => 'Lastname',
                'user_email' => 'user@mail.com',
                'action_url' => 'https://www.your-url.xyz',
                'ticket_title' => 'Ticket Title',
                'article_title' => 'Article Title',
                'message_text' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.',
            ));
        }

        return array_merge(
            array(
                'action_label' => isset($template_config['action_label_option']) ?
                    MKB_Options::option($template_config['action_label_option']) :
                    'Open Link'
            ),
            $email_context,
            $template_vars
        );
    }

    public function get_email_content($template_id, $args, $is_preview = false) {
        $email_context = $this->get_email_context($template_id, $args, $is_preview);

        ob_start();
        include(MINERVA_KB_PLUGIN_DIR . 'lib/templates/email/email-base-template.php');
        return ob_get_clean();
    }

    public function email_header($email_context) {
        include(MINERVA_KB_EMAIL_TMPL_DIR . 'email-header.php');
    }

    public function email_footer($email_context) {
        include(MINERVA_KB_EMAIL_TMPL_DIR . 'email-footer.php');
    }

    public function email_content_header($email_context) {
        include(MINERVA_KB_EMAIL_TMPL_DIR . 'email-content-header.php');
    }

    public function email_content_footer($email_context) {
        include(MINERVA_KB_EMAIL_TMPL_DIR . 'email-content-footer.php');
    }

    public function email_content_message($email_context) {
        include(MINERVA_KB_EMAIL_TMPL_DIR . 'email-content-message.php');
    }

    public function email_content_action($email_context) {
        include(MINERVA_KB_EMAIL_TMPL_DIR . 'email-content-action.php');
    }

    public function email_content_action_fallback($email_context) {
        include(MINERVA_KB_EMAIL_TMPL_DIR . 'email-content-action-fallback.php');
    }

    public function email_content_before($email_context) {
        include(MINERVA_KB_EMAIL_TMPL_DIR . 'email-content-container-start.php');
    }

    public function email_content_after($email_context) {
        include(MINERVA_KB_EMAIL_TMPL_DIR . 'email-content-container-end.php');
    }

    public function email_content_body($email_context) {
        $templates_config = self::get_email_templates_config();
        $template_config = $templates_config[$email_context['id']];

        echo self::parse_email_tags(MKB_Options::option($template_config['body_option']), $email_context, true);
    }

    /**
     * @param $content
     * @param $email_context
     * @param $is_preview
     * @return string|string[]
     */
    public static function parse_email_tags($content, $email_context, $is_preview) {
        $template_tags = self::get_email_tags();

        // parse simple tags
        foreach ($template_tags as $tag_id) {
            $content = str_replace('{{' . $tag_id . '}}',
                isset($email_context[$tag_id]) ? $email_context[$tag_id] : '',
                $content
            );
        }

        // parse template tags with render callbacks
        $content = preg_replace_callback(
            "/{{[a-zA-Z_]+}}/",
            function($matches) use ($email_context) {
                $tag = trim($matches[0], '{}');
                $result = $matches[0];

                switch($tag) {
                    case 'message':
                        ob_start();
                        do_action('minerva_email_content_message', $email_context);
                        $result = ob_get_clean();
                        break;

                    case 'action_button':
                        ob_start();
                        do_action('minerva_email_content_action', $email_context);
                        $result = ob_get_clean();
                        break;

                    case 'action_button_fallback':
                        ob_start();
                        do_action('minerva_email_content_action_fallback', $email_context);
                        $result = ob_get_clean();
                        break;

                    default:
                        break;
                }

                return $result;
            },
            $content
        );

        return $content;
    }

    /**
     * @return array
     */
    public static function get_email_tags() {
        return array(
            'site_url',
            'company_url',
            'company_name',
            'company_logo',
            'footer_copyright',
            'footer_text',
            'fallback_caption',
            'guest_firstname',
            'agent_firstname',
            'user_firstname',
            'user_lastname',
            'user_email',
            'action_url',
            'action_label',
            'ticket_id',
            'ticket_title',
            'article_title',
        );
    }

    /**
     * @param $to
     * @param $template_id
     * @param $template_args
     * @return bool
     */
    public function send($to, $template_id, $template_args) {
        if (defined('MINERVA_DEMO_MODE')) {
            return false; // no emails on demo sites
        }

        $templates_config = self::get_email_templates_config();
        $template_config = $templates_config[$template_id];

        $from_name = MKB_Options::option('email_sender_name');
        $from_email = MKB_Options::option('email_sender_from_email');

        $reply_to_name = $from_name; // same as from currently
        $reply_to_email = MKB_Options::option('email_sender_replyto_email');

        $body = $this->get_email_content($template_id, $template_args);

        $email_headers = array(
            "Content-type: text/html; charset=utf-8",
            "From: $from_name <$from_email>",
            "Reply-To: $reply_to_name <$reply_to_email>",
        );

        $context = $this->get_email_context($template_id, $template_args);

        $subject = stripslashes(
            $this->parse_email_tags(
                MKB_Options::option($template_config['subject_option']),
                $context,
                false
            )
        );

        return wp_mail($to, $subject, $body, $email_headers);
    }
}
