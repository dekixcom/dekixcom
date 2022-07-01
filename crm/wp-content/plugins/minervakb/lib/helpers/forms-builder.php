<?php
/**
 * Project: Minerva KB
 * Copyright: 2015-2016 @KonstruktStudio
 */

class MKB_FormsBuilder {

    const FORMS_SAVED_CONFIG_OPTION_KEY = 'minerva-kb-forms-config';

    /**
     * @param $form
     * @param bool $is_editor
     */
    public static function render_form($form, $is_editor = false) {
        $options = $form['options'];

        $form_classes = isset($options['formClasses']) ? $options['formClasses'] : [];

        if ($is_editor) {
            $form_classes[]= 'js-mkb-form-editable mkb-form--editable';
        }

        $form_classes_attr = sizeof($form_classes) ? ' ' . implode(' ', $form_classes) : '';

        ?><form action=""
                <?php if ($is_editor): ?> id="<?php echo esc_attr($form['id']); ?>"<?php endif; ?>
                class="mkb-form<?php echo esc_attr($form_classes_attr); ?>"<?php if ($is_editor): ?> novalidate<?php endif; ?>
                <?php if ($is_editor): ?> data-form-id="<?php echo esc_attr($form['id']); ?>"<?php endif; ?>>

        <div class="js-mkb-form-messages mkb-form-messages"></div><?php

        if (isset($options['actionField'])):
            ?><input type="hidden" name="action" value="<?php echo esc_attr($options['actionField']); ?>"><?php
        endif;

        // built-in fields
        $referrer_type = isset($_REQUEST['referrer_type']) ? $_REQUEST['referrer_type'] : '';
        $referrer_meta = isset($_REQUEST['referrer_meta']) ? $_REQUEST['referrer_meta'] : '';

        if ($referrer_type): ?>
            <input type="hidden" name="referrer_type" value="<?php echo esc_attr($referrer_type); ?>">
        <?php endif; ?>
        <?php if ($referrer_meta): ?>
            <input type="hidden" name="referrer_meta" value="<?php echo esc_attr($referrer_meta); ?>">
        <?php endif;

        // end of built-in fields

        if (sizeof($form['rows'])) {
            foreach($form['rows'] as $row) {

                ?><div class="mkb-form-row<?php if ($is_editor):?> js-mkb-form-editor-row mkb-form-editor-row<?php endif; ?>"><?php

                switch($row['type']) {
                    case '1col':
                        self::render_cell($row['content'][0], $is_editor);
                        break;

                    case '2col':
                        ?>
                        <span class="mkb-form-columns mkb-form-columns__2col">
                            <span class="mkb-form-column">
                                <?php self::render_cell($row['content'][0], $is_editor, 0); ?>
                            </span>

                            <span class="mkb-form-column">
                                <?php self::render_cell($row['content'][1], $is_editor, 1); ?>
                            </span>
                        </span>
                        <?php
                        break;

                    default:
                        break;
                }

                ?></div><?php

            }
        }

        ?>
        <input type="submit"
               class="js-mkb-form-submit"
               data-progress-label="<?php echo esc_attr($options['submitProgressLabel']); ?>"
               value="<?php echo esc_attr($options['submitLabel']); ?>">

        </form>

        <?php
    }

    /**
     * @param $config
     * @param $is_editor
     * @param $cell_index
     */
    public static function render_cell($config, $is_editor = false, $cell_index = 0) {

        if (!$config && !$is_editor) {
            return;
        }

        if ($is_editor): ?>
            <span class="js-mkb-form-editor-cell mkb-form-editor-cell" data-cell-index="<?php echo esc_attr($cell_index); ?>">
                <?php

                if (!$config): //empty cell ?>
                        <span class="js-mkb-form-editor-item js-mkb-form-editor-insert-item mkb-form-editor-insert-item"><i class="fa fa-plus"></i><?php _e('Insert Field', 'minerva-kb'); ?></span>
                    </span><?php

                    return;
                endif;

                ?>
                <span class="js-mkb-form-editor-item mkb-form-editor-item">
        <?php
        endif;

        // todo: add support for non-field types, currently type === 'field' is assumed

        self::render_field($config, $is_editor);

        if ($is_editor):
            ?>
                </span>
            </span>
            <?php
        endif;
    }

    /**
     * @param $config
     * @param $is_editor
     */
    public static function render_field($config, $is_editor) {
        $value = isset($config['fieldValue']) ? $config['fieldValue'] : '';

        switch($config['fieldType']) {
            case 'text':
                ?>
                <label<?php if ($is_editor): ?> class="js-mkb-field-label"<?php endif; ?> for="<?php echo esc_attr($config['fieldId']); ?>"><?php self::render_label_content($config, $is_editor); ?></label>
                <input type="text"
                       id="<?php echo esc_attr($config['fieldId']); ?>"
                       name="<?php echo esc_attr($config['fieldName']); ?>"
                       value="<?php echo esc_attr($value); ?>"
                    <?php if ($config['fieldRequired']): ?> required<?php endif; ?>>
                <?php
                break;

            case 'email':
                ?>
                <label<?php if ($is_editor): ?> class="js-mkb-field-label"<?php endif; ?> for="<?php echo esc_attr($config['fieldId']); ?>"><?php self::render_label_content($config, $is_editor); ?></label>
                <input type="email"
                       id="<?php echo esc_attr($config['fieldId']); ?>"
                       name="<?php echo esc_attr($config['fieldName']); ?>"
                       value="<?php echo esc_attr($value); ?>"
                    <?php if ($config['fieldRequired']): ?> required<?php endif; ?>>
                <?php
                break;

            case 'password':
                ?>
                <label<?php if ($is_editor): ?> class="js-mkb-field-label"<?php endif; ?> for="<?php echo esc_attr($config['fieldId']); ?>"><?php self::render_label_content($config, $is_editor); ?></label>
                <input type="password"
                       id="<?php echo esc_attr($config['fieldId']); ?>"
                       name="<?php echo esc_attr($config['fieldName']); ?>"
                       value="<?php echo esc_attr($value); ?>"
                    <?php if ($config['fieldRequired']): ?> required<?php endif; ?>>
                <?php
                break;

            case 'hidden':
                if ($is_editor):
                    ?><?php _e('Hidden field:'); ?> <strong class="js-mkb-form-hidden-field-label"><?php esc_html_e($config['fieldName']); ?></strong><?php
                endif;
                ?>
                <input type="hidden" name="<?php echo esc_attr($config['fieldName']); ?>" value="<?php echo esc_attr($value); ?>">
                <?php
                break;

            case 'textarea':
                ?>
                <label<?php if ($is_editor): ?> class="js-mkb-field-label"<?php endif; ?> for="<?php echo esc_attr($config['fieldId']); ?>"><?php self::render_label_content($config, $is_editor); ?></label>
                <textarea name="<?php echo esc_attr($config['fieldName']); ?>" id="<?php echo esc_attr($config['fieldId']); ?>" cols="30" rows="10"<?php if ($config['fieldRequired']): ?> required<?php endif; ?>><?php esc_html_e($value); ?></textarea>
                <?php
                break;

            case 'checkbox':
                ?>
                <input type="checkbox"
                       id="<?php echo esc_attr($config['fieldId']); ?>"
                       name="<?php echo esc_attr($config['fieldName']); ?>"
                    <?php if ($value): ?> checked<?php endif; ?>
                    <?php if ($config['fieldRequired']): ?> required<?php endif; ?>>
                <label<?php if ($is_editor): ?> class="js-mkb-field-label"<?php endif; ?> for="<?php echo esc_attr($config['fieldId']); ?>"><?php self::render_label_content($config, $is_editor); ?></label>
                <?php
                break;

            case 'radio':
                if (sizeof($config['fieldOptions'])) {
                    ?><div class="mkb-field-multiple-label<?php if ($is_editor): ?> js-mkb-field-label<?php endif; ?>">
                        <span class="mkb-field-label-text<?php if ($is_editor): ?> js-mkb-field-label-text<?php endif; ?>"><?php esc_html_e($config['fieldLabel']); ?></span><?php if ($config['fieldRequired']): ?><span class="mkb-field-required-dot">*</span><?php endif; ?>
                    </div>
                    <div class="mkb-field-radio-group<?php if ($is_editor): ?> js-mkb-field-radio-group<?php endif; ?> layout--<?php echo esc_attr($config['fieldOptionsLayout']); ?>"><?php
                        foreach($config['fieldOptions'] as $key => $value):
                            $label = $value;

                            if (!$label) {
                                $label = $key;
                            }

                            ?><div class="mkb-form-radio-option">
                                <label><input type="radio" name="<?php echo esc_attr($config['fieldName']); ?>" value="<?php echo esc_attr($key); ?>"
                                            <?php if ($value === $key): ?> checked<?php endif; ?>
                                    <?php if ($config['fieldRequired']): ?> required<?php endif; ?>> <?php esc_html_e($label); ?></label>
                            </div><?php
                        endforeach;
                    ?></div><?php
                }
                break;

            case 'select':
                if (!sizeof($config['fieldOptions'])) {
                    break;
                }

                ?><label<?php if ($is_editor): ?> class="js-mkb-field-label"<?php endif; ?> for="<?php echo esc_attr($config['fieldId']); ?>"><?php self::render_label_content($config, $is_editor); ?></label>
                <select name="<?php echo esc_attr($config['fieldName']); ?>" id="<?php echo esc_attr($config['fieldId']); ?>"<?php if ($config['fieldRequired']): ?> required<?php endif; ?>>
                <option value=""><?php esc_html_e($config['emptyValueLabel']); ?></option><?php
                    foreach($config['fieldOptions'] as $key => $label):
                        ?><option value="<?php echo esc_attr($key); ?>"<?php if ($value === $key): ?> selected<?php endif; ?>><?php echo esc_attr($label); ?></option><?php
                    endforeach;
                ?></select><?php

                break;

            case 'taxonomySelect':
                $terms = get_terms(array(
                    'taxonomy' => $config['taxonomy'],
                    'hide_empty' => false,
                ));

                if (!$terms || is_wp_error($terms) || !sizeof($terms)) {
                    break; // todo: actual error
                }

                ?><label<?php if ($is_editor): ?> class="js-mkb-field-label"<?php endif; ?> for="<?php echo esc_attr($config['fieldId']); ?>"><?php self::render_label_content($config, $is_editor); ?></label>
                <select name="<?php echo esc_attr($config['fieldName']); ?>" id="<?php echo esc_attr($config['fieldId']); ?>"<?php if ($config['fieldRequired']): ?> required<?php endif; ?>>
                    <option value=""><?php echo esc_html($config['emptyValueLabel']); ?></option><?php
                        foreach($terms as $term):
                            ?><option value="<?php echo esc_attr($term->term_id); ?>"<?php if ((int)$value === $term->term_id): ?> selected<?php endif; ?>><?php echo esc_attr($term->name); ?></option><?php
                        endforeach;
                ?></select><?php

                break;

            case 'editor':
                ?>
                <label<?php if ($is_editor): ?> class="js-mkb-field-label"<?php endif; ?>><?php self::render_label_content($config, $is_editor); ?></label>
                <span class="js-mkb-ticket-message mkb-editor-container mkb-create-ticket-message"></span><?php // todo: generic editor classes

                // TODO: use attachments setting
                if (!$is_editor && MKB_Users::instance()->can_user_attach_files()) {
                    MKB_TemplateHelper::render_ticket_editor_attachments(
                        'mkb_ticket_create_file_upload',
                        'mkb_ticket_create_files'
                    ); // TODO: generic editor, maybe
                }

                break;

            default:
                echo 'Unknown field: ' . $config['fieldName'];
                break;
        }

        if (isset($config['fieldDescription']) && trim($config['fieldDescription'])) {
            ?><div class="mkb-field-description"><?php echo wp_kses_post(trim($config['fieldDescription'])); ?></div><?php
        }
    }

    /**
     * @param $config
     * @param bool $is_editor
     */
    public static function render_label_content($config, $is_editor = false) {
        ?><span class="mkb-field-label-text<?php if ($is_editor): ?> js-mkb-field-label-text<?php endif; ?>"><?php
        echo wp_kses_post($config['fieldLabel']);
        ?></span><?php if ($config['fieldRequired']): ?><span class="mkb-field-required-dot<?php if ($is_editor): ?> js-mkb-field-required-dot<?php endif; ?>">*</span><?php endif;
    }

    /**
     * @param $field_id
     * @param $form_id
     * @return false|string
     */
    public static function get_field_html($field_id, $form_id) {
        $config = self::parse_field_config(array('id' => $field_id), $form_id);

        ob_start();
        self::render_field($config, true);
        return ob_get_clean();
    }

    /**
     * @param $form_id
     * @param $post_id
     */
    public static function save_form_extra_fields($post_id, $form_id) {
        $form = self::get_form_config($form_id);

        $fields_meta = self::get_fields_meta_config();

        if (!$form || !$post_id) {
            return;
        }

        $fields = array();

        if (!sizeof($form['rows'])) {
            return;
        }

        foreach($form['rows'] as $row) {
            foreach($row['content'] as $field) {
                if (!$field || !isset($field['fieldName']) || isset($fields[$field['fieldName']])) {
                    continue;
                }

                $fields[$field['fieldName']] = $field;
            }
        }

        $custom_fields_meta = array();

        // process custom fields
        foreach($fields as $field) {
            if (!array_key_exists($field['id'], $fields_meta['user'])) {
                continue;
            }

            $custom_fields_meta[]= $field['fieldName'];

            $field_name = $field['fieldName'];

            $value = isset($_REQUEST[$field_name]) ? wp_strip_all_tags($_REQUEST[$field_name]) : '';
            // checkboxes, selected vs not, maybe not add value. or always save all values, no matter what

            $field_meta = array(
                'name' => $field['fieldName'],
                'id' => $field['id'],
                'type' => $field['fieldType'],
                'label' => $field['fieldLabel'],
                'value' => $value,
                'required' => (bool)$field['fieldRequired']
            );

            update_post_meta($post_id, '_mkb_custom_field_' . $field_name, json_encode($field_meta, JSON_UNESCAPED_UNICODE));
        }

        if (sizeof($custom_fields_meta)) {
            update_post_meta($post_id, '_mkb_custom_fields', json_encode($custom_fields_meta, JSON_UNESCAPED_UNICODE));
        }

    }

    /**
     * @param $config
     * @param $form_id
     * @return null
     */
    private static function parse_field_config($config, $form_id) {
        $field_id = isset($config['id']) ? $config['id'] : null;

        if (!$field_id) {
            return null;
        }

        $fields_meta = self::get_fields_meta_config();
        $parsed_field_config = null;

        if (isset($fields_meta[$form_id]) && isset($fields_meta[$form_id][$field_id])) {
            // 1. form-specific built-in field
            $parsed_field_config = wp_parse_args($config, $fields_meta[$form_id][$field_id]);
        } else if (isset($fields_meta['system'][$field_id])) {
            // 2. system built-in field
            $parsed_field_config = wp_parse_args($config, $fields_meta['system'][$field_id]);
        } else if (isset($fields_meta['user'][$field_id])) {
            // 3. any other registered field type
            $parsed_field_config = wp_parse_args($config, $fields_meta['user'][$field_id]);
        }

        if (!$parsed_field_config) {
            return null;
        }

        // parse custom non-string types
        if (isset($parsed_field_config['fieldRequired'])) {
            $parsed_field_config['fieldRequired'] = filter_var($parsed_field_config['fieldRequired'], FILTER_VALIDATE_BOOLEAN);
        }

        if (isset($parsed_field_config['fieldType']) && $parsed_field_config['fieldType'] === 'checkbox') {
            $parsed_field_config['fieldValue'] = filter_var($parsed_field_config['fieldValue'], FILTER_VALIDATE_BOOLEAN);
        }
        // TODO: parse any future bool values

        return $parsed_field_config;
    }

    /**
     * @return array
     */
    public static function get_system_forms_config() {
        return array(
            'guestTicketForm' => self::get_guest_create_ticket_form_config(),
            'userTicketForm' => self::get_user_create_ticket_form_config(),
            'loginForm' => self::get_login_form_config(),
            'registerForm' => self::get_register_form_config(),
        );
    }

    /**
     * @return array
     */
    public static function get_guest_create_ticket_form_config() {
        return array(

            'id' => 'guestTicketForm',

            // global form options
            'options' => array(
                'formClasses' => ['mkb-create-ticket-form', 'js-mkb-create-ticket'],
                'actionField' => 'mkb_create_guest_support_ticket',
                'submitLabel' => 'Create Ticket',
                'submitProgressLabel' => 'Creating Ticket...',
                'canInsertFields' => true,
                'requiredFields' => array(
                    'createTicketTitle',
                    'createTicketMessage',
                    'createTicketAcceptTerms'
                )
            ),

            // default rows config
            'rows' => array(
                // 1st row
                array(
                    'type' => '1col',
                    'content' => array(
                        array('id' => 'createTicketTitle')
                    )
                ),
                // 2nd row
                array(
                    'type' => '2col',
                    'content' => array(
                        // left cell
                        array('id' => 'createTicketFirstName'),
                        // right cell
                        array('id' => 'createTicketLastName')
                    )
                ),
                // 3rd row
                array(
                    'type' => '1col',
                    'content' => array(
                        array('id' => 'createTicketEmail')
                    )
                ),
                // 4th row
                array(
                    'type' => '1col',
                    'content' => array(
                        array('id' => 'createTicketType')
                    )
                ),
                // 5th row
                array(
                    'type' => '1col',
                    'content' => array(
                        array('id' => 'createTicketMessage')
                    )
                ),
                // 6th row
                array(
                    'type' => '1col',
                    'content' => array(
                        array('id' => 'createTicketAcceptTerms')
                    )
                )
            )
        );
    }

    /**
     * @return array
     */
    public static function get_user_create_ticket_form_config() {
        return array(

            'id' => 'userTicketForm',

            // global form options
            'options' => array(
                'formClasses' => ['mkb-create-ticket-form', 'js-mkb-create-ticket'],
                'actionField' => 'mkb_create_support_ticket',
                'submitLabel' => 'Create Ticket',
                'submitProgressLabel' => 'Creating Ticket...',
                'canInsertFields' => true,
                'requiredFields' => array(
                    'createTicketTitle',
                    'createTicketMessage'
                )
            ),

            // default rows config
            'rows' => array(
                // 1st row
                array(
                    'type' => '1col',
                    'content' => array(
                        array('id' => 'createTicketTitle')
                    )
                ),
                // 2nd row
                array(
                    'type' => '1col',
                    'content' => array(
                        array('id' => 'createTicketType')
                    )
                ),
                // 3rd row
                array(
                    'type' => '1col',
                    'content' => array(
                        array('id' => 'createTicketMessage')
                    )
                )
            )
        );
    }

    /**
     * @return array
     */
    public static function get_login_form_config() {
        return array(

            'id' => 'loginForm',

            // global form options
            'options' => array(
                'formClasses' => ['mkb-support-account-login-form', 'js-mkb-support-account-login-form'],
                'actionField' => 'mkb_account_login',
                'submitLabel' => 'Sign in',
                'submitProgressLabel' => 'Signing in...',
                'requiredFields' => array(
                    'loginUsername',
                    'loginPassword'
                )
            ),

            // default rows config
            'rows' => array(
                // 1st row
                array(
                    'type' => '2col',
                    'content' => array(
                        array('id' => 'loginUsername'),
                        array('id' => 'loginPassword'),
                    )
                ),
                // 2nd row
                array(
                    'type' => '1col',
                    'content' => array(
                        array('id' => 'loginRememberMe')
                    )
                ),
            )
        );
    }

    /**
     * @return array
     */
    public static function get_register_form_config() {
        return array(

            'id' => 'registerForm',

            // global form options
            'options' => array(
                'formClasses' => ['mkb-create-support-account-form', 'js-mkb-create-support-account-form'],
                'actionField' => 'mkb_create_support_account',
                'submitLabel' => 'Register Account',
                'submitProgressLabel' => 'Registering Account...',
                'requiredFields' => array(
                    'registerFirstname',
                    'registerLastname',
                    'registerEmail',
                    'registerPassword',
                    'registerAcceptTerms',
                )
            ),

            // default rows config
            'rows' => array(
                // 1st row
                array(
                    'type' => '1col',
                    'content' => array(
                        array('id' => 'registerFirstname'),
                    )
                ),
                // 2nd row
                array(
                    'type' => '1col',
                    'content' => array(
                        array('id' => 'registerLastname')
                    )
                ),
                // 3rd row
                array(
                    'type' => '1col',
                    'content' => array(
                        array('id' => 'registerEmail')
                    )
                ),
                // 4th row
                array(
                    'type' => '1col',
                    'content' => array(
                        array('id' => 'registerPassword')
                    )
                ),
                // 5th row
                array(
                    'type' => '1col',
                    'content' => array(
                        array('id' => 'registerAcceptTerms')
                    )
                ),
            )
        );
    }

    /**
     * @return array
     */
    public static function get_fields_meta_config() {
        return array(
            // global built-in fields
            'system' => array(
                'createTicketTitle' => array(
                    'id' => 'createTicketTitle',
                    'type' => 'field',
                    'fieldType' => 'text',
                    'fieldName' => 'title',
                    'fieldId' => 'mkb_ticket_title',
                    'fieldLabel' => 'Ticket Title',
                    'fieldRequired' => true,
                    'editableProps' => ['fieldLabel', 'fieldDescription']
                ),
                'createTicketMessage' => array(
                    'id' => 'createTicketMessage',
                    'type' => 'field',
                    'fieldType' => 'editor',
                    'fieldName' => 'message',
                    'fieldId' => 'mkb_ticket_message',
                    'fieldLabel' => 'Ticket Message',
                    'fieldRequired' => true,
                    'attachments' => true,
                    'editableProps' => ['fieldLabel', 'fieldDescription']
                ),
                'createTicketType' => array(
                    'id' => 'createTicketType',
                    'type' => 'field',
                    'fieldType' => 'taxonomySelect',
                    'taxonomy' => 'mkb_ticket_type',
                    'fieldName' => 'mkb_ticket_type',
                    'fieldId' => 'mkb_ticket_type',
                    'fieldLabel' => 'Ticket Type',
                    'emptyValueLabel' => 'Please, select ticket type',
                    'fieldValue' => '',
                    'fieldRequired' => true,
                    'editableProps' => ['fieldLabel', 'emptyValueLabel', 'fieldValue', 'fieldRequired', 'fieldDescription']
                ),
                'createTicketProduct' => array(
                    'id' => 'createTicketProduct',
                    'type' => 'field',
                    'fieldType' => 'taxonomySelect',
                    'taxonomy' => 'mkb_ticket_product',
                    'fieldName' => 'mkb_ticket_product',
                    'fieldId' => 'mkb_ticket_product',
                    'fieldLabel' => 'Ticket Product',
                    'emptyValueLabel' => 'Please, select ticket product',
                    'fieldValue' => '',
                    'fieldRequired' => true,
                    'editableProps' => ['fieldLabel', 'emptyValueLabel', 'fieldValue', 'fieldRequired', 'fieldDescription']
                ),
                'createTicketDepartment' => array(
                    'id' => 'createTicketDepartment',
                    'type' => 'field',
                    'fieldType' => 'taxonomySelect',
                    'taxonomy' => 'mkb_ticket_department',
                    'fieldName' => 'mkb_ticket_department',
                    'fieldId' => 'mkb_ticket_department',
                    'fieldLabel' => 'Ticket Department',
                    'emptyValueLabel' => 'Please, select ticket department',
                    'fieldValue' => '',
                    'fieldRequired' => true,
                    'editableProps' => ['fieldLabel', 'emptyValueLabel', 'fieldValue', 'fieldRequired', 'fieldDescription']
                ),
                'createTicketPriority' => array(
                    'id' => 'createTicketPriority',
                    'type' => 'field',
                    'fieldType' => 'taxonomySelect',
                    'taxonomy' => 'mkb_ticket_priority',
                    'fieldName' => 'mkb_ticket_priority',
                    'fieldId' => 'mkb_ticket_priority',
                    'fieldLabel' => 'Ticket Priority',
                    'emptyValueLabel' => 'Please, select ticket priority',
                    'fieldValue' => '',
                    'fieldRequired' => true,
                    'editableProps' => ['fieldLabel', 'emptyValueLabel', 'fieldValue', 'fieldRequired', 'fieldDescription']
                ),
            ),
            // form-specific built-in fields
            'guestTicketForm' => array(
                'createTicketFirstName' => array(
                    'id' => 'createTicketFirstName',
                    'type' => 'field',
                    'fieldType' => 'text',
                    'fieldName' => 'firstname',
                    'fieldId' => 'mkb_ticket_firstname',
                    'fieldLabel' => 'First Name',
                    'fieldValue' => '',
                    'fieldRequired' => true,
                    'editableProps' => ['fieldLabel', 'fieldRequired', 'fieldDescription']
                ),
                'createTicketLastName' => array(
                    'id' => 'createTicketLastName',
                    'type' => 'field',
                    'fieldType' => 'text',
                    'fieldName' => 'lastname',
                    'fieldId' => 'mkb_ticket_lastname',
                    'fieldLabel' => 'Last Name',
                    'fieldValue' => '',
                    'fieldRequired' => true,
                    'editableProps' => ['fieldLabel', 'fieldRequired', 'fieldDescription']
                ),
                'createTicketEmail' => array(
                    'id' => 'createTicketEmail',
                    'type' => 'field',
                    'fieldType' => 'email',
                    'fieldName' => 'email',
                    'fieldId' => 'mkb_ticket_email',
                    'fieldLabel' => 'Your Email',
                    'fieldValue' => '',
                    'fieldRequired' => true,
                    'editableProps' => ['fieldLabel', 'fieldRequired', 'fieldDescription']
                ),
                'createTicketAcceptTerms' => array(
                    'id' => 'createTicketAcceptTerms',
                    'type' => 'field',
                    'fieldType' => 'checkbox',
                    'fieldName' => 'privacy_accept',
                    'fieldId' => 'mkb_privacy_accept',
                    'fieldLabel' => 'By creating this ticket, you agree to our <a href="#">Privacy Policy</a> and <a href="#">Terms of use</a>.',
                    'fieldRequired' => true,
                    'fieldValue' => false,
                    'editableProps' => ['fieldLabel']
                )
            ),
            'userTicketForm' => array(),
            'loginForm' => array(
                'loginUsername' => array(
                    'id' => 'loginUsername',
                    'type' => 'field',
                    'fieldType' => 'text',
                    'fieldName' => 'mkb_account_login',
                    'fieldId' => 'mkb_account_login',
                    'fieldLabel' => 'Username (email)',
                    'fieldRequired' => true,
                    'editableProps' => ['fieldLabel', 'fieldDescription']
                ),
                'loginPassword' => array(
                    'id' => 'loginPassword',
                    'type' => 'field',
                    'fieldType' => 'password',
                    'fieldName' => 'mkb_account_password',
                    'fieldId' => 'mkb_account_password',
                    'fieldLabel' => 'Password',
                    'fieldRequired' => true,
                    'editableProps' => ['fieldLabel', 'fieldDescription']
                ),
                'loginRememberMe' => array(
                    'id' => 'loginRememberMe',
                    'type' => 'field',
                    'fieldType' => 'checkbox',
                    'fieldName' => 'mkb_remember_me',
                    'fieldId' => 'mkb-account-remember-me',
                    'fieldLabel' => 'Remember me',
                    'fieldRequired' => false,
                    'fieldValue' => false,
                    'editableProps' => ['fieldLabel']
                )
            ),
            'registerForm' => array(
                'registerFirstname' => array(
                    'id' => 'registerFirstname',
                    'type' => 'field',
                    'fieldType' => 'text',
                    'fieldName' => 'mkb_account_firstname',
                    'fieldId' => 'mkb_account_firstname',
                    'fieldLabel' => 'First name',
                    'fieldRequired' => true,
                    'editableProps' => ['fieldLabel', 'fieldDescription']
                ),
                'registerLastname' => array(
                    'id' => 'registerLastname',
                    'type' => 'field',
                    'fieldType' => 'text',
                    'fieldName' => 'mkb_account_lastname',
                    'fieldId' => 'mkb_account_lastname',
                    'fieldLabel' => 'Last name',
                    'fieldRequired' => true,
                    'editableProps' => ['fieldLabel', 'fieldDescription']
                ),
                'registerEmail' => array(
                    'id' => 'registerEmail',
                    'type' => 'field',
                    'fieldType' => 'email',
                    'fieldName' => 'mkb_account_email',
                    'fieldId' => 'mkb_account_email',
                    'fieldLabel' => 'Your email',
                    'fieldRequired' => true,
                    'editableProps' => ['fieldLabel', 'fieldDescription']
                ),
                'registerPassword' => array(
                    'id' => 'registerPassword',
                    'type' => 'field',
                    'fieldType' => 'password',
                    'fieldName' => 'mkb_account_password',
                    'fieldId' => 'mkb_account_password',
                    'fieldLabel' => 'Your password',
                    'fieldRequired' => true,
                    'editableProps' => ['fieldLabel', 'fieldDescription']
                ),
                'registerAcceptTerms' => array(
                    'id' => 'registerAcceptTerms',
                    'type' => 'field',
                    'fieldType' => 'checkbox',
                    'fieldName' => 'mkb_reg_privacy_accept',
                    'fieldId' => 'mkb_reg_privacy_accept',
                    'fieldLabel' => 'By creating an account, you agree to our <a href="#">Privacy Policy</a> and <a href="#">Terms of use</a>.',
                    'fieldRequired' => true,
                    'fieldValue' => false,
                    'editableProps' => ['fieldLabel']
                )
            ),
            'user' => array(
                'text' => array(
                    'id' => 'text',
                    'type' => 'field',
                    'fieldType' => 'text',
                    'fieldName' => 'text_field',
                    'fieldId' => 'text_field_1',
                    'fieldLabel' => 'Text Field',
                    'fieldValue' => '',
                    'fieldRequired' => false,
                    'editableProps' => ['fieldLabel', 'fieldName', 'fieldId', 'fieldRequired', 'fieldValue', 'fieldDescription']
                ),
                'email' => array(
                    'id' => 'email',
                    'type' => 'field',
                    'fieldType' => 'email',
                    'fieldName' => 'email_field',
                    'fieldId' => 'email_field_1',
                    'fieldLabel' => 'Email Field',
                    'fieldValue' => '',
                    'fieldRequired' => false,
                    'editableProps' => ['fieldLabel', 'fieldName', 'fieldId', 'fieldRequired', 'fieldValue', 'fieldDescription']
                ),
                'password' => array(
                    'id' => 'password',
                    'type' => 'field',
                    'fieldType' => 'password',
                    'fieldName' => 'password_field',
                    'fieldId' => 'password_field_1',
                    'fieldLabel' => 'Password Field',
                    'fieldValue' => '',
                    'fieldRequired' => false,
                    'editableProps' => ['fieldLabel', 'fieldName', 'fieldId', 'fieldRequired', 'fieldValue', 'fieldDescription']
                ),
                'textarea' => array(
                    'id' => 'textarea',
                    'type' => 'field',
                    'fieldType' => 'textarea',
                    'fieldName' => 'textarea_field',
                    'fieldId' => 'textarea_field_1',
                    'fieldLabel' => 'Textarea Field',
                    'fieldValue' => '',
                    'fieldRequired' => false,
                    'editableProps' => ['fieldLabel', 'fieldName', 'fieldId', 'fieldRequired', 'fieldValue', 'fieldDescription']
                ),
                'hidden' => array(
                    'id' => 'hidden',
                    'type' => 'field',
                    'fieldType' => 'hidden',
                    'fieldName' => 'hidden_field',
                    'fieldLabel' => 'Hidden Field',
                    'fieldValue' => '',
                    'fieldRequired' => false,
                    'editableProps' => ['fieldName', 'fieldLabel', 'fieldValue']
                ),
                'checkbox' => array(
                    'id' => 'checkbox',
                    'type' => 'field',
                    'fieldType' => 'checkbox',
                    'fieldName' => 'checkbox_field',
                    'fieldId' => 'checkbox_field_1',
                    'fieldLabel' => 'Checkbox Field',
                    'fieldValue' => false,
                    'fieldRequired' => false,
                    'editableProps' => ['fieldLabel', 'fieldName', 'fieldId', 'fieldRequired', 'fieldValue']
                ),
                'radio' => array(
                    'id' => 'radio',
                    'type' => 'field',
                    'fieldType' => 'radio',
                    'fieldName' => 'radio_field',
                    'fieldId' => 'radio_field_1',
                    'fieldLabel' => 'Radio Field',
                    'fieldOptions' => array(
                        'option_1' => 'Option 1',
                        'option_2' => 'Option 2',
                        'option_3' => 'Option 3',
                    ),
                    'fieldOptionsLayout' => 'inline',
                    'fieldValue' => 'option_1',
                    'fieldRequired' => false,
                    'editableProps' => ['fieldLabel', 'fieldName', 'fieldId', 'fieldRequired', 'fieldOptionsLayout', 'fieldOptions', 'fieldValue', 'fieldDescription']
                ),
                'select' => array(
                    'id' => 'select',
                    'type' => 'field',
                    'fieldType' => 'select',
                    'fieldName' => 'dropdown_field',
                    'fieldId' => 'dropdown_field_1',
                    'fieldLabel' => 'Dropdown Field',
                    'fieldOptions' => array(
                        'option_1' => 'Option 1',
                        'option_2' => 'Option 2',
                        'option_3' => 'Option 3',
                    ),
                    'fieldValue' => 'option_1',
                    'emptyValueLabel' => 'Please, select an option',
                    'fieldRequired' => false,
                    'editableProps' => ['fieldLabel', 'fieldName', 'fieldId', 'fieldRequired', 'emptyValueLabel', 'fieldOptions', 'fieldValue', 'fieldDescription']
                ),
                // TODO: recaptcha
            )
        );
    }

    /**
     * @param $form_id
     * @return mixed|null
     */
    public static function get_form_config($form_id) {
        $all_forms = self::get_system_forms_config(); // form default configs

        if (!isset($all_forms[$form_id])) {
            return null; // unknown error
        }

        $form_config = $all_forms[$form_id]; // default config

        // check user-saved config
        $saved_forms = self::get_forms_saved_config();

        if (isset($saved_forms[$form_id])) {
            $saved_form = $saved_forms[$form_id];

            // copy editable form props
            if (isset($saved_form['options']['submitLabel'])) {
                $form_config['options']['submitLabel'] = $saved_form['options']['submitLabel'];
            }

            if (isset($saved_form['options']['submitProgressLabel'])) {
                $form_config['options']['submitProgressLabel'] = $saved_form['options']['submitProgressLabel'];
            }

            // copy actual form data
            $form_config['rows'] = $saved_form['rows'];
        }

        // extend saved field props with system data
        foreach($form_config['rows'] as $row_index => $row) {
            foreach($row['content'] as $cell_index => $cell) {
                if (!$cell) {
                    continue;
                }

                $form_config['rows'][$row_index]['content'][$cell_index] = self::parse_field_config($cell, $form_config['id']);
            }
        }

        return $form_config;
    }

    /**
     *
     */
    public static function get_forms_saved_config() {
        $saved_forms_config = json_decode(get_option(self::FORMS_SAVED_CONFIG_OPTION_KEY), true);

        return !empty($saved_forms_config) ? stripslashes_deep($saved_forms_config) : array();
    }

    /**
     * @param $form_id
     * @param $form_config
     * @return bool
     */
    public static function save_form_config($form_id, $form_config) {
        $system_forms = self::get_system_forms_config();

        $saved_forms_config = self::get_forms_saved_config();

        if (!isset($system_forms[$form_id])) {
            return false; // some unknown form, remove if we allow custom forms
        }

        $system_form_config = $system_forms[$form_id]['options'];
        $required_fields = $system_form_config['requiredFields'];
        $missing_required_fields = $required_fields; // copy, not ref

        $new_form_config = array(
            'options' => array(
                'submitLabel' => $form_config['options']['submitLabel'],
                'submitProgressLabel' => $form_config['options']['submitProgressLabel']
            ),
            'rows' => array()
        );

        // TODO: check for required fields

        foreach($form_config['rows'] as $row_index => $row) {
            $new_form_config['rows'][] = $row;
        }

        foreach($new_form_config['rows'] as $row_index => $row) {
            foreach($row['content'] as $cell_index => $field) {
                if (!$field) {
                    continue; // empty cell, skip
                }

                $field_id = $field['id'];

                if (!$field_id) {
                    continue;
                }

                if (($key = array_search($field_id, $missing_required_fields)) !== false) {
                    unset($missing_required_fields[$key]);
                }

                $field_config = self::parse_field_config(array('id' => $field['id']), $form_id);
                $saved_field_config = array(
                    'id' => $field['id']
                );

                $editable_props = isset($field_config['editableProps']) ?
                    $field_config['editableProps'] : // system field
                    array('fieldLabel', 'fieldRequired', 'fieldName', 'fieldId'); // generic fields

                foreach($editable_props as $prop) {
                    if (isset($field[$prop])) {
                        $saved_field_config[$prop] = $field[$prop];
                    }
                }

                $new_form_config['rows'][$row_index]['content'][$cell_index] = $saved_field_config;
            }
        }

        $saved_forms_config[$form_id] = $new_form_config;

        update_option(self::FORMS_SAVED_CONFIG_OPTION_KEY,
            json_encode(stripslashes_deep($saved_forms_config), JSON_UNESCAPED_UNICODE)
            , true);

        return true;
    }

    /**
     * @param $form_id
     */
    public static function reset_form_config($form_id) {
        $saved_forms_config = self::get_forms_saved_config();

        unset($saved_forms_config[$form_id]);

        update_option(self::FORMS_SAVED_CONFIG_OPTION_KEY, json_encode($saved_forms_config), true);
    }
}