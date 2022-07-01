<?php
/**
 * Project: Minerva KB
 * Copyright: 2015-2016 @KonstruktStudio
 */
global $post;

$ticket = $post;
$ticket_id = get_the_ID();
$is_guest_ticket = get_post_meta($ticket_id, '_mkb_guest_ticket_access_token', true);
$user = wp_get_current_user();
$is_ticket_author_viewing = (bool)$user && $user->ID === (int)$ticket->post_author;

// note: must be before header for redirect
MKB_Tickets::verify_ticket_access($ticket);

if (!MKB_Options::option('no_ticket_header')):
	get_header();
endif;

do_action('minerva_ticket_root_before');

?><div class="<?php echo esc_attr(MKB_TemplateHelper::root_class('ticket')); ?>"><?php

		MKB_TemplateHelper::maybe_render_left_sidebar( 'ticket' );

		?><div class="<?php echo esc_attr(MKB_TemplateHelper::content_class('ticket')); ?>">
            <div id="mkb-ticket-<?php the_ID(); ?>"><?php

					do_action('minerva_ticket_title_before');

					?><div class="mkb-page-header"><?php

						do_action('minerva_ticket_title_inside_before');

                        $ticket_status = MKB_Tickets::get_ticket_status($ticket_id);

                        the_title('<h1 class="mkb-page-title">' .

                            '<span class="mkb-ticket-status mkb-ticket-status--' . esc_attr($ticket_status['id']) . '">' .
                                '<i class="fa ' . esc_attr($ticket_status['icon']) . '"></i>' . ' ' .
                                esc_html($ticket_status['label']) .
                            '</span>' .
                            MKB_Options::option('ticket_page_title_prefix'),

                        '</h1>');

						do_action('minerva_ticket_title_inside_after');

					?></div><!-- .mkb-entry-header --><?php

					do_action('minerva_ticket_title_after');

					?>
					<div class="mkb-ticket-content"><?php

						do_action('minerva_ticket_content_inside_before');

                        $ticket_timestamp = get_post_time('U', false, $ticket_id);
                        $ticket_timestamp_gmt = get_post_time('U', true, $ticket_id);

                        $replies_query_args = array(
                            'post_type' => 'mkb_ticket_reply',
                            'posts_per_page' => -1,
                            'ignore_sticky_posts' => 1,
                            'post_parent' => $ticket_id
                        );

                        $replies = new WP_Query( $replies_query_args );

                        wp_reset_postdata();

                        $ticket_author = get_user_by('ID', $post->post_author);

                        $opener_text = MKB_Options::option($is_guest_ticket ? 'ticket_page_opened_guest_text' : 'ticket_page_opened_text');

                        if (!$is_guest_ticket) {
                            $opener_text = str_replace('{{USER}}', $ticket_author->display_name, $opener_text);
                        }

                        $opener_text = str_replace('{{DATE}}', MKB_Utils::get_human_date_html($ticket_timestamp_gmt, $ticket_timestamp, true), $opener_text);

                        ?><div class="mkb-ticket-top-info"><?php
                            echo $opener_text; ?> - <em><?php esc_attr_e($replies->found_posts); ?></em> <?php
                            esc_html_e($replies->found_posts === 1 ?
                                MKB_Options::option('ticket_page_reply_text') :
                                MKB_Options::option('ticket_page_replies_text')); ?></div><?php

						?><div class="mkb-ticket-content__text"><?php

							do_action('minerva_ticket_text_before');

							?><span class="mkb-original-message-label"><?php esc_html_e(MKB_Options::option('ticket_page_message_label')); ?></span><?php

							the_content();

							do_action('minerva_ticket_text_after');

                            MKB_TemplateHelper::render_ticket_attachments();

						?></div><?php

                        ?><div class="mkb-ticket-meta"><?php

                            MKB_TemplateHelper::render_ticket_meta();

                        ?></div><?php

						do_action('minerva_ticket_content_inside_after');

					?></div><!-- .mkb-single-content --><?php

					do_action('minerva_ticket_content_after');

					?></div><!-- #mkb-article-## --><?php

            MKB_TemplateHelper::render_ticket_replies();

            if ($is_guest_ticket || $is_ticket_author_viewing) { // user ticket
                MKB_TemplateHelper::render_ticket_reply_form();
            }

            ?>

    </div><!--.mkb-content-main-->
    <?php MKB_TemplateHelper::maybe_render_right_sidebar( 'ticket' ); ?>
</div><!--.mkb-container--><?php

do_action('minerva_ticket_root_after');

if (!MKB_Options::option('no_ticket_footer')):
	get_footer();
endif;

?>