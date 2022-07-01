<?php
/**
 * Project: MinervaKB.
 * Copyright: 2015-2017 @KonstruktStudio
 */

class MinervaKB_TicketEdit {

    private $is_new_ticket = false;

	/**
	 * Constructor
	 */
	public function __construct($deps) {
		$this->setup_dependencies($deps);

        add_action( 'auth_redirect', array($this, 'add_open_ticket_count_filter'));

        add_action('current_screen', array($this, 'page_setup'));
	}

    public function page_setup() {
        $screen = get_current_screen();

        if (isset($screen) && ($screen->base == 'post' || $screen->base == 'edit') && $screen->post_type == 'mkb_ticket') {
            global $pagenow;

            $this->is_new_ticket = in_array($pagenow, array('post-new.php'));

            add_action( 'edit_form_top', array($this, 'heading_status_badge'));
            add_action( 'add_meta_boxes', array($this, 'add_meta_boxes') );
            add_action( 'save_post', array($this, 'save_post'), 999, 2);

            add_action( 'admin_footer', array($this, 'ticket_tmpl'), 30 );

            add_action( 'admin_notices', array($this, 'admin_notices'));
            add_action( 'add_meta_boxes', array($this, 'remove_seo_meta_box'), 99999);

            add_filter( 'post_updated_messages', array($this, 'ticket_updated_messages'));
            add_action( 'admin_menu', function() {
                remove_meta_box('submitdiv', 'mkb_ticket', 'side');
            });
        }
    }

	/**
	 * Sets up dependencies
	 * @param $deps
	 */
	private function setup_dependencies($deps) {
		// just in case
	}

	/**
	 * Register meta box(es).
	 */
	public function add_meta_boxes() {
        // replies meta box
        add_meta_box(
            'mkb-ticket-meta-replies',
            __( 'Ticket Discussion', 'minerva-kb' ),
            array($this, 'replies_html'),
            'mkb_ticket',
            'normal',
            'high'
        );


        // private notes meta box
        add_meta_box(
            'mkb-ticket-meta-private-notes',
            __( 'Private Notes', 'minerva-kb' ),
            array($this, 'private_notes_html'),
            'mkb_ticket',
            'normal',
            'high'
        );

		/**
         * Sidebar
         */
        // ticket edit
        add_meta_box(
            'mkb-ticket-meta-update-id',
            __( 'Ticket Update', 'minerva-kb' ),
            array($this, 'update_html'),
            'mkb_ticket',
            'side',
            'high'
        );

        if (!$this->is_new_ticket) {
            // ticket info
            add_meta_box(
                'mkb-ticket-meta-info-id',
                __( 'Ticket Information', 'minerva-kb' ),
                array($this, 'info_html'),
                'mkb_ticket',
                'side',
                'high'
            );


            // ticket credentials
            add_meta_box(
                'mkb-ticket-meta-credentials-id',
                __( 'Ticket Credentials', 'minerva-kb' ),
                array($this, 'credentials_html'),
                'mkb_ticket',
                'side',
                'high'
            );
        }
	}

    /**
     * Adds status badge to ticket edit screen
     * @param $post
     */
    public function heading_status_badge($post) {
        if ($post->post_type !== 'mkb_ticket') {
            return;
        }

        $ticket_status = MKB_Tickets::get_ticket_status($post->ID);

        ?>
        <script>
            window.jQuery('h1.wp-heading-inline').after(
                '<span class="mkb-ticket-title-status-badge status--<?php esc_attr_e($ticket_status['id']); ?>">' +
                    '<i class="fa <?php esc_attr_e($ticket_status['icon']); ?>"></i> ' + '<?php esc_attr_e($ticket_status['label']); ?>' +
                '</span>'
            );
        </script>
        <?php
    }

    /**
     * Update metabox
     * @param $ticket
     */
    public function update_html( $ticket ) {
        // NOTE: required
        wp_nonce_field( 'mkb_save_ticket', 'mkb_save_ticket_nonce' );

        $current_user = wp_get_current_user();

        $ticket_status = MKB_Tickets::get_ticket_status($ticket->ID);
        $allowed_ticket_statuses = MKB_Tickets::get_allowed_ticket_statuses();

        $is_ticket_assigned = MKB_Tickets::is_ticket_assigned($ticket->ID);

        $user_can_modify_ticket = MKB_Tickets::user_can_modify_ticket($current_user, $ticket);
        $user_can_reply_to_ticket = $is_ticket_assigned && MKB_Tickets::user_can_reply_to_ticket($current_user, $ticket);
        $user_can_assign_ticket = current_user_can('administrator') ||
            current_user_can('mkb_assign_tickets') ||
            (!$is_ticket_assigned && current_user_can('mkb_assign_unassigned_tickets_to_self'));

        ?>
        <div class="submitbox" id="submitpost"><?php

            if ($user_can_modify_ticket || $user_can_reply_to_ticket || $user_can_assign_ticket):

                ?>
                <div id="major-publishing-actions">
                    <?php

                    // 1. new ticket screen, permissions are checked on WP level
                    if ($this->is_new_ticket):

                        ?>
                        <input type="submit"
                               name="publish"
                               id="publish"
                               class="mkb-ticket-submit-btn button button-primary button-large button-hero"
                               value="<?php _e('Open Ticket', 'minerva-kb'); ?>"><?php

                    // 2. non-closed ticket, check general modification caps
                    elseif ($ticket_status['id'] !== 'closed'):

                        // 2.1 user is allowed to modify current ticket, show submit with status modification
                        if ($user_can_modify_ticket):

                            ?>
                            <a href="#" class="js-mkb-ticket-submit-btn mkb-ticket-submit-btn button button-primary button-hero">
                                <span class="js-mkb-ticket-submit-btn-text">
                                    <?php _e('Submit as', 'minerva-kb'); ?>&nbsp;<strong class="js-mkb-ticket-submit-btn-status-text"><?php esc_attr_e($ticket_status['label']); ?></strong>
                                </span>
                                <span class="js-mkb-btn-dropdown-toggle mkb-btn-dropdown-toggle"></span>
                                <span class="js-mkb-btn-dropdown mkb-btn-dropdown"><?php

                                    foreach($allowed_ticket_statuses as $id => $label):

                                        ?>
                                        <span class="mkb-btn-dropdown-option <?php if ($id === $ticket_status['id']) { esc_attr_e('mkb-btn-dropdown-option--checked'); } ?>"
                                              data-option="<?php esc_attr_e($id); ?>">
                                            <?php _e('Submit as', 'minerva-kb'); ?>&nbsp;<strong><?php esc_attr_e($label); ?></strong>
                                        </span><?php

                                    endforeach;

                                    ?>
                                </span>
                            </a><?php

                        // 2.2 user is not allowed to modify ticket taxonomies, but can reply
                        elseif ($user_can_reply_to_ticket || $user_can_assign_ticket):

                            ?>
                            <a href="#" class="js-mkb-ticket-submit-btn mkb-ticket-submit-btn button button-primary button-hero">
                                <span class="js-mkb-ticket-submit-btn-text"><?php _e('Submit Ticket', 'minerva-kb'); ?></span>
                            </a><?php

                        endif;

                    // 3. closed ticket, check reopen caps
                    else:

                        if ($user_can_modify_ticket):

                            ?>
                            <a href="#" class="js-mkb-ticket-reopen-btn mkb-ticket-submit-btn button button-primary button-hero">
                                <span class="js-mkb-ticket-submit-btn-text"><?php _e('Reopen Ticket', ''); ?></span>
                            </a><?php

                        endif;

                    // end of ticket modification
                    endif; ?>

                    <input type="hidden" name="mkb_ticket_status" class="js-mkb-ticket-status-store" value="<?php esc_attr_e($ticket_status['id'] !== 'closed' ? $ticket_status['id'] : 'open'); ?>">

                    <div class="mkb-ticket-after-submit-action"><?php

                        $stay_on_page = isset($_COOKIE["mkb_ticket_stay_on_edit"]) && $_COOKIE["mkb_ticket_stay_on_edit"] == 1 ? true : false;

                        ?>
                        <input type="checkbox"
                               name="mkb_ticket_stay_on_ticket"
                               id="mkb_ticket_stay_on_ticket"
                               class="mkb-ticket-stay-on-ticket"
                            <?php if($stay_on_page): ?>checked<?php endif; ?>>
                        <label for="mkb_ticket_stay_on_ticket"><?php _e('Stay on ticket?', 'minerva-kb'); ?></label>
                    </div>
                </div><?php

            endif;

            /**
             * Ticket taxonomies edit
             */

            ?>
            <div id="minor-publishing"><?php

            if ($ticket_status['id'] !== 'closed'):
                // Priorities
                self::render_ticket_taxonomy_meta_select(
                    $ticket,
                    'mkb_ticket_priority',
                    $current_user,
                    __('Ticket Priority', 'minerva-kb'),
                    __('Please, select ticket priority', 'minerva-kb'),
                    'mkb_assign_ticket_priorities'
                );

                // Types
                self::render_ticket_taxonomy_meta_select(
                    $ticket,
                    'mkb_ticket_type',
                    $current_user,
                    __('Ticket Type', 'minerva-kb'),
                    __('Please, select ticket type', 'minerva-kb'),
                    'mkb_assign_ticket_types'
                );

                // Departments
                self::render_ticket_taxonomy_meta_select(
                    $ticket,
                    'mkb_ticket_department',
                    $current_user,
                    __('Ticket Department', 'minerva-kb'),
                    __('Please, select ticket department', 'minerva-kb'),
                    'mkb_assign_ticket_departments'
                );

                // Products
                self::render_ticket_taxonomy_meta_select(
                    $ticket,
                    'mkb_ticket_product',
                    $current_user,
                    __('Ticket Product', 'minerva-kb'),
                    __('Please, select ticket product', 'minerva-kb'),
                    'mkb_assign_ticket_products'
                );

                // Assignee
                $ticket_assignees = MKB_Users::instance()->get_agents();
                $current_assignee = get_post_meta($ticket->ID, '_mkb_ticket_assignee', true);

                if (!$current_assignee && $this->is_new_ticket) {
                    $current_assignee = $current_user->ID;
                }

                $can_edit_ticket_assignee = MKB_Tickets::user_can_assign_ticket_assignee($current_user, $ticket->ID);

                // if user cannot modify assignee, but ticket is unassigned and user is allowed to assign to self
                if (!$can_edit_ticket_assignee && !$current_assignee && current_user_can('mkb_assign_unassigned_tickets_to_self')) {
                    $ticket_assignees = array(
                        $current_user
                    );

                    $can_edit_ticket_assignee = true;
                }

                ?>
                <p>
                <?php if ($user_can_assign_ticket): ?>
                    <label for="mkb_ticket_assignee"><?php _e('Ticket Assignee', 'minerva-kb'); ?></label>
                <?php else: ?>
                    <span class="mkb-disabled-select-label"><?php _e('Ticket Assignee', 'minerva-kb'); ?></span>
                <?php endif; ?>

                    <?php

                    if (sizeof($ticket_assignees)):

                        if ($can_edit_ticket_assignee):

                            ?>
                            <br>
                            <select name="mkb_ticket_assignee" id="mkb_ticket_assignee">
                                <option value="" <?php if ($current_assignee) { echo 'disabled'; } ?>><?php _e('Please, select ticket assignee', 'minerva-kb'); ?></option>
                                <?php if ($current_assignee): ?>
                                    <option value="unassigned"><?php _e('Unassigned', 'minerva-kb'); ?></option>
                                <?php endif; ?>
                                <?php foreach($ticket_assignees as $user): ?>
                                    <option value="<?php esc_attr_e($user->ID); ?>" <?php if ($current_assignee == $user->ID) { echo 'selected'; } ?>>
                                        <?php esc_html_e($user->display_name); ?><?php if ($current_user->ID === $user->ID): ?>&nbsp;(you)<?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php

                        else: // cannot edit assignee

                            if ($current_assignee):

                                if ($this->is_new_ticket) { // assign new ticket to agent
                                    ?><input type="hidden" name="mkb_ticket_assignee" value="<?php esc_attr_e($current_assignee); ?>" /><?php
                                }

                                $assigned_user = get_user_by('ID', $current_assignee);

                                if ($assigned_user && !is_wp_error($assigned_user)): ?>
                                    <span class="mkb-disabled-select-value">
                                        <?php esc_html_e($assigned_user->display_name); ?><?php if ($current_user->ID === $assigned_user->ID): ?>&nbsp;(you)<?php endif; ?>
                                    </span><?php
                                endif;

                            else:

                                ?><span class="mkb-disabled-select-value"><?php _e('Unassigned', 'minerva-kb'); ?></span><?php

                            endif;


                        endif; // end of can edit assignee check

                    endif; // end of existing assignees check

                ?></p><?php

                if ($this->is_new_ticket) {

                    // Author
                    $ticket_authors = MKB_Users::get_users_by_cap('mkb_ticket_author');
                    // TODO: !!! important - get also users by role (Minerva Support User or from setting)

                    // TODO: remove assignee from list
                    ?>
                    <p>
                        <label for="mkb_ticket_author"><?php _e('Ticket Author', 'minerva-kb'); ?></label>

                        <?php if (sizeof($ticket_authors)): ?>
                            <br>
                            <select name="post_author" id="mkb_ticket_author">
                                <option value=""><?php _e('Please, select ticket initiator', 'minerva-kb'); ?></option>
                                <?php foreach($ticket_authors as $user): ?>
                                    <option value="<?php esc_attr_e($user->ID); ?>">
                                        <?php esc_html_e($user->display_name); ?><?php if ($current_user->ID === $user->ID): ?>&nbsp;(you)<?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php else: ?>
                            <br>
                            <?php _e('No ticket authors found in system. You may create a Support User to select as Ticket Author.', 'minerva-kb'); ?>
                        <?php endif; ?>
                    </p>
                    <?php
                }

                if ($this->is_new_ticket) { // assign new ticket to agent
                    ?><input type="hidden" name="mkb_new_ticket_save" value="1" /><?php
                }

                // Channel

                $can_assign_channel = current_user_can('administrator') || current_user_can('mkb_assign_ticket_channels');
                $channel_not_assigned = false;
                $ticket_channels = MKB_Tickets::get_allowed_ticket_channels();

                if ($this->is_new_ticket) {
                    $channel_not_assigned = true;
                } else {
                    $channel_not_assigned = !(bool)MKB_Tickets::get_ticket_channel($ticket->ID);
                }

                if ($can_assign_channel && $channel_not_assigned) {
                    ?>
                    <p>
                        <label for="mkb_ticket_channel"><?php _e('Ticket Channel', 'minerva-kb'); ?></label>

                        <?php if (sizeof($ticket_channels)): ?>
                            <br>
                            <select name="mkb_ticket_channel" id="mkb_ticket_channel">
                                <option value=""><?php _e('Please, select ticket channel', 'minerva-kb'); ?></option>
                                <?php foreach($ticket_channels as $id => $label): ?>
                                    <option value="<?php esc_attr_e($id); ?>"><?php esc_html_e($label); ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php else: ?>
                            <br>
                            <?php _e('No ticket authors found in system. You may create a Support User to select as Ticket Author.', 'minerva-kb'); ?>
                        <?php endif; ?>
                    </p>
                    <?php
                }

                endif; // closed check
            ?>
            </div>
        </div>
        <?php
    }

    /**
     * Renders metabox taxonomy select
     * @param $ticket
     * @param $taxonomy
     * @param $user
     * @param $label
     * @param $empty_label
     * @param $cap
     */
    private static function render_ticket_taxonomy_meta_select($ticket, $taxonomy, $user, $label, $empty_label, $cap) {
        $selected_term = MKB_Tickets::get_active_ticket_term($ticket->ID, $taxonomy, true);
        $all_terms = get_terms(array(
            'taxonomy' => $taxonomy,
            'hide_empty' => false
        ));
        $can_edit_term = MKB_Tickets::user_can_assign_ticket_taxonomy($user, $ticket, $cap);
        $display_type = in_array($taxonomy, array('mkb_ticket_product', 'mkb_ticket_department')) ? 'icon' : 'color';
        ?>
        <p>
            <?php if ($can_edit_term): ?>
                <span class="js-mkb-nice-select-container mkb-nice-select-container">
                    <label for="<?php esc_attr_e($taxonomy); ?>"><?php esc_html_e($label); ?></label>
                    <br>
                    <span class="js-mkb-nice-select-wrap mkb-nice-select-wrap">
                        <select name="<?php esc_attr_e($taxonomy); ?>"
                                id="<?php esc_attr_e($taxonomy); ?>"
                                data-display-type="<?php esc_attr_e($display_type); ?>"
                                data-original-value="<?php esc_attr_e($selected_term ? $selected_term->term_id : ''); ?>">
                            <option value="" <?php if ($selected_term) { echo 'disabled'; } ?>><?php esc_html_e($empty_label); ?></option>
                            <?php foreach ($all_terms as $term): ?>
                                <option value="<?php esc_attr_e($term->term_id); ?>" <?php if ($selected_term && $term->term_id === $selected_term->term_id) { echo 'selected'; } ?>
                                    data-icon="<?php esc_attr_e(MKB_TemplateHelper::get_taxonomy_option($term, $taxonomy, 'icon')); ?>"
                                    data-color="<?php esc_attr_e(MKB_TemplateHelper::get_taxonomy_option($term, $taxonomy, 'color')); ?>"><?php esc_attr_e($term->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($display_type === 'icon'): ?>
                            <span class="js-mkb-nice-select-visual mkb-nice-select__icon fa <?php esc_attr_e(MKB_TemplateHelper::get_taxonomy_option($selected_term, $taxonomy, 'icon')); ?>" style="color: <?php esc_attr_e(MKB_TemplateHelper::get_taxonomy_option($selected_term, $taxonomy, 'color')); ?>;"></span>
                        <?php else: ?>
                            <span class="js-mkb-nice-select-visual mkb-nice-select__dot" style="background: <?php esc_attr_e(MKB_TemplateHelper::get_taxonomy_option($selected_term, $taxonomy, 'color')); ?>;"></span>
                        <?php endif; ?>
                    </span>
                    <?php if ($selected_term): ?>
                        <span class="mkb-nice-select__current-value"><?php _e('Current value:', 'minerva-kb'); ?> <strong><?php esc_html_e($selected_term->name); ?></strong></span>
                    <?php endif; ?>
                </span>
            <?php else: ?>
                <span class="mkb-disabled-select-label"><?php esc_html_e($label); ?></span>
                <span class="mkb-disabled-select-value mkb-disabled-select-value--with-visual">
                    <?php if ($display_type === 'icon'): ?>
                        <span class="mkb-disabled-select-value__icon fa <?php esc_attr_e(MKB_TemplateHelper::get_taxonomy_option($selected_term, $taxonomy, 'icon')); ?>" style="color: <?php esc_attr_e(MKB_TemplateHelper::get_taxonomy_option($selected_term, $taxonomy, 'color')); ?>;"></span>
                    <?php else: ?>
                        <span class="mkb-disabled-select-value__dot" style="background: <?php esc_attr_e(MKB_TemplateHelper::get_taxonomy_option($selected_term, $taxonomy, 'color')); ?>;"></span>
                    <?php endif; ?>
                    <?php esc_attr_e(isset($selected_term->name) ? $selected_term->name : __('Not selected', 'minerva-kb')); ?>
                </span>
            <?php endif; ?>
        </p>
        <?php
    }

    /**
     * Info metabox
     * @param $ticket
     */
    public function info_html( $ticket ) {
        $ticket_channel = MKB_Tickets::get_ticket_channel($ticket->ID);
        $author = get_user_by('ID', $ticket->post_author);

        $link = null;

        $is_guest_ticket = MKB_Tickets::is_guest_ticket($ticket->ID);

        $guest_email = get_post_meta($ticket->ID, '_mkb_guest_ticket_email', true);
        $guest_firstname = get_post_meta($ticket->ID, '_mkb_guest_ticket_firstname', true);
        $guest_lastname = get_post_meta($ticket->ID, '_mkb_guest_ticket_lastname', true);

        $opener_email = $is_guest_ticket ? $guest_email : $author->user_email;

        $referrer_type = get_post_meta($ticket->ID, '_mkb_referrer_type', true);
        $referrer_meta = get_post_meta($ticket->ID, '_mkb_referrer_meta', true);

        ?>
        <p><?php
            _e('Opened by:', 'minerva-kb'); ?>&nbsp;<strong><?php
            if ($is_guest_ticket):
                if ($guest_firstname || $guest_lastname):
                    esc_html_e(implode(' ', array($guest_firstname, $guest_lastname)));
                else:
                    _e('Not provided', 'minerva-kb');
                endif;
            else:
                if ($author) {
                    the_author_meta('display_name' , $ticket->post_author);
                } else {
                    echo '[DELETED]';
                }
            endif;
            ?></strong>
        </p>
        <p><?php _e('Opener email:', 'minerva-kb'); ?> <strong>
                <?php if($opener_email): ?>
                    <a href="mailto:<?php esc_attr_e($opener_email); ?>"><?php esc_html_e($opener_email); ?></a>
                <?php else: ?>
                    <?php _e('Not provided', 'minerva-kb'); ?>
                <?php endif; ?>
            </strong></p>
        <p><?php _e('Opened on:', 'minerva-kb'); ?> <strong><?php echo get_the_date(); ?></strong></p>
        <p><?php _e('Ticket channel:', 'minerva-kb');?> <strong><?php esc_html_e($ticket_channel['label']); ?></strong></p>
        <p><?php _e('Referred from:', 'minerva-kb'); ?> <?php

        switch($referrer_type) {
            case 'page':
                ?><a href="<?php get_the_permalink($referrer_meta); ?>"><?php esc_html_e(get_the_title($referrer_meta)); ?></a> (<?php _e('Page', 'minerva-kb'); ?>)<?php
                break;

            case 'post':
                ?><a href="<?php get_the_permalink($referrer_meta); ?>"><?php esc_html_e(get_the_title($referrer_meta)); ?></a> (<?php _e('Blog Post', 'minerva-kb'); ?>)<?php
                break;

            case 'article':
                ?><a href="<?php get_the_permalink($referrer_meta); ?>"><?php esc_html_e(get_the_title($referrer_meta)); ?></a> (<?php _e('KB Article', 'minerva-kb'); ?>)<?php
                break;

            case 'topic':
                $topic = get_term_by('id', (int)$referrer_meta, MKB_Options::option( 'article_cpt_category' ));

                if ($topic) {
                    ?><a href="<?php get_term_link($topic); ?>"><?php esc_html_e($topic->name); ?></a> (<?php _e('KB Topic', 'minerva-kb'); ?>)<?php
                }

                break;

            case 'blog':
                ?><strong><?php _e('Blog', 'minerva-kb'); ?></strong><?php
                break;

            case 'search':
                _e('Search for', 'minerva-kb'); esc_html_e(' <strong>' . $referrer_meta . '</strong>');
                break;

            default:
                ?><strong><?php _e('Not set', 'minerva-kb'); ?></strong><?php
                break;
        }

        ?></p><?php

        if ($is_guest_ticket) {
            $ticket_url = get_permalink($ticket->ID) . '?ticket_access_token=' . get_post_meta($ticket->ID, '_mkb_guest_ticket_access_token', true);

            ?><p><?php _e('Access URL:', 'minerva-kb'); ?> <a href="<?php echo esc_url($ticket_url); ?>" target="_blank"><?php the_title(); ?></a></p><?php
        } else {
            ?><p><?php _e('View Ticket:', 'minerva-kb'); ?> <a href="<?php echo esc_url(get_the_permalink($ticket->ID)); ?>" target="_blank"><?php the_title(); ?></a></p><?php
        }

        $custom_fields = get_post_meta($ticket->ID, '_mkb_custom_fields', true);

        if ($custom_fields) {
            $custom_fields = json_decode($custom_fields, true);
        }

        if ($custom_fields && sizeof($custom_fields)) {
            foreach($custom_fields as $custom_field_name) {
                $field = get_post_meta($ticket->ID, '_mkb_custom_field_' . $custom_field_name, true);

                if ($field) {
                    $field = json_decode($field, true);

                    ?><p>
                    <?php esc_html_e(
                        isset($field['label']) && trim($field['label']) ?
                            $field['label'] :
                            $field['name']
                        );
                    ?>:
                        <strong>
                            <?php if ($field['type'] === 'checkbox'): ?>
                                <i class="fa <?php echo $field['value'] ? 'fa-check-square-o' : 'fa-square-o'; ?>"></i>
                            <?php else: ?>
                                <?php esc_html_e($field['value']); ?>
                            <?php endif; ?>
                        </strong>
                    </p><?php
                }
            }
        }
    }

    /**
     * Credentials metabox
     * @param $ticket
     */
    public function credentials_html( $ticket ) {
        $credentials = MKB_Tickets::get_ticket_credentials($ticket->ID);

        if ($credentials):
            $url_regexp = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";

            if (preg_match($url_regexp, $credentials, $url)) {
                $credentials = preg_replace($url_regexp, '<a href="' . $url[0] . '" target="_blank">' . $url[0] . '</a>', $credentials);
            }

            ?>
            <div class="mkb-ticket-credentials-view"><?php echo wpautop(wp_kses_post($credentials)); ?></div>
        <?php else: ?>
            <p><?php _e('No credentials provided', 'minerva-kb'); ?></p>
        <?php endif;
    }

	/**
	 * Replies info
	 * @param $ticket
	 */
	public function replies_html( $ticket ) {

        if ($this->is_new_ticket) {
            MKB_Tickets::render_new_ticket_content_form();
            return;
        }

        $ticket_status = MKB_Tickets::get_ticket_status($ticket->ID);
        $current_user = wp_get_current_user();
        $is_ticket_assigned = MKB_Tickets::is_ticket_assigned($ticket->ID);

        if ($ticket_status['id'] === 'closed') {
            MKB_TemplateHelper::render_admin_alert('You cannot reply to a closed ticket', 'info', 'lock');
        } else if (!$is_ticket_assigned) {
            MKB_TemplateHelper::render_admin_alert('You need to assign ticket first to add replies', 'info', 'lock');
        } else if (MKB_Tickets::user_can_reply_to_ticket($current_user, $ticket)) {
            MKB_Tickets::render_ticket_admin_main_reply_form($current_user);
        } else {
            MKB_TemplateHelper::render_admin_alert('You are not currently allowed to reply to this ticket', 'info', 'lock');
        }

        $history = MKB_History::get_ticket_history($ticket->ID);

        $history = array_map(function($history_item) {
            $history_item['type'] = 'history';
            return $history_item;
        }, $history);

        $guest_user = get_user_by('login', 'minerva_support_guest_user');
        $replies = array();

        $query_args = array(
            'post_type' => 'mkb_ticket_reply',
            'posts_per_page' => -1,
            'ignore_sticky_posts' => 1,
            'post_parent' => $ticket->ID,
            'order_by' => 'date',
            'order' => 'DESC',
            'post_status' => array('publish', 'trash')
        );

        $reply_posts = get_posts($query_args);

        if (!empty($reply_posts)) {
            foreach($reply_posts as $reply){
                global $post;

                $role = 'agent';

                $author_id = $reply->post_author;

                $reply_side_meta = get_post_meta($reply->ID, '_mkb_ticket_reply_side', true);

                if ($reply_side_meta) {
                    $role = $reply_side_meta === 'admin' ? 'agent' : 'client';
                } else {
                    if (isset($guest_user) && !is_wp_error($guest_user) && $author_id == $guest_user->ID) {
                        $role = 'client'; // guest
                    } else if ($author_id === $ticket->post_author) {
                        $role = 'client'; // user
                    }
                }

                $reply = array(
                    'type' => 'reply',
                    'role' => $role,
                    'post_id' => $reply->ID,
                    'is_edited' => $reply->post_date !== $reply->post_modified,
                    'content' => $reply->post_content,
                    'status' => $reply->post_status,
                    'author_id' => $author_id,
                    'timestamp' => get_post_time('U', false, $reply)
                );

                array_push($replies, $reply);
            }
        }

        $timeline = array_merge($history, $replies);

        usort($timeline, function($a, $b) {
            if ($a['type'] === 'history' && $b['type'] === 'history') {
                return $a['id'] > $b['id'] ? -1 : 1;
            }

            if ($a['timestamp'] == $b['timestamp']) {
                // show meta changes after reply
                if ($a['type'] === 'history' && $b['type'] !== 'history') {
                    return -1;
                } else if ($a['type'] !== 'history' && $b['type'] === 'history') {
                    return 1;
                }
            }

            return $a['timestamp'] > $b['timestamp'] ? -1 : 1;
        });

        ?>

        <h3><?php _e('Ticket Discussion', 'minerva-kb'); ?></h3>

        <div class="mkb-admin-ticket-replies-wrap">
            <?php // view settings ?>

            <div class="mkb-admin-replies-view-settings">
                <i class="fa fa-cog"></i>
                <ul class="mkb-admin-replies-view-settings__list">
                    <li>
                        <label>
                            <input type="checkbox" class="js-mkb-view-hide-agent-replies" name="mkb_view_hide_agent_replies"><?php _e('Hide agent replies?', 'minerva-kb'); ?>
                        </label>
                    </li>
                    <li>
                        <label>
                            <input type="checkbox" class="js-mkb-view-hide-customer-replies" name="mkb_view_hide_customer_replies"><?php _e('Hide customer replies?', 'minerva-kb'); ?>
                        </label>
                    </li>
                    <li>
                        <label>
                            <input type="checkbox" class="js-mkb-view-hide-history-entries" name="mkb_view_hide_history_entries"><?php _e('Hide history entries?', 'minerva-kb'); ?>
                        </label>
                    </li>
                    <li>
                        <label>
                            <input type="checkbox" class="js-mkb-view-hide-deleted-replies" name="mkb_view_hide_deleted_replies"><?php _e('Hide deleted replies?', 'minerva-kb'); ?>
                        </label>
                    </li>
                    <li>
                        <label>
                            <input type="checkbox" class="js-mkb-view-limit-replies-height" name="mkb_view_limit_replies_height"><?php _e('Fixed replies list height?', 'minerva-kb'); ?>
                        </label>
                    </li>
                </ul>
            </div>

            <div class="mkb-admin-ticket-replies js-mkb-admin-ticket-replies"><?php

                if (sizeof($timeline)) {
                    foreach($timeline as $timeline_item) {
                        if ($timeline_item['type'] === 'reply') {
                            MKB_Tickets::render_ticket_admin_reply(
                                $timeline_item,
                                'reply'
                            );
                        } else if ($timeline_item['type'] === 'history') {
                            MKB_Tickets::render_ticket_admin_history_entry(
                                $timeline_item['text'],
                                $timeline_item['timestamp'],
                                $timeline_item['timestamp_gmt'],
                                $timeline_item['event_id']
                            );
                        }
                    }
                }

                // original message
                MKB_Tickets::render_ticket_admin_reply(
                    array(
                        'post_id' => $ticket->ID,
                        'content' => $ticket->post_content,
                        'role' => 'client',
                        'status' => 'publish',
                        'author_id' => $ticket->post_author,
                        'is_edited' => $ticket->post_date !== $ticket->post_modified
                    ),
                    'original'
                );
            ?>
            </div><!--.mkb-admin-ticket-replies-->
        </div><!--.mkb-admin-ticket-replies-wrap-->
        <?php
	}

    /**
     * Private notes
     * @param $ticket
     */
	public function private_notes_html($ticket) {

	    $current_user = wp_get_current_user();

        $user_can_modify_ticket = MKB_Tickets::user_can_modify_ticket($current_user, $ticket);
        $user_can_reply_to_ticket = MKB_Tickets::user_can_reply_to_ticket($current_user, $ticket);

        $user_can_edit_notes = $user_can_modify_ticket || $user_can_reply_to_ticket;

	    $notes = get_post_meta($ticket->ID, '_mkb_private_notes', true);
	    $notes = $notes ? wp_kses_post($notes) : '';

	    ?>
        <textarea name="mkb_private_notes" class="mkb-private-notes" id="mkb_private_notes" cols="30" rows="10" <?php if (!$user_can_edit_notes) { echo 'readonly'; } ?>><?php echo $notes; ?></textarea>
        <p><?php _e('You can add ticket related notes here, customer will not see them.', 'minerva-kb'); ?></p>
        <?php
    }

	/**
	 * Templates
	 */
	public function ticket_tmpl() {
        // ticket reply insert FAQ popup
        ?>
        <script type="text/html" id="tmpl-mkb-ticket-reply-editor-insert-faq">
            <div>
                <form action="" novalidate>
                    <select class="js-mkb-ticket-reply-editor-insert-faq-select"
                            name="mkb_ticket_reply_editor_select_faq"
                            id="mkb_ticket_reply_editor_select_faq">
                        <option value=""><?php _e('Select FAQ to insert', 'minerva-kb'); ?></option>
                        <?php
                        $query_args = array(
                            'post_type' => 'mkb_faq',
                            'posts_per_page' => -1,
                            'ignore_sticky_posts' => 1,
                            'orderby' => 'menu_order',
                            'order' => 'ASC'
                        );

                        $loop = new WP_Query($query_args);

                        if ( $loop->have_posts() ) :
                            while ( $loop->have_posts() ) : $loop->the_post(); ?>
                                <option value="<?php esc_attr_e(get_the_ID()); ?>"
                                    data-link="<?php esc_attr_e(get_the_permalink()); ?>">
                                    <?php the_title(); ?>
                                </option>
                            <?php
                            endwhile;
                        endif;

                        wp_reset_postdata();

                        ?>
                    </select>
                </form>
            </div>
        </script>
        <?php

        // ticket reply insert FAQ popup actions
        ?>
        <script type="text/html" id="tmpl-mkb-ticket-reply-editor-insert-faq-actions">
            <a href="#" class="js-mkb-ticket-reply-editor-insert-faq-link mkb-action-button mkb-action-default">
                <?php _e( 'Insert FAQ link', 'minerva-kb'); ?>
            </a>
            <a href="#" class="js-mkb-ticket-reply-editor-insert-faq-content mkb-action-button">
                <?php _e( 'Insert FAQ content', 'minerva-kb'); ?>
            </a>
        </script>
        <?php

        // ticket reply insert KB popup
        ?>
        <script type="text/html" id="tmpl-mkb-ticket-reply-editor-insert-kb">
            <div>
                <form action="" novalidate>
                    <select class="js-mkb-ticket-reply-editor-insert-kb-select"
                            name="mkb_ticket_reply_editor_select_kb"
                            id="mkb_ticket_reply_editor_select_kb">
                        <option value=""><?php _e('Select KB Article to insert', 'minerva-kb'); ?></option>
                        <?php
                        $query_args = array(
                            'post_type' => MKB_Options::option('article_cpt'),
                            'posts_per_page' => -1,
                            'ignore_sticky_posts' => 1,
                            'status' => 'publish'
                        );

                        $loop = new WP_Query($query_args);

                        if ( $loop->have_posts() ) :
                            while ( $loop->have_posts() ) : $loop->the_post(); ?>
                                <option value="<?php esc_attr_e(get_the_ID()); ?>"
                                        data-link="<?php esc_attr_e(get_the_permalink()); ?>">
                                    <?php the_title(); ?>
                                </option>
                            <?php
                            endwhile;
                        endif;

                        wp_reset_postdata();

                        ?>
                    </select>
                </form>
            </div>
        </script>
        <?php

        // ticket reply insert KB popup actions
        ?>
        <script type="text/html" id="tmpl-mkb-ticket-reply-editor-insert-kb-actions">
            <a href="#" class="js-mkb-ticket-reply-editor-insert-kb-link mkb-action-button mkb-action-default">
                <?php _e( 'Insert KB Article link', 'minerva-kb'); ?>
            </a>
        </script>
        <?php

        // ticket reply insert canned response popup
        ?>
        <script type="text/html" id="tmpl-mkb-ticket-reply-editor-insert-canned-response">
            <div>
                <form action="" novalidate>
                    <select class="js-mkb-ticket-reply-editor-insert-canned-response-select"
                            name="mkb_ticket_reply_editor_select_canned_response"
                            id="mkb_ticket_reply_editor_select_canned_response">
                        <option value=""><?php _e('Select Canned Response to insert', 'minerva-kb'); ?></option>
                        <?php
                        $query_args = array(
                            'post_type' => 'mkb_canned_response',
                            'posts_per_page' => -1,
                            'ignore_sticky_posts' => 1,
                            'status' => 'publish'
                        );

                        $loop = new WP_Query($query_args);

                        if ( $loop->have_posts() ) :
                            while ( $loop->have_posts() ) : $loop->the_post(); ?>
                                <option value="<?php esc_attr_e(get_the_ID()); ?>">
                                    <?php the_title(); ?>
                                </option>
                            <?php
                            endwhile;
                        endif;

                        wp_reset_postdata();
                        ?>
                    </select>
                </form>
            </div>
        </script>
        <?php

        // ticket reply insert canned response popup actions
        ?>
        <script type="text/html" id="tmpl-mkb-ticket-reply-editor-insert-canned-response-actions">
            <a href="#" class="js-mkb-ticket-reply-editor-insert-canned-response mkb-action-button mkb-action-default">
                <?php _e( 'Insert Canned Response', 'minerva-kb'); ?>
            </a>
        </script>
        <?php

        // ticket reply save as popup
        ?>
        <script type="text/html" id="tmpl-mkb-ticket-reply-save-as">
            <div>
                <form action="" class="mkb-form" novalidate>
                    <label for="mkb_ticket_reply_save_as_title"><?php _e( 'Choose Title:', 'minerva-kb'); ?></label>
                    <br>
                    <input type="text" id="mkb_ticket_reply_save_as_title" class="js-mkb-ticket-reply-save-as-title" />
                </form>
                <br>
                <span class="js-mkb-reply-save-as-response"></span>
            </div>
        </script>
        <?php

        // ticket reply save as popup actions
        ?>
        <script type="text/html" id="tmpl-mkb-ticket-reply-save-as-actions">
            <a href="#" class="js-mkb-ticket-reply-save-as mkb-action-button mkb-action-default" data-label-success="<?php esc_attr_e(__('Saved!', 'minerva-kb')); ?>">
                <?php _e( 'Save Reply', 'minerva-kb'); ?>
            </a>
        </script>
        <?php

        /**
         * Pre-rendered popups
         */
        // reply edit
        ?>
        <div class="mkb-popup-wrap">
            <div class="mkb-popup mkb-popup--auto-height js-mkb-ticket-reply-edit-popup mkb-ticket-reply-edit-popup mkb-hidden">
                <div class="mkb-popup__header mkb-clearfix">
                    <div class="mkb-popup__header-controls--left"></div>
                    <div class="mkb-popup__header-title"><?php _e('Edit Reply', 'minerva-kb'); ?></div>
                    <div class="mkb-popup__header-controls--right">
                        <a href="#" class="fn-mkb-popup-close mkb-popup-close">
                            <i class="fa fa-lg fa-times-circle"></i>
                        </a>
                    </div>
                </div>
                <div class="mkb-popup__body">
                    <form action="" novalidate>
                        <?php wp_editor('', 'mkb_single_reply_edit', array(
                            'textarea_name' => 'mkb_single_reply_edit_content',
                            'tinymce' => array(
                                'toolbar1'      => 'formatselect,bold,italic,underline,forecolor,separator,blockquote,bullist,numlist,alignleft,aligncenter,alignright,separator,link,charmap,removeformat',
                                'toolbar2'      => '',
                                'height' => 500
                            )
                        )); ?>
                    </form>
                </div>
                <div class="mkb-popup__footer mkb-clearfix">
                    <div class="mkb-popup__footer-controls--left"></div>
                    <div class="mkb-popup__footer-controls--center"></div>
                    <div class="mkb-popup__footer-controls--right">
                        <a href="#" class="js-mkb-ticket-reply-edit-save mkb-action-button mkb-action-default">
                            <?php _e('Save Changes', 'minerva-kb'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
<?php
	}

	/**
	 * Saves ticket
	 * @param $post_id
	 * @param $post
	 * @return mixed|void
	 */
	function save_post( $post_id, $post ) {
		/**
		 * Verify user is indeed user
		 */
		if (
			! isset( $_POST['mkb_save_ticket_nonce'] )
			|| ! wp_verify_nonce( $_POST['mkb_save_ticket_nonce'], 'mkb_save_ticket' )
		) {
			return;
		}

		$save_notices = array();
		$post_type = get_post_type($post_id);
		$is_new_post = isset($_REQUEST['mkb_new_ticket_save']);

		if ($post_type !== 'mkb_ticket') {
			return;
		}

		$this->ticket_id = $post_id;

		if ($is_new_post) {
            MKB_Tickets::maybe_set_custom_ticket_id($post_id);
        }

        $current_user = wp_get_current_user();
		$user_id = $current_user->ID;
        $ticket_url = get_the_permalink($post_id);
        $post_author_id = get_post_field('post_author', $post_id);
        $post_author = get_user_by('ID', $post_author_id);

        $is_guest_ticket = MKB_Tickets::is_guest_ticket($post_id);
        $guest_email = null;

        $email_template_context = array(
            'ticket_title' => get_the_title($post_id),
            'ticket_id' => $post_id,
            'action_url' => $ticket_url,
        );

        if ($is_guest_ticket) {
            $guest_email = get_post_meta($post_id, '_mkb_guest_ticket_email');

            $email_template_context['guest_firstname'] = get_post_meta($post_id, '_mkb_guest_ticket_firstname', true);
            $email_template_context['action_url'] = MKB_Tickets::get_guest_ticket_access_link($post_id);
        } else {
            $email_template_context['user_firstname'] = $post_author->first_name;
        }

        // TODO: more restriction checks

        /**
         * Private notes
         */
        update_post_meta($post_id, '_mkb_private_notes', $_REQUEST['mkb_private_notes']);

        /**
         * Ticket type
         */
        $result = self::process_taxonomy_term(
            'mkb_ticket_type',
            $post_id,
            $current_user,
            'mkb_assign_ticket_types',
            MKB_History::EVENT_TYPE_TICKET_TYPE_CHANGED,
            __('You are not allowed to assign ticket type.', 'minerva-kb')
        );

        if ($result !== true) {
            array_push($save_notices, array(
                'message' => $result,
                'type' => 'error'
            ));
        }

        /**
         * Ticket priority
         */
        $result = self::process_taxonomy_term(
            'mkb_ticket_priority',
            $post_id,
            $current_user,
            'mkb_assign_ticket_priorities',
            MKB_History::EVENT_TYPE_TICKET_PRIORITY_CHANGED,
            __('You are not allowed to assign ticket priority.', 'minerva-kb')
        );

        if ($result !== true) {
            array_push($save_notices, array(
                'message' => $result,
                'type' => 'error'
            ));
        }

        /**
         * Ticket department
         */
        $result = self::process_taxonomy_term(
            'mkb_ticket_department',
            $post_id,
            $current_user,
            'mkb_assign_ticket_departments',
            MKB_History::EVENT_TYPE_TICKET_DEPARTMENT_CHANGED,
            __('You are not allowed to assign ticket department.', 'minerva-kb')
        );

        if ($result !== true) {
            array_push($save_notices, array(
                'message' => $result,
                'type' => 'error'
            ));
        }

        /**
         * Ticket product
         */
        $result = self::process_taxonomy_term(
            'mkb_ticket_product',
            $post_id,
            $current_user,
            'mkb_assign_ticket_products',
            MKB_History::EVENT_TYPE_TICKET_PRODUCT_CHANGED,
            __('You are not allowed to assign ticket product.', 'minerva-kb')
        );

        if ($result !== true) {
            array_push($save_notices, array(
                'message' => $result,
                'type' => 'error'
            ));
        }

        /**
         * Ticket assignee
         */
        $ticket_assignee = isset($_REQUEST['mkb_ticket_assignee']) ? $_REQUEST['mkb_ticket_assignee'] : '';
        $is_assignee_changed = false;
        $current_assignee = MKB_Tickets::get_ticket_assignee($post_id);

        if (!$current_assignee) {
            $current_assignee = null;
            // TODO: maybe check for existing user
        }

        if ($ticket_assignee === 'unassigned') {
            // TODO: permission check
            delete_post_meta($post_id, '_mkb_ticket_assignee');

            $is_assignee_changed = true;
        } else if ($ticket_assignee && $current_assignee != $ticket_assignee) {
            update_post_meta($post_id, '_mkb_ticket_assignee', $ticket_assignee);

            $is_assignee_changed = true;
        }

        if ($is_assignee_changed) {
            MKB_History::track_ticket_assignee_change($post_id, $user_id, $ticket_assignee === 'unassigned' ? null : $ticket_assignee, $current_assignee);

            if ($ticket_assignee !== 'unassigned' && MKB_Options::option('email_agent_ticket_assigned_switch')) {
                $assignee_user = get_user_by('ID', $ticket_assignee);

                MKB_Emails::instance()->send(
                    $assignee_user->user_email,
                    MKB_Emails::EMAIL_TYPE_AGENT_TICKET_ASSIGNED,
                    array(
                        'agent_firstname' => $assignee_user->first_name,
                        'ticket_title' => get_the_title($post_id),
                        'ticket_id' => $post_id,
                        'action_url' => MKB_Utils::get_post_edit_admin_url($post_id)
                    )
                );
            }
        }

        /**
         * Channel
         */
        $channel = isset($_REQUEST['mkb_ticket_channel']) ? $_REQUEST['mkb_ticket_channel'] : null;

        if ($channel && current_user_can('mkb_assign_ticket_channels')) {
            if (MKB_Tickets::set_ticket_channel($post_id, $channel)) {
                MKB_History::track_ticket_channel_change($post_id, $user_id, $channel, null); // add previous channel if we add channel modification
            }
        }

        /**
         * Add ticket reply
         */
		if (isset($_REQUEST['mkb_reply_content']) && !empty($_REQUEST['mkb_reply_content'])) {

            if (MKB_Tickets::user_can_reply_to_ticket($current_user, $post)) {

                $escaped_reply_content = wp_kses_post($_REQUEST['mkb_reply_content']);

                $reply_post = array(
                    'post_type'     => 'mkb_ticket_reply',
                    'post_title'    => 'SUPPORT TICKET #' . $post_id . ' REPLY',
                    'post_content'  => $escaped_reply_content,
                    'post_status'   => 'publish',
                    'post_author'   => $current_user->ID,
                    'post_parent'   => $post_id
                );

                $reply_id = wp_insert_post( $reply_post );

                if (!$reply_id) {
                    // general reply creation error
                    array_push($save_notices, array(
                        'message' => __('Unknown ticket reply error.', 'minerva-kb'),
                        'type' => 'error'
                    ));
                } else if (is_wp_error( $reply_id )) {
                    $error = $reply_id;
                    // WP_Error
                    array_push($save_notices, array(
                        'message' => __('Ticket reply error.', 'minerva-kb') . ' ' . $error->get_error_message(),
                        'type' => 'error'
                    ));
                } else {
                    MKB_Tickets::clear_awaiting_agent_reply_flag($post_id);
                    MKB_Tickets::increase_unread_agent_replies_count_flag($post_id);

                    add_post_meta($reply_id, '_mkb_ticket_reply_side', 'admin', true);

                    $email_template_context['message_text'] = $escaped_reply_content;

                    if ($is_guest_ticket && $guest_email && MKB_Options::option('email_guest_ticket_reply_added_switch')) {
                        MKB_Emails::instance()->send(
                            $guest_email,
                            MKB_Emails::EMAIL_TYPE_GUEST_TICKET_REPLY_ADDED,
                            $email_template_context
                        );
                    } else if (!$is_guest_ticket && MKB_Options::option('email_user_ticket_reply_added_switch')) { // user
                        MKB_Emails::instance()->send(
                            $post_author->user_email,
                            MKB_Emails::EMAIL_TYPE_USER_TICKET_REPLY_ADDED,
                            $email_template_context
                        );
                    }

                    // process attachments
                    $uploader = new MKB_Attachments(
                        'mkb_ticket_reply_files',
                        'ticket' . $post_id,
                        $reply_id
                    );

                    $attachments_errors = $uploader->process_files();

                    if (sizeof($attachments_errors)) {
                        array_push($save_notices, array(
                            'message' => $attachments_errors[0],
                            'type' => 'error'
                        ));
                    }
                }
            } else {
                // permissions error
                array_push($save_notices, array(
                    'message' => __('You are not allowed to reply to this ticket.', 'minerva-kb'),
                    'type' => 'error'
                ));
            }
        }

        /**
         * Ticket status
         */
        $changed_status = MKB_Tickets::update_ticket_status_on_post_save($post_id, $user_id, $is_new_post);

        // ticket closed by agent
        if ($changed_status && $changed_status === MKB_Tickets::TICKET_STATUS_CLOSED) {
            if ($is_guest_ticket && $guest_email && MKB_Options::option('email_guest_ticket_closed_switch')) { // guest
                MKB_Emails::instance()->send(
                    $guest_email,
                    MKB_Emails::EMAIL_TYPE_GUEST_TICKET_CLOSED,
                    $email_template_context
                );
            } else if (!$is_guest_ticket && MKB_Options::option('email_user_ticket_closed_switch')) { // user
                MKB_Emails::instance()->send(
                    $post_author->user_email,
                    MKB_Emails::EMAIL_TYPE_USER_TICKET_CLOSED,
                    $email_template_context
                );
            }
        }

        set_transient("_mkb_save_ticket_notices_for_user_{$user_id}", $save_notices, 45);

        if (!isset($_REQUEST['mkb_ticket_stay_on_ticket'])) {
            $url = admin_url() . 'edit.php?post_type=mkb_ticket';
            wp_redirect($url);
            exit;
        }
	}

    /**
     * Saves and track ticket taxonomy
     * @param $taxonomy
     * @param $post_id
     * @param $user
     * @param $cap
     * @param $tracking_event
     * @param $permission_error_message
     * @return array|bool
     */
	private static function process_taxonomy_term($taxonomy, $post_id, $user, $cap, $tracking_event, $permission_error_message) {
        $term_id = isset($_REQUEST[$taxonomy]) ? $_REQUEST[$taxonomy] : null;

        if ($term_id && !MKB_Tickets::is_currently_active_term($post_id, $term_id, $taxonomy)) {
            if (MKB_Tickets::user_can_assign_ticket_taxonomy($user, $post_id, $cap)) {
                $previous_term = null;

                $current_terms = wp_get_post_terms($post_id, $taxonomy);

                if ($current_terms && !is_wp_error($current_terms) && isset($current_terms[0])) {
                    $previous_term = $current_terms[0]->term_id;
                }

                $result = wp_set_post_terms($post_id, array($term_id), $taxonomy, false);

                if (!is_wp_error($result)) {
                    MKB_History::track_ticket_taxonomy_term_change($tracking_event, $post_id, $user->ID, $term_id, $previous_term);
                }

                return true;
            } else {
                // permissions error
                return array(
                    'message' => $permission_error_message,
                    'type' => 'error'
                );
            }
        }

        return true;
    }

    /**
     * TODO: move to some notices manager class
     */
    public function admin_notices() {
        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;
        $transient_name = "_mkb_save_ticket_notices_for_user_{$user_id}";

        if ( $save_notices = get_transient($transient_name) ) {
            if (sizeof($save_notices)) {
                foreach($save_notices as $notice) {
                    MKB_TemplateHelper::render_admin_notice($notice['message'], $notice['type']);
                }
            }

            delete_transient($transient_name);
        }
    }

    public function remove_seo_meta_box() {
        remove_meta_box('wpseo_meta', 'mkb_ticket', 'normal');
    }

    public function add_open_ticket_count_filter() {
        add_filter('attribute_escape', array($this, 'display_open_tickets_count'), 20, 2);
    }

    public function display_open_tickets_count( $safe_text = '', $text = '' ) {
        if ( substr_count($text, '%%MKBTicketCount%%') ) {
            // this is the menu name we want to modify
            // TODO: rewrite replace
            $text = trim( str_replace('%%MKBTicketCount%%', '', $text) );

            // once you have found the string you want to modify, no need to use the filter
            remove_filter('attribute_escape', 'mkb_display_count_cpt_posts_pending_approval', 20, 2);

            $tickets_count = MinervaKB_App::instance()->info->get_user_tickets_count();
            $count = $tickets_count['active'];

            if ( $count > 0 ) {
                // if there are posts pending approval
                $safe_text = esc_attr($text) . ' ' .
                    '<span class="js-mkb-active-tickets-count-wrap">' .
                        '<span class="awaiting-mod count-' . $count .'">' .
                            '<span class="mkb-open-tickets" aria-hidden="true">' . $count . '</span>' .
                            '<span class="open-tickets-text screen-reader-text">' . $count . __(' open tickets', 'minerva-kb') . '</span>' .
                        '</span>' .
                    '</span>';
            } else {
                $safe_text = esc_attr($text) . ' <span class="js-mkb-active-tickets-count-wrap"></span>';
            }
        }

        return $safe_text;
    }

    public function ticket_updated_messages( $messages ) {
        global $post, $post_ID;

        $messages['mkb_ticket'] = array(
            0 => '', // Unused. Messages start at index 1.
            1 => sprintf( __('Ticket updated. <a href="%s">View ticket</a>', 'minerva-kb'), esc_url( get_permalink($post_ID) ) ),
            2 => __('Custom field updated.', 'minerva-kb'),
            3 => __('Custom field deleted.', 'minerva-kb'),
            4 => __('Ticket updated.', 'minerva-kb'),
            /* translators: %s: date and time of the revision */
            5 => isset($_GET['revision']) ? sprintf( __('Ticket restored to revision from %s', 'minerva-kb'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
            6 => sprintf( __('Ticket published. <a href="%s">View ticket</a>', 'minerva-kb'), esc_url( get_permalink($post_ID) ) ),
            7 => __('Ticket saved.', 'minerva-kb'),
            8 => sprintf( __('Ticket submitted. <a target="_blank" href="%s">Preview ticket</a>', 'minerva-kb'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
            9 => sprintf( __('Ticket scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview ticket</a>', 'minerva-kb'),
                // translators: Publish box date format, see http://php.net/date
                date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
            10 => sprintf( __('Ticket draft updated. <a target="_blank" href="%s">Preview ticket</a>', 'minerva-kb'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
        );

        return $messages;
    }
}
