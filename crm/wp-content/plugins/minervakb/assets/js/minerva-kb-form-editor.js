/**
 * Project: Minerva KB
 * Copyright: 2015-2020 @KonstruktStudio
 */
(function($) {

    var GLOBAL_DATA = window.MinervaKB;
    var FORM_EDITOR_DATA = window.MinervaFormEditor;
    var ui = window.MinervaUI;

    var formEditorEmptyCellContentTmpl = wp.template('mkb-form-editor-empty-cell-content');
    var formEditorNewRow1colTmpl = wp.template('mkb-form-editor-new-row-1col');
    var formEditorNewRow2colTmpl = wp.template('mkb-form-editor-new-row-2col');
    var formEditorNewFieldPopupTmpl = wp.template('mkb-form-editor-new-field-popup');

    function setupFormEditors() {
        var $forms = $('.js-mkb-form-editable');

        $forms.each(function(index, form) {
            var $form = $(form);
            var $container = $form.parents('.js-mkb-form-editor-tab-content');
            var $settingsForm = $container.find('.js-mkb-form-settings-form');
            var $rowSettingsForm = $container.find('.js-mkb-row-settings-form');
            var $fieldSettingsForm = $container.find('.js-mkb-field-settings-form');
            var $rows = $form.find('.js-mkb-form-editor-row');
            var rows = [].slice.apply($rows);
            var formId = $form.data('formId');

            var formData = FORM_EDITOR_DATA.forms[formId];

            var activeField = null;
            var activeFieldIndex = null;
            var $activeFieldEl = null;

            var activeRow = null;
            var activeRowIndex = null;
            var $activeRowEl = null;

            setupFormSettingsTabs($container);

            // form preview
            $form.submit(function(e) {
                e.preventDefault();
                e.stopImmediatePropagation();

                return false;
            });

            // settings
            $settingsForm.submit(function(e){
                e.preventDefault();
                e.stopImmediatePropagation();

                ui.fetch({
                    action: 'mkb_save_form_config',
                    formId: formId,
                    formConfig: formData
                }).done(function(response) {
                    if (!response || response.status == 1) {
                        toastr.error('Could not save form data due to errors!');
                    } else {
                        toastr.success('Form data has been saved!');
                    }
                });

                return false;
            });

            // rows reorder
            new Sortable(form, {
                draggable: '.js-mkb-form-editor-row',
                selectedClass: 'mkb-drag-selected',
                animation: 150,
                onUpdate: function(evt) {
                    // update cached rows
                    $rows = $form.find('.js-mkb-form-editor-row');
                    rows = [].slice.apply($rows);

                    // update data
                    updateFormRowsPosition(evt.oldDraggableIndex, evt.newDraggableIndex);
                }
            });

            // form data rows reorder
            function updateFormRowsPosition(from, to) {
                if (from === to) {
                    return;
                }

                formData.rows.splice(to, 0, formData.rows.splice(from, 1)[0]);

                // TODO: remove, debug only
                showFormDebugInfo();
            }

            /**
             * Form data debug info, shows current rows, cells and fields
             */
            function showFormDebugInfo() {
                // console.log('form data updated', formData.rows.map(function(row) {
                //     function getCellLabel(field) {
                //         return field && field.fieldLabel || 'Empty Cell';
                //     }
                //
                //     return row.type === '2col' ?
                //         getCellLabel(row.content[0]) + ' / ' + getCellLabel(row.content[1]):
                //         getCellLabel(row.content[0]);
                // }));
            }

            // field select
            $form.on('click', '.js-mkb-form-editor-item', function(e) {
                var $item = $(e.currentTarget);

                if ($item.hasClass('js-mkb-form-editor-insert-item')) {
                    return;
                }

                e.preventDefault();
                e.stopImmediatePropagation();

                var $cell = $item.parent();
                var $row = $cell.parents('.js-mkb-form-editor-row');
                var rowIndex = rows.indexOf($row[0]);
                var cellIndex = $cell.data('cellIndex');
                var newActiveField = formData.rows[rowIndex].content[cellIndex];

                if (activeField === newActiveField) {
                    unsetActiveField();

                    openFormSettingsTab();

                    return;
                }

                $form.find('.js-mkb-form-editor-item').removeClass('state--selected');
                $item.addClass('state--selected');

                activeField = newActiveField;
                activeFieldIndex = cellIndex;
                $activeFieldEl = $item;

                setActiveFieldForm();
                openFieldSettingsTab();
                unsetActiveRow();

                // console.log('row', rowIndex, 'cell', cellIndex, 'field', activeField);
            });

            function getItemFormLocation($item) {
                var $cell = $item.parent();
                var $row = $cell.parents('.js-mkb-form-editor-row');
                var rowIndex = rows.indexOf($row[0]);
                var cellIndex = $cell.data('cellIndex');

                return [rowIndex, cellIndex];
            }

            /**
             * New cell insert
             */
            var insertNewFieldPopup = new ui.Popup();

            var $insertItem = null;
            var insertItemLocation = null;

            insertNewFieldPopup.bindEvents({
                'click .fn-mkb-popup-close': insertNewFieldPopup.close.bind(insertNewFieldPopup),
                'click .js-mkb-form-editor-insert-field-selector a': function(e) {
                    e.preventDefault();

                    var $link = $(e.currentTarget);
                    var $popupContent = insertNewFieldPopup.$el.find('.js-mkb-form-editor-insert-field-selector');

                    var category = $link.data('category');
                    var fieldId = $link.data('id');

                    var newFieldSrc = null;

                    if (category === 'custom') {
                        newFieldSrc = FORM_EDITOR_DATA.fieldsMeta.user[fieldId];
                    } else if (FORM_EDITOR_DATA.fieldsMeta[formId] && FORM_EDITOR_DATA.fieldsMeta[formId][fieldId]) { // form-specific
                        newFieldSrc = FORM_EDITOR_DATA.fieldsMeta[formId][fieldId];
                    } else if (FORM_EDITOR_DATA.fieldsMeta.system[fieldId]) { // system
                        newFieldSrc = FORM_EDITOR_DATA.fieldsMeta.system[fieldId];
                    }

                    if (!newFieldSrc) {
                        toastr.error('Unknown field error!');
                        return;
                    }

                    $popupContent.addClass('state--loading');

                    ui.fetch({
                        method: 'GET',
                        action: 'mkb_get_form_field_html',
                        formId: formId,
                        fieldId: fieldId  // TODO: actual field
                    }).done(function(response) {
                        if (!response || response.status == 1) {
                            toastr.error('Could not load field template');
                        } else {
                            formData.rows[insertItemLocation[0]].content[insertItemLocation[1]] = clone(newFieldSrc); // TODO: field category

                            $insertItem.html(response.html)
                                .removeClass('js-mkb-form-editor-insert-item mkb-form-editor-insert-item')
                                .addClass('mkb-form-editor-item');

                            insertNewFieldPopup.close();
                        }
                    }).always(function() {
                        $popupContent.removeClass('state--loading');
                    });
                }
            });

            /**
             * Insert item click
             */
            $form.on('click', '.js-mkb-form-editor-insert-item', function(e) {
                e.preventDefault();
                e.stopImmediatePropagation();

                $insertItem = $(e.currentTarget);
                insertItemLocation = getItemFormLocation($insertItem);

                insertNewFieldPopup.render({
                    title: 'Insert Field',
                    content: formEditorNewFieldPopupTmpl({}),
                    footerControlsRight: [],
                    autoHeight: true,
                    extraCSSClass: 'mkb-form-editor-insert-field'
                });
            });

            function clone(thing) {
                return JSON.parse(JSON.stringify(thing));
            }

            // form cells reorder
            $form.find('.js-mkb-form-editor-cell').each(function(index, cell) {
                setCellDragNDrop(cell);
            });

            $container.on('click', '.js-mkb-reset-form', function(e) {
                e.preventDefault();

                if (confirm('Are you sure you want to reset this form to default fields and settings?')) {
                    ui.fetch({
                        action: 'mkb_reset_form_config',
                        formId: formId
                    }).done(function(response) {
                        if (!response || response.status == 1) {
                            toastr.error('Form data was not reset!');
                        } else {
                            toastr.success('Form data has been reset to defaults!');

                            window.location.reload();
                        }
                    });
                }
            });

            /**
             * Field settings form
             */
            var NO_ACTIVE_FIELD_STATE = 'state--no-field';

            var fieldsValidation = {
                id: [
                    {
                        type: 'required',
                        message: 'This property is required'
                    },
                    {
                        type: 'regex',
                        message: 'Please, use only latin characters, numbers, _ and -',
                        regex: /^[a-zA-Z0-9-_]*$/
                    }
                ],
                name: [
                    {
                        type: 'required',
                        message: 'This property is required'
                    },
                    {
                        type: 'regex',
                        message: 'Please, use only latin characters, numbers, _ and -',
                        regex: /^[a-zA-Z0-9-_]*$/
                    }
                ]
            };

            function validateField(name, value, $el) {
                setFieldValid($el);

                if (!fieldsValidation[name]) {
                    return true;
                }

                var fieldValidation = fieldsValidation[name];

                var errors = fieldValidation.reduce(function(allErrors, validation) {
                    switch (validation.type) {
                        case 'required':
                            if (!value) {
                                allErrors.push(validation.message);
                            }
                            break;

                        case 'regex':
                            if (!validation.regex.test(value)) {
                                allErrors.push(validation.message);
                            }
                            break;

                        default:
                            break;
                    }

                    return allErrors;
                }, []);

                if (errors.length) {
                    // console.log('errors', errors);

                    $el.append('<div class="js-mkb-field-validation-errors mkb-field-validation-errors">' +
                        errors.join('<br>')
                    + '</div>');
                }
            }

            function setFieldValid($el) {
                $el.find('.js-mkb-field-validation-errors').remove();
            }

            function setActiveFieldForm() {
                if (!activeField) {
                    unsetActiveFieldForm();

                    return;
                }

                var editableProps = activeField.editableProps || [];

                if (activeField.fieldType === 'checkbox') {
                    editableProps = editableProps.map(function(prop) {
                        return prop === 'fieldValue' ? 'fieldValueBoolean' : prop;
                    });
                }

                $fieldSettingsForm.find('.js-mkb-field-setting').each(function(index, field) {
                    var $field = $(field);

                    $field.toggleClass('mkb-hidden', !editableProps.includes($field.data('fieldProp')));
                });

                // delete field button
                $fieldSettingsForm.find('.js-mkb-delete-field-wrap').toggleClass('mkb-hidden', formData.options.requiredFields.includes(activeField.id));

                $fieldSettingsForm.find('textarea[name="label"]').val(activeField.fieldLabel);
                $fieldSettingsForm.find('textarea[name="description"]').val(activeField.fieldDescription);
                $fieldSettingsForm.find('input[name="id"]').val(activeField.fieldId);
                $fieldSettingsForm.find('input[name="name"]').val(activeField.fieldName);
                $fieldSettingsForm.find('input[name="required"]').prop('checked', activeField.fieldRequired);

                if (activeField.fieldOptionsLayout) {
                    $fieldSettingsForm.find('select[name="optionsLayout"]').val(activeField.fieldOptionsLayout);
                }

                if (activeField.fieldOptions) {
                    $fieldSettingsForm.find('textarea[name="options"]').val(convertOptionsToText(activeField.fieldOptions));
                }

                if (activeField.fieldType === 'select' || activeField.fieldType === 'taxonomySelect') {
                    $fieldSettingsForm.find('input[name="emptyValueLabel"]').val(activeField.emptyValueLabel);
                }

                if (activeField.fieldType === 'checkbox') {
                    $fieldSettingsForm.find('input[name="value"]').prop('checked', activeField.fieldValue);
                } else {
                    $fieldSettingsForm.find('input[name="value"]').val(activeField.fieldValue !== undefined ? activeField.fieldValue : '');
                }

                $fieldSettingsForm.removeClass(NO_ACTIVE_FIELD_STATE);
            }

            function unsetActiveField() {
                $form.find('.js-mkb-form-editor-item').removeClass('state--selected');

                activeField = null;
                activeFieldIndex = null;
                $activeFieldEl = null;

                unsetActiveFieldForm();
            }

            function unsetActiveFieldForm() {
                $fieldSettingsForm.addClass(NO_ACTIVE_FIELD_STATE);
            }

            /**
             * Form settings
             */
            $container.on('input', '.js-mkb-form-setting input[type="text"]', handleFormSettingChange);

            function handleFormSettingChange(e) {
                var formSettingEl = e.currentTarget;
                var $formSetting = $(formSettingEl);
                var $wrap = $formSetting.parents('.js-mkb-form-setting');
                var fieldName = $formSetting.attr('name');

                var isCheckbox = formSettingEl.tagName === 'INPUT' && formSettingEl.type === 'checkbox';
                var value = isCheckbox ? Boolean($formSetting.prop('checked')) : $formSetting.val().trim();

                if (!validateField(fieldName, value, $wrap)) {
                    return;
                }

                switch(fieldName) {
                    case 'submitLabel':
                        formData.options.submitLabel = value;
                        $form.find('input[type="submit"]').val(value);
                        break;

                    case 'submitProgressLabel':
                        formData.options.submitProgressLabel = value;
                        break;

                    default:
                        break;
                }
            }

            /**
             * Field settings
             */
            $container.on('input', '.js-mkb-field-setting input[type="text"], .js-mkb-field-setting textarea', handleFieldSettingChange);
            $container.on('change', '.js-mkb-field-setting input[type="checkbox"]', handleFieldSettingChange);
            $container.on('change', '.js-mkb-field-setting select', handleFieldSettingChange);

            /**
             * Setting change handler
             * @param e
             */
            function handleFieldSettingChange(e) {
                if (!activeField) {
                    return;
                }

                var fieldSettingEl = e.currentTarget;
                var $fieldSetting = $(fieldSettingEl);
                var $wrap = $fieldSetting.parents('.js-mkb-field-setting');
                var fieldName = $fieldSetting.attr('name');

                var isCheckbox = fieldSettingEl.tagName === 'INPUT' && fieldSettingEl.type === 'checkbox';
                var value = isCheckbox ? Boolean($fieldSetting.prop('checked')) : $fieldSetting.val().trim();

                if (!validateField(fieldName, value, $wrap)) {
                    return;
                }

                switch(fieldName) {
                    case 'label':
                        activeField.fieldLabel = value;
                        $activeFieldEl.find('.js-mkb-field-label-text').text(value);
                        break;

                    case 'description':
                        activeField.fieldDescription = value;
                        // $activeFieldEl.find('.js-mkb-field-description-text').text(value);
                        break;

                    case 'name':
                        activeField.fieldName = value;
                        break;

                    case 'id':
                        activeField.fieldId = value;
                        break;

                    case 'required':
                        activeField.fieldRequired = value;

                        if (value) {
                            $activeFieldEl.find('.js-mkb-field-label').append('<span class="mkb-field-required-dot js-mkb-field-required-dot">*</span>');
                        } else {
                            $activeFieldEl.find('.js-mkb-field-required-dot').remove(); // TODO: js- class
                        }
                        break;

                    case 'value':
                        activeField.fieldValue = value;

                        if (isCheckbox) {
                            $activeFieldEl.find('input[type="checkbox"]').prop('checked', value);
                        } else if (activeField.fieldType === 'radio') {
                            $activeFieldEl.find('input[type="radio"]').prop('checked', false);
                            $activeFieldEl.find('input[value="' + value + '"]').prop('checked', true);
                        } else if (activeField.fieldType === 'select' || activeField.fieldType === 'taxonomySelect') { // add taxonomySelect, if we ever make it editable
                            $activeFieldEl.find('option').prop('selected', false);
                            $activeFieldEl.find('option[value="' + value + '"]').prop('selected', true);
                        } else if (['text', 'email'].includes(activeField.fieldType)) {
                            $activeFieldEl.find('input[type="' + activeField.fieldType + '"]').val(value);
                        } else if (activeField.fieldType === 'textarea') {
                            $activeFieldEl.find('textarea').val(value);
                        }
                        break;

                    case 'emptyValueLabel':
                        activeField.emptyValueLabel = value;
                        $activeFieldEl.find('option[value=""]').text(value);
                        break;

                    case 'options':
                        var optionsValue = parseOptionsFromText(value);

                        activeField.fieldOptions = optionsValue;

                        if (activeField.fieldType === 'radio') {
                            var $radioGroup = $activeFieldEl.find('.js-mkb-field-radio-group');
                            var radioHTML = '';

                            for (var key in optionsValue) {
                                if (!optionsValue.hasOwnProperty(key)) { continue; }

                                radioHTML += '<div class="mkb-form-radio-option">' +
                                    '<label><input type="radio" name="' + activeField.fieldName + '" value="' + key + '"' + (key === activeField.fieldValue ? ' checked' : '') + '> ' +
                                        optionsValue[key] +
                                    '</label>' +
                                '</div>';
                            }

                            $radioGroup.html(radioHTML);
                        } else if (activeField.fieldType === 'select') { // add taxonomySelect, if we ever make it editable
                            var $fieldSelect = $activeFieldEl.find('select');
                            var optionsHTML = '<option value="">' + activeField.emptyValueLabel + '</option>';

                            for (var key in optionsValue) {
                                if (!optionsValue.hasOwnProperty(key)) { continue; }

                                optionsHTML += '<option value="' + key + '"' + (key === activeField.fieldValue ? ' selected' : '') + '>' +
                                    optionsValue[key] +
                                '</option>';
                            }

                            $fieldSelect.html(optionsHTML);
                        }

                        // console.log(optionsValue);
                        break;

                    case 'optionsLayout':
                        activeField.fieldOptionsLayout = value;

                        $activeFieldEl.find('.js-mkb-field-radio-group')
                            .removeClass('layout--inline layout--vertical')
                            .addClass('layout--' + value);
                        break;

                    default:
                        break;
                }

                // console.log('field value:', activeField);
            }

            /**
             * gets multiline options text and converts to object
             * @param textValue
             * @returns {{}}
             */
            function parseOptionsFromText(textValue) {
                return textValue.split('\n').reduce(function(options, line) {
                    var splitValue = line.trim().split('|');

                    if (!splitValue.length || !splitValue[0]) {
                        return options;
                    }

                    if (splitValue.length === 1) { // no label
                        options[splitValue[0]] = splitValue[0];
                    } else { // has label
                        options[splitValue[0]] = splitValue[1];
                    }

                    return options;
                }, {})
            }

            /**
             * { k: v } => 'k|v'
             * @param fieldOptions
             */
            function convertOptionsToText(fieldOptions) {
                var optionsText = '';

                for (var key in fieldOptions) {
                    if (!fieldOptions.hasOwnProperty(key)) { continue; }

                    optionsText += (key === fieldOptions[key] ? key : key + '|' + fieldOptions[key]) + '\n';
                }

                return optionsText;
            }

            /**
             * Field delete
             */
            $container.on('click', '.js-mkb-form-delete-field', function(e) {
                e.preventDefault();
                e.stopImmediatePropagation();

                if (!activeField) {
                    return;
                }

                if (formData.options.requiredFields.includes(activeField.id)) {
                    toastr.error('Can\'t delete system required field');
                    return;
                }

                if (confirm('Are you sure you want to delete this field?')) {
                    var $currentRow = $activeFieldEl.parents('.js-mkb-form-editor-row');
                    var currentRowIndex = rows.indexOf($currentRow[0]);

                    formData.rows[currentRowIndex].content[activeFieldIndex] = null;
                    $activeFieldEl.after(formEditorEmptyCellContentTmpl({}));
                    $activeFieldEl.remove();

                    unsetActiveField();
                    unsetActiveFieldForm();
                    openFormSettingsTab();

                    showFormDebugInfo();
                }
            });

            /**
             * Active row
             */
            $form.on('click', '.js-mkb-form-editor-row', function(e) {
                e.preventDefault();
                e.stopImmediatePropagation();

                var $row = $(e.currentTarget);
                var rowIndex = rows.indexOf($row[0]);
                var newActiveRow = formData.rows[rowIndex];

                if (activeRow === newActiveRow) {
                    unsetActiveRow();

                    openFormSettingsTab();

                    return;
                }

                $form.find('.js-mkb-form-editor-row').removeClass('state--selected');
                $row.addClass('state--selected');

                activeRow = newActiveRow;
                activeRowIndex = rowIndex;
                $activeRowEl = $row;

                setActiveRowForm();
                openRowSettingsTab();

                unsetActiveField();

                // console.log('row', rowIndex, 'row', activeRow);
            });

            function unsetActiveRow() {
                $form.find('.js-mkb-form-editor-row').removeClass('state--selected');

                activeRow = null;
                activeRowIndex = null;
                $activeRowEl = null;

                unsetActiveRowForm();
            }

            var NO_ACTIVE_ROW_STATE = 'state--no-row';

            function setActiveRowForm() {
                if (!activeRow) {
                    unsetActiveRowForm();

                    return;
                }

                $rowSettingsForm.removeClass(NO_ACTIVE_ROW_STATE);
            }

            function unsetActiveRowForm() {
                $rowSettingsForm.addClass(NO_ACTIVE_ROW_STATE);
            }

            /**
             * Insert row before / after
             */
            $container.on('click', '.js-mkb-form-row-insert', function(e) {
                e.preventDefault();

                if (!$activeRowEl) {
                    return;
                }

                var $btn = $(e.currentTarget);
                var position = $btn.data('position');
                var layout = $btn.data('layout');
                var rowHTML = layout === '2col' ? formEditorNewRow2colTmpl({}) : formEditorNewRow1colTmpl({});

                $activeRowEl[position](rowHTML);

                var $insertedRow = $activeRowEl[position === 'before' ? 'prev' : 'next']();

                $insertedRow.find('.js-mkb-form-editor-cell').each(function(index, cell) {
                    setCellDragNDrop(cell);
                });

                formData.rows.splice(position === 'before' ? activeRowIndex : activeRowIndex + 1, 0, {
                    type: layout,
                    content: layout === '2col' ? [null, null] : [null]
                });

                // update cached rows
                $rows = $form.find('.js-mkb-form-editor-row');
                rows = [].slice.apply($rows);

                // console.log(formData);
            });

            /**
             * Row delete
             */
            $container.on('click', '.js-mkb-form-delete-row', function(e) {
                e.preventDefault();
                e.stopImmediatePropagation();

                if (!activeRow) {
                    return;
                }

                var hasRequiredFields = activeRow.content.some(function(field) {
                    return field && field.id && formData.options.requiredFields.includes(field.id);
                });

                if (hasRequiredFields) {
                    toastr.error('Cannot delete, selected row contains system required fields');
                    return;
                }

                if (confirm('Are you sure you want to delete this row?')) {
                    $activeRowEl.remove();
                    formData.rows.splice(activeRowIndex, 1);

                    // update cached rows
                    $rows = $form.find('.js-mkb-form-editor-row');
                    rows = [].slice.apply($rows);

                    unsetActiveRow();
                    openFormSettingsTab();

                    showFormDebugInfo();
                }
            });

            /**
             * Tab helpers
             */
            function openFormSettingsTab() {
                $container.find('.js-mkb-form-editor-settings-tabs a[href="#form_' + formId + '_settings"]').click();
            }

            function openRowSettingsTab() {
                $container.find('.js-mkb-form-editor-settings-tabs a[href="#form_' + formId + '_row_settings"]').click();
            }

            function openFieldSettingsTab() {
                $container.find('.js-mkb-form-editor-settings-tabs a[href="#form_' + formId + '_field_settings"]').click();
            }

            /**
             * Drag n drop for fields reorder
             * @param cell
             */
            function setCellDragNDrop(cell) {
                new Sortable(cell, {
                    swap: true,
                    group: formId + 'Cells',
                    draggable: '#' + formId + ' .js-mkb-form-editor-item',
                    animation: 150,
                    swapClass: 'mkb-drop-highlight',
                    onEnd: function(evt) {
                        var fromCell = evt.from;
                        var toCell = evt.to;

                        if (fromCell === toCell) {
                            return;
                        }

                        // from
                        var $fromCell = $(fromCell);
                        var $fromRow = $fromCell.parents('.js-mkb-form-editor-row');
                        var fromRowIndex = rows.indexOf($fromRow[0]);
                        var fromCellIndex = $fromCell.data('cellIndex');

                        // to
                        var $toCell = $(toCell);
                        var toCellIndex = $toCell.data('cellIndex');
                        var $toRow = $toCell.parents('.js-mkb-form-editor-row');
                        var toRowIndex = rows.indexOf($toRow[0]);

                        // fields
                        var fromField = formData.rows[fromRowIndex].content[fromCellIndex];
                        var toField = formData.rows[toRowIndex].content[toCellIndex];

                        formData.rows[fromRowIndex].content[fromCellIndex] = toField;
                        formData.rows[toRowIndex].content[toCellIndex] = fromField;

                        // console.log('cell reorder drop:', 'from', fromRowIndex, fromCellIndex, 'to', toRowIndex, toCellIndex);

                        showFormDebugInfo();
                    }
                });
            }
        });
    }

    /**
     * Form settings tabs
     * @param $container
     */
    function setupFormSettingsTabs($container) {
        var ACTIVE_CLASS = 'state--active';
        var $tabsWrap = $container.find('.js-mkb-form-editor-settings-tabs');
        var $tabLinks = $tabsWrap.find('a');
        var $tabs = $container.find('.js-mkb-form-settings-tab');

        $tabsWrap.on('click', 'a', function(e) {
            var $tabLink = $(e.currentTarget);
            var tabId = $tabLink.attr('href');

            e.preventDefault();

            // links
            $tabLinks.removeClass(ACTIVE_CLASS);
            $tabLink.addClass(ACTIVE_CLASS);

            // content
            $tabs.removeClass(ACTIVE_CLASS);
            $(tabId).addClass(ACTIVE_CLASS);
        });
    }

    /**
     * Form editor page tabs
     */
    function setupFormEditorTabs() {
        var ACTIVE_CLASS = 'state--active';
        var $tabsWrap = $('#mkb-form-editor-tabs');
        var $tabLinks = $('#mkb-form-editor-tabs a');
        var $tabs = $('.js-mkb-form-editor-tab-content');

        $tabsWrap.on('click', 'a', function(e) {
            var $tabLink = $(e.currentTarget);
            var tabId = $tabLink.attr('href');

            e.preventDefault();

            // links
            $tabLinks.removeClass(ACTIVE_CLASS);
            $tabLink.addClass(ACTIVE_CLASS);

            // content
            $tabs.removeClass(ACTIVE_CLASS);
            $(tabId).addClass(ACTIVE_CLASS);
        });
    }

    function init() {
        setupFormEditors();
        setupFormEditorTabs();

        toastr.options.positionClass = "toast-top-right";
        toastr.options.timeOut = 5000;
        toastr.options.showDuration = 200;
    }

    $(document).ready(init);
})(jQuery);