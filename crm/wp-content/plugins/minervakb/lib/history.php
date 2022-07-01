<?php
/**
 * Project: MinervaKB.
 * Copyright: 2015-2017 @KonstruktStudio
 */

/**
 * MinervaKB custom history DB tables schema and internal API
 */
class MKB_History {

    // tables plugin prefix
    const PLUGIN_PREFIX = 'mkb_';

    // table names
    const HISTORY_TABLE_NAME = 'history';

    /**
     * Event types
     */
    const EVENT_TYPE_TICKET_OPENED = 103;
    const EVENT_TYPE_TICKET_CLOSED = 104;
    const EVENT_TYPE_TICKET_REOPENED = 105;

    // Ticket Meta
    const EVENT_TYPE_TICKET_ASSIGNEE_CHANGED = 120;
    const EVENT_TYPE_TICKET_TYPE_CHANGED = 121;
    const EVENT_TYPE_TICKET_STATUS_CHANGED = 122;
    const EVENT_TYPE_TICKET_PRIORITY_CHANGED = 123;
    const EVENT_TYPE_TICKET_DEPARTMENT_CHANGED = 124;
    const EVENT_TYPE_TICKET_PRODUCT_CHANGED = 125;
    const EVENT_TYPE_TICKET_CHANNEL_CHANGED = 126;

    /**
     * Gets human readable event info
     * @param $event_code
     */
    public static function get_event_info($event_code) {
        $events_info = array(
            self::EVENT_TYPE_TICKET_OPENED => array(
                'id' => 'open'
            ),
            self::EVENT_TYPE_TICKET_CLOSED => array(
                'id' => 'close'
            ),
            self::EVENT_TYPE_TICKET_REOPENED => array(
                'id' => 'reopen'
            ),
        );

        if (!isset($events_info[$event_code])) {
            return array(
                'id' => null
            );
        }

        return $events_info[$event_code];
    }

    /**
     * General tax term change tracking
     * @param $event_type
     * @param $post_id
     * @param $user_id
     * @param $term_id
     * @param $previous_term_id
     */
    public static function track_ticket_taxonomy_term_change($event_type, $post_id, $user_id, $term_id, $previous_term_id = null) {
        if (!$post_id) {
            return;
        }

        self::register_history_event($event_type, $post_id, $user_id, $term_id, $previous_term_id);
    }

    /**
     * General status change tracking
     * @param $post_id
     * @param $user_id
     * @param $status
     * @param $status_label
     * @param $previous_status
     * @param $previous_status_label
     * @param $initiator
     */
    public static function track_ticket_status_change($post_id, $user_id, $status, $status_label, $previous_status, $previous_status_label, $initiator) {
        if (!$post_id) {
            return;
        }

        self::register_history_event(
            self::EVENT_TYPE_TICKET_STATUS_CHANGED,
            $post_id,
            $user_id,
            $status,
            $status_label,
            $previous_status,
            $previous_status_label,
            $initiator
        );
    }

    /**
     * Assignee change tracking
     * @param $post_id
     * @param $user_id
     * @param $assignee
     * @param $previous_assignee
     */
    public static function track_ticket_assignee_change($post_id, $user_id, $assignee, $previous_assignee) {
        if (!$post_id) {
            return;
        }

        self::register_history_event(
            self::EVENT_TYPE_TICKET_ASSIGNEE_CHANGED,
            $post_id,
            $user_id,
            $assignee,
            $previous_assignee
        );
    }

    /**
     * Channel change tracking
     * @param $post_id
     * @param $user_id
     * @param $channel
     * @param $previous_channel
     */
    public static function track_ticket_channel_change($post_id, $user_id, $channel, $previous_channel) {
        if (!$post_id) {
            return;
        }

        self::register_history_event(
            self::EVENT_TYPE_TICKET_CHANNEL_CHANGED,
            $post_id,
            $user_id,
            $channel,
            $previous_channel
        );
    }

    /**
     * Open tracking
     * @param $post_id
     * @param $user_id
     * @param $previous_status
     * @param $previous_status_label
     * @param $initiator
     * TODO: unused vars
     */
    public static function track_ticket_opened_event($post_id, $user_id, $previous_status, $previous_status_label, $initiator) {
        if (!$post_id) {
            return;
        }

        self::register_history_event(
            self::EVENT_TYPE_TICKET_OPENED,
            $post_id,
            $user_id,
            null,
            null,
            null,
            null,
            $initiator
        );
    }

    /**
     * Close tracking
     * @param $post_id
     * @param $user_id
     * @param $previous_status
     * @param $previous_status_label
     * @param $initiator
     */
    public static function track_ticket_closed_event($post_id, $user_id, $previous_status, $previous_status_label, $initiator) {
        if (!$post_id) {
            return;
        }

        self::register_history_event(
            self::EVENT_TYPE_TICKET_CLOSED,
            $post_id,
            $user_id,
            null,
            null,
            $previous_status,
            $previous_status_label,
            $initiator
        );
    }

    /**
     * Reopen tracking
     * @param $post_id
     * @param $user_id
     * @param $previous_status
     * @param $previous_status_label
     * @param $initiator
     */
    public static function track_ticket_reopen_event($post_id, $user_id, $previous_status, $previous_status_label, $initiator) {
        if (!$post_id) {
            return;
        }

        self::register_history_event(
            self::EVENT_TYPE_TICKET_REOPENED,
            $post_id,
            $user_id,
            MKB_Tickets::get_ticket_assignee($post_id),
            null,
            $previous_status,
            $previous_status_label,
            $initiator
        );
    }

    public static function get_tickets_events_by_type($event_type) {
        global $wpdb;

        $history_table_name = self::get_table_name_for( self::HISTORY_TABLE_NAME );
        $order_by = 'id';
        $order = 'DESC';

        return $wpdb->get_results(
            $wpdb->prepare(
            "SELECT
                    id,
                    event_type,
                    event_datetime,
                    event_datetime_gmt,
                    post_id,
                    user_id,
                    meta1,
                    meta2,
                    meta3,
                    meta4,
                    meta5,
                    meta6,
                    extra1,
                    extra2
                FROM $history_table_name
                WHERE event_type=%d
                ORDER BY $order_by $order;",
                $event_type
            )
        );
    }

    public static function get_tickets_close_events() {
        return self::get_tickets_events_by_type(self::EVENT_TYPE_TICKET_CLOSED);
    }

    public static function get_tickets_reopen_events() {
        return self::get_tickets_events_by_type(self::EVENT_TYPE_TICKET_REOPENED);
    }

    /**
     * @param $post_id
     * @return array|object|null
     */
    public static function get_raw_ticket_history($post_id) {
        global $wpdb;

        $history_table_name = self::get_table_name_for( self::HISTORY_TABLE_NAME );
        $order_by = 'id';
        $order = 'DESC';

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT
                    id,
					event_type,
					event_datetime,
					event_datetime_gmt,
					user_id,
					meta1,
					meta2,
					meta3,
					meta4,
					meta5,
					meta6,
					extra1,
					extra2
				FROM $history_table_name
				WHERE post_id=%d
				ORDER BY $order_by $order;",
                $post_id
            )
        );
    }

    public static function get_ticket_history($post_id) {
        $deleted_text = apply_filters('minerva_entry_deleted_or_null_text','deleted');

        $history = self::get_raw_ticket_history($post_id);

        if (!is_array($history) || empty($history)) {
            return array();
        }

        $parsed_history = array();

        foreach($history as $history_info) {
            $user = false;
            $user_name = $deleted_text;

            if ($history_info->user_id) {
                $user = get_user_by('ID', $history_info->user_id);

                if ($user) {
                    $user_name = $user->display_name; // TODO check
                }
            }

            $event_info = self::get_event_info($history_info->event_type);

            switch($history_info->event_type) {
                /**
                 * Type change
                 */
                case self::EVENT_TYPE_TICKET_TYPE_CHANGED:
                    array_push($parsed_history,
                        self::get_taxonomy_change_event_data(
                            $history_info,
                            'mkb_ticket_type',
                            __('<strong>%s</strong> has changed ticket type to <strong>%s</strong>', 'minerva-kb'),
                            $user_name,
                            $history_info->event_type
                        )
                    );
                    break;

                /**
                 * Status change
                 */
                case self::EVENT_TYPE_TICKET_STATUS_CHANGED:
                    $status = MKB_Tickets::get_ticket_status_by_id($history_info->meta1);

                    if (!$status) {
                        break;
                    }

                    array_push($parsed_history,
                        array(
                            'id' => $history_info->id,
                            'timestamp' => strtotime($history_info->event_datetime),
                            'timestamp_gmt' => strtotime($history_info->event_datetime_gmt),
                            'text' => sprintf(
                                __('<strong>%s</strong> has changed ticket status to <strong>%s</strong>', 'minerva-kb'),
                                $user_name,
                                $status['label']
                            ),
                            'event_id' => $event_info['id']
                        )
                    );
                    break;

                /**
                 * Channel change
                 */
                case self::EVENT_TYPE_TICKET_CHANNEL_CHANGED:
                    $channel = MKB_Tickets::get_ticket_channel_by_id($history_info->meta1);

                    if (!$channel) {
                        break;
                    }

                    array_push($parsed_history,
                        array(
                            'id' => $history_info->id,
                            'timestamp' => strtotime($history_info->event_datetime),
                            'timestamp_gmt' => strtotime($history_info->event_datetime_gmt),
                            'text' => sprintf(
                                __('<strong>%s</strong> has changed ticket channel to <strong>%s</strong>', 'minerva-kb'),
                                $user_name,
                                $channel['label']
                            ),
                            'event_id' => $event_info['id']
                        )
                    );
                    break;

                /**
                 * Assignee change
                 */
                case self::EVENT_TYPE_TICKET_ASSIGNEE_CHANGED:
                    $assignee_label = '';

                    if ($history_info->meta1) {
                        $assignee = get_user_by('id', $history_info->meta1);

                        if ($assignee) {
                            $assignee_label = $assignee->display_name;
                        } else {
                            $assignee_label = '[DELETED]';
                        }
                    } else {
                        // unassigned
                        $assignee_label = __('Unassigned', 'minerva-kb');
                    }

                    array_push($parsed_history,
                        array(
                            'id' => $history_info->id,
                            'timestamp' => strtotime($history_info->event_datetime),
                            'timestamp_gmt' => strtotime($history_info->event_datetime_gmt),
                            'text' => sprintf(
                                __('<strong>%s</strong> has changed assignee to <strong>%s</strong>', 'minerva-kb'),
                                $user_name,
                                $assignee_label
                            ),
                            'event_id' => $event_info['id']
                        )
                    );
                    break;

                /**
                 * Opened
                 */
                case self::EVENT_TYPE_TICKET_OPENED:
                    array_push($parsed_history,
                        array(
                            'id' => $history_info->id,
                            'timestamp' => strtotime($history_info->event_datetime),
                            'timestamp_gmt' => strtotime($history_info->event_datetime_gmt),
                            'text' => sprintf(
                                __('<strong>%s</strong> has opened this ticket', 'minerva-kb'),
                                $user_name
                            ),
                            'event_id' => $event_info['id']
                        )
                    );
                    break;

                /**
                 * Closed
                 */
                case self::EVENT_TYPE_TICKET_CLOSED:
                    array_push($parsed_history,
                        array(
                            'id' => $history_info->id,
                            'timestamp' => strtotime($history_info->event_datetime),
                            'timestamp_gmt' => strtotime($history_info->event_datetime_gmt),
                            'text' => sprintf(
                                __('<strong>%s</strong> has closed this ticket', 'minerva-kb'),
                                $user_name
                            ),
                            'event_id' => $event_info['id']
                        )
                    );
                    break;

                /**
                 * Reopen
                 */
                case self::EVENT_TYPE_TICKET_REOPENED:
                    array_push($parsed_history,
                        array(
                            'id' => $history_info->id,
                            'timestamp' => strtotime($history_info->event_datetime),
                            'timestamp_gmt' => strtotime($history_info->event_datetime_gmt),
                            'text' => sprintf(
                                __('<strong>%s</strong> has reopened this ticket', 'minerva-kb'),
                                $user_name
                            ),
                            'event_id' => $event_info['id']
                        )
                    );
                    break;

                /**
                 * Priority change
                 */
                case self::EVENT_TYPE_TICKET_PRIORITY_CHANGED:
                    array_push($parsed_history,
                        self::get_taxonomy_change_event_data(
                            $history_info,
                            'mkb_ticket_priority',
                            __('<strong>%s</strong> has changed ticket priority to <strong>%s</strong>', 'minerva-kb'),
                            $user_name,
                            $history_info->event_type
                        )
                    );
                    break;

                /**
                 * Department change
                 */
                case self::EVENT_TYPE_TICKET_DEPARTMENT_CHANGED:
                    array_push($parsed_history,
                        self::get_taxonomy_change_event_data(
                            $history_info,
                            'mkb_ticket_department',
                            __('<strong>%s</strong> has changed ticket department to <strong>%s</strong>', 'minerva-kb'),
                            $user_name,
                            $history_info->event_type
                        )
                    );
                    break;

                /**
                 * Product change
                 */
                case self::EVENT_TYPE_TICKET_PRODUCT_CHANGED:
                    array_push($parsed_history,
                        self::get_taxonomy_change_event_data(
                            $history_info,
                            'mkb_ticket_product',
                            __('<strong>%s</strong> has changed ticket product to <strong>%s</strong>', 'minerva-kb'),
                            $user_name,
                            $history_info->event_type
                        )
                    );
                    break;

                default:
                    break;
            }
        }

        return $parsed_history;
    }

    /**
     * @param $event
     * @param $taxonomy
     * @param $text_template
     * @param $user_name
     * @return array
     */
    private static function get_taxonomy_change_event_data($event, $taxonomy, $text_template, $user_name, $event_type) {
        $deleted_text = apply_filters('minerva_entry_deleted_or_null_text','deleted');

        $term = get_term_by( 'term_id', $event->meta1, $taxonomy);

        if (!$term || is_wp_error($term)) {
            $term = null;
        }

        $event_info = self::get_event_info($event_type);

        return array(
            'id' => $event->id,
            'timestamp' => strtotime($event->event_datetime),
            'timestamp_gmt' => strtotime($event->event_datetime_gmt),
            'text' => sprintf($text_template, $user_name, isset($term->name) ? $term->name : $deleted_text),
            'event_id' => $event_info['id']
        );
    }

    /**
     * General history entry insert
     * @param $event_type
     * @param null $post_id
     * @param null $user_id
     * @param null $meta1
     * @param null $meta2
     * @param null $meta3
     * @param null $meta4
     * @param null $meta5
     * @param null $meta6
     * @param null $extra1
     * @param null $extra2
     */
    private static function register_history_event(
        $event_type,
        $post_id = null,
        $user_id = null,
        $meta1 = null,
        $meta2 = null,
        $meta3 = null,
        $meta4 = null,
        $meta5 = null,
        $meta6 = null,
        $extra1 = null,
        $extra2 = null
    ) {
        global $wpdb;

        $creation_timestamp = current_time( 'mysql' );
        $creation_timestamp_gmt = current_time( 'mysql', true );

        $wpdb->insert(
            self::get_table_name_for( self::HISTORY_TABLE_NAME ),
            array(
                'event_type' => $event_type,
                'event_datetime' => $creation_timestamp,
                'event_datetime_gmt' => $creation_timestamp_gmt,
                'post_id' => $post_id,
                'user_id' => $user_id,
                'meta1' => $meta1,
                'meta2' => $meta2,
                'meta3' => $meta3,
                'meta4' => $meta4,
                'meta5' => $meta5,
                'meta6' => $meta6,
                'extra1' => $extra1,
                'extra2' => $extra2,
            ),
            array(
                '%d',
                '%s',
                '%s',
                '%d',
                '%d',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
            )
        );
    }

    /**
     * @param $ticket_id
     */
    public static function delete_history_for_ticket_id($ticket_id) {
        global $wpdb;

        $wpdb->delete(self::get_table_name_for( self::HISTORY_TABLE_NAME ), array('post_id' => $ticket_id));
    }

    /**
     * Removes all history data
     */
    public static function reset_history_data() {
        self::delete_schema();
        self::create_schema();
    }

    /**
     * Helper to build table names
     * @param $name
     *
     * @return string
     */
    private static function get_table_name_for( $name ) {
        global $wpdb;

        return $wpdb->prefix . self::PLUGIN_PREFIX . $name;
    }

    /**
     * For use in WP SQL filters
     * @param $name
     *
     * @return string
     */
    private static function get_wp_table_name_for( $name ) {
        global $wpdb;

        return $wpdb->prefix . $name;
    }

    /**
     * Helper to get table charset and collate
     * @return string
     */
    private static function get_wp_charset_collate() {
        global $wpdb;
        $charset_collate = '';

        if ( ! empty( $wpdb->charset ) ) {
            $charset_collate = " DEFAULT CHARACTER SET $wpdb->charset";
        }

        if ( ! empty( $wpdb->collate ) ) {
            $charset_collate .= " COLLATE $wpdb->collate";
        }

        return $charset_collate;
    }

    /**
     * Gets SQL to create history table
     * @return string
     */
    private static function get_history_structure() {
        $table_name = self::get_table_name_for( self::HISTORY_TABLE_NAME );

        return "CREATE TABLE $table_name (
		      id bigint unsigned NOT NULL auto_increment,
		      event_type smallint unsigned NOT NULL,
		      event_datetime datetime NOT NULL,
		      event_datetime_gmt datetime NOT NULL,
		      post_id bigint default NULL,
		      user_id bigint default NULL,
		      meta1 varchar(255) default NULL,
		      meta2 varchar(255) default NULL,
		      meta3 varchar(255) default NULL,
		      meta4 varchar(255) default NULL,
		      meta5 varchar(255) default NULL,
		      meta6 varchar(255) default NULL,
		      extra1 longtext default NULL,
		      extra2 longtext default NULL,
		      PRIMARY KEY id (id)
		    )";
    }

    public static function get_all_table_names() {
        return array(
            self::get_table_name_for( self::HISTORY_TABLE_NAME )
        );
    }

    /**
     * Creates custom tables DB schema (to be called on plugin activation)
     */
    public static function create_schema() {
        $wp_charset_collate = self::get_wp_charset_collate();
        $sql_postfix = $wp_charset_collate . ';';

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        dbDelta( self::get_history_structure() . $sql_postfix );
    }

    /**
     * Deletes all custom tables
     */
    public static function delete_schema() {
        global $wpdb;

        if ( !current_user_can( 'administrator' ) ) {
            wp_die();
        }

        $wpdb->query( 'DROP TABLE IF EXISTS ' . self::get_table_name_for( self::HISTORY_TABLE_NAME ) );
    }
}

// delete the table whenever a blog is deleted
function mkb_history_on_delete_blog( $tables ) {
    $mkb_tables = MKB_History::get_all_table_names();

    foreach($mkb_tables as $table) {
        $tables[] = $table;
    }

    return $tables;
}
add_filter( 'wpmu_drop_tables', 'mkb_history_on_delete_blog' );