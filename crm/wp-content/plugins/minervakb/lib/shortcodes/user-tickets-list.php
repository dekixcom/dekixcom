<?php
/**
 * Project: MinervaKB.
 * Copyright: 2015-2017 @KonstruktStudio
 */

class MinervaKB_UserTicketsListShortcode extends KST_Shortcode implements KST_Shortcode_Interface {

	protected $ID = 'user-tickets-list';
	protected $name = 'User Tickets List';
	protected $description = 'Renders user support tickets';
	protected $icon = 'fa fa-paper-plane-o';

	/**
	 * Renders shortcode
	 * @param $atts
	 * @param string $content
	 */
	public function render($atts, $content = '') {
        if (!is_user_logged_in()) {
            return;
        }

        $query_args = array(
            'post_type' => 'mkb_ticket',
            'ignore_sticky_posts' => 1,
            'posts_per_page' => -1, // TODO: limit option
            'post_status' => 'publish',
            'author' => get_current_user_id()
        );

        $tickets_loop = new WP_Query( $query_args );

        if ($tickets_loop->have_posts()):
            ?>
            <div class="mkb-tickets-list">
                <div class="mkb-tickets-list__row mkb-tickets-list__row--head">
                    <span class="mkb-tickets-list__cell cell--id"><?php _e('ID', 'minerva-kb'); ?></span>
                    <span class="mkb-tickets-list__cell cell--ticket"><?php _e('Ticket', 'minerva-kb'); ?></span>
                    <span class="mkb-tickets-list__cell cell--type"><?php _e('Ticket Type', 'minerva-kb'); ?></span>
                    <span class="mkb-tickets-list__cell cell--date"><?php _e('Opened', 'minerva-kb'); ?></span>
                    <span class="mkb-tickets-list__cell cell--replies"><?php _e('Replies', 'minerva-kb'); ?></span>
                </div>
            <?php

            while ( $tickets_loop->have_posts() ) : $tickets_loop->the_post();

                $ticket_id = get_the_ID();
                $ticket_timestamp = get_post_time('U', false, $ticket_id);
                $ticket_timestamp_gmt = get_post_time('U', true, $ticket_id);

                ?>
                <div class="mkb-tickets-list__row">

                    <span class="mkb-tickets-list__cell"><?php the_ID(); ?></span>

                    <span class="mkb-tickets-list__cell">
                        <?php MKB_Tickets::render_ticket_status_badge($ticket_id); ?> <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                    </span>

                    <span class="mkb-tickets-list__cell">
                        <?php MKB_Tickets::render_ticket_type_badge($ticket_id); ?>
                    </span>

                    <span class="mkb-tickets-list__cell">
                        <?php MKB_Utils::render_human_date($ticket_timestamp_gmt, $ticket_timestamp, true); ?>
                    </span>

                    <span class="mkb-tickets-list__cell">
                        <?php
                        $replies = get_posts(array(
                            'post_type' => 'mkb_ticket_reply',
                            'posts_per_page' => -1,
                            'ignore_sticky_posts' => 1,
                            'post_parent' => $ticket_id,
                            'post_status' => array('publish', 'trash')
                        ));

                        $unread_replies_count = MKB_Tickets::get_unread_agent_replies_count($ticket_id);

                        if ($unread_replies_count) {
                           ?><span class="mkb-unread-replies-count-badge"><?php echo esc_html($unread_replies_count); ?> new</span> <?php
                        }

                        $replies_count = sizeof($replies);

                        if ($replies_count === 0) {
                            echo esc_html(MKB_Options::option('ticket_discussion_no_replies_text'));
                        } else {
                            ?><strong><?php echo esc_html($replies_count); ?></strong> <?php

                            echo esc_html(MKB_Options::option($replies_count === 1 ? 'ticket_page_reply_text' : 'ticket_page_replies_text'));
                        }

                        if (sizeof($replies)) {
                            $latest_reply = $replies[0];
                            $reply_author_id = (int)$latest_reply->post_author;
                            $reply_author = get_user_by('id', $reply_author_id);

                            $reply_timestamp = get_post_time('U', false, $latest_reply->ID);
                            $reply_timestamp_gmt = get_post_time('U', true, $latest_reply->ID);

                            if ($reply_author) {
                                ?>, latest reply from <strong><?php echo esc_html($reply_author->display_name); ?></strong> <?php
                                MKB_Utils::render_human_date($reply_timestamp_gmt, $reply_timestamp, true);
                            }
                        }

                        ?>
                    </span>
                </div><?php

            endwhile;

            ?>
            </div>
        <?php

        else:
            if (MKB_Tickets::user_can_create_tickets()) {
                echo do_shortcode('[mkb-info]' .
                    __('Looks like you don\'t have tickets yet.', 'minerva-kb') .
                    '[/mkb-info]'
                );
            }
        endif;

        wp_reset_postdata();

        wp_enqueue_script( 'minerva-kb/moment-js', MINERVA_KB_PLUGIN_URL . 'assets/js/vendor/moment/moment-with-locales.js', array(), '2.24.0', true );
	}
}