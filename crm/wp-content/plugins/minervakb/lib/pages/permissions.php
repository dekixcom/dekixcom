<?php

/**
 * Permissions page controller
 * Class MinervaKB_DashboardPage
 */

class MinervaKB_PermissionsPage {

	private $SCREEN_BASE = null;

	public function __construct() {

		$this->SCREEN_BASE = MKB_Options::option('article_cpt') . '_page_minerva-kb-submenu-permissions';

		add_action( 'admin_menu', array( $this, 'add_submenu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'load_assets' ) );
	}

	/**
	 * Sets up dependencies
	 * @param $deps
	 */
	private function setup_dependencies($deps) {}

	/**
	 * Adds dashboard submenu page
	 */
	public function add_submenu() {
		add_submenu_page(
			'edit.php?post_type=' . MKB_Options::option('article_cpt'),
			__( 'Permissions', 'minerva-kb' ),
			__( 'Permissions', 'minerva-kb' ),
			'manage_options', // admins only
			'minerva-kb-submenu-permissions',
			array( $this, 'submenu_html' )
		);
	}

	/**
	 * Gets dashboard page html
	 */
	public function submenu_html() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'minerva-kb' ) );
		}

        global $wp_roles;

		$wp_roles_names = $wp_roles->get_names();

		$roles = array_unique(
            array_merge(
                array(
                    'mkb_support_agent' => isset($wp_roles->roles['mkb_support_agent']) ?
                        $wp_roles_names['mkb_support_agent'] :
                        '[DELETED] Minerva Support Agent',
                    'mkb_support_manager' => isset($wp_roles->roles['mkb_support_manager']) ?
                        $wp_roles_names['mkb_support_manager'] :
                        '[DELETED] Minerva Support Manager',
                    'mkb_support_user' => isset($wp_roles->roles['mkb_support_user']) ?
                        $wp_roles_names['mkb_support_user'] :
                        '[DELETED] Minerva Support User'
                ),
                $wp_roles_names
            )
        );

		$known_caps = MKB_Users::get_known_capabilities();

		$edited_role = 'mkb_support_agent';

		if (isset($_REQUEST['edited_role'])) {
            $edited_role = esc_html($_REQUEST['edited_role']);
        }

        ?>
		<div class="mkb-admin-page-header">
			<span class="mkb-header-logo mkb-header-item" data-version="v<?php echo esc_attr(MINERVA_KB_VERSION); ?>">
				<img class="logo-img" src="<?php echo esc_attr( MINERVA_KB_IMG_URL . 'logo.png' ); ?>" title="logo"/>
			</span>
			<span class="mkb-header-title mkb-header-item"><?php _e( 'Permissions', 'minerva-kb' ); ?></span>
			<?php MinervaKB_AutoUpdate::registered_label(); ?>
		</div>

		<div id="mkb-permissions">

            <form action="" class="js-mkb-update-permissions-form">

                <label for="mkb_edited_role"><?php _e('Edited Role:', 'minerva-kb'); ?> </label>
                <select name="mkb_edited_role" id="mkb_edited_role">
                    <?php foreach($roles as $role => $name): if ($role === 'administrator') { continue; } ?>
                        <option value="<?php echo esc_attr($role); ?>"<?php if ($edited_role === $role) { echo ' selected'; } ?>><?php echo esc_html($name); ?></option>
                    <?php endforeach; ?>
                </select>

                <br>
                <br>

                <?php

                MKB_TemplateHelper::render_admin_alert(__('Please note, that this page only displays and changes capabilities related to MinervaKB plugin. You can add or remove other capabilities using dedicated plugins for editing WordPress user roles.', 'minerva-kb'), 'info');

                ?>

                <br>

                <a href="#" class="button mkb-caps-unfold-all js-mkb-caps-unfold-all">
                    <span class="mkb-caps-unfold-closed"><i class="fa fa-plus"></i> <?php _e('Unfold All', 'minerva-kb'); ?></span>
                    <span class="mkb-caps-unfold-open"><i class="fa fa-minus"></i> <?php _e('Fold All', 'minerva-kb'); ?></span>
                </a>&nbsp;
                <a href="#" class="button js-mkb-caps-grant-all"><i class="fa fa-unlock"></i> <?php _e('Grant All', 'minerva-kb'); ?></a>&nbsp;
                <a href="#" class="button js-mkb-caps-deny-all"><i class="fa fa-lock"></i> <?php _e('Deny All', 'minerva-kb'); ?></a>&nbsp;
                <?php if (in_array($edited_role,
                    array(
                        'contributor',
                        'author',
                        'editor',
                        'mkb_support_user',
                        'mkb_support_agent',
                        'mkb_support_manager',
                    )
                )): ?>
                    <a href="#" class="button js-mkb-load-default-caps-preset" data-role="<?php echo esc_attr($edited_role); ?>"><i class="fa fa-user-circle"></i> <?php _e('Default Preset for Role', 'minerva-kb'); ?></a>
                <?php endif; ?>
            <?php

            $cap_category_names = $this->get_cap_category_names();

            $edited_role_caps = array();

            if (isset($wp_roles->roles[$edited_role])):
                $edited_role_caps = $wp_roles->roles[$edited_role]['capabilities'];

                ?><div class="mkb-caps-edit-wrap"><?php

                    foreach($known_caps as $category => $category_caps):

                        if ($category === 'wp' && !in_array($edited_role, array('mkb_support_agent', 'mkb_support_manager', 'mkb_support_user'))) {
                            continue;
                        }

                        ?><div class="mkb-caps-edit-group js-mkb-caps-edit-group"><?php

                            $group_granted_caps = sizeof(
                                array_intersect(
                                    array_keys($edited_role_caps),
                                    array_keys($category_caps)
                                )
                            );

                            ?>
                            <h3>
                                <i class="fa <?php echo esc_attr($cap_category_names[$category]['icon']); ?> mkb-caps-edit-group-icon"></i>
                                <?php echo esc_html($cap_category_names[$category]['name']); ?>
                                (<span class="js-mkb-granted-caps-count"><?php echo esc_attr($group_granted_caps); ?></span>/<?php echo sizeof($category_caps); ?>)
                                &nbsp;<i class="fa fa-caret-right mkb-caps-edit-group-unfold-icon"></i>
                            </h3>

                            <div class="mkb-caps-edit-group-items">

                                <div class="mkb-edit-caps-toggle-row">
                                    <input type="checkbox" class="js-mkb-caps-edit-group-toggle" id="mkb_cap_group_toggle_<?php echo esc_attr($category); ?>">
                                    <label for="mkb_cap_group_toggle_<?php echo esc_attr($category); ?>"><?php _e('Toggle all', 'minerva-kb'); ?></label>
                                </div>

                                <?php

                                foreach($category_caps as $cap_id => $cap_description):
                                    ?>
                                    <div class="mkb-edit-caps-row">
                                        <input type="checkbox" class="js-mkb-cap-value" id="<?php echo esc_attr($cap_id); ?>" name="<?php echo esc_attr($cap_id); ?>"<?php if (array_key_exists($cap_id, $edited_role_caps)) { echo 'checked'; } ?>>
                                        <label for="<?php echo esc_attr($cap_id); ?>"><strong><?php echo esc_html($cap_id); ?></strong> - <?php echo esc_html($cap_description); ?></label>
                                    </div>
                                    <?php
                                endforeach;

                            ?></div><?php

                        ?></div><?php

                    endforeach;

                    ?></div><?php

            else:
                echo '<p>Role <strong>' . $edited_role . '</strong> has been deleted</p>';
            endif;

                ?>
                <br>
                <button type="submit" class="button button-primary"><?php _e('Update Role Permissions', 'minerva-kb'); ?></button>
            </form>
		</div>
	<?php
	}

	public static function get_cap_category_names() {
	    return array(
            // TODO: Ticket caps
            // General WP
            'wp' => array(
                'name' => __('WordPress', 'minerva-kb'),
                'icon' => 'fa-wordpress'
            ),
            // Knowledge Base
            'kb' => array(
                'name' => __('Knowledge Base', 'minerva-kb'),
                'icon' => 'fa-university'
            ),
            'kb_edit' => array(
                'name' => __('Knowledge Base - Edit & Publish', 'minerva-kb'),
                'icon' => 'fa-university'
            ),
            'kb_delete' => array(
                'name' => __('Knowledge Base - Delete', 'minerva-kb'),
                'icon' => 'fa-university'
            ),
            'kb_tax' => array(
                'name' => __('Knowledge Base - Taxonomies', 'minerva-kb'),
                'icon' => 'fa-university'
            ),
            // FAQ
            'faq' => array(
                'name' => __('FAQ', 'minerva-kb'),
                'icon' => 'fa-question-circle'
            ),
            'faq_edit' => array(
                'name' => __('FAQ - Edit & Publish', 'minerva-kb'),
                'icon' => 'fa-question-circle'
            ),
            'faq_delete' => array(
                'name' => __('FAQ - Delete', 'minerva-kb'),
                'icon' => 'fa-question-circle'
            ),
            'faq_tax' => array(
                'name' => __('FAQ - Taxonomies', 'minerva-kb'),
                'icon' => 'fa-question-circle'
            ),
            // Glossary
            'glossary' => array(
                'name' => __('Glossary', 'minerva-kb'),
                'icon' => 'fa-comment-o'
            ),
            'glossary_edit' => array(
                'name' => __('Glossary - Edit & Publish', 'minerva-kb'),
                'icon' => 'fa-comment-o'
            ),
            'glossary_delete' => array(
                'name' => __('Glossary - Delete', 'minerva-kb'),
                'icon' => 'fa-comment-o'
            ),
            // Tickets
            'tickets' => array(
                'name' => __('Tickets', 'minerva-kb'),
                'icon' => 'fa-life-ring'
            ),
            'tickets_tax' => array(
                'name' => __('Tickets - Taxonomies', 'minerva-kb'),
                'icon' => 'fa-life-ring'
            ),
            'tickets_canned' => array(
                'name' => __('Tickets - Canned Responses', 'minerva-kb'),
                'icon' => 'fa-life-ring'
            ),
        );
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

		wp_enqueue_script( 'minerva-kb/admin-permissions-js', MINERVA_KB_PLUGIN_URL . 'assets/js/minerva-kb-permissions.js', array(
			'jquery',
            'wp-util',
			'minerva-kb/admin-ui-js',
			'minerva-kb/admin-toastr-js',
		), null, true );

		wp_localize_script( 'minerva-kb/admin-permissions-js', 'MinervaPermissions', array(
            'presets' => array(
                'contributor' => MKB_Users::get_default_caps_preset_for_role('contributor'),
                'author' => MKB_Users::get_default_caps_preset_for_role('author'),
                'editor' => MKB_Users::get_default_caps_preset_for_role('editor'),
                'mkb_support_user' => MKB_Users::get_default_caps_preset_for_role('mkb_support_user'),
                'mkb_support_agent' => MKB_Users::get_default_caps_preset_for_role('mkb_support_agent'),
                'mkb_support_manager' => MKB_Users::get_default_caps_preset_for_role('mkb_support_manager'),
            )
        ));
	}
}