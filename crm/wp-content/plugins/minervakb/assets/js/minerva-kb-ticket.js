/**
 * Project: Minerva KB
 * Copyright: 2015-2016 @KonstruktStudio
 */
(function($) {
    'use strict';

    var GLOBAL_DATA = window.MinervaKB;
    var ARTICLE_DATA = GLOBAL_DATA.articleEdit;
    var ui = window.MinervaUI;
    var settings = GLOBAL_DATA.settings;
    var i18n = GLOBAL_DATA.i18n;

    function initReplyForm($container) {
        var $submitForm = $('form#post');
        var isSubmitting = false;
        var $reopenBtn = $('.js-mkb-ticket-reopen-btn');
        var isClosed = Boolean($reopenBtn.length);
        var replyEditorInsertFAQPopupTmpl = wp.template('mkb-ticket-reply-editor-insert-faq');
        var replyEditorInsertFAQPopupActionsTmpl = wp.template('mkb-ticket-reply-editor-insert-faq-actions');
        var replyEditorInsertKBPopupTmpl = wp.template('mkb-ticket-reply-editor-insert-kb');
        var replyEditorInsertKBPopupActionsTmpl = wp.template('mkb-ticket-reply-editor-insert-kb-actions');
        var replyEditorInsertCannedResponsePopupTmpl = wp.template('mkb-ticket-reply-editor-insert-canned-response');
        var replyEditorInsertCannedResponsePopupActionsTmpl = wp.template('mkb-ticket-reply-editor-insert-canned-response-actions');
        // save as
        var replySaveAsPopupTmpl = wp.template('mkb-ticket-reply-save-as');
        var replySaveAsPopupActionsTmpl = wp.template('mkb-ticket-reply-save-as-actions');

        /**
         * Must reopen to perform any actions
         */
        if (isClosed) {
            $reopenBtn.on('click', function(e) {
                e.preventDefault();
                e.stopImmediatePropagation();

                $submitForm.submit();
            });

            return;
        }

        /**
         * Editor
         */
        var isAttachmentsOpen = false;

        // insert
        var $insertFAQ = $('.js-mkb-reply-editor-insert-faq');
        var $insertKB = $('.js-mkb-reply-editor-insert-kb');
        var $insertCannedResponse = $('.js-mkb-reply-editor-insert-canned-response');
        // save as
        var $saveAsFAQ = $('.js-mkb-ticket-reply-save-faq');
        var $saveAsKB = $('.js-mkb-ticket-reply-save-kb');
        var $saveAsCannedResponse = $('.js-mkb-ticket-reply-save-canned-response');
        var $uploadButton = $('.js-mkb-reply-editor-upload');

        var $attachmentsSection = $('.js-mkb-ticket-admin-attachments-section');

        /**
         * Insert FAQ
         */
        var replyEditorInsertFAQPopup = new ui.Popup();

        replyEditorInsertFAQPopup.bindEvents({
            'click .fn-mkb-popup-close': replyEditorInsertFAQPopup.close.bind(replyEditorInsertFAQPopup),
            'click .js-mkb-ticket-reply-editor-insert-faq-link': function() {
                var $select = replyEditorInsertFAQPopup.$el.find('.js-mkb-ticket-reply-editor-insert-faq-select');
                var $option = $select.find('option:selected');

                if ($option.length) {
                    var replyEditor = tinyMCE.get('mkb_reply_editor');

                    var linkHTML = '<a href="' + $option.data('link') + '">' + $option.text().trim() + '</a>';
                    var insertHTML = settings['tickets_insert_faq_html'].replace('{{FAQ_LINK}}', linkHTML);

                    replyEditor.setContent(replyEditor.getContent() + insertHTML);
                }

                replyEditorInsertFAQPopup.close();
            },
            'click .js-mkb-ticket-reply-editor-insert-faq-content': function(e) {
                var faqId = replyEditorInsertFAQPopup.$el.find('.js-mkb-ticket-reply-editor-insert-faq-select').val();
                var $select = replyEditorInsertFAQPopup.$el.find('.js-mkb-ticket-reply-editor-insert-faq-select');
                var $option = $select.find('option:selected');

                // TODO: insert content as shortcode maybe

                // var $btn = $(e.currentTarget);

                var replyEditor = tinyMCE.get('mkb_reply_editor');
                replyEditor.setContent(replyEditor.getContent() +
                    '<p>Looks like we have this answered in FAQ, please check:</p>' + '[mkb-faq-content id="' + faqId + '" title="' + $option.text().trim() + '"]');

                replyEditorInsertFAQPopup.close();
            }
        });

        $insertFAQ.on('click', function() {
            replyEditorInsertFAQPopup.render({
                title: 'Insert FAQ',
                content: replyEditorInsertFAQPopupTmpl({}),
                footerControlsRight: [
                    replyEditorInsertFAQPopupActionsTmpl({})
                ],
                autoHeight: true,
                extraCSSClass: 'mkb-ticket-reply-editor-insert-faq'
            });
        });

        /**
         * Insert KB
         */
        var replyEditorInsertKBPopup = new ui.Popup();

        replyEditorInsertKBPopup.bindEvents({
            'click .fn-mkb-popup-close': replyEditorInsertKBPopup.close.bind(replyEditorInsertKBPopup),
            'click .js-mkb-ticket-reply-editor-insert-kb-link': function() {
                var $select = replyEditorInsertKBPopup.$el.find('.js-mkb-ticket-reply-editor-insert-kb-select');
                var $option = $select.find('option:selected');

                if ($option.length) {
                    var replyEditor = tinyMCE.get('mkb_reply_editor');

                    var linkHTML = '<a href="' + $option.data('link') + '">' + $option.text().trim() + '</a>';
                    var insertHTML = settings['tickets_insert_kb_html'].replace('{{KB_LINK}}', linkHTML);

                    replyEditor.setContent(replyEditor.getContent() + insertHTML);
                }

                replyEditorInsertKBPopup.close();
            }
        });

        $insertKB.on('click', function() {
            replyEditorInsertKBPopup.render({
                title: 'Insert KB Article',
                content: replyEditorInsertKBPopupTmpl({}),
                footerControlsRight: [
                    replyEditorInsertKBPopupActionsTmpl({})
                ],
                autoHeight: true,
                extraCSSClass: 'mkb-ticket-reply-editor-insert-kb'
            });
        });

        /**
         * Insert Canned Response
         */
        var replyEditorInsertCannedResponsePopup = new ui.Popup();

        replyEditorInsertCannedResponsePopup.bindEvents({
            'click .fn-mkb-popup-close': replyEditorInsertCannedResponsePopup.close.bind(replyEditorInsertCannedResponsePopup),
            'click .js-mkb-ticket-reply-editor-insert-canned-response': function() {
                var $select = replyEditorInsertCannedResponsePopup.$el.find('.js-mkb-ticket-reply-editor-insert-canned-response-select');
                var $option = $select.find('option:selected');

                if ($option.length) {
                    var replyEditor = tinyMCE.get('mkb_reply_editor');

                    replyEditor.setContent(replyEditor.getContent() + window.MinervaKB.ticketEdit.cannedResponses[$select.val()].post_content);
                }

                replyEditorInsertCannedResponsePopup.close();
            }
        });

        $insertCannedResponse.on('click', function() {
            replyEditorInsertCannedResponsePopup.render({
                title: 'Insert Canned Response',
                content: replyEditorInsertCannedResponsePopupTmpl({}),
                footerControlsRight: [
                    replyEditorInsertCannedResponsePopupActionsTmpl({})
                ],
                autoHeight: true,
                extraCSSClass: 'mkb-ticket-reply-editor-insert-canned-response'
            });
        });

        /**
         * Save as FAQ/KB/Canned Response
         */
        var replySaveAsPopup = new ui.Popup();
        var saveAsReplyId = null;
        var saveAsReplyTarget = 'canned';
        var saveReplyInProgress = false;

        replySaveAsPopup.bindEvents({
            'click .fn-mkb-popup-close': replySaveAsPopup.close.bind(replySaveAsPopup),
            'click .js-mkb-ticket-reply-save-as': function(e) {
                e.preventDefault();

                if (saveReplyInProgress) {
                    return;
                }

                var btn = e.currentTarget;
                var $btn = $(btn);

                replySaveAsPopup.$el.find('.js-mkb-ticket-reply-save-as-title').val();
                saveReplyInProgress = true;
                $btn.addClass('mkb-disabled');

                ui.fetch({
                    method: 'POST',
                    action: 'mkb_save_reply_as',
                    id: saveAsReplyId,
                    title: replySaveAsPopup.$el.find('.js-mkb-ticket-reply-save-as-title').val().trim() || 'Saved from ticket reply #' + saveAsReplyId,
                    target: saveAsReplyTarget
                }).then(function(response) {
                    replySaveAsPopup.$el.find('.js-mkb-reply-save-as-response').html('<a href="' + response.url + '" target="_blank">Open post edit in new tab</a>');
                    $btn.html($btn.data('labelSuccess'));
                }).always(function() {
                    saveReplyInProgress = false;
                });
            }
        });

        // save as FAQ
        $saveAsFAQ.on('click', getSaveAsClickHandler('faq'));
        $saveAsKB.on('click', getSaveAsClickHandler('kb'));
        $saveAsCannedResponse.on('click', getSaveAsClickHandler('canned'));

        // TODO: i18n
        var popupTitles = {
            canned: 'Save Reply as Canned Response',
            faq: 'Save Reply as FAQ',
            kb: 'Save Reply as KB Article'
        };

        function getSaveAsClickHandler(target) {
            return function(e) {
                e.preventDefault();

                var link = e.currentTarget;
                var $meta = $(link).parents('.js-mkb-ticket-reply-meta');

                saveAsReplyId = $meta.data('replyId');
                saveAsReplyTarget = target;

                replySaveAsPopup.render({
                    title: popupTitles[target],
                    content: replySaveAsPopupTmpl({}),
                    footerControlsRight: [
                        replySaveAsPopupActionsTmpl({})
                    ],
                    autoHeight: true,
                    extraCSSClass: 'mkb-ticket-reply-save-as-popup'
                });
            };
        }

        /**
         * Attachments
         */
        $uploadButton.on('click', function() {
            $uploadButton.toggleClass('state--on');

            $attachmentsSection[isAttachmentsOpen ? 'slideUp' : 'slideDown'](250);

            isAttachmentsOpen = !isAttachmentsOpen;

            // $attachmentsSection.toggleClass('state--visible', isAttachmentsOpen);
        });

        /**
         * Submit
         */
        var $submitBtn = $('.js-mkb-ticket-submit-btn');
        var $submitBtnDropdownToggle = $submitBtn.find('.js-mkb-btn-dropdown-toggle');
        var $submitBtnDropdown = $submitBtn.find('.js-mkb-btn-dropdown');
        var $submitStatus = $submitForm.find('.js-mkb-ticket-status-store');
        var $stayOnPage = $('[name="mkb_ticket_stay_on_ticket"]');
        var submitToggleOn = false;

        $stayOnPage.on('change', function() {
            Cookies.set('mkb_ticket_stay_on_edit', $stayOnPage.prop('checked') ? 1 : 0);
        });

        $submitBtnDropdownToggle.on('click', function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            $submitBtn.toggleClass('state--dropdown-open');
        });

        $submitBtnDropdown.on('click', function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            // just in case if there are paddings later
        });

        $submitBtnDropdown.on('click', '.mkb-btn-dropdown-option', function(e) {
            var option = e.currentTarget;
            var $option = $(option);
            var value = option.dataset.option;

            e.preventDefault();
            e.stopImmediatePropagation();

            $submitBtnDropdown.find('.mkb-btn-dropdown-option').removeClass('mkb-btn-dropdown-option--checked');
            $option.addClass('mkb-btn-dropdown-option--checked');
            $submitBtn.find('.js-mkb-ticket-submit-btn-text').html($option.html());
            $submitBtn.removeClass('state--dropdown-open');

            $submitStatus.val(value);
        });

        /**
         * Ticket page submit
         */
        $submitBtn.on('click', function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            $submitForm.submit();
        });
    }


    /**
     * File uploader
     */
    function setupAttachmentsUpload() {
        var dropArea = document.getElementById('drop-area');

        if (!dropArea) {
            return;
        }

        var $preview = $('.js-mkb-attachments-upload-preview');
        var $clearFiles = $('.js-mkb-admin-ticket-attachments-clear');
        var $fileStore = $(".js-mkb-ticket-reply-file-store");
        var $dropErrors = $(".js-mkb-ticket-attachments-drop-errors");
        var allowedTypes = $fileStore.attr('accept') || '';

        allowedTypes = allowedTypes.split(',').map(function(type) {
            return type.trim().replace('.', '');
        });

        // Prevent default drag behaviors
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(function(eventName) {
            dropArea.addEventListener(eventName, preventDefaults, false);
            // TODO: change for current el (maybe)
            document.body.addEventListener(eventName, preventDefaults, false);
        });

        // Highlight drop area when item is dragged over it
        ['dragenter', 'dragover'].forEach(function(eventName) {
            dropArea.addEventListener(eventName, highlight, false)
        });

        ['dragleave', 'drop'].forEach(function(eventName) {
            dropArea.addEventListener(eventName, unhighlight, false)
        });

        function preventDefaults (e) {
            e.preventDefault();
            e.stopPropagation();
        }

        // Handle dropped files
        dropArea.addEventListener('drop', handleDrop, false);

        function highlight(e) {
            dropArea.classList.add('mkb-drop-highlight')
        }

        function unhighlight(e) {
            dropArea.classList.remove('active')
        }

        class _DataTransfer {
            constructor() {
                return new ClipboardEvent("").clipboardData || new DataTransfer();
            }
        }

        function handleDrop(e) {
            // TODO: merge all files, check across browsers
            var oldFilesArr = [].map.call($fileStore.prop('files'), function(item) { return item; });
            var oldFileNames = oldFilesArr.map(function(item) { return item.name; });
            var newFilesArr = [].map.call(e.dataTransfer.files, function(item) { return item; });
            var hasNotAllowedFiles = false;
            var dropErrors = [];

            console.log('dropped', newFilesArr);

            newFilesArr = newFilesArr.filter(function(item) {
                var extension = item.name.split('.').pop().toLowerCase();
                var isAllowed = allowedTypes.includes(extension);

                if (!isAllowed) {
                    hasNotAllowedFiles = true;
                }

                return isAllowed && !oldFileNames.includes(item.name);
            });
            var allFilesArr = oldFilesArr.concat(newFilesArr);
            var allFiles = new _DataTransfer();

            allFilesArr.forEach(function(file) {
                allFiles.items.add(file);
            });

            if (allFiles.files.length) {
                $fileStore.prop('files', allFiles.files);
            }

            if (hasNotAllowedFiles) {
                dropErrors.push('Some of the dropped files are not allowed!');
            }

            if (dropErrors.length) {
                $dropErrors.html(
                    dropErrors.reduce(function(html, error) {
                        return html + '<div>' + error + '</div>'
                    }, '')
                );
            } else {
                $dropErrors.html('')
            }

            handleFiles(allFiles.files)
        }

        function humanFileSize(bytes, si) {
            var thresh = si ? 1000 : 1024;

            if (Math.abs(bytes) < thresh) {
                return bytes + 'B';
            }

            var units = si
                ? ['kB','MB','GB','TB','PB','EB','ZB','YB']
                : ['KiB','MiB','GiB','TiB','PiB','EiB','ZiB','YiB'];
            var u = -1;

            do {
                bytes /= thresh;
                ++u;
            } while(Math.abs(bytes) >= thresh && u < units.length - 1);

            return bytes.toFixed(1) + '' + units[u];
        }

        function handleFiles(files) {
            dropArea.classList.add('mkb-drop-uploading');

            files = [...files];

            $preview.html('');
            files.forEach(previewFile);

            // TODO: normalize this
            $('form#post').attr('enctype', 'multipart/form-data');
        }

        // TODO: remove
        window.handleFiles = handleFiles;

        // TODO: only one file is being uploaded

        function previewFile(file) {
            var isImage = /^image\//.test(file.type);
            var $item = $(
                '<div class="js-mkb-attachment-upload-preview-item mkb-attachment-upload-preview-item">' +
                    '<a href="#" class="js-mkb-attachment-preview-remove mkb-attachment-preview-remove fa fa-times-circle"></a>' +
                '</div>');

            if (isImage) {
                var reader = new FileReader();
                reader.readAsDataURL(file);

                reader.onloadend = function() {
                    var img = document.createElement('img');
                    img.src = reader.result;
                    $item.append(img);
                };

                $item.addClass('type--image');
            } else {
                // non-image files
                $item.append('<span>' + file.name + ' (' + humanFileSize(file.size, true) + ')' + '</span>');
                $item.addClass('type--file');
            }

            $preview.append($item);
        }

        function getElementIndex(node) {
            var index = 0;

            while ((node = node.previousElementSibling)) {
                index++;
            }

            return index;
        }

        function removeFileAtIndex(indexToRemove) {
            // TODO: merge all files, check across browsers
            var filesArr = [].map.call($fileStore.prop('files'), function(item) { return item; });
            var allFiles = new _DataTransfer();
            var i = 0;

            for (let file of filesArr) {
                if (i++ === indexToRemove) {
                    continue;
                }

                allFiles.items.add(file);
            }

            if (allFiles.files.length) {
                $fileStore.prop('files', allFiles.files);
            }

            // console.log('before drop', $fileStore.prop('files'));
            // $fileStore.prop('files', newFiles);
            console.log('dropped', files);
            //console.log('after drop', $fileStore.prop('files'));

            handleFiles(allFiles.files)
        }

        $preview.on('click', '.js-mkb-attachment-preview-remove', function(e) {
            e.preventDefault();

            var remove = e.currentTarget;
            var item = remove.parentNode;
            var indexToRemove = getElementIndex(item);

            removeFileAtIndex(indexToRemove);
        });

        $clearFiles.on('click', function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            $fileStore.val(null); // if causes issues across browser, replace with clone
            handleFiles([]);
        });
    }

    function setupTicketElapsedTimeTickers() {
        // TODO: test this or read
        moment.locale($('html').attr('lang'));

        $('.js-mkb-human-readable-time').each(function(index, item) {
            var $item = $(item);
            var timestamp = item.dataset.timestamp * 1000;
            var ONCE_A_MINUTE = 1000 * 60;

            $item.html(moment.utc(timestamp).fromNow());

            setInterval(function() {
                $item.html(moment(timestamp).fromNow());
            }, ONCE_A_MINUTE);
        });
    }

    function setupElapsedTimeTicker($item) {
        var timestamp = $item.data('timestamp') * 1000;
        var ONCE_A_MINUTE = 1000 * 60;

        $item.html(moment.utc(timestamp).fromNow());

        setInterval(function() {
            $item.html(moment(timestamp).fromNow());
        }, ONCE_A_MINUTE);
    }

    function setupRepliesViewSettings() {
        var $repliesContainer = $('.js-mkb-admin-ticket-replies');

        var $viewSettingsContainer = $('.mkb-admin-replies-view-settings');
        var $agentToggle = $('.js-mkb-view-hide-agent-replies');
        var $customerToggle = $('.js-mkb-view-hide-customer-replies');
        var $deletedToggle = $('.js-mkb-view-hide-deleted-replies');
        var $historyToggle = $('.js-mkb-view-hide-history-entries');

        function updateIconState() {
            var isDataHidden = Boolean($agentToggle.attr('checked')) ||
                Boolean($customerToggle.attr('checked')) ||
                Boolean($historyToggle.attr('checked'));

            $viewSettingsContainer.toggleClass('state--data-hidden', isDataHidden);
        }

        // agent
        $agentToggle.on('change', function(e) {
            $repliesContainer.toggleClass('state--agent-replies-hidden');

            updateIconState();
        });

        // customer
        $customerToggle.on('change', function(e) {
            $repliesContainer.toggleClass('state--customer-replies-hidden');

            updateIconState();
        });

        // history
        $historyToggle.on('change', function(e) {
            $repliesContainer.toggleClass('state--history-hidden');

            updateIconState();
        });

        // deleted
        $deletedToggle.on('change', function(e) {
            $repliesContainer.toggleClass('state--deleted-hidden');
        });

        var $heightCheckbox = $('.js-mkb-view-limit-replies-height');
        var heightSetting = ui.storage.get('mkbTicketViewLimitRepliesListHeight') || false;

        $heightCheckbox.on('change', function(e) {
            $repliesContainer.toggleClass('state--limit-height');

            ui.storage.set('mkbTicketViewLimitRepliesListHeight', e.currentTarget.checked ? true : '');
        });

        if (heightSetting) {
            $heightCheckbox.attr('checked', true).trigger('change');
        }
    }

    /**
     * Reply actions
     */
    function setupReplyActions() {
        var isActionPending = false;
        var $container = $('.js-mkb-admin-ticket-replies');

        // will be init after edit click
        var $replyContentHolder;
        var $reply;
        var replyEditEditor; // TinyMCE not ready yet
        var replyId;

        // edit popup
        var replyEditPopup = new ui.Popup({
            $el: $('.js-mkb-ticket-reply-edit-popup')
        });

        // moved to top level to avoid multiple popup binds
        replyEditPopup.bindEvents({
            'click .fn-mkb-popup-close': replyEditPopup.close.bind(replyEditPopup),
            'click .js-mkb-ticket-reply-edit-save': handleReplyEditSave
        });

        function handleReplyEditSave(e) {
            var $btn = $(e.currentTarget);

            e.preventDefault();
            // TODO:

            if ($btn.hasClass('mkb-disabled')) {
                return;
            }

            var editedReplyHTML = replyEditEditor.getContent().trim();
            replyEditEditor.setProgressState(true);
            $btn.addClass('mkb-disabled');

            ui.fetch({
                method: 'POST',
                action: 'mkb_edit_ticket_reply',
                reply_id: replyId,
                edited_reply: editedReplyHTML
            }).then(function() {
                $reply.addClass('mkb-ticket-reply--status-edited');
                $replyContentHolder.html(editedReplyHTML);

                var $metaDate = $reply.find('.js-mkb-ticket-reply-meta-date');
                var $metaEdited = $metaDate.find('.js-mkb-ticket-reply-meta-edited');
                var now = new Date();

                if (!$metaEdited.length) {
                    // first edit
                    $metaDate.append('<span class="mkb-ticket-reply__meta--edited js-mkb-ticket-reply-meta-edited"> - ' +
                        'Edited by: ' + GLOBAL_DATA.user.data.display_name +
                        ' <span class="js-mkb-human-readable-time mkb-human-readable-time" data-timestamp="' + now.getTime() / 1000 + '" title="' + now.toLocaleString() + '"></span>'
                    );

                    setupElapsedTimeTicker($metaDate.find('.js-mkb-ticket-reply-meta-edited .js-mkb-human-readable-time'));
                } else {
                    // TODO: update timestamp + momentjs
                }

                replyEditPopup.close();
            }).always(function() {
                $btn.removeClass('mkb-disabled');
            });
        }

        /**
         * Edit
         */
        $container.on('click', '.js-mkb-ticket-reply-edit', function(e) {
            e.preventDefault();

            if (isActionPending) {
                return;
            }

            var $link = $(e.currentTarget);
            var $metaWrap = $link.parents('.js-mkb-ticket-reply-meta');

            $reply = $metaWrap.parents('.js-mkb-ticket-reply');
            $replyContentHolder = $reply.find('.js-mkb-reply-content-holder');
            replyId = $metaWrap.data('replyId');

            isActionPending = true;

            replyEditEditor = tinyMCE.get('mkb_single_reply_edit');

            replyEditEditor.setContent('Loading reply...');
            replyEditEditor.setProgressState(true);
            replyEditEditor.setMode('readonly');

            replyEditPopup.render();

            ui.fetch({
                method: 'GET',
                action: 'mkb_get_ticket_reply_for_edit',
                reply_id: replyId
            }).then(function(response) {
                replyEditEditor.setContent(response.html);
                replyEditEditor.setMode('design');
                replyEditEditor.setProgressState(false);
            }).always(function() {
                isActionPending = false;
            });
        });

        /**
         * Delete
         */
        $container.on('click', '.js-mkb-ticket-reply-remove', function(e) {
            e.preventDefault();

            if (isActionPending) {
                return;
            }

            if (confirm('Are you sure you want to delete this reply?')) {
                // remove
                var $link = $(e.currentTarget);
                var $metaWrap = $link.parents('.js-mkb-ticket-reply-meta');
                var $reply = $metaWrap.parents('.js-mkb-ticket-reply');
                var replyId = $metaWrap.data('replyId');

                isActionPending = true;

                $reply.addClass('mkb-ticket-reply--request-pending');

                ui.fetch({
                    method: 'POST',
                    action: 'mkb_delete_ticket_reply',
                    reply_id: replyId
                }).then(function(response) {
                    $reply.addClass('mkb-ticket-reply--status-trash');
                }).always(function() {
                    isActionPending = false;
                    $reply.removeClass('mkb-ticket-reply--request-pending');
                });
            }
        });

        /**
         * Restore
         */
        $container.on('click', '.js-mkb-ticket-reply-restore', function(e) {
            e.preventDefault();

            if (isActionPending) {
                return;
            }

            if (confirm('Are you sure you want to restore this reply?')) {
                // remove
                var $link = $(e.currentTarget);
                var $metaWrap = $link.parents('.js-mkb-ticket-reply-meta');
                var $reply = $metaWrap.parents('.js-mkb-ticket-reply');
                var replyId = $metaWrap.data('replyId');

                isActionPending = true;
                $reply.addClass('mkb-ticket-reply--request-pending');

                ui.fetch({
                    method: 'POST',
                    action: 'mkb_restore_ticket_reply',
                    reply_id: replyId
                }).then(function(response) {
                    $reply.removeClass('mkb-ticket-reply--status-trash');
                }).always(function() {
                    isActionPending = false;
                    $reply.removeClass('mkb-ticket-reply--request-pending');
                });
            }
        });

        /**
         * View history
         */
        $container.on('click', '.js-mkb-ticket-reply-history', function(e) {
            // TODO:
            e.preventDefault();
        });
    }

    function init() {
        var $repliesContainer = $('#mkb-ticket-meta-replies');
        var $submitContainer = $('#mkb-ticket-meta-update-id');

        setupAttachmentsUpload();
        setupTicketElapsedTimeTickers();
        setupRepliesViewSettings();
        setupReplyActions();

        initReplyForm($repliesContainer);
        ui.setupNiceSelect($submitContainer);
    }

    $(document).ready(init);
})(jQuery);