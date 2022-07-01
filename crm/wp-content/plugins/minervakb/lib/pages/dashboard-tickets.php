<?php

/**
 * Tickets Dashboard page controller
 * Class MinervaKB_TicketsDashboardPage
 */

class MinervaKB_TicketsDashboardPage {

    private $department = null;

    private $agent = null;

    private $agent_colors = array();

    const DAILY_CHART_DAYS = 14;

    const NOT_SET_COLOR = 'rgb(205,207,208)';

    private $NO_TICKETS_TEXT = '';

	private $SCREEN_BASE = null;

    /**
     * TODO: maybe cache similar requests for performance
     * MinervaKB_TicketsDashboardPage constructor.
     * @param $deps
     */
	public function __construct($deps) {

		$this->setup_dependencies( $deps );

		$this->SCREEN_BASE = 'mkb_ticket_page_minerva-mkb_ticket-submenu-dashboard';
        $this->NO_TICKETS_TEXT = __('There are no tickets for current filters', 'minerva-kb');

        add_action( 'admin_menu', array( $this, 'add_submenu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'load_assets' ) );
	}

    /**
     * Parse active filter
     */
	private function parse_filters() {
        if (isset($_REQUEST['department']) && $_REQUEST['department']) {
            $term_id = (int)$_REQUEST['department'];

            $term = get_term_by('id', $term_id, 'mkb_ticket_department');

            if ($term) {
                $this->department = $term;
            }
        }

        if (isset($_REQUEST['agent']) && $_REQUEST['agent']) {
            $user_id = (int)$_REQUEST['agent'];

            $user = get_user_by('id', $user_id);

            if ($user) {
                $this->agent = $user;
            }
        }
    }

    /**
     * Default agent colors, for graphs consistency
     */
    private function set_agent_default_colors() {
        $default_colors = [
            'rgb(54,162,235)',
            'rgb(255,194,35)',
            'rgb(246,150,255)',
            'rgb(255,136,32)',
            'rgb(34,192,69)',
            'rgb(255,51,31)',
            'rgb(94,16,9)',
            'rgb(255,119,82)',
            'rgb(205,207,208)',
            'rgb(38,98,148)',
            'rgb(255,101,163)',
            'rgb(38,132,38)',
            'rgb(35,10,10)',
            'rgb(153,102,255)'
        ];

        $agents = MKB_Users::instance()->get_agents();

        foreach ($agents as $index => $agent) {
            $this->agent_colors[$agent->ID] = isset($default_colors[$index]) ? $default_colors[$index] : null;
        }
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
			__( 'Dashboard', 'minerva-kb' ),
			__( 'Dashboard', 'minerva-kb' ),
			current_user_can('administrator') ?
                'manage_options' :
                'mkb_view_tickets_dashboard',
			'minerva-mkb_ticket-submenu-dashboard',
			array( $this, 'submenu_html' )
		);

        $this->parse_filters();
        $this->set_agent_default_colors();
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
            <span class="mkb-header-title mkb-header-item"><?php _e( 'Tickets Dashboard', 'minerva-kb' ); ?></span>
            <?php MinervaKB_AutoUpdate::registered_label(); ?>
        </div>

        <div id="tickets-dashboard">

            <div class="mkb-dashboard-tickets-filters">
                <?php $this->render_filters_form(); ?>
            </div>

            <div class="mkb-dashboard-chart-cell">
                <h3><?php _e('Total tickets by status', 'minerva-kb'); ?></h3>
                <canvas id="mkb_chart_tickets_by_status" width="400" height="400"></canvas>
            </div>

            <div class="mkb-dashboard-chart-cell">
                <h3><?php _e('Active tickets by channel', 'minerva-kb'); ?></h3>
                <canvas id="mkb_chart_tickets_by_channel" width="400" height="400"></canvas>
            </div>

            <div class="mkb-dashboard-chart-cell">
                <h3><?php _e('Active tickets by type', 'minerva-kb'); ?></h3>
                <canvas id="mkb_chart_tickets_by_type" width="400" height="400"></canvas>
            </div>

            <div class="mkb-dashboard-chart-cell">
                <h3><?php _e('Active tickets by priority', 'minerva-kb'); ?></h3>
                <canvas id="mkb_chart_tickets_by_priority" width="400" height="400"></canvas>
            </div>

            <div class="mkb-dashboard-chart-cell">
                <h3><?php _e('Active tickets by product', 'minerva-kb'); ?></h3>
                <canvas id="mkb_chart_tickets_by_product" width="400" height="400"></canvas>
            </div>

            <?php if (!$this->department): ?>
                <div class="mkb-dashboard-chart-cell">
                    <h3><?php _e('Active tickets by department', 'minerva-kb'); ?></h3>
                    <canvas id="mkb_chart_tickets_by_department" width="400" height="400"></canvas>
                </div>
            <?php endif; ?>

            <?php if (!$this->agent): ?>
                <div class="mkb-dashboard-chart-cell">
                    <h3><?php _e('Open tickets by agent', 'minerva-kb'); ?></h3>
                    <canvas id="mkb_chart_tickets_by_agent" width="400" height="400"></canvas>
                </div>
            <?php endif; ?>

            <br>

            <div class="mkb-daily-chart-wrap">
                <h3><?php _e('New / Closed / Reopened tickets daily chart', 'minerva-kb'); ?></h3>
                <canvas id="mkb_chart_tickets_daily" width="600" height="300"></canvas>
            </div>

            <div class="mkb-daily-chart-wrap">
                <h3><?php _e('New tickets per agent daily chart', 'minerva-kb'); ?></h3>
                <canvas id="mkb_chart_new_tickets_per_agent_daily" width="600" height="300"></canvas>
            </div>

            <div class="mkb-daily-chart-wrap">
                <h3><?php _e('Closed tickets per agent daily chart', 'minerva-kb'); ?></h3>
                <canvas id="mkb_chart_closed_tickets_per_agent_daily" width="600" height="300"></canvas>
            </div>

            <div class="mkb-daily-chart-wrap">
                <h3><?php _e('Reopened tickets per agent daily chart', 'minerva-kb'); ?></h3>
                <canvas id="mkb_chart_reopened_tickets_per_agent_daily" width="600" height="300"></canvas>
            </div>

            <br>

            <div class="mkb-dashboard-tickets-list">
                <h3><?php _e('Recent tickets', 'minerva-kb'); ?></h3>
                <?php $this->render_recent_tickets_list(); ?>
            </div>

            <div class="mkb-dashboard-tickets-list">
                <h3><?php _e('Recently closed tickets', 'minerva-kb'); ?></h3>
                <?php $this->render_recently_closed_tickets_list(); ?>
            </div>

            <div class="mkb-dashboard-tickets-list">
                <h3><?php _e('Recently reopened tickets', 'minerva-kb'); ?></h3>
                <?php $this->render_recently_reopened_tickets_list(); ?>
            </div>

            <?php

            // TODO:
            // [CURRENT] Active high-priority tickets list - need priority weight in tax
            // [CURRENT] Tickets opened for a long time
            // [CURRENT] Open tickets waiting for agent reply
            // [CURRENT] Open tickets without agent replies
            // [CURRENT] Active Guest vs User tickets
            // [CURRENT] Agents List (with total open / closed / reopened / replies / active - inactive - deleted)
            ?>
        </div>
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

        wp_enqueue_script( 'minerva-kb/admin-dashboard-chart-js', MINERVA_KB_PLUGIN_URL . 'assets/js/vendor/chart.bundle.min.js', array(), null, true );
        wp_enqueue_script( 'minerva-kb/admin-dashboard-counter-js', MINERVA_KB_PLUGIN_URL . 'assets/js/vendor/count-up.min.js', array(), null, true );
        wp_enqueue_script( 'minerva-kb/admin-tickets-dashboard-js', MINERVA_KB_PLUGIN_URL . 'assets/js/minerva-kb-tickets-dashboard.js', array(
            'jquery',
            'minerva-kb/admin-ui-js',
            'minerva-kb/admin-toastr-js',
            'minerva-kb/admin-dashboard-chart-js'
        ), null, true );

		wp_localize_script( 'minerva-kb/admin-tickets-dashboard-js', 'MinervaTicketsDashboard', array(
            'ticketsByStatus' => $this->get_open_tickets_count_by_meta('_mkb_ticket_status'),
            'ticketsByAgent' => $this->get_open_tickets_count_by_meta('_mkb_ticket_assignee'),
            'ticketsByChannel' => $this->get_open_tickets_count_by_meta('_mkb_ticket_channel'),
            'ticketsByType' => $this->get_open_tickets_count_by_taxonomy('mkb_ticket_type'),
            'ticketsByPriority' => $this->get_open_tickets_count_by_taxonomy('mkb_ticket_priority'),
            'ticketsByProduct' => $this->get_open_tickets_count_by_taxonomy('mkb_ticket_product'),
            'ticketsByDepartment' => $this->get_open_tickets_count_by_taxonomy('mkb_ticket_department'),
            'dailyChartRange' => $this->get_daily_range(self::DAILY_CHART_DAYS, 'D, M j'),
            'ticketsDaily' => $this->get_daily_tickets_chart_data(),
            'ticketsDailyNewPerAgent' => $this->get_daily_new_tickets_per_agent_chart_data(),
            'ticketsDailyClosedPerAgent' => $this->get_daily_closed_tickets_per_agent_chart_data(),
            'ticketsDailyReopenedPerAgent' => $this->get_daily_reopened_tickets_per_agent_chart_data(),
        ));
	}

    /**
     * Filters form
     */
	private function render_filters_form() {
	    ?>
        <form action="<?php echo admin_url('edit.php'); ?>" class="js-mkb-tickets-dashboard-filters-form" novalidate>
            <input type="hidden" name="post_type" value="mkb_ticket">
            <input type="hidden" name="page" value="minerva-mkb_ticket-submenu-dashboard">

            <select name="agent" id="mkb_dashboard_agent_filter">
                <option value=""><?php _e('All agents', 'minerva-kb'); ?></option>
                <?php

                $agents = MKB_Users::instance()->get_agents();

                if (sizeof($agents)):
                    foreach($agents as $agent):

                        ?>
                    <option value="<?php esc_attr_e($agent->ID); ?>"<?php
                    if ($this->agent && $this->agent->ID === $agent->ID) { echo ' selected'; } ?>>
                        <?php esc_html_e($agent->display_name); ?>
                        </option><?php

                    endforeach;
                endif;

                ?>
            </select>

            <select name="department" id="mkb_dashboard_department_filter">
                <option value=""><?php _e('All departments', 'minerva-kb'); ?></option>
                <?php

                $departments = get_terms(array(
                    'taxonomy' => 'mkb_ticket_department',
                    'hide_empty' => false
                ));

                if ($departments && !is_wp_error($departments)):
                    foreach($departments as $department):

                        ?>
                    <option value="<?php esc_attr_e($department->term_id); ?>"<?php
                    if ($this->department && $this->department->term_id === $department->term_id) { echo ' selected'; } ?>>
                        <?php esc_html_e($department->name); ?>
                        </option><?php

                    endforeach;
                endif;

                ?>
            </select>

            <input type="submit" class="button" value="<?php _e('Filter', 'minerva-kb'); ?>">
        </form>
        <?php
    }

    /**
     * Recent tickets
     */
	public function render_recent_tickets_list() {
        $query_args = array(
            'post_type' => 'mkb_ticket',
            'ignore_sticky_posts' => 1,
            'posts_per_page' => 10,
            'post_status' => 'publish'
        );

        if ($this->department) {
            $query_args['tax_query'] = array(
                array(
                    'taxonomy' => 'mkb_ticket_department',
                    'field' => 'id',
                    'terms' => $this->department->term_id
                )
            );
        }

        if ($this->agent) {
            $query_args['meta_query'] = array(
                array('key' => '_mkb_ticket_assignee', 'compare' => 'EXISTS'),
                array('key' => '_mkb_ticket_assignee', 'value'   => $this->agent->ID, 'compare' => '='),
            );
        }

        $tickets_loop = new WP_Query( $query_args );

        if ($tickets_loop->have_posts()):

            ?><ol><?php

            while ($tickets_loop->have_posts()) : $tickets_loop->the_post();

                $ticket_id = get_the_ID();

                $status = MKB_Tickets::get_ticket_status($ticket_id);

                $status_label = '';

                if ($status) {
                    $status_label = $status['label'];
                } else {
                    $status_label = __('Unknown status', 'minerva-kb');
                }

                $assignee_value = MKB_Tickets::get_ticket_assignee($ticket_id);

                $assignee_label = '';

                if ($assignee_value) {
                    $assignee = get_user_by('ID', $assignee_value);

                    if ($assignee) {
                        $assignee_label = $assignee->display_name;
                    } else {
                        $assignee_label = __('[DELETED]', 'minerva-kb');
                    }
                } else {
                    $assignee_label = __('Unassigned', 'minerva-kb');
                }

                $timestamp = get_post_time('U', false, $ticket_id);
                $timestamp_gmt = get_post_time('U', true, $ticket_id);

                ?>
                <li><a href="<?php echo admin_url('post.php?post=' . get_the_ID() . '&action=edit'); ?>" target="_blank">
                    <?php the_title(); ?></a> - <strong><?php esc_html_e($status_label); ?></strong>, <?php _e('opened', 'minerva-kb'); ?> <?php MKB_Utils::render_human_date($timestamp_gmt, $timestamp); ?>, <?php if ($assignee_value) { echo __('assigned to', 'minerva-kb') . ' '; } ?><strong><?php esc_html_e($assignee_label); ?></strong>
                </li><?php
            endwhile;

            ?></ol><?php

        else:

            ?><p><?php esc_html_e($this->NO_TICKETS_TEXT); ?></p><?php

        endif;

        wp_reset_postdata();
    }

    /**
     * Recently closed tickets list
     */
    public function render_recently_closed_tickets_list() {
        $events = MKB_History::get_tickets_close_events();
        $limit = 10;
        $step = 0;

        if (sizeof($events)):

            ?>
            <ol><?php

            foreach ($events as $event):

                if ($step >= $limit) {
                    break;
                }

                $ticket = get_post($event->post_id);

                if ($this->department) {
                    $department = wp_get_post_terms($ticket->ID, 'mkb_ticket_department');

                    if (!$department || is_wp_error($department) || !sizeof($department) || $department[0]->term_id !== $this->department->term_id) {
                        continue;
                    }
                }

                /**
                 * For current agent, it makes more sense to list recently closed tickets that are assigned to agent, both by agent and user
                 */
                if ($this->agent && (int)MKB_Tickets::get_ticket_assignee($event->post_id) !== $this->agent->ID) {
                    continue;
                }

                $user = get_user_by('id', $event->user_id);
                $user_name = $user ? $user->display_name : '[DELETED]';

                $timestamp = strtotime($event->event_datetime);
                $timestamp_gmt = strtotime($event->event_datetime_gmt);

                ++$step;

                ?>
                <li>
                    <a href="<?php echo admin_url('post.php?post=' . $ticket->ID . '&action=edit'); ?>" target="_blank"><?php echo get_the_title($ticket); ?></a> <?php _e('closed by', 'minerva-kb'); ?>
                    <strong><?php esc_html_e($user_name); ?></strong> <?php MKB_Utils::render_human_date($timestamp_gmt, $timestamp); ?>
                </li><?php

            endforeach;

            ?></ol><?php

        endif;

        if ($step === 0):
            ?><p><?php esc_html_e($this->NO_TICKETS_TEXT); ?></p><?php
        endif;
    }

    /**
     * Recently reopened tickets
     */
    public function render_recently_reopened_tickets_list() {
        $events = MKB_History::get_tickets_reopen_events();
        $limit = 10;
        $step = 0;

        if (sizeof($events)):

            ?><ol><?php

            foreach ($events as $event):
                if ($step >= $limit) { break; }

                $ticket = get_post($event->post_id);

                if ($this->department) {
                    $department = wp_get_post_terms($ticket->ID, 'mkb_ticket_department');

                    if (!$department || is_wp_error($department) || !sizeof($department) || $department[0]->term_id !== $this->department->term_id) {
                        continue;
                    }
                }

                /**
                 * For current agent, it makes more sense to list recently closed tickets that are assigned to agent, both by agent and user
                 */
                if ($this->agent && (int)$event->meta1 !== $this->agent->ID) {
                    continue;
                }

                $user = get_user_by('id', $event->user_id);
                $user_name = $user ? $user->display_name : '[DELETED]';

                $timestamp = strtotime($event->event_datetime);
                $timestamp_gmt = strtotime($event->event_datetime_gmt);

                ++$step;

                ?>
                <li>
                <a href="<?php echo admin_url('post.php?post=' . $ticket->ID . '&action=edit'); ?>" target="_blank">
                    <?php echo get_the_title($ticket); ?></a> <?php _e('reopened by', 'minerva-kb'); ?> <strong><?php esc_html_e($user_name); ?></strong> <?php MKB_Utils::render_human_date($timestamp_gmt, $timestamp); ?>
                </li><?php
            endforeach;

            ?></ol><?php

        endif;
        ?>
        <?php if ($step === 0): ?>
            <p><?php esc_html_e($this->NO_TICKETS_TEXT); ?></p>
        <?php endif;
    }

    /**
     * @param $taxonomy
     * @return array
     */
    public function get_open_tickets_count_by_taxonomy($taxonomy) {
        $query_args = array(
            'post_type' => 'mkb_ticket',
            'ignore_sticky_posts' => 1,
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'meta_query' => array(
                'relation' => 'AND',
                array('key' => '_mkb_ticket_status', 'compare' => 'EXISTS'),
                array('key' => '_mkb_ticket_status', 'value' => 'closed', 'compare' => '!='),
            )
        );

        if ($this->department) {
            $query_args['tax_query'] = array(
                array(
                    'taxonomy' => 'mkb_ticket_department',
                    'field' => 'id',
                    'terms' => $this->department->term_id
                )
            );
        }

        if ($this->agent) {
            $query_args['meta_query'] = array_merge($query_args['meta_query'],
                array(
                    array('key' => '_mkb_ticket_assignee', 'compare' => 'EXISTS'),
                    array('key' => '_mkb_ticket_assignee', 'value'   => $this->agent->ID, 'compare' => '=')
                )
            );
        }

        $tickets_loop = new WP_Query( $query_args );

        $term_groups = array();
        $not_set_group = array();

        if ($tickets_loop->have_posts()):
            while ($tickets_loop->have_posts()) : $tickets_loop->the_post();
                $ticket_id = get_the_ID();
                $terms = wp_get_post_terms($ticket_id, $taxonomy);
                $term_id = sizeof($terms) && $terms[0] ? $terms[0]->term_id : 'none';

                if ($term_id === 'none') {
                    $not_set_group[] = true; // value is not used, just count
                } else {
                    $term_groups[$term_id][] = true;
                }
            endwhile;
        endif;

        wp_reset_postdata();

        $labels = array();
        $values = array();
        $colors = array();

        foreach($term_groups as $term_id => $term_group):
            $count = sizeof($term_group);

            $values[] = $count;
            $group_label = __('Not set', 'minerva-kb');

            if ($term_id !== 'none') {
                $term = get_term_by('id', $term_id, $taxonomy);

                if (!$term || !isset($term->name)) {
                    continue; // invalid or deleted terms
                }

                $group_label = $term->name;
                $colors[] = MKB_TemplateHelper::get_taxonomy_option($term, $taxonomy, 'color');
            }

            $labels[] = $group_label;
            // TODO: colors
        endforeach;

        if (sizeof($not_set_group)) {
            $count = sizeof($not_set_group);

            $labels[] = __('Not set', 'minerva-kb');
            $values[] = $count;
            $colors[] = self::NOT_SET_COLOR;
        }

        if (empty($values)) {
            return $this->get_empty_radial_chart_data();
        }

        return array(
            'labels' => $labels,
            'values' => $values,
            'colors' => $colors
        );
    }

    /**
     * @param $meta_key
     * @return array
     */
    public function get_open_tickets_count_by_meta($meta_key) {
        $meta_groups = array();
        $not_set_group = array();

        $tickets = $this->get_active_tickets_for_filters();

        if (sizeof($tickets)):
            foreach($tickets as $ticket):
                $meta_value = get_post_meta($ticket->ID, $meta_key, true);
                $meta_value = isset($meta_value) && $meta_value ? $meta_value : 'none';

                if ($meta_value === 'none') {
                    $not_set_group[] = $ticket;
                } else {
                    $meta_groups[$meta_value][] = $ticket;
                }
            endforeach;
        endif;

        $labels = array();
        $values = array();
        $colors = array();

        foreach($meta_groups as $meta_value => $meta_group):
            $count = sizeof($meta_group);
            $values[] = $count;
            $group_label = $meta_value;

            switch($meta_key) {
                case '_mkb_ticket_assignee':
                    $user = get_user_by('ID', $meta_value);

                    if ($user) {
                        $group_label = $user->display_name;
                        $colors[] = $this->agent_colors[$user->ID];
                    } else {
                        $group_label = __('[DELETED]', 'minerva-kb');
                        $colors[] = self::NOT_SET_COLOR;
                    }
                    break;

                case '_mkb_ticket_status':
                    $status = MKB_Tickets::get_ticket_status_by_id($meta_value);

                    if ($status) {
                        $group_label = $status['label'];
                        $colors[] = $status['color'];
                    } else {
                        $group_label = __('Unknown status', 'minerva-kb');
                    }
                    break;

                case '_mkb_ticket_channel':
                    $channel = MKB_Tickets::get_ticket_channel_by_id($meta_value);

                    if ($channel) {
                        $group_label = $channel['label'];
                    } else {
                        $group_label = __('[DELETED]', 'minerva-kb');
                    }
                    break;
            }

            $labels[] = $group_label;

            // TODO auto colors (for users & others)
        endforeach;

        if (sizeof($not_set_group)) {
            $count = sizeof($not_set_group);
            $not_set_label = '';

            switch($meta_key) {
                case '_mkb_ticket_assignee':
                    $not_set_label = __('Unassigned', 'minerva-kb');
                    break;

                case '_mkb_ticket_status':
                    $not_set_label = __('Not set', 'minerva-kb');
                    $colors[] = self::NOT_SET_COLOR;
                    break;

                case '_mkb_ticket_channel':
                    $not_set_label = __('Not set', 'minerva-kb');
                    break;
            }

            $labels[] = $not_set_label;
            $values[] = $count;
        }

        if (empty($values)) {
            return $this->get_empty_radial_chart_data();
        }

        return array(
            'labels' => $labels,
            'values' => $values,
            'colors' => $colors
        );
    }

    private function get_empty_radial_chart_data() {
        return array(
            'labels' => [$this->NO_TICKETS_TEXT],
            'values' => [1],
            'colors' => self::NOT_SET_COLOR
        );
    }

    /**
     * @param string $meta_key
     * @return int[]|WP_Post[]
     */
    private function get_active_tickets_for_filters($meta_key = '') {
        $query_args = array(
            'post_type' => 'mkb_ticket',
            'ignore_sticky_posts' => 1,
            'posts_per_page' => -1,
            'post_status' => 'publish'
        );

        if ($meta_key !== '_mkb_ticket_status') {
            $query_args['meta_query'] = array(
                'relation' => 'AND',
                array('key' => '_mkb_ticket_status', 'compare' => 'EXISTS'),
                array('key' => '_mkb_ticket_status', 'value' => 'closed', 'compare' => '!='),
            );
        }

        if ($this->department) {
            $query_args['tax_query'] = array(
                array(
                    'taxonomy' => 'mkb_ticket_department',
                    'field' => 'id',
                    'terms' => $this->department->term_id
                )
            );
        }

        if ($this->agent) {
            if ($meta_key === '_mkb_ticket_status') {
                $query_args['meta_query'] = array(
                    'relation' => 'AND',
                    array('key' => '_mkb_ticket_assignee', 'compare' => 'EXISTS'),
                    array('key' => '_mkb_ticket_assignee', 'value'   => $this->agent->ID, 'compare' => '='),
                );
            } else {
                $query_args['meta_query'] = array_merge($query_args['meta_query'], array(
                    array('key' => '_mkb_ticket_assignee', 'compare' => 'EXISTS'),
                    array('key' => '_mkb_ticket_assignee', 'value'   => $this->agent->ID, 'compare' => '='),
                ));
            }
        }

        return get_posts($query_args);
    }

    /**
     *
     */
    public function get_daily_tickets_chart_data() {
        $now = time();

        $range = self::get_daily_range(self::DAILY_CHART_DAYS);

        $results = array(
            'new' => array(),
            'closed' => array(),
            'reopened' => array()
        );

        // new tickets
        $query_args = array(
            'post_type' => 'mkb_ticket',
            'ignore_sticky_posts' => 1,
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'date_query' => array(
                'after' => date('d-m-Y', $now - (self::DAILY_CHART_DAYS + 1) * 60 * 60 * 24)
            )
        );

        if ($this->department) {
            $query_args['tax_query'] = array(
                array(
                    'taxonomy' => 'mkb_ticket_department',
                    'field' => 'id',
                    'terms' => $this->department->term_id
                )
            );
        }

        if ($this->agent) {
            $query_args['meta_query'] = array(
                array('key' => '_mkb_ticket_assignee', 'compare' => 'EXISTS'),
                array('key' => '_mkb_ticket_assignee', 'value'   => $this->agent->ID, 'compare' => '='),
            );
        }

        $tickets_loop = new WP_Query( $query_args );

        $daily_new_tickets = array();

        if ($tickets_loop->have_posts()):
            while ($tickets_loop->have_posts()) : $tickets_loop->the_post();
                $date = get_the_date('d-m-Y');

                if (!isset($daily_new_tickets[$date])) {
                    $daily_new_tickets[$date] = 0;
                }

                ++$daily_new_tickets[$date];
            endwhile;
        endif;

        wp_reset_postdata();

        foreach($range as $day) {
            $results['new'][] = isset($daily_new_tickets[$day]) ? $daily_new_tickets[$day] : 0;
        }

        // closed tickets
        $daily_closed_tickets = array();
        $daily_closed_ticket_ids = array();

        $events = MKB_History::get_tickets_close_events();

        if (sizeof($events)):

            foreach ($events as $event):

                $ticket_id = $event->post_id;

                if ($this->department) {
                    $department = wp_get_post_terms($ticket_id, 'mkb_ticket_department');

                    if (!$department || is_wp_error($department) || !sizeof($department) || $department[0]->term_id !== $this->department->term_id) {
                        continue;
                    }
                }

                if ($this->agent && (int)$event->post_id !== $this->agent->ID) {
                    continue;
                }

                $timestamp = strtotime($event->event_datetime);
                $date = date('d-m-Y', $timestamp);

                if (!isset($daily_closed_tickets[$date])) {
                    $daily_closed_tickets[$date] = 0;
                }

                if (!isset($daily_closed_ticket_ids[$date])) {
                    $daily_closed_ticket_ids[$date] = array();
                }

                if (in_array($ticket_id, $daily_closed_ticket_ids[$date])) {
                    continue; // skip multiple close events during the same day for same ticket
                    // TODO: maybe filter out close events for same ticket for all period, but it's questionable
                    // TODO: maybe also remove closed tickets that has been reopened the same day
                }

                ++$daily_closed_tickets[$date];
                $daily_closed_ticket_ids[$date][]= $ticket_id;
            endforeach;
        endif;

        foreach($range as $day) {
            $results['closed'][] = isset($daily_closed_tickets[$day]) ? $daily_closed_tickets[$day] : 0;
        }

        // reopened tickets

        $daily_reopened_tickets = array();
        $daily_reopened_ticket_ids = array();

        $events = MKB_History::get_tickets_reopen_events();

        if (sizeof($events)):

            foreach ($events as $event):

                $ticket_id = $event->post_id;

                if ($this->department) {
                    $department = wp_get_post_terms($ticket_id, 'mkb_ticket_department');

                    if (!$department || is_wp_error($department) || !sizeof($department) || $department[0]->term_id !== $this->department->term_id) {
                        continue;
                    }
                }

                if ($this->agent && (int)$event->meta1 !== $this->agent->ID) {
                    continue;
                }

                $timestamp = strtotime($event->event_datetime);
                $date = date('d-m-Y', $timestamp);

                if (!isset($daily_reopened_tickets[$date])) {
                    $daily_reopened_tickets[$date] = 0;
                }

                if (!isset($daily_reopened_ticket_ids[$date])) {
                    $daily_reopened_ticket_ids[$date] = array();
                }

                if (in_array($ticket_id, $daily_reopened_ticket_ids[$date])) {
                    continue; // skip multiple close events during the same day for same ticket
                }

                ++$daily_reopened_tickets[$date];
                $daily_reopened_ticket_ids[$date][]= $ticket_id;

            endforeach;

        endif;

        foreach($range as $day) {
            $results['reopened'][] = isset($daily_reopened_tickets[$day]) ? $daily_reopened_tickets[$day] : 0;
        }

        return array(
            array(
                'label' => __('New', 'minerva-kb'),
                'values' => $results['new'],
                'color' => 'rgb(7,194,45)'
            ),
            array(
                'label' => __('Closed', 'minerva-kb'),
                'values' => $results['closed'],
                'color' => 'rgb(52,108,235)'
            ),
            array(
                'label' => __('Reopened', 'minerva-kb'),
                'values' => $results['reopened'],
                'color' => 'rgb(255,32,35)'
            )
        );
    }

    /**
     * New tickets, that are _currently_ assigned to agent
     * @return array
     */
    public function get_daily_new_tickets_per_agent_chart_data() {
        $now = time();

        $range = self::get_daily_range(self::DAILY_CHART_DAYS);

        $results = [];

        // new tickets
        $query_args = array(
            'post_type' => 'mkb_ticket',
            'ignore_sticky_posts' => 1,
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'date_query' => array(
                'after' => date('d-m-Y', $now - (self::DAILY_CHART_DAYS + 1) * 60 * 60 * 24)
            )
        );

        if ($this->department) {
            $query_args['tax_query'] = array(
                array(
                    'taxonomy' => 'mkb_ticket_department',
                    'field' => 'id',
                    'terms' => $this->department->term_id
                )
            );
        }

        if ($this->agent) {
            $query_args['meta_query'] = array(
                array('key' => '_mkb_ticket_assignee', 'compare' => 'EXISTS'),
                array('key' => '_mkb_ticket_assignee', 'value'   => $this->agent->ID, 'compare' => '='),
            );
        }

        $tickets_loop = new WP_Query($query_args);

        $daily_new_tickets = array();
        $all_assignees = array();

        if ($tickets_loop->have_posts()):
            while ($tickets_loop->have_posts()) : $tickets_loop->the_post();
                $date = get_the_date('d-m-Y');
                $assignee = get_post_meta(get_the_ID(), '_mkb_ticket_assignee', true);

                if ($assignee) {
                    $assignee = get_user_by('id', $assignee);
                    if ($assignee) {
                        $assignee = $assignee->display_name;
                    } else { continue; }
                } else { continue; }

                if (!isset($daily_new_tickets[$date])) {
                    $daily_new_tickets[$date] = array();
                }

                if (!isset($daily_new_tickets[$date][$assignee])) {
                    $daily_new_tickets[$date][$assignee] = 0;
                }

                if (!in_array($assignee, $all_assignees)) {
                    $all_assignees[]= $assignee;
                }

                ++$daily_new_tickets[$date][$assignee];
            endwhile;
        endif;

        wp_reset_postdata();

        foreach($all_assignees as $assignee) {
            $results[$assignee] = [];
        }

        foreach($range as $day) {
            foreach($all_assignees as $assignee) {
                $results[$assignee][] = isset($daily_new_tickets[$day]) && isset($daily_new_tickets[$day][$assignee]) ?
                    $daily_new_tickets[$day][$assignee] :
                    0;
            }
        }

        $chart_results = array();

        foreach($results as $assignee => $daily) {
            $chart_results[] = array(
                'label' => $assignee,
                'values' => $daily
            );
        }

        return $chart_results;
    }

    /**
     * @return array
     */
    public function get_daily_closed_tickets_per_agent_chart_data() {
        $range = self::get_daily_range(self::DAILY_CHART_DAYS);

        $results = [];

        $daily_tickets = array();
        $daily_closed_ticket_ids = array();

        $all_users = array();
        $user_names = array();

        $events = MKB_History::get_tickets_close_events();

        if (sizeof($events)):
            foreach ($events as $event):
                if ($this->department) {
                    $department = wp_get_post_terms($event->post_id, 'mkb_ticket_department');

                    if (!$department || is_wp_error($department) || !sizeof($department) || $department[0]->term_id !== $this->department->term_id) {
                        continue;
                    }
                }

                if ($this->agent && (int)$event->user_id !== $this->agent->ID) {
                    continue;
                }

                $timestamp = strtotime($event->event_datetime);
                $date = date('d-m-Y', $timestamp);
                $user = $event->user_id;

                if (!isset($daily_closed_ticket_ids[$date])) {
                    $daily_closed_ticket_ids[$date] = array();
                }

                if (in_array($event->post_id, $daily_closed_ticket_ids[$date])) {
                    continue; // skip multiple close events during the same day for same ticket
                }

                if ($user) {
                    $user = get_user_by('id', $user);
                    if ($user) {
                        if (!MKB_Users::user_can($user, 'mkb_ticket_assignee')) {
                            continue; // show only agents
                        }

                        $user_names[$user->ID] = $user->display_name;
                        $user = $user->ID;
                    } else { continue; }
                } else { continue; }

                if (!isset($daily_tickets[$date])) {
                    $daily_tickets[$date] = array();
                }

                if (!isset($daily_tickets[$date][$user])) {
                    $daily_tickets[$date][$user] = 0;
                }

                if (!in_array($user, $all_users)) {
                    $all_users[]= $user;
                }

                ++$daily_tickets[$date][$user];

                $daily_closed_ticket_ids[$date][] = $event->post_id;
            endforeach;
        endif;

        foreach($all_users as $user) {
            $results[$user] = [];
        }

        foreach($range as $day) {
            foreach($all_users as $user) {
                $results[$user][] = isset($daily_tickets[$day]) && isset($daily_tickets[$day][$user]) ?
                    $daily_tickets[$day][$user] :
                    0;
            }
        }

        $chart_results = array();

        foreach($results as $user => $daily) {
            $chart_results[] = array(
                'label' => $user_names[$user],
                'values' => $daily,
                'color' => $this->agent_colors[$user]
            );
        }

        return $chart_results;
    }

    /**
     * @return array
     */
    public function get_daily_reopened_tickets_per_agent_chart_data() {
        $range = self::get_daily_range(self::DAILY_CHART_DAYS);

        $results = [];

        $daily_tickets = array();
        $daily_reopened_ticket_ids = array();

        $all_users = array();
        $user_names = array();

        $events = MKB_History::get_tickets_reopen_events();

        if (sizeof($events)):
            foreach ($events as $event):
                if ($this->department) {
                    $department = wp_get_post_terms($event->post_id, 'mkb_ticket_department');

                    if (!$department || is_wp_error($department) || !sizeof($department) || $department[0]->term_id !== $this->department->term_id) {
                        continue;
                    }
                }

                if ($this->agent && (int)$event->meta1 !== $this->agent->ID) {
                    continue;
                }

                $timestamp = strtotime($event->event_datetime);
                $date = date('d-m-Y', $timestamp);
                $user = $event->meta1;

                if (!isset($daily_reopened_ticket_ids[$date])) {
                    $daily_reopened_ticket_ids[$date] = array();
                }

                if (in_array($event->post_id, $daily_reopened_ticket_ids[$date])) {
                    continue; // skip multiple events during the same day for same ticket
                }

                if ($user) {
                    $user = get_user_by('id', $user);
                    if ($user) {
                        if (!MKB_Users::user_can($user, 'mkb_ticket_assignee')) {
                            continue; // show only agents
                        }

                        $user_names[$user->ID] = $user->display_name;
                        $user = $user->ID;
                    } else { continue; }
                } else { continue; }

                if (!isset($daily_tickets[$date])) {
                    $daily_tickets[$date] = array();
                }

                if (!isset($daily_tickets[$date][$user])) {
                    $daily_tickets[$date][$user] = 0;
                }

                if (!in_array($user, $all_users)) {
                    $all_users[]= $user;
                }

                ++$daily_tickets[$date][$user];

                $daily_reopened_ticket_ids[$date][] = $event->post_id;
            endforeach;
        endif;

        foreach($all_users as $user) {
            $results[$user] = [];
        }

        foreach($range as $day) {
            foreach($all_users as $user) {
                $results[$user][] = isset($daily_tickets[$day]) && isset($daily_tickets[$day][$user]) ?
                    $daily_tickets[$day][$user] :
                    0;
            }
        }

        $chart_results = array();

        foreach($results as $user => $daily) {
            $chart_results[] = array(
                'label' => $user_names[$user],
                'values' => $daily,
                'color' => $this->agent_colors[$user]
            );
        }

        return $chart_results;
    }

    /**
     * @param int $total_days
     * @param string $format
     * @return array
     */
    public static function get_daily_range($total_days = 14, $format = 'd-m-Y') {
        $now = time();
        $today = date($format, $now);

        $range = array($today);

        for ($i=1; $i<$total_days; $i++) {
            $range[]= date($format, $now - $i * 60 * 60 * 24);
        }

        return array_reverse($range);
    }
}