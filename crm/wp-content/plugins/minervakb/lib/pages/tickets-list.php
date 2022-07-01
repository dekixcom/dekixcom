<?php

/**
 * Tickets Admin List page controller
 * Class MinervaKB_TicketsListPage
 */

class MinervaKB_TicketsListPage {

	private $SCREEN_BASE = null;

    /**
     * TODO: maybe cache similar requests for performance
     * MinervaKB_TicketsDashboardPage constructor.
     * @param $deps
     */
	public function __construct($deps) {

		$this->setup_dependencies( $deps );

		$this->SCREEN_BASE = 'mkb_ticket_page_minerva-mkb_ticket-submenu-tickets-list';

		add_action( 'admin_menu', array( $this, 'add_submenu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'load_assets' ) );
	}

	/**
	 * Sets up dependencies
	 * @param $deps
	 */
	private function setup_dependencies($deps) {
		// TODO:
	}

	/**
	 * Adds dashboard submenu page
	 */
	public function add_submenu() {
		add_submenu_page(
			'edit.php?post_type=' . 'mkb_ticket',
			__( 'Support Tickets', 'minerva-kb' ),
			__( 'Support Tickets', 'minerva-kb' ),
			current_user_can('administrator') ?
                'manage_options' :
                'mkb_view_tickets', // TODO: edit permissions
			'minerva-mkb_ticket-submenu-tickets-list',
			array( $this, 'submenu_html' ),
            0
		);
	}

	/**
	 * Gets dashboard page html
	 */
	public function submenu_html() {
		?>
        <div class="mkb-admin-page-header">
			<span class="mkb-header-logo mkb-header-item" data-version="v<?php echo esc_attr(MINERVA_KB_VERSION); ?>">
				<img class="logo-img" src="<?php echo esc_attr( MINERVA_KB_IMG_URL . 'logo.png' ); ?>" title="logo"/>
			</span>
            <span class="mkb-header-title mkb-header-item"><?php _e( 'Support Tickets', 'minerva-kb' ); ?></span>
            <?php MinervaKB_AutoUpdate::registered_label(); ?>
        </div>

        <div id="tickets-list"></div>
	<?php
	}

	/**
	 * Loads admin assets
	 */
	public function load_assets() {

		$screen = get_current_screen();

		if ( $screen->base !== $this->SCREEN_BASE ) {
			return;
		}

        // toastr
        wp_enqueue_style( 'minerva-kb/admin-toastr-css', MINERVA_KB_PLUGIN_URL . 'assets/css/vendor/toastr/toastr.min.css', false, '2.1.3' );

        wp_enqueue_script( 'minerva-kb/admin-toastr-js', MINERVA_KB_PLUGIN_URL . 'assets/js/vendor/toastr/toastr.min.js', array(), '2.1.3', true );

        wp_enqueue_script( 'minerva-kb/moment-js', MINERVA_KB_PLUGIN_URL . 'assets/js/vendor/moment/moment-with-locales.js', array(), '2.24.0', true );

        wp_enqueue_script( 'minerva-kb/admin-tickets-list-js', MINERVA_KB_PLUGIN_URL . 'assets/js/admin/tickets-list.js', array(
            'jquery',
            'minerva-kb/admin-ui-js',
            'minerva-kb/admin-toastr-js'
        ), null, true );

		wp_localize_script( 'minerva-kb/admin-tickets-list-js', 'MinervaTicketsList', array(
            'statuses' => MKB_Tickets::get_ticket_statuses(),
            'types' => $this->get_ticket_taxonomy('mkb_ticket_type', array('color')),
            'products' => $this->get_ticket_taxonomy('mkb_ticket_product'),
            'departments' => $this->get_ticket_taxonomy('mkb_ticket_department'),
            'priorities' => $this->get_ticket_taxonomy('mkb_ticket_priority', array('color')),
        ));
	}

    public function get_ticket_taxonomy($tax, $options = array()) {
        $terms = get_terms(array('taxonomy' => $tax, 'hide_empty' => false));
        $result = array();

        if (!empty($terms)) {

            foreach($terms as $t) {
                $term = (array)$t;

                if (!empty($options)) {
                    foreach($options as $option) {
                        $term[$option] = MKB_TemplateHelper::get_taxonomy_option($t, $tax, $option);
                    }
                }

                $result []= $term;
            }
        }

        return $result;
    }
}