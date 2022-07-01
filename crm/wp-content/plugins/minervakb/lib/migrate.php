<?php
/**
 * Project: MinervaKB.
 * Copyright: 2015-2020 @KonstruktStudio
 */
class MKB_Migrations {

    const MIGRATIONS_OPTIONS_KEY = 'minerva-kb-migrations';

    public function __construct() {
        if (is_admin()) {
            add_action('init', array($this, 'run'), 999);
        }
    }

    public function run() {
        $is_updated = false;
        $completed = self::get_completed_migrations();

        if (!in_array('2.0.0', $completed)) {
            self::migrate_to_2_0_0();
            $completed[] = '2.0.0';
            $is_updated = true;
        }

        if (!in_array('2.0.1', $completed)) {
            self::migrate_to_2_0_1();
            $completed[] = '2.0.1';
            $is_updated = true;
        }

        if ($is_updated) {
            add_action('admin_notices', array($this, 'admin_notices'));

            update_option(self::MIGRATIONS_OPTIONS_KEY, $completed);
        }
    }

    private static function get_completed_migrations() {
        $migrations = get_option(self::MIGRATIONS_OPTIONS_KEY);

        return $migrations && is_array($migrations) ? $migrations : array();
    }

    public function admin_notices() {
        MKB_TemplateHelper::render_admin_notice('Plugin data has been updated', 'success');
    }

    /**
     * 2.0.0 migration
     * - ticket user roles & permissions for FAQ, KB, Glossary
     */
    private static function migrate_to_2_0_0() {
        MKB_Users::create_users_and_caps();

        self::create_ticket_default_terms();

        flush_rewrite_rules(false); // new CPTs added
    }

    /**
     * 2.0.1 migration
     * mkb_view_tickets removed from customer role
     */
    private static function migrate_to_2_0_1() {
        // users
        $support_user_role = get_role(MKB_Users::CUSTOMER_ROLE);

        if ($support_user_role) {
            $support_user_role->remove_cap('mkb_view_tickets');
        }
    }

    public static function create_ticket_default_terms() {
        // ticket types
        $types = array(
            array(
                'name' => 'Question',
                'color' => '#4bb7e5'
            ),
            array(
                'name' => 'Bug',
                'color' => '#e5504b'
            ),
            array(
                'name' => 'Feature Request',
                'color' => '#9e4ae2'
            ),
        );

        foreach($types as $type) {
            if (!get_term_by('name', $type['name'], 'mkb_ticket_type')) {
                $term = wp_insert_term($type['name'], 'mkb_ticket_type');

                if (!is_wp_error($term)) {
                    MKB_TemplateHelper::set_taxonomy_option($term['term_id'], 'mkb_ticket_type', 'color', $type['color']);
                }
            }
        }

        // ticket priorities
        $priorities = array(
            array(
                'name' => 'Low',
                'color' => '#4f9fef'
            ),
            array(
                'name' => 'Medium',
                'color' => '#f98f52'
            ),
            array(
                'name' => 'High',
                'color' => '#dd3333'
            ),
            array(
                'name' => 'Critical',
                'color' => '#ff3a3a'
            )
        );

        foreach($priorities as $priority) {
            if (!get_term_by('name', $priority['name'], 'mkb_ticket_priority')) {
                $term = wp_insert_term($priority['name'], 'mkb_ticket_priority');

                if (!is_wp_error($term)) {
                    MKB_TemplateHelper::set_taxonomy_option($term['term_id'], 'mkb_ticket_priority', 'color', $priority['color']);
                }
            }
        }
    }
}
