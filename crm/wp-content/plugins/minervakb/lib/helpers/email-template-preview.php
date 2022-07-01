<?php
/**
 * Basic email template preview in browser
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!is_user_logged_in()) {
    wp_die('You do not have access to this page');
}

$template_id = isset($_REQUEST['mkb_email_template_id']) ? $_REQUEST['mkb_email_template_id'] : null;

$allowed_email_templates = MKB_Emails::get_allowed_templates();

if (!$template_id || !in_array($template_id, $allowed_email_templates)) {
    wp_die('Email template not found');
}

echo MKB_Emails::instance()->get_email_content($template_id, array(), true);

exit;
