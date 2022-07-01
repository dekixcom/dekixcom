<?php
/**
 * Project: MinervaKB.
 * Copyright: 2015-2017 @KonstruktStudio
 */

class MinervaKB_UserEdit {
	/**
	 * Constructor
	 */
	public function __construct($deps) {
		$this->setup_dependencies($deps);

        // fires when the current user is editing their own profile
        add_action('show_user_profile', array($this, 'user_profile_meta'), 0);
        // fires when admin is editing user own profile
        add_action('edit_user_profile', array($this, 'user_profile_meta'), 0);

        // fires when the current user is editing their own profile
        add_action('personal_options_update', array($this, 'save_user_meta'));
        // fires when admin is editing user profile
        add_action('edit_user_profile_update', array($this, 'save_user_meta'));
	}

	/**
	 * Sets up dependencies
	 * @param $deps
	 */
	private function setup_dependencies($deps) {}

	/**
	 * Additional user meta
	 * @param $user
	 */
	public function user_profile_meta( $user ) {
        // TODO: admin / permissions checks
	    ?>

        <h2><?php _e( 'Minerva Support', 'minerva-kb' ); ?></h2>

        <table class="form-table js-mkb-user-settings" role="presentation">
            <tbody>
            <?php

            $settings_helper = new MKB_SettingsBuilder(array(
                'user' => true,
                'no_tabs' => true
            ));

            // for future use
            $options = array(
//                array(
//                    'id' => 'custom_avatar',
//                    'type' => 'media',
//                    'label' => __( 'Custom user avatar', 'minerva-kb' ),
//                    'default' => '',
//                    'description' => __( 'You can use URL or select image from media library', 'minerva-kb' )
//                )
            );

		    foreach ( $options as $option ):

			?>
                <tr class="mkb-user-option">
                    <th>
                        <label for="<?php echo esc_attr($option["id"]); ?>"><?php echo esc_html($option["label"]); ?></label>
                    </th>
                    <td>
                        <?php

                        $saved_value = stripslashes(get_user_option('mkb_option_' . $option["id"], $user->ID));
                        $value = $saved_value || $saved_value === 'false' ? $saved_value : $option['default'];

                        $settings_helper->render_option(
                            $option["type"],
                            $value,
                            $option
                        );

                        ?>

                        <p class="description"><?php echo esc_html($option["description"]); ?></p>
                    </td>
                </tr>
            <?php

            endforeach;

            $user_edit_nonce = wp_create_nonce('mkb_user_edit_nonce');

            if (MKB_Users::is_user_pending_admin_approval($user->ID) && current_user_can('administrator')) {
                ?>
                <p>This account is currently in pending state and user is not able to login and perform any actions. Approve it or deny the registration (this will delete the account).</p>
                <p>
                    <a href="<?php echo admin_url('users.php?mkb_nonce=' . $user_edit_nonce . '&mkb_action=approve-support-user&user_id=' . $user->ID); ?>" class="button button-primary">Approve Account</a>&nbsp;
                    <a href="<?php echo admin_url('users.php?mkb_nonce=' . $user_edit_nonce . '&mkb_action=deny-support-user&user_id=' . $user->ID); ?>" class="button mkb-button-danger">Deny & Delete Account</a></p>
                <br>
                <?php
            }

            // user meta here
            ?>
            </tbody>
        </table>
        <?php
	}

	/**
	 * Saves user meta box fields
	 */
	function save_user_meta( $user_id ) {
		/**
		 * Verify user is indeed user
		 */
        if (!current_user_can( 'edit_user', $user_id)) {
            return;
        }

//        $mkb_some_option = filter_input(INPUT_POST, 'mkb_some_option');
//        $custom_avatar = filter_input(INPUT_POST, 'mkb_option_custom_avatar');

//        if ($mkb_some_option) {
//            update_user_option($user_id, 'mkb_some_option', $mkb_some_option);
//        }

//        update_user_option($user_id, 'mkb_option_custom_avatar', $custom_avatar);
//        update_user_option($user_id, 'mkb_option_custom_avatar', $_POST['mkb_option_custom_avatar']);
	}
}
