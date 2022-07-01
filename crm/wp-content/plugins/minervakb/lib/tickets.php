<?php
/**
 * Project: MinervaKB
 * Copyright: 2015-2020 @KonstruktStudio
 */

class MKB_Tickets {
    // post types
    const TICKET_POST_TYPE = 'mkb_ticket';
    const TICKET_REPLY_POST_TYPE = 'mkb_ticket_reply';

    // meta storage keys
    const TICKET_ASSIGNEE_META_KEY = '_mkb_ticket_assignee';
    const TICKET_STATUS_META_KEY = '_mkb_ticket_status';
    const TICKET_CHANNEL_META_KEY = '_mkb_ticket_channel';
    const TICKET_CREDENTIALS_META_KEY = '_mkb_ticket_credentials';
    const TICKET_AWAITING_AGENT_REPLY_META_KEY = '_mkb_ticket_awaiting_agent_reply';
    const TICKET_UNREAD_AGENT_REPLIES_COUNT_META_KEY = '_mkb_ticket_unread_agent_replies_count';
    const TICKET_GUEST_TICKET_META_KEY = '_mkb_guest_ticket';
    const TICKET_CUSTOM_ID_META_KEY = '_mkb_ticket_id';

    // options
    const TICKET_NEXT_CUSTOM_ID_OPTION_KEY = '_mkb_next_custom_ticket_id';

    // statuses
    const TICKET_STATUS_OPEN = 'open';
    const TICKET_STATUS_CLOSED = 'closed';
    const TICKET_STATUS_IN_PROGRESS = 'in-progress';
    const TICKET_STATUS_ON_HOLD = 'on-hold';

    // channels
    const TICKET_CHANNEL_FORM = 'form';

    // ticket change initiators
    const TICKET_ACTION_INITIATOR_AGENT = 'agent';
    const TICKET_ACTION_INITIATOR_GUEST = 'guest';
    const TICKET_ACTION_INITIATOR_USER = 'user';

    /**
     * @param $post_id
     * @return bool
     */
    public static function is_ticket($post_id) {
        return get_post_type($post_id) === self::TICKET_POST_TYPE;
    }

    /**
     * @param $ticket_id
     * @return bool
     */
    public static function is_guest_ticket($ticket_id) {
        return (bool)get_post_meta($ticket_id, self::TICKET_GUEST_TICKET_META_KEY, true);
    }

    /**
     * @param $ticket_id
     * @return string
     */
    public static function get_guest_ticket_access_link($ticket_id) {
        return add_query_arg( array(
            'ticket_access_token' => get_post_meta($ticket_id, '_mkb_guest_ticket_access_token', true)
        ), get_the_permalink($ticket_id));
    }

    /**
     * @param $ticket_id
     * @return string
     */
    public static function make_ticket_reply_title($ticket_id) {
        return 'SUPPORT TICKET #' . $ticket_id . ' REPLY';
    }

    /**
     * @param $ticket
     * @return bool
     */
    private static function validate_ticket($ticket) {
        if (!$ticket || is_wp_error($ticket)) {
            return false;
        }

        return $ticket;
    }

    /**
     * Detects if ticket is assigned to useris_ticket_assigned_to_user
     * @param $ticket_id
     * @param $user_id
     * @return bool
     */
    public static function is_ticket_assigned_to_user($ticket_id, $user_id) {
        return (int)self::get_ticket_assignee($ticket_id) === $user_id;
    }

    /**
     * Detects if ticket is assigned
     * @param $ticket_id
     * @return bool
     */
    public static function is_ticket_assigned($ticket_id) {
        return (bool)self::get_ticket_assignee($ticket_id);
    }

    /**
     * @param $ticket_id
     * @return mixed
     */
    public static function get_ticket_assignee($ticket_id) {
        return get_post_meta($ticket_id, self::TICKET_ASSIGNEE_META_KEY, true);
    }

    /**
     * @param $ticket_id
     * @return bool|WP_User
     */
    public static function get_ticket_assignee_user($ticket_id) {
        return get_user_by('ID', self::get_ticket_assignee($ticket_id));
    }



    /**
     * @param $ticket_id
     * @return bool
     */
    public static function is_ticket_awaiting_agent_reply($ticket_id) {
        return (bool)get_post_meta($ticket_id, self::TICKET_AWAITING_AGENT_REPLY_META_KEY, true);
    }

    /**
     * @param $ticket_id
     */
    public static function set_awaiting_agent_reply_flag($ticket_id) {
        update_post_meta($ticket_id, self::TICKET_AWAITING_AGENT_REPLY_META_KEY, true);
    }

    /**
     * @param $ticket_id
     */
    public static function clear_awaiting_agent_reply_flag($ticket_id) {
        delete_post_meta($ticket_id, self::TICKET_AWAITING_AGENT_REPLY_META_KEY);
    }

    /**
     * @param $ticket_id
     * @return int
     */
    public static function get_unread_agent_replies_count($ticket_id) {
        $current_count = get_post_meta($ticket_id, self::TICKET_UNREAD_AGENT_REPLIES_COUNT_META_KEY, true);

        if (!$current_count) {
            $current_count = 0;
        }

        return (int)$current_count;
    }

    /**
     * @param $ticket_id
     */
    public static function increase_unread_agent_replies_count_flag($ticket_id) {
        update_post_meta($ticket_id, self::TICKET_UNREAD_AGENT_REPLIES_COUNT_META_KEY, self::get_unread_agent_replies_count($ticket_id) + 1);
    }

    public static function delete_unread_agent_replies_count_flag($ticket_id) {
        delete_post_meta($ticket_id, self::TICKET_UNREAD_AGENT_REPLIES_COUNT_META_KEY);
    }

    /**
     * Client-side ticket view
     * @param $ticket_id
     * @return bool
     */
    public static function is_ticket_author_view($ticket_id) {
        if (is_admin()) {
            return false;
        }

        if (is_user_logged_in()) {
            // user
            $user = wp_get_current_user();
            $ticket = get_post($ticket_id);

            if ($user && $user->ID === (int)$ticket->post_author) {
                return true;
            }
        } else {
            // guest
            $access_token = get_post_meta($ticket_id, '_mkb_guest_ticket_access_token', true);
            $request_access_token = isset($_GET['ticket_access_token']) ? $_GET['ticket_access_token'] : '';

            if ($access_token && $request_access_token === $access_token) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verify access to ticket
     * @param $ticket
     */
    public static function verify_ticket_access($ticket) {
        $access_allowed = false;

        // 1. guest ticket with access token check
        $access_token = get_post_meta($ticket->ID, '_mkb_guest_ticket_access_token', true);
        $request_access_token = isset($_GET['ticket_access_token']) ? $_GET['ticket_access_token'] : '';

        if ($access_token && $request_access_token === $access_token) {
            $access_allowed = true;
        }

        $user = wp_get_current_user();

        // 2. user ticket
        if ($user && $user->ID === (int)$ticket->post_author) {
            $access_allowed = true;
        }

        // 3. admins & agents
        if (
                current_user_can('administrator') ||
                current_user_can('mkb_view_tickets') && $user && (int)self::get_ticket_assignee($ticket->ID) === $user->ID || // user can view tickets and ticket is assigned to him
                current_user_can('mkb_view_unassigned_tickets') && !self::is_ticket_assigned($ticket->ID) || // user can see unassigned tickets
                current_user_can('mkb_view_others_tickets')
            ) {

            $access_allowed = true;
        }

        if (!$access_allowed) {
            wp_safe_redirect(get_bloginfo('url'));
            exit();
        }
    }

    /**
     * @param null $user
     * @return bool|null
     */
    public static function user_can_create_tickets($user = null) {
        if (!is_user_logged_in()) { // guest
            return MKB_Options::option('tickets_allow_guest_tickets');
        } else { // user
            if (!MKB_Options::option('tickets_allow_user_tickets')) {
                return false;
            }

            if (!$user) {
                $user = wp_get_current_user();
            }

            if (in_array('administrator', $user->roles)) {
                return true;
            }

            $mode = MKB_Options::option('tickets_users_mode');

            switch ($mode) {
                case 'minerva':
                    return MKB_Users::is_minerva_support_user();
                    break;

                case 'roles':
                    $roles = json_decode(MKB_Options::option('tickets_users_roles'), true);

                    return sizeof(array_intersect($roles, $user->roles)) > 0;
                    break;

                default:
                    break;
            }
        }

        return false;
    }

    /**
     * Types assign permission check
     * @param $user
     * @param $ticket
     * @param $cap
     * @return bool
     */
    public static function user_can_assign_ticket_taxonomy($user, $ticket, $cap) {
        if (current_user_can('administrator')) {
            return true;
        }

        if (!self::validate_ticket($ticket)) {
            return false;
        }

        if (!is_object($ticket)) {
            $ticket = get_post($ticket);
        }

        // check ticket assignee
        if (!self::is_ticket_assigned_to_user($ticket->ID, $user->ID)) {
            return MKB_Users::user_can($user, 'mkb_modify_others_tickets') && MKB_Users::user_can($user, $cap);
        }

        return MKB_Users::user_can($user, $cap);
    }

    /**
     * General ticket modification access check (for non-assignee)
     * @param $user
     * @param $ticket
     * @return bool
     * TODO: rewrite all access check to current user
     */
    public static function user_can_modify_ticket($user, $ticket) {
        if (current_user_can('administrator')) {
            return true;
        }

        if (!self::validate_ticket($ticket)) {
            return false;
        }

        if (!is_object($ticket)) {
            $ticket = get_post($ticket);
        }

        if (!self::is_ticket_assigned_to_user($ticket->ID, $user->ID)) {
            return MKB_Users::user_can($user, 'mkb_modify_others_tickets');
        }

        return true; // TODO: add other checks here
    }

    /**
     * @param null $status_id
     * @param null $default_status_id
     * @return array|null
     */
    public static function get_ticket_status_by_id($status_id = null, $default_status_id = null) {
        $allowed_ticket_statuses = self::get_allowed_ticket_statuses();
        $ticket_status_icons = self::get_ticket_status_icons();
        $ticket_status_colors = self::get_ticket_status_colors();

        if (!$status_id || !isset($allowed_ticket_statuses[$status_id]) && $default_status_id) {
            $status_id = $default_status_id;
        }

        if (!isset($allowed_ticket_statuses[$status_id])) {
            return null;
        }

        return array(
            'id' => $status_id,
            'label' => $allowed_ticket_statuses[$status_id],
            'icon' => $ticket_status_icons[$status_id],
            'color' => $ticket_status_colors[$status_id]
        );
    }

    /**
     * @param $ticket_id
     * @return array
     */
    public static function get_ticket_status($ticket_id) {
        return self::get_ticket_status_by_id(
            get_post_meta($ticket_id, self::TICKET_STATUS_META_KEY, true),
            self::TICKET_STATUS_OPEN
        );
    }

    /**
     * @return array|null
     */
    private static function get_ticket_status_from_request() {
        return self::get_ticket_status_by_id(
            isset($_REQUEST['mkb_ticket_status']) ? $_REQUEST['mkb_ticket_status'] : ''
        );
    }

    /**
     * @return array
     */
    public static function get_ticket_statuses() {
        $all = self::get_allowed_ticket_statuses();
        $statuses = array();

        foreach($all as $id => $value) {
            $statuses []= self::get_ticket_status_by_id($id);
        }

        return $statuses;
    }

    /**
     * @return array
     */
    public static function get_allowed_ticket_statuses() {
        return array(
            self::TICKET_STATUS_OPEN => __('Open', 'minerva-kb'),
            self::TICKET_STATUS_CLOSED =>  __('Closed', 'minerva-kb'),
            self::TICKET_STATUS_IN_PROGRESS => __('In progress', 'minerva-kb'),
            self::TICKET_STATUS_ON_HOLD => __('On hold', 'minerva-kb')
        );
    }

    /**
     * @return array
     */
    public static function get_ticket_status_icons() {
        return array(
            self::TICKET_STATUS_OPEN => 'fa-clock-o',
            self::TICKET_STATUS_CLOSED => 'fa-lock',
            self::TICKET_STATUS_IN_PROGRESS => 'fa-rocket',
            self::TICKET_STATUS_ON_HOLD => 'fa-pause-circle',
        );
    }

    /**
     * @return array
     */
    public static function get_ticket_status_colors() {
        return array(
            self::TICKET_STATUS_OPEN => '#2cbe4e',
            self::TICKET_STATUS_CLOSED => '#d73a49',
            self::TICKET_STATUS_IN_PROGRESS => '#00a0d2',
            self::TICKET_STATUS_ON_HOLD => '#eb6420',
        );
    }

    /**
     * @param null $ticket_id
     */
    public static function render_ticket_status_badge($ticket_id = null) {
        if (!$ticket_id) {
            $ticket_id = get_the_ID();
        }

        if (!$ticket_id) { return; }

        $ticket_status = self::get_ticket_status($ticket_id);

        ?><span class="mkb-ticket-status-badge status--<?php esc_attr_e($ticket_status['id']); ?>" style="background: <?php esc_attr_e($ticket_status['color']); ?>;">
        <i class="fa <?php esc_attr_e($ticket_status['icon']); ?>"></i> <?php esc_attr_e($ticket_status['label']); ?>
        </span><?php
    }

    /**
     * @param $post_id
     * @param $user_id
     * @param bool $force_update
     */
    public static function update_ticket_status_on_post_save($post_id, $user_id, $force_update = false) {
        $new_status = self::get_ticket_status_from_request();

        if (!$new_status && !$force_update) {
            return false;
        }

        $previous_status = self::get_ticket_status($post_id);

        if ($previous_status['id'] === $new_status['id'] && !$force_update) {
            return false;
        }

        // TODO: permission check
        update_post_meta($post_id, self::TICKET_STATUS_META_KEY, $new_status['id']);

        if ($new_status['id'] === self::TICKET_STATUS_CLOSED) {
            MKB_History::track_ticket_closed_event(
                $post_id,
                $user_id,
                $previous_status['id'],
                $previous_status['label'],
                self::TICKET_ACTION_INITIATOR_AGENT
            );
        } else if ($new_status['id'] === self::TICKET_STATUS_OPEN && $previous_status['id'] === self::TICKET_STATUS_CLOSED) {
            MKB_History::track_ticket_reopen_event(
                $post_id,
                $user_id,
                $previous_status['id'],
                $previous_status['label'],
                self::TICKET_ACTION_INITIATOR_AGENT
            );
        } else {
            MKB_History::track_ticket_status_change(
                $post_id,
                $user_id,
                $new_status['id'],
                $new_status['label'],
                $previous_status['id'],
                $previous_status['label'],
                self::TICKET_ACTION_INITIATOR_AGENT
            );
        }

        return $new_status['id'];
    }

    /**
     * @param null $ticket_id
     * @param null $status_id
     */
    private static function update_ticket_status($ticket_id = null, $status_id = null) {
        $status = self::get_ticket_status_by_id($status_id);

        if (!$ticket_id || !$status) {
            trigger_error('Invalid ticket status save attempt', E_USER_WARNING);

            return;
        }

        update_post_meta($ticket_id, self::TICKET_STATUS_META_KEY, $status['id']);
    }

    /**
     * @return array
     */
    public static function get_tickets($args = array()) {
        $options = wp_parse_args($args, array(
            'group' => 'active'
        ));

        $tickets = array();

        $query_args = array(
            'post_type' => 'mkb_ticket',
            'ignore_sticky_posts' => 1,
            'posts_per_page' => -1,
            'post_status' => $options['group'] === 'trash' ? 'trash' : 'publish,draft'
        );

        $tickets_loop = new WP_Query( $query_args );

        if ($tickets_loop->have_posts()):

            while($tickets_loop->have_posts()) : $tickets_loop->the_post();
                global $post;

                $ticket = $post;
                $ticket_id = $ticket->ID;

                $ticket_status = self::get_ticket_status($ticket_id);

                if (
                    $options['group'] === 'closed' && $ticket_status['id'] !== 'closed' ||
                    $options['group'] === 'active' && $ticket_status['id'] === 'closed'
                ) {
                    // TODO: maybe merge meta in global admin tickets query modifier instead
                    continue;
                }

                $type = wp_get_post_terms( $ticket_id, array( 'mkb_ticket_type' ) );
                $priority = wp_get_post_terms( $ticket_id, array( 'mkb_ticket_priority' ) );
                $product = wp_get_post_terms( $ticket_id, array( 'mkb_ticket_product' ) );
                $department = wp_get_post_terms( $ticket_id, array( 'mkb_ticket_department' ) );
                $ticket_assignee_id = get_post_meta($ticket_id, '_mkb_ticket_assignee', true);
                $is_guest = self::is_guest_ticket($ticket_id);
                $is_awaiting_agent_reply = self::is_ticket_awaiting_agent_reply($ticket_id) &&
                    $ticket_status['id'] !== self::TICKET_STATUS_CLOSED;

                $replies = get_posts(array(
                    'post_type' => 'mkb_ticket_reply',
                    'posts_per_page' => -1,
                    'ignore_sticky_posts' => 1,
                    'post_parent' => $ticket_id,
                    'post_status' => array('publish', 'trash')
                ));

                $replies_count = sizeof($replies);
                $latest_reply = sizeof($replies) ? $replies[0] : $ticket;

                $openedTime = get_post_time('U', true, $ticket_id);
                $latestReplyTime = sizeof($replies) ? get_post_time('U', true, $latest_reply->ID) : $openedTime;

                $post_lock = wp_check_post_lock($ticket_id);

                $ticket_info = array(
                    'postId' => $ticket_id,
                    'postStatus' => $ticket->post_status,
                    'ticketId' => self::get_ticket_id_from_post_id($ticket_id),
                    'title' => get_the_title(),
                    'isGuest' => $is_guest,
                    'link' => get_the_permalink($ticket_id),
                    'editLink' => get_edit_post_link($ticket_id, 'url'),
                    'deleteLink' => get_delete_post_link($ticket_id),
                    'forceDeleteLink' => get_delete_post_link($ticket_id, '', true),
                    'restoreLink' => wp_nonce_url(
                        "post.php?action=untrash&post=$ticket_id",
                        "untrash-post_$ticket_id"
                    ),
                    'type' => $type ? $type[0]->term_id : null,
                    'priority' => $priority ? $priority[0]->term_id : null,
                    'product' => $product ? $product[0]->term_id : null,
                    'department' => $department ? $department[0]->term_id : null,
                    'status' => $ticket_status['id'],
                    'opened' => $openedTime * 1000,
                    'latestReplyTime' => $latestReplyTime * 1000,
                    'repliesCount' => $replies_count,
                    'isAwaitingAgentReply' => $is_awaiting_agent_reply,
                    'assigneeId' => $ticket_assignee_id ? (int)$ticket_assignee_id : null,
                    'authorId' => $ticket->post_author ? (int)$ticket->post_author : null,
                    'lockingUserId' => $post_lock ? (int)$post_lock : null
                );

                if ($is_guest) {
                    $ticket_info['openerFirstName'] = get_post_meta($ticket_id, '_mkb_guest_ticket_firstname', true);
                    $ticket_info['openerLastName'] = get_post_meta($ticket_id, '_mkb_guest_ticket_lastname', true);
                }

                array_push($tickets, $ticket_info);
            endwhile;

        endif;

        wp_reset_postdata();

        return $tickets;
    }

    public static function get_ticket_list_users($tickets) {
        $users = array();
        $user_keys = array('assigneeId', 'authorId', 'lockingUserId');

        foreach($tickets as $ticket) {
            foreach($user_keys as $key) {
                $user_id = $ticket[$key];

                if (!$user_id || isset($users[$user_id])) { continue; }

                $user_info = self::get_ticket_list_user_info($user_id);

                if (!$user_info) { continue; }

                $users[$user_id] = $user_info;
            }
        }

        return $users;
    }

    public static function get_ticket_list_user_info($user_id) {
        $user = get_user_by('ID', $user_id);
        $user_info = null;

        if (!$user) {
            return null;
        }

        $user_info = array(
            'displayName' => $user->data->display_name,
            'profileLink' => get_current_user_id() == $user_id ?
                get_edit_profile_url($user_id) :
                add_query_arg( 'user_id', $user_id, self_admin_url( 'user-edit.php')),
            'avatarHTML' => get_avatar($user_id, 18)
        );

        return $user_info;
    }

    /**
     * @return array
     */
    public static function calculate_tickets_count() {
        $tickets_count = array(
            'active' => 0,
            'closed' => 0,
            'trash' => 0,
            // TODO: total, assigned to user, awaiting agent reply
        );

        // active / closed
        $query_args = array(
            'post_type' => 'mkb_ticket',
            'ignore_sticky_posts' => 1,
            'posts_per_page' => -1,
            'post_status' => 'publish,draft'
        );

        $tickets_loop = new WP_Query( $query_args );

        if ($tickets_loop->have_posts()):
            while($tickets_loop->have_posts()) : $tickets_loop->the_post();
                global $post;

                $ticket = $post;

                $ticket_status = self::get_ticket_status($ticket->ID);

                if ($ticket_status['id'] !== 'closed') {
                    ++$tickets_count['active'];
                } else {
                    ++$tickets_count['closed'];
                }
            endwhile;
        endif;

        wp_reset_postdata();

        // trash
        // TODO: check if it uses user assignee filter
        $tickets_count['trash'] = wp_count_posts('mkb_ticket')->trash;

        return $tickets_count;
    }

    /**
     * @param $post_id
     * @return int|null
     */
    public static function get_ticket_id_from_post_id($post_id) {
        if (!MKB_Options::option('tickets_use_custom_ids')) {
            return $post_id;
        }

        $tickets_id = get_post_meta($post_id, self::TICKET_CUSTOM_ID_META_KEY, true);

        return $tickets_id ? (int)$tickets_id : $post_id;
    }

    /**
     * @param $next_ticket_id
     */
    public static function update_next_ticket_custom_id($next_ticket_id) {
        update_option(self::TICKET_NEXT_CUSTOM_ID_OPTION_KEY, $next_ticket_id);
    }

    /**
     * @param $ticket_post_id
     */
    public static function maybe_set_custom_ticket_id($ticket_post_id) {
        if (!MKB_Options::option('tickets_use_custom_ids')) {
            return;
        }

        $next_custom_id = get_option(self::TICKET_NEXT_CUSTOM_ID_OPTION_KEY);

        if ($next_custom_id === false) {
            $next_custom_id = MKB_Options::option('tickets_custom_ids_start_from');
        }

        if (!is_numeric($next_custom_id)) {
            return; // some random non-numeric value entered by customer or saved in options, can be fixed by reset
        }

        $next_custom_id = (int)$next_custom_id;

        if ($next_custom_id < 0) {
            return; // only positive integers or 0
        }

        update_post_meta($ticket_post_id, self::TICKET_CUSTOM_ID_META_KEY, $next_custom_id);
        self::update_next_ticket_custom_id(++$next_custom_id);
    }

    /**
     * @param $ticket_id
     * @param $user_id
     * @param $initiator
     * for future use, maybe
     */
    public static function open_ticket($ticket_id, $user_id, $initiator) {
        self::update_ticket_status($ticket_id, self::TICKET_STATUS_OPEN);

        MKB_History::track_ticket_opened_event(
            $ticket_id,
            $user_id,
            null,
            null,
            $initiator
        );
    }

    /**
     * @param $ticket_id
     * @param $user_id
     * @param $initiator
     */
    public static function close_ticket($ticket_id, $user_id, $initiator) {
        // TODO: check permissions, maybe, or leave permissions at ajax level
        $previous_status = MKB_Tickets::get_ticket_status($ticket_id);

        // TODO: check for new tickets
        self::update_ticket_status($ticket_id, self::TICKET_STATUS_CLOSED);

        MKB_History::track_ticket_closed_event(
            $ticket_id,
            $user_id,
            $previous_status['id'],
            $previous_status['label'],
            $initiator
        );

        self::delete_ticket_credentials($ticket_id);
    }

    /**
     * @param $ticket_id
     * @param $user_id
     * @param $initiator
     */
    public static function reopen_ticket($ticket_id, $user_id, $initiator) {
        $previous_status = self::get_ticket_status($ticket_id);

        self::update_ticket_status($ticket_id, self::TICKET_STATUS_OPEN);

        MKB_History::track_ticket_reopen_event(
            $ticket_id,
            $user_id,
            $previous_status['id'],
            $previous_status['label'],
            $initiator
        );
    }

    /**
     * @param null $ticket_id
     */
    public static function render_ticket_type_badge($ticket_id = null) {
        if (!$ticket_id) {
            $ticket_id = get_the_ID();
        }

        if (!$ticket_id) { return; }

        $ticket_type = wp_get_post_terms($ticket_id, array('mkb_ticket_type'));

        if (!$ticket_type) {
            return;
        }

        $ticket_type = $ticket_type[0];
        $color = MKB_TemplateHelper::get_taxonomy_option($ticket_type, 'mkb_ticket_type', 'color');

        ?><span class="mkb-ticket-type-badge type--<?php esc_attr_e($ticket_type->slug); ?>" style="background: <?php esc_attr_e($color); ?>;"><?php
            esc_html_e($ticket_type->name);
            ?></span><?php
    }

    /**
     * @return array
     */
    public static function get_allowed_ticket_channels() {
        return array(
            'form' => __('Contact Form', 'minerva-kb'),
            'email' => __('Email', 'minerva-kb'),
            'phone' => __('Phone', 'minerva-kb'),
            'facebook' => __('Facebook', 'minerva-kb'),
        );
    }

    /**
     * @param $ticket_id
     * @return array|null
     */
    public static function get_ticket_channel($ticket_id) {
        return self::get_ticket_channel_by_id(
            get_post_meta($ticket_id, self::TICKET_CHANNEL_META_KEY, true)
        );
    }

    /**
     * @param null $channel_id
     * @param null $default_channel_id
     * @return array|null
     */
    public static function get_ticket_channel_by_id($channel_id = null, $default_channel_id = null) {
        $allowed_ticket_channels = self::get_allowed_ticket_channels();

        if (!$channel_id || !isset($allowed_ticket_channels[$channel_id]) && $default_channel_id) {
            $channel_id = $default_channel_id;
        }

        if (!isset($allowed_ticket_channels[$channel_id])) {
            return null;
        }

        return array(
            'id' => $channel_id,
            'label' => $allowed_ticket_channels[$channel_id]
        );
    }

    /**
     * @param null $ticket_id
     * @param null $channel_id
     */
    public static function set_ticket_channel($ticket_id = null, $channel_id = null) {
        $channel = self::get_ticket_channel_by_id($channel_id);

        if (!$ticket_id || !$channel) {
            trigger_error('Invalid ticket channel save attempt', E_USER_WARNING);

            return false;
        }

        update_post_meta($ticket_id, self::TICKET_CHANNEL_META_KEY, $channel['id']);

        return true;
    }

    /**
     * @param $ticket_id
     */
    public static function set_ticket_channel_form($ticket_id) {
        self::set_ticket_channel($ticket_id, MKB_Tickets::TICKET_CHANNEL_FORM);
    }

    /**
     * @param $ticket_id
     * @return bool|false|string|null
     */
    public static function get_ticket_credentials($ticket_id) {
        if (is_admin() && !current_user_can('mkb_ticket_assignee')) {
            return ''; // TODO: maybe add more checks
        }

        $stored_credentials = get_post_meta($ticket_id, self::TICKET_CREDENTIALS_META_KEY, true);

        return $stored_credentials ? MKB_Utils::decrypt($stored_credentials) : '';
    }

    /**
     * @param $ticket_id
     * @return bool
     */
    public static function has_ticket_credentials($ticket_id) {
        return (bool)get_post_meta($ticket_id, self::TICKET_CREDENTIALS_META_KEY, true);
    }

    /**
     * @param $ticket_id
     * @param $credentials
     */
    public static function set_ticket_credentials($ticket_id, $credentials) {
        update_post_meta($ticket_id, self::TICKET_CREDENTIALS_META_KEY, MKB_Utils::encrypt(stripslashes_deep($credentials)));
    }

    /**
     * @param $ticket_id
     */
    public static function delete_ticket_credentials($ticket_id) {
        delete_post_meta($ticket_id, self::TICKET_CREDENTIALS_META_KEY);
    }

    /**
     * @param $ticket_id
     * @return bool
     */
    public static function verify_guest_ticket_access_token($ticket_id) {
        $access_token = get_post_meta($ticket_id, '_mkb_guest_ticket_access_token', true);
        $request_access_token = isset($_REQUEST['ticket_access_token']) ? $_REQUEST['ticket_access_token'] : '';

        // NOTE: we always enforce ticket token check here.
        // In case meta gets broken or corrupted, ticket must not be shown and actions must not be permitted
        return $access_token &&
            $request_access_token == $access_token &&
            get_post_type($ticket_id) === 'mkb_ticket';
    }

    /**
     * Assignee assign permission check
     * @param $user
     * @param $ticket
     * @return bool
     */
    public static function user_can_assign_ticket_assignee($user, $ticket) {
        if (!self::validate_ticket($ticket)) {
            return false;
        }

        return current_user_can('administrator') || MKB_Users::user_can($user, 'mkb_assign_tickets');
    }

    /**
     * @param $user
     * @param $ticket
     * @return bool
     */
    public static function user_can_reply_to_ticket($user, $ticket) {
        if (current_user_can('administrator')) {
            return true;
        }

        if (!$ticket || is_wp_error($ticket)) {
            return false;
        }

        if (!is_object($ticket)) {
            $ticket = get_post($ticket);
        }

        if ($user->ID === (int)$ticket->post_author) {
            return true;
        }

        if (!self::is_ticket_assigned_to_user($ticket->ID, $user->ID)) {
            return MKB_Users::user_can($user, 'mkb_reply_to_others_tickets');
        }

        return MKB_Users::user_can($user, 'mkb_reply_to_tickets');
    }

    /**
     * @param $ticket_id
     * @param $new_term_id
     * @param $taxonomy
     * @return bool
     */
    public static function is_currently_active_term($ticket_id, $new_term_id, $taxonomy) {
        $current_term = MKB_Tickets::get_active_ticket_term($ticket_id, $taxonomy);

        return $current_term && (int)$new_term_id === $current_term->term_id;
    }

    /**
     * @param $ticket_id
     * @param $taxonomy
     * @param bool $use_defaults
     * @return mixed|null
     */
    public static function get_active_ticket_term($ticket_id, $taxonomy, $use_defaults = false) {
        $term  = null;
        $all_terms = wp_get_post_terms($ticket_id, $taxonomy);

        if ($all_terms && !is_wp_error($all_terms) && sizeof($all_terms)) {
            $term = $all_terms[0];
        }

        if (!$term && $use_defaults) {
            $term = self::get_default_term_for_taxonomy($taxonomy);
        }

        return $term;
    }

    /**
     * Gets default term from plugin settings
     * @param $taxonomy
     * @return array|false|WP_Term|null
     */
    public static function get_default_term_for_taxonomy($taxonomy) {
        $option_key = 'tickets_default_' . str_replace('mkb_ticket_', '', $taxonomy);

        if (MKB_Options::option($option_key)) {
            $term = get_term_by('id', MKB_Options::option($option_key), $taxonomy);

            if (isset($term) && !is_wp_error($term)) {
                return $term;
            }
        }

        return null;
    }

    /**
     * Templates
     */
    public static function the_user_avatar($user_id) {
        echo get_avatar(
            get_the_author_meta('email', $user_id),
            96,
            '',
            get_the_author_meta('nickname', $user_id) . ' avatar'
        );
    }

    public static function render_new_ticket_content_form() {
        // TODO: attachments
        ?>
        <h3><?php _e('Ticket Message', 'minerva-kb'); ?></h3>

        <?php wp_editor('', 'content', array(
            'textarea_name' => 'content',
            'tinymce' => array(
                'toolbar1'      => 'formatselect,bold,italic,underline,forecolor,separator,blockquote,bullist,numlist,alignleft,aligncenter,alignright,separator,link,charmap,removeformat',
                'toolbar2'      => '',
                'height' => 300
            )
        ));
    }

    /**
     * @param bool $is_guest
     * @return array
     */
    public static function process_user_new_reply_request($is_guest = false) {

        $status = 1;
        $errors = array();

        // request data
        $ticket_id = isset($_POST['ticket_id']) ? (int)$_POST['ticket_id'] : null;
        $close_ticket = isset($_POST['close_ticket']) ? (bool)$_POST['close_ticket'] : false;
        $reply_content = isset($_POST['reply']) ? wp_kses_post($_POST['reply']) : '';

        $user = $is_guest ? MKB_Users::get_guest_support_user() : wp_get_current_user();;

        // validate data
        if (!$reply_content) {
            return array(
                __('Reply must not be empty', 'minerva-kb')
            );
        }

        $ticket = get_post($ticket_id);

        if (!$ticket || $ticket->post_status !== 'publish') {
            return array(
                __('Invalid or deleted ticket', 'minerva-kb')
            );
        }

        if (!self::is_ticket($ticket_id)) { // post type check
            return array(
                __('Post is not a ticket', 'minerva-kb')
            );
        }

        if (
            ($is_guest && !self::verify_guest_ticket_access_token($ticket_id)) || // access token is valid (guests)
            !$user || // user exists, also for guest users
            (int)$ticket->post_author !== $user->ID // ensure current user is ticket author
        ) {
            return array(
                __('Invalid user or insufficient rights', 'minerva-kb')
            );
        }

        if (!$ticket || $ticket->post_status !== 'publish') {
            return array(
                __('Invalid or deleted ticket', 'minerva-kb')
            );
        }

        if (MKB_Tickets::get_ticket_status($ticket_id) === MKB_Tickets::TICKET_STATUS_CLOSED) {
            return array(
                __('Cannot reply to a closed ticket', 'minerva-kb')
            );
        }

        // all is good, proceed with reply insert
        $reply_post = array(
            'post_type'     => self::TICKET_REPLY_POST_TYPE,
            'post_title'    => self::make_ticket_reply_title($ticket_id),
            'post_content'  => $reply_content,
            'post_status'   => 'publish',
            'post_author'   => $user->ID,
            'post_parent'   => $ticket_id
        );

        $reply_id = wp_insert_post($reply_post);

        if (!isset($reply_id) || is_wp_error($reply_id)) {
            // TODO: note, ticket close and other actions also terminate here, maybe allow to proceed
            return array(
                __('Could not add reply', 'minerva-kb')
            );
        }

        $status = 0; // reply added

        add_post_meta($reply_id, '_mkb_ticket_reply_side', 'client', true);

        // process attachments
        $uploader = new MKB_Attachments(
            'mkb_ticket_reply_files',
            'ticket' . $ticket_id,
            $reply_id
        );

        $attachments_errors = $uploader->process_files();

        $assignee = self::get_ticket_assignee_user($ticket_id);

        $email_template_context = array();

        if ($assignee) {
            $email_template_context = array(
                'agent_firstname' => $assignee->first_name,
                'ticket_title' => get_the_title($ticket_id),
                'ticket_id' => $ticket_id,
                'action_url' => MKB_Utils::get_post_edit_admin_url($ticket_id),
                'message_text' => $reply_content
            );
        }

        // reply added notification
        if ($assignee && MKB_Options::option('email_agent_ticket_reply_added_switch')) {
            MKB_Emails::instance()->send(
                $assignee->user_email,
                MKB_Emails::EMAIL_TYPE_AGENT_TICKET_REPLY_ADDED,
                $email_template_context
            );
        }

        // close ticket
        if ($close_ticket) {

            self::close_ticket(
                $ticket_id,
                $user->ID,
                $is_guest ? self::TICKET_ACTION_INITIATOR_GUEST : self::TICKET_ACTION_INITIATOR_USER
            );

            if ($assignee && MKB_Options::option('email_agent_ticket_closed_switch')) {
                MKB_Emails::instance()->send(
                    $assignee->user_email,
                    MKB_Emails::EMAIL_TYPE_AGENT_TICKET_CLOSED,
                    $email_template_context
                );
            }
        } else {
            self::set_awaiting_agent_reply_flag($ticket_id);
        }

        echo json_encode(array(
            'status' => $status,
            'errors' => $errors,
            'fileUploadErrors' => $attachments_errors
        ));

        wp_die();
    }

    /**
     * @param bool $is_guest
     * @return array
     */
    public static function process_user_ticket_reopen_request($is_guest = false) {

        $ticket_id = isset($_REQUEST['ticket_id']) ? (int)$_REQUEST['ticket_id'] : null;

        $user = $is_guest ? MKB_Users::get_guest_support_user() : wp_get_current_user();

        // validate data
        $ticket = get_post($ticket_id);

        if (!$ticket || $ticket->post_status !== 'publish') {
            return array(
                __('Invalid or deleted ticket', 'minerva-kb')
            );
        }

        if (!self::is_ticket($ticket_id)) { // post type check
            return array(
                __('Post is not a ticket', 'minerva-kb')
            );
        }

        if (
                ($is_guest && !MKB_Tickets::verify_guest_ticket_access_token($ticket_id)) ||
                !$user ||
                (int)$ticket->post_author !== $user->ID // ensure current user is ticket author
        ) {

            return array(
                __('Invalid user or insufficient rights', 'minerva-kb')
            );
        }

        self::reopen_ticket(
            $ticket_id,
            $user->ID,
            $is_guest ? self::TICKET_ACTION_INITIATOR_GUEST : self::TICKET_ACTION_INITIATOR_USER
        );

        $assignee = self::get_ticket_assignee_user($ticket_id);

        if ($assignee && MKB_Options::option('email_agent_ticket_reopened_switch')) {
            MKB_Emails::instance()->send(
                $assignee->user_email,
                MKB_Emails::EMAIL_TYPE_AGENT_TICKET_REOPENED,
                array(
                    'agent_firstname' => $assignee->first_name,
                    'ticket_title' => get_the_title($ticket_id),
                    'ticket_id' => $ticket_id,
                    'action_url' => MKB_Utils::get_post_edit_admin_url($ticket_id)
                )
            );
        }

        return array();
    }

    /**
     * Agent reply form
     * @param $user
     */
    public static function render_ticket_admin_main_reply_form($user) {
        ?>
        <div class="mkb-admin-ticket-reply-form-wrap">
            <div class="js-mkb-admin-ticket-reply-form mkb-admin-ticket-reply-form">
                <h3>Reply to ticket <span class="mkb-ticket-reply-title-as">(as <?php
                        echo $user->user_login . ' ' . get_avatar(
                            $user->ID,
                            14,
                            '',
                            $user->nickname . ' avatar'
                    ); ?>)</span></h3>

                <?php wp_editor('', 'mkb_reply_editor', array(
                    'textarea_name' => 'mkb_reply_content',
                    'tinymce' => array(
                        'toolbar1'      => 'formatselect,bold,italic,underline,code,forecolor,separator,blockquote,bullist,numlist,alignleft,aligncenter,alignright,separator,link,charmap,removeformat',
                        'toolbar2' => '',
                        'height' => 300
                    )
                )); ?>

                <script>
                    jQuery('#wp-mkb_reply_editor-media-buttons').after(
                        '<div class="mkb-ticket-reply-editor-actions"><ul>' +
                            '<li><a href="#" class="js-mkb-reply-editor-upload"><i class="fa fa-paperclip"></i>Attach file(s)</a></li>' +
                            '<li><a href="#" class="js-mkb-reply-editor-insert-faq"><i class="fa fa-question"></i>Insert FAQ</a></li>' +
                            '<li><a href="#" class="js-mkb-reply-editor-insert-kb"><i class="fa fa-university"></i>Insert KB link</a></li>' +
                            '<li><a href="#" class="js-mkb-reply-editor-insert-canned-response"><i class="fa fa-star-o"></i>Insert Canned Response</a></li>' +
                        '</ul></div>'
                    );
                </script>

                <div class="js-mkb-ticket-admin-attachments-section mkb-ticket-admin-attachments-section">
                    <br>
                    <?php
                    $upload_limits = MKB_Users::instance()->get_current_user_ticket_file_limits();
                    $max_files_text = sprintf(__('Maximum <strong>%s</strong> file(s), up to <strong>%sMb</strong> each.', 'minerva-kb'),
                        $upload_limits['max_files'], $upload_limits['max_file_size']
                    );

                    $allowed_types_text = sprintf(__('Allowed file types: %s', 'minerva-kb'),
                        implode(', ', MKB_Users::instance()->get_current_user_allowed_ticket_filetypes())
                    );

                    MKB_TemplateHelper::render_admin_alert(
                        $max_files_text . ' ' . $allowed_types_text,
                            'info'
                    );
                    ?>
                    <div id="drop-area">
                        <form class="my-form">
                            <i class="mkb-admin-ticket-attachments-bg-icon fa fa-cloud-upload fa-3x"></i>
                            <p><?php _e('Drag n drop files to this area or press button to open file dialog', 'minerva-kb'); ?></p>
                            <div class="js-mkb-ticket-attachments-drop-errors mkb-ticket-attachments-drop-errors"></div>
                            <?php

                            $allowed_filetypes = MKB_Users::instance()->get_current_user_allowed_ticket_filetypes();
                            $allowed_filetypes = array_map(function($type) {
                                return '.' . $type;
                                }, $allowed_filetypes);
                            $allowed_filetypes = implode(',', $allowed_filetypes);

                            ?>
                            <input type="file" name="mkb_ticket_reply_files[]" id="fileElem" class="js-mkb-ticket-reply-file-store" multiple accept="<?php esc_attr_e($allowed_filetypes); ?>" onchange="handleFiles(this.files)">
                            <label class="button button-primary" for="fileElem"><?php _e('Upload file(s)', 'minerva-kb'); ?></label>
                            <button class="js-mkb-admin-ticket-attachments-clear mkb-admin-ticket-attachments-clear button button-link-delete"><?php _e('Remove files', 'minerva-kb'); ?></button>
                        </form>
                        <br>
                        <br>
                        <div class="js-mkb-attachments-upload-preview" id="gallery"></div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * @param array $reply
     * @param string $type
     */
    public static function render_ticket_admin_reply($reply, $type = 'reply') {
        $post_id = $reply['post_id'];
        $content = $reply['content'];
        $role = $reply['role'];
        $status = $reply['status'];
        $author_id = $reply['author_id'];
        $is_edited = $type === 'original' ? false : $reply['is_edited'];

        $row_extra_class = 'mkb-ticket-timeline-row--role-' . esc_attr($role);

        ?>
        <div class="mkb-ticket-timeline-row <?php esc_attr_e($row_extra_class); ?>">
            <div class="mkb-ticket-reply js-mkb-ticket-reply mkb-ticket-reply--role-<?php esc_attr_e($role); ?> mkb-ticket-reply--status-<?php esc_attr_e($status); ?><?php if ($is_edited): ?> mkb-ticket-reply--status-edited<?php endif; ?>">
                <span class="mkb-ticket-reply-label mkb-ticket-reply-label--edited"><?php _e('Edited', 'minerva-kb'); ?></span>
                <span class="mkb-ticket-reply-label mkb-ticket-reply-label--deleted"><?php _e('Deleted', 'minerva-kb'); ?></span>
                <div class="mkb-ticket-reply__content">
                    <?php if ($type === 'original'): ?>
                        <p><strong><?php _e('Original message', 'minerva-kb'); ?></strong></p>
                    <?php endif; ?>

                    <div class="js-mkb-reply-content-holder mkb-reply-content-holder">
                        <?php echo apply_filters('the_content', $content) ?>
                    </div>

                    <div>
                        <?php
                        $attachments = get_posts( array(
                            'post_type' => 'attachment',
                            'posts_per_page' => -1,
                            'post_parent' => $post_id,
                            'exclude' => get_post_thumbnail_id($post_id)
                        ) );

                        if ($attachments): ?>
                            <div class="mkb-admin-reply-attachments-title"><?php _e('Attached files', 'minerva-kb'); ?></div>
                            <ul class="mkb-admin-reply-attachments-list">
                                <?php foreach ($attachments as $attachment):

                                    $url = wp_get_attachment_url($attachment->ID);
                                    $filename = basename($url);

                                    ?><li><a href="<?php esc_attr_e($url); ?>" target="_blank"><?php esc_html_e($filename); ?></a></li><?php
                                endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mkb-ticket-reply__avatar">
                    <?php self::the_user_avatar($author_id); ?>
                </div>

                <div class="mkb-ticket-reply__meta js-mkb-ticket-reply-meta" data-reply-id="<?php esc_attr_e($post_id); ?>">
                    <span class="mkb-ticket-reply__meta-date js-mkb-ticket-reply-meta-date">
                        <?php

                        if ($status === 'trash'): // deleted reply

                            $status_modifier_id = get_post_meta($post_id, '_mkb_status_last_modified_by', true);
                            $reply_timestamp = get_post_modified_time('U', false, $post_id);
                            $reply_timestamp_gmt = get_post_modified_time('U', true, $post_id);

                            ?><span class="mkb-ticket-reply__meta--deleted"><?php
                                _e('Deleted by:', 'minerva-kb'); ?>&nbsp;<?php
                                the_author_meta('display_name', $status_modifier_id); ?>&nbsp;<?php
                                MKB_Utils::render_human_date($reply_timestamp_gmt, $reply_timestamp);
                            ?></span><?php

                        endif;

                        $reply_timestamp = get_post_time('U', false, $post_id);
                        $reply_timestamp_gmt = get_post_time('U', true, $post_id);

                        ?><span class="mkb-ticket-reply__meta--posted"><?php
                            _e('Posted by:', 'minerva-kb'); ?>&nbsp;<?php
                            the_author_meta('display_name', $author_id); ?>&nbsp;<?php
                            MKB_Utils::render_human_date($reply_timestamp_gmt, $reply_timestamp);
                        ?></span><?php

                        if ($is_edited): // edited reply

                            $recent_editor_id = get_post_meta($post_id, '_edit_last', true);
                            $recent_editor_id = $recent_editor_id ? $recent_editor_id : $author_id;
                            $recent_editor = get_user_by('ID', $recent_editor_id);
                            $reply_modified_timestamp = get_post_modified_time('U', false, $post_id);
                            $reply_modified_timestamp_gmt = get_post_modified_time('U', true, $post_id);

                            ?><span class="mkb-ticket-reply__meta--edited js-mkb-ticket-reply-meta-edited"> - <?php
                                _e('Edited by:', 'minerva-kb'); ?>&nbsp;<?php
                                the_author_meta('display_name', $recent_editor->ID); ?>&nbsp;<?php
                                MKB_Utils::render_human_date($reply_modified_timestamp_gmt, $reply_modified_timestamp);
                            ?></span><?php

                        endif; // end of edited

                    ?></span>

                    <?php if ($role === 'agent'): ?>
                        <div class="mkb-ticket-reply__actions">
                            <ul>
                                <li class="mkb-ticket-reply-save-as"><?php _e('Save as:', 'minerva-kb'); ?></li>
                                <li class="mkb-ticket-reply-save-faq"><a href="#" class="js-mkb-ticket-reply-save-faq"><i class="fa fa-question"></i> <?php _e('FAQ', 'minerva-kb'); ?></a></li>
                                <li class="mkb-ticket-reply-save-kb"><a href="#" class="js-mkb-ticket-reply-save-kb"><i class="fa fa-university"></i> <?php _e('KB', 'minerva-kb'); ?></a></li>
                                <li class="mkb-ticket-reply-save-canned"><a href="#" class="js-mkb-ticket-reply-save-canned-response"><i class="fa fa-star-o"></i> <?php _e('Canned Response', 'minerva-kb'); ?></a></li>
                                <li class="mkb-ticket-reply-edit"><a href="#" class="js-mkb-ticket-reply-edit"><i class="fa fa-pencil"></i> <?php _e('Edit', 'minerva-kb'); ?></a></li>
                                <li class="mkb-ticket-reply-delete"><a href="#" class="js-mkb-ticket-reply-remove"><i class="fa fa-trash"></i> <?php _e('Remove', 'minerva-kb'); ?></a></li>
                                <li class="mkb-ticket-reply-restore"><a href="#" class="js-mkb-ticket-reply-restore"><i class="fa fa-undo"></i> <?php _e('Restore', 'minerva-kb'); ?></a></li>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="mkb-ticket-reply__request-overlay"><?php _e('Please wait...', 'minerva-kb'); ?></div>
            </div>
        </div>
        <?php
    }

    /**
     * @param $content
     * @param $timestamp
     * @param $timestamp_gmt
     * @param string $event_id
     */
    public static function render_ticket_admin_history_entry($content, $timestamp, $timestamp_gmt, $event_id) {
        $event_id = $event_id ? $event_id : 'default';

        $icons_map = array(
            'default' => 'fa-tag',
            'close' => 'fa-lock',
            'reopen' => 'fa-undo',
            'open' => 'fa-arrow-up'
        );

        ?>
        <div class="mkb-ticket-timeline-row mkb-ticket-timeline-row--history">
            <div class="mkb-ticket-history-entry">
                <span class="mkb-ticket-history-entry-icon mkb-ticket-history-entry-icon--<?php esc_attr_e($event_id); ?>">
                    <i class="fa <?php esc_attr_e($icons_map[$event_id]); ?>"></i>
                </span>

                <div class="mkb-ticket-history-entry-text">
                    <?php echo $content; ?>, <?php MKB_Utils::render_human_date($timestamp_gmt, $timestamp); ?>
                </div>
            </div>
        </div>
        <?php
    }

}