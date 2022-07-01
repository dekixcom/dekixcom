<?php
/**
 * Project: MinervaKB.
 * Copyright: 2015-2017 @KonstruktStudio
 */
class MKB_WooCommerce {

    private $account_support_url = 'support';

    public function __construct() {
        $this->add_support_section();
    }

    private function add_support_section() {
        if (!MKB_Options::option('woo_add_support_account_tab')) {
            return;
        }

        if (MKB_Options::option('woo_account_section_url')) {
            $this->account_support_url = MKB_Options::option('woo_account_section_url');
        }

        add_action('init', array($this, 'add_support_endpoint'));
        add_filter('query_vars', array($this, 'support_query_vars'), 0);
        add_filter('woocommerce_account_menu_items', array($this, 'add_support_link_to_account'));
        add_filter('the_title', array($this, 'support_endpoint_title'));
        add_action('woocommerce_account_' . $this->account_support_url . '_endpoint', array($this, 'support_content'));
    }

    /**
     * Support endpoint rewrite
     */
    public function add_support_endpoint() {
        add_rewrite_endpoint($this->account_support_url, EP_ROOT | EP_PAGES);
    }

    /**
     * Parse query var
     * @param $vars
     * @return array
     */
    public function support_query_vars( $vars ) {
        $vars[] = $this->account_support_url;
        return $vars;
    }

    /**
     * Account items
     * @param $items
     * @return array
     */
    public function add_support_link_to_account($items) {
        $total_items = count($items);
        $insert_at = $total_items - 1;

        $items = array_slice($items, 0, $insert_at, true) +
            array($this->account_support_url => MKB_Options::option('woo_account_section_title')) +
            array_slice($items, $insert_at, $total_items - 1, true) ;

        return $items;
    }

    /**
     * Account item title
     * @param $title
     * @return string
     */
    public function support_endpoint_title($title) {
        global $wp_query;

        $is_support_endpoint = isset($wp_query->query_vars[$this->account_support_url]);

        if ($is_support_endpoint && !is_admin() && is_main_query() && in_the_loop() && function_exists('is_account_page') && is_account_page()) {
            $title = MKB_Options::option('woo_account_section_title');
            remove_filter( 'the_title', array($this, 'support_endpoint_title'));
        }

        return $title;
    }

    /**
     * Account item title
     */
    public function support_content() {
        echo do_shortcode(MKB_Options::option('woo_account_section_content'));
    }
}
