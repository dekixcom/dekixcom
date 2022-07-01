<?php

/**
 * Dashboard page controller
 * Class MinervaKB_DashboardPage
 */

class MinervaKB_FormEditorPage {

	private $SCREEN_BASE = null;

	public function __construct() {

		$this->SCREEN_BASE = MKB_Options::option('article_cpt') . '_page_minerva-kb-submenu-form-editor';

        add_action( 'admin_menu', array( $this, 'add_submenu' ) );

        if (!isset($_REQUEST['page']) || $_REQUEST['page'] !== 'minerva-kb-submenu-form-editor' || !isset($_REQUEST['post_type']) || $_REQUEST['post_type'] !== MKB_Options::option('article_cpt')) {
            return;
        }

		add_action( 'admin_enqueue_scripts', array( $this, 'load_assets' ) );
        add_action( 'admin_footer', array($this, 'editor_tmpl'), 30 );
	}

	/**
	 * Sets up dependencies
	 * @param $deps
	 */
	private function setup_dependencies($deps) {

	}

	/**
	 * Adds dashboard submenu page
	 */
	public function add_submenu() {
		add_submenu_page(
			'edit.php?post_type=' . MKB_Options::option('article_cpt'),
			__( 'Form Editor', 'minerva-kb' ),
			__( 'Form Editor', 'minerva-kb' ),
			'manage_options', // admins only
			'minerva-kb-submenu-form-editor',
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

		?>
		<div class="mkb-admin-page-header">
			<span class="mkb-header-logo mkb-header-item" data-version="v<?php echo esc_attr(MINERVA_KB_VERSION); ?>">
				<img class="logo-img" src="<?php echo esc_attr( MINERVA_KB_IMG_URL . 'logo.png' ); ?>" title="logo"/>
			</span>
			<span class="mkb-header-title mkb-header-item"><?php _e( 'Form Editor', 'minerva-kb' ); ?></span>
			<?php MinervaKB_AutoUpdate::registered_label(); ?>
		</div>

        <ul id="mkb-form-editor-tabs">
            <li><a href="#tab_guest_ticket_form" class="state--active"><?php _e('Create Ticket Guest', 'minerva-kb'); ?></a></li>
            <li><a href="#tab_user_ticket_form"><?php _e('Create Ticket User', 'minerva-kb'); ?></a></li>
            <li><a href="#tab_login_form"><?php _e('Login', 'minerva-kb'); ?></a></li>
            <li><a href="#tab_register_form"><?php _e('Register', 'minerva-kb'); ?></a></li>
        </ul>

		<div id="mkb-form-editor">
            <div id="tab_guest_ticket_form" class="mkb-form-editor-tab-content js-mkb-form-editor-tab-content state--active">
                <h2><?php _e('Guest Create Ticket Form', 'minerva-kb'); ?></h2>
                <?php

                self::render_form_editor(
                    MKB_FormsBuilder::get_form_config('guestTicketForm')
                );

                ?>
            </div>

            <div id="tab_user_ticket_form" class="mkb-form-editor-tab-content js-mkb-form-editor-tab-content">
                <h2><?php _e('User Create Ticket Form', 'minerva-kb'); ?></h2>
                <?php

                self::render_form_editor(
                    MKB_FormsBuilder::get_form_config('userTicketForm')
                );

                ?>
            </div>

            <div id="tab_login_form" class="mkb-form-editor-tab-content js-mkb-form-editor-tab-content">
                <h2><?php _e('Login Form', 'minerva-kb'); ?></h2>
                <?php

                self::render_form_editor(
                    MKB_FormsBuilder::get_form_config('loginForm')
                );

                ?>
            </div>

            <div id="tab_register_form" class="mkb-form-editor-tab-content js-mkb-form-editor-tab-content">
                <h2><?php _e('Register Form', 'minerva-kb'); ?></h2>
                <?php

                self::render_form_editor(
                    MKB_FormsBuilder::get_form_config('registerForm')
                );

                ?>
            </div>
		</div>
	<?php
	}

    /**
     * @param $form
     */
	public function render_form_editor($form) {
	    ?>
        <div class="mkb-form-editor-container">
            <div class="mkb-form-editor-form-container">
                <?php MKB_FormsBuilder::render_form($form, true); ?>
            </div>

            <div class="mkb-form-editor-settings-container">
                <ul class="mkb-form-editor-settings-tabs js-mkb-form-editor-settings-tabs">
                    <li><a href="#form_<?php esc_attr_e($form['id']); ?>_settings" class="state--active"><?php _e('Form', 'minerva-kb'); ?></a></li>
                    <li><a href="#form_<?php esc_attr_e($form['id']); ?>_row_settings""><?php _e('Row', 'minerva-kb'); ?></a></li>
                    <li><a href="#form_<?php esc_attr_e($form['id']); ?>_field_settings""><?php _e('Field', 'minerva-kb'); ?></a></li>
                </ul>

                <div id="form_<?php esc_attr_e($form['id']); ?>_settings" class="js-mkb-form-settings-tab mkb-form-settings-tab state--active">
                    <h3><?php _e('Form Settings', 'minerva-kb'); ?></h3>
                    <form action="" class="js-mkb-form-settings-form mkb-form" novalidate>
                        <p class="js-mkb-form-setting">
                            <label><?php _e('Form Submit Label', 'minerva-kb'); ?></label><br>
                            <input type="text" name="submitLabel" value="<?php esc_attr_e($form['options']['submitLabel']); ?>">
                        </p>

                        <p class="js-mkb-form-setting">
                            <label><?php _e('Form Submit Progress Label', 'minerva-kb'); ?></label><br>
                            <input type="text" name="submitProgressLabel" value="<?php esc_attr_e($form['options']['submitProgressLabel']); ?>">
                        </p>

                        <p>
                            <input type="submit" class="button button-primary js-mkb-save-form" value="<?php _e('Save Form', 'minerva-kb'); ?>">
                            <input type="button" class="button js-mkb-reset-form" value="<?php _e('Reset Form to Defaults', 'minerva-kb'); ?>">
                        </p>
                    </form>
                </div>

                <div id="form_<?php esc_attr_e($form['id']); ?>_row_settings" class="js-mkb-form-settings-tab mkb-form-settings-tab">
                    <h3><?php _e('Row Settings', 'minerva-kb'); ?></h3>
                    <form action="" class="js-mkb-row-settings-form mkb-row-settings-form mkb-form state--no-row" novalidate>
                        <?php if (isset($form['options']['canInsertFields']) && $form['options']['canInsertFields']): ?>
                            <h4><?php _e('Insert Row Before', 'minerva-kb'); ?></h4>
                            <p>
                                <button class="button js-mkb-form-row-insert" data-position="before" data-layout="1col"><?php _e('1 column', 'minerva-kb'); ?></button>
                                <button class="button js-mkb-form-row-insert" data-position="before" data-layout="2col"><?php _e('2 columns', 'minerva-kb'); ?></button>
                            </p>

                            <h4><?php _e('Insert Row After', 'minerva-kb'); ?></h4>
                            <p>
                                <button class="button js-mkb-form-row-insert" data-position="after" data-layout="1col"><?php _e('1 column', 'minerva-kb'); ?></button>
                                <button class="button js-mkb-form-row-insert" data-position="after" data-layout="2col"><?php _e('2 columns', 'minerva-kb'); ?></button>
                            </p>
                        <?php endif; ?>

                        <h4><?php _e('Delete', 'minerva-kb'); ?></h4>
                        <p>
                            <button class="button mkb-button-danger js-mkb-form-delete-row"><?php _e('Delete Row', 'minerva-kb'); ?></button>
                        </p>

                        <p class="mkb-no-row-message"><?php _e('No row is currently selected', 'minerva-kb'); ?></p>
                    </form>
                </div>

                <div id="form_<?php esc_attr_e($form['id']); ?>_field_settings" class="js-mkb-form-settings-tab mkb-form-settings-tab">
                    <h3><?php _e('Field Settings', 'minerva-kb'); ?></h3>
                    <form action="" class="js-mkb-field-settings-form mkb-field-settings-form mkb-form state--no-field" novalidate>
                        <p class="js-mkb-field-setting mkb-field-setting" data-field-prop="fieldLabel">
                            <label><?php _e('Field Label', 'minerva-kb'); ?></label><br>
                            <textarea name="label" rows="3"></textarea>
                        </p>

                        <p class="js-mkb-field-setting mkb-field-setting" data-field-prop="fieldDescription">
                            <label><?php _e('Field Description (optional, will display after field)', 'minerva-kb'); ?></label><br>
                            <textarea name="description" rows="3"></textarea>
                        </p>

                        <p class="js-mkb-field-setting mkb-field-setting" data-field-prop="fieldName">
                            <label><?php _e('Field Name', 'minerva-kb'); ?></label><br>
                            <input type="text" name="name" value="">
                        </p>

                        <p class="js-mkb-field-setting mkb-field-setting" data-field-prop="fieldId">
                            <label><?php _e('Field ID', 'minerva-kb'); ?></label><br>
                            <input type="text" name="id" value="">
                        </p>

                        <p class="js-mkb-field-setting mkb-field-setting" data-field-prop="fieldValue">
                            <label><?php _e('Field Value', 'minerva-kb'); ?></label><br>
                            <input type="text" name="value" value="">
                        </p>

                        <p class="js-mkb-field-setting mkb-field-setting" data-field-prop="fieldOptions">
                            <label><?php _e('Field Options', 'minerva-kb'); ?></label><br>
                            <textarea name="options" rows="7">
option_1|Option 1
option_2|Option 2
option_3|Option 3</textarea>
                            <span><?php _e('Use key/value pairs, separated by <strong>|</strong>', 'minerva-kb'); ?></span>
                        </p>

                        <p class="js-mkb-field-setting mkb-field-setting" data-field-prop="emptyValueLabel">
                            <label><?php _e('Empty Value Label', 'minerva-kb'); ?></label><br>
                            <input type="text" name="emptyValueLabel" value="">
                        </p>

                        <p class="js-mkb-field-setting mkb-field-setting" data-field-prop="fieldOptionsLayout">
                            <label><?php _e('Options Layout', 'minerva-kb'); ?></label><br>
                            <select name="optionsLayout">
                                <option value="inline"><?php _e('Inline', 'minerva-kb'); ?></option>
                                <option value="vertical"><?php _e('Vertical', 'minerva-kb'); ?></option>
                            </select>
                        </p>

                        <p class="js-mkb-field-setting mkb-field-setting" data-field-prop="fieldValueBoolean">
                            <label><input type="checkbox" name="value"> <?php _e('Default value', 'minerva-kb'); ?></label>
                        </p>

                        <p class="js-mkb-field-setting mkb-field-setting" data-field-prop="fieldRequired">
                            <label><input type="checkbox" name="required"> <?php _e('Required field?', 'minerva-kb'); ?></label>
                        </p>

                        <p class="js-mkb-delete-field-wrap mkb-delete-field-wrap">
                            <button class="button mkb-button-danger js-mkb-form-delete-field"><?php _e('Delete Field', 'minerva-kb'); ?></button>
                        </p>

                        <p class="mkb-no-field-message"><?php _e('No field is currently selected', 'minerva-kb'); ?></p>
                    </form>
                </div>
            </div>
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

		// sortable
		wp_enqueue_script( 'minerva-kb/admin-sortable-js', MINERVA_KB_PLUGIN_URL . 'assets/js/vendor/Sortable.js', array(), '1.10.2', true );

		wp_enqueue_script( 'minerva-kb/admin-form-editor-js', MINERVA_KB_PLUGIN_URL . 'assets/js/minerva-kb-form-editor.js', array(
			'jquery',
            'wp-util',
            'minerva-kb/admin-sortable-js',
			'minerva-kb/admin-ui-js',
			'minerva-kb/admin-toastr-js',
		), null, true );

		wp_localize_script( 'minerva-kb/admin-form-editor-js', 'MinervaFormEditor', array(
            'forms' => array(
                'guestTicketForm' => MKB_FormsBuilder::get_form_config('guestTicketForm'),
                'userTicketForm' => MKB_FormsBuilder::get_form_config('userTicketForm'),
                'loginForm' => MKB_FormsBuilder::get_form_config('loginForm'),
                'registerForm' => MKB_FormsBuilder::get_form_config('registerForm'),
            ),
            'fieldsMeta' => MKB_FormsBuilder::get_fields_meta_config()
        ));
	}

    /**
     * Templates
     */
    public function editor_tmpl() {
        // form editor empty cell content
        ?>
        <script type="text/html" id="tmpl-mkb-form-editor-empty-cell-content">
            <span class="js-mkb-form-editor-item js-mkb-form-editor-insert-item mkb-form-editor-insert-item">
                <i class="fa fa-plus"></i><?php _e('Insert Field', 'minerva-kb'); ?>
            </span>
        </script>
        <?php

        // form editor new row 1col
        ?>
        <script type="text/html" id="tmpl-mkb-form-editor-new-row-1col">
            <div class="mkb-form-row js-mkb-form-editor-row mkb-form-editor-row">
                <span class="js-mkb-form-editor-cell mkb-form-editor-cell" data-cell-index="0">
                    <span class="js-mkb-form-editor-item js-mkb-form-editor-insert-item mkb-form-editor-insert-item"><i class="fa fa-plus"></i><?php _e('Insert Field', 'minerva-kb'); ?></span>
                </span>
            </div>
        </script>
        <?php

        // form editor new row 2col
        ?>
        <script type="text/html" id="tmpl-mkb-form-editor-new-row-2col">
            <div class="mkb-form-row js-mkb-form-editor-row mkb-form-editor-row">
                <span class="mkb-form-columns mkb-form-columns__2col">
                    <span class="mkb-form-column">
                        <span class="js-mkb-form-editor-cell mkb-form-editor-cell" data-cell-index="0">
                            <span class="js-mkb-form-editor-item js-mkb-form-editor-insert-item mkb-form-editor-insert-item"><i class="fa fa-plus"></i><?php _e('Insert Field', 'minerva-kb'); ?></span>
                        </span>
                    </span>
                    <span class="mkb-form-column">
                        <span class="js-mkb-form-editor-cell mkb-form-editor-cell" data-cell-index="1">
                            <span class="js-mkb-form-editor-item js-mkb-form-editor-insert-item mkb-form-editor-insert-item"><i class="fa fa-plus"></i><?php _e('Insert Field', 'minerva-kb'); ?></span>
                        </span>
                    </span>
                </span>
            </div>
        </script>
        <?php

        // insert field popup
        ?>
        <script type="text/html" id="tmpl-mkb-form-editor-new-field-popup">
            <div class="js-mkb-form-editor-insert-field-selector mkb-form-editor-insert-field-selector">
                <h3><?php _e('System Fields', 'minerva-kb'); ?></h3>
                <ul>
                    <li><a href="#" data-category="system" data-id="createTicketType"><?php _e('Ticket Type', 'minerva-kb'); ?></a></li>
                    <li><a href="#" data-category="system" data-id="createTicketDepartment"><?php _e('Ticket Department', 'minerva-kb'); ?></a></li>
                    <li><a href="#" data-category="system" data-id="createTicketProduct"><?php _e('Ticket Product', 'minerva-kb'); ?></a></li>
                    <li><a href="#" data-category="system" data-id="createTicketPriority"><?php _e('Ticket Priority', 'minerva-kb'); ?></a></li>
                </ul>

                <h3><?php _e('Custom Fields', 'minerva-kb'); ?></h3>
                <ul>
                    <li><a href="#" data-category="custom" data-id="text"><i class="fa fa-font"></i><?php _e('Text', 'minerva-kb'); ?></a></li>
                    <li><a href="#" data-category="custom" data-id="textarea"><i class="fa fa-align-left"></i><?php _e('Textarea', 'minerva-kb'); ?></a></li>
                    <li><a href="#" data-category="custom" data-id="email"><i class="fa fa-envelope-o"></i><?php _e('Email', 'minerva-kb'); ?></a></li>
                    <li><a href="#" data-category="custom" data-id="hidden"><i class="fa fa-asterisk"></i><?php _e('Hidden', 'minerva-kb'); ?></a></li>
                    <li><a href="#" data-category="custom" data-id="checkbox"><i class="fa fa-check-square-o"></i><?php _e('Checkbox', 'minerva-kb'); ?></a></li>
                    <li><a href="#" data-category="custom" data-id="radio"><i class="fa fa-dot-circle-o"></i><?php _e('Radio', 'minerva-kb'); ?></a></li>
                    <li><a href="#" data-category="custom" data-id="select"><i class="fa fa-reorder"></i><?php _e('Dropdown', 'minerva-kb'); ?></a></li>
                </ul>
            </div>
        </script>
        <?php
    }
}