<?php
/**
 * Base email HTML template
 *
 * see lib/emails.php for a list of default action handlers
 * $email_context must be defined before include
 *
 * Project: MinervaKB
 * Copyright: 2015-2020 @KonstruktStudio
 */

if (!defined('ABSPATH')) { exit; }

/**
 * email <head> with styles and opening body tags
 * uses lib/templates/email/email-header.php
 */
do_action('minerva_email_header', $email_context);

/**
 * email common header, usually logo or company name
 * uses lib/templates/email/email-content-header.php
 */
do_action('minerva_email_content_header', $email_context);

/**
 * email content opening tags
 * uses lib/templates/email/email-content-container-start.php
 */
do_action('minerva_email_content_before', $email_context);

/**
 * main email content
 * uses template-specific options
 */
do_action('minerva_email_content_body', $email_context);

/**
 * email content closing tags
 * uses lib/templates/email/email-content-container-end.php
 */
do_action('minerva_email_content_after', $email_context);

/**
 * email common footer, with text & copyright from settings
 * uses lib/templates/email/email-content-footer.php
 */
do_action('minerva_email_content_footer', $email_context);

/**
 * closing email body tags
 * uses lib/templates/email/email-footer.php
 */
do_action('minerva_email_footer', $email_context);
