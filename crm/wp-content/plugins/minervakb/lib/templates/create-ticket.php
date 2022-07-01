<?php
/**
 * Template Name: MinervaKB Create Ticket Page Template
 *
 * Project: Minerva KB
 * Copyright: 2015-2016 @KonstruktStudio
 */

if (!MKB_Options::option('no_page_header')):
	get_header();
endif;

do_action('minerva_create_ticket_page_root_before');

?><div class="<?php echo esc_attr(MKB_TemplateHelper::root_class('create_ticket')); ?>"><?php

	MKB_TemplateHelper::maybe_render_left_sidebar( 'create_ticket' );

	?><div class="<?php echo esc_attr(MKB_TemplateHelper::content_class('create_ticket')); ?>"><?php

		while (have_posts()) : the_post(); // main loop

            do_action('minerva_create_ticket_page_title_before');

            ?><div class="mkb-create-ticket-page-header"><?php

                do_action('minerva_create_ticket_page_title_inside_before');

                the_title( '<h1 class="mkb-page-title">', '</h1>' );

                do_action('minerva_create_ticket_page_title_inside_after');

            ?></div><?php

            do_action('minerva_create_ticket_page_title_after');

			do_action('minerva_create_ticket_page_loop_before');

			?><div class="mkb-create-ticket-page-content"><?php

				do_action('minerva_create_ticket_page_content_inside_before');

				the_content();

				do_action('minerva_create_ticket_page_content_inside_after');

			?></div><!-- .mkb-entry-content --></div><?php

			do_action('minerva_create_ticket_page_loop_after');

		endwhile;

	MKB_TemplateHelper::maybe_render_right_sidebar( 'create_ticket' );

	?></div><!--.mkb-container--><?php

do_action('minerva_create_ticket_page_root_after');

if (!MKB_Options::option('no_page_footer')):
	get_footer();
endif;

?>