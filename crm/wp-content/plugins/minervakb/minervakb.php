<?php
/*
* Plugin Name: Minerva Support: Knowledge Base | FAQ | Glossary | Tickets
* Old Plugin Name: MinervaKB Knowledge Base for WordPress with Analytics
* Description: MinervaKB - Knowledge Base for WordPress with Analytics
* Plugin URI: https://codecanyon.net/item/minervakb-knowledge-base-for-wordpress-with-analytics/19185769?ref=KonstruktStudio
* Author: KonstruktStudio
* Author URI: https://codecanyon.net/user/konstruktstudio/portfolio?ref=KonstruktStudio
* Text Domain: minerva-kb
* Domain Path: /languages/
* Version: 2.0.3
*/

define('MINERVA_KB_VERSION', '2.0.3');
define('MINERVA_KB_OPTION_PREFIX', 'mkb_option_');
define('MINERVA_KB_PLUGIN_FILE', __FILE__ );
define('MINERVA_KB_PLUGIN_URL', plugin_dir_url( __FILE__ ));
define('MINERVA_KB_PLUGIN_PREFIX', 'minerva-kb-');
define('MINERVA_KB_IMG_URL', MINERVA_KB_PLUGIN_URL . 'assets/img/');
define('MINERVA_KB_UPLOAD_SUBDIR', '/minerva-support');
define('MINERVA_KB_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('MINERVA_THEME_DIR', get_stylesheet_directory());

add_action( 'plugins_loaded', 'minervakb_load_textdomain' );

function minervakb_load_textdomain() {
    load_plugin_textdomain( 'minerva-kb', false, dirname(plugin_basename( __FILE__ )) . '/languages' );
}

// register custom DB on plugin activation
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/db.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/attachments.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/users.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/history.php');
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/migrate.php');

// create search analytics table on plugin activation
function mkb_on_activate( $network_wide ) {
	global $wpdb;

	if (is_multisite() && $network_wide) {
		// Get all blogs in the network and activate plugin on each one
		$blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );

		foreach ($blog_ids as $blog_id) {
			switch_to_blog( $blog_id );
            MKB_Attachments::create_upload_directory();
			MKB_DbModel::create_schema();
			MKB_History::create_schema();
			restore_current_blog();
		}
	} else {
        MKB_Attachments::create_upload_directory();
		MKB_DbModel::create_schema();
        MKB_History::create_schema();
    }
}
register_activation_hook(__FILE__, 'mkb_on_activate');

require_once(MINERVA_KB_PLUGIN_DIR . 'lib/restrict.php');
// removes plugin technical data on uninstall
function mkb_on_uninstall () {
	MKB_Options::remove_flush_flags();
	MinervaKB_Restrict::invalidate_restriction_cache(false);
}
register_uninstall_hook(__FILE__, 'mkb_on_uninstall');

// create search analytics table whenever a new blog is created
function mkb_on_create_blog($blog_id, $user_id, $domain, $path, $site_id, $meta ) {
	if (is_plugin_active_for_network( 'minervakb/minervakb.php')) {
		switch_to_blog( $blog_id );
		MKB_DbModel::create_schema();
        MKB_History::create_schema();
		restore_current_blog();
	}
}
add_action( 'wpmu_new_blog', 'mkb_on_create_blog', 10, 6 );

// init app
require_once(MINERVA_KB_PLUGIN_DIR . 'lib/app.php');

function MinervaKB_Init() {
	global $minerva_kb;
	$minerva_kb = MinervaKB_App::instance();
}
add_action('init', 'MinervaKB_Init', 0);
