/**
 * Project: Minerva KB
 * Copyright: 2015-2020 @KonstruktStudio
 */
(function($) {

    var GLOBAL_DATA = window.MinervaKB;
    var PERMISSIONS_DATA = window.MinervaPermissions;
    var ui = window.MinervaUI;

    function setupPermissions() {
        var $permissionsPage = $('#mkb-permissions');
        var $groups = $permissionsPage.find('.js-mkb-caps-edit-group');
        var $caps = $permissionsPage.find('.js-mkb-cap-value');
        var $roleSelect = $permissionsPage.find('select[name="mkb_edited_role"]');
        var currentRole = $roleSelect.val();
        var isEdited = false;
        var isMassUpdate = false;
        var isUnfoldAll = false;

        // group unfold
        $permissionsPage.on('click', '.js-mkb-caps-edit-group h3', function(e) {
            var $heading = $(e.currentTarget);
            var $group = $heading.parents('.js-mkb-caps-edit-group');

            $group.toggleClass('state--open');
        });

        // unfold all
        $permissionsPage.on('click', '.js-mkb-caps-unfold-all', function(e) {
            e.preventDefault();

            isUnfoldAll = !isUnfoldAll;

            $(e.currentTarget).toggleClass('state--open');
            $groups.toggleClass('state--open', isUnfoldAll);
        });

        // group toggles
        $permissionsPage.on('change', '.js-mkb-caps-edit-group-toggle', function(e) {
            var $toggle = $(e.currentTarget);
            var isChecked = $toggle.prop('checked');
            var $group = $toggle.parents('.js-mkb-caps-edit-group');

            isMassUpdate = true;

            $group.find('.js-mkb-cap-value').prop('checked', isChecked);

            updateGroupCount($group);

            isEdited = true;
            isMassUpdate = false;
        });

        // cap change
        $permissionsPage.on('change', '.js-mkb-cap-value', function(e) {
            if (isMassUpdate) {
                return;
            }

            var $cap = $(e.currentTarget);
            var $group = $cap.parents('.js-mkb-caps-edit-group');

            updateGroupCount($group);

            isEdited = true;
        });

        // grant all
        $permissionsPage.on('click', '.js-mkb-caps-grant-all', function(e) {
            e.preventDefault();

            isMassUpdate = true;

            $permissionsPage.find('.js-mkb-cap-value').prop('checked', true);
            $groups.each(function(i, group) { updateGroupCount($(group)); });

            isEdited = true;
            isMassUpdate = false;
        });

        // deny all
        $permissionsPage.on('click', '.js-mkb-caps-deny-all', function(e) {
            e.preventDefault();

            isMassUpdate = true;

            $permissionsPage.find('.js-mkb-cap-value').prop('checked', false);
            $groups.each(function(i, group) { updateGroupCount($(group)); });

            isEdited = true;
            isMassUpdate = false;
        });

        // presets
        $permissionsPage.on('click', '.js-mkb-load-default-caps-preset', function(e) {
            e.preventDefault();

            var role = e.currentTarget.dataset.role;
            var preset = PERMISSIONS_DATA.presets[role];

            if (!preset) {
                toastr.error('Preset not found for role!');

                return;
            }

            if (!confirm('Change all capabilities to defaults?')) {
                return;
            }

            isMassUpdate = true;

            $caps.each(function(i, el) {
                el.checked = preset.includes(el.getAttribute('name'));
            });

            $groups.each(function(i, group) { updateGroupCount($(group)); });

            isEdited = true;
            isMassUpdate = false;
        });

        function updateGroupCount($group) {
            $group.find('.js-mkb-granted-caps-count').html($group.find('.js-mkb-cap-value:checked').length);
        }

        // role select
        $permissionsPage.on('change', 'select[name="mkb_edited_role"]', function(e) {
            var $roleSelect = $(e.currentTarget);
            var newRole = $roleSelect.val();

            if (newRole === currentRole) {
                return;
            }

            if (isEdited && !confirm('Load role settings? You will lose any unsaved changes.')) {
                // todo: reset select, in timeout maybe
                e.preventDefault();

                setTimeout(function() {
                    $roleSelect.find('option[value="' + currentRole + '"]').prop('selected', true);
                });

                return;
            }

            var params = new URLSearchParams(window.location.search);

            if (params.has('edited_role')) {
                params.delete('edited_role');
            }

            params.append('edited_role', $roleSelect.val());

            window.location.href = window.location.href.replace(window.location.search, '?' + params.toString());
        });

        // submit
        $permissionsPage.on('submit', '.js-mkb-update-permissions-form', function(e) {
            e.preventDefault();

            var $form = $(e.currentTarget);
            var caps = Array.prototype.reduce.call($form.find('.js-mkb-cap-value'), function(all, input) {
                var $input = $(input);
                var name = input.getAttribute('name');

                all[name] = $input.prop('checked');

                return all;
            }, {});
            var $btn = $form.find('button[type="submit"]');

            if ($btn.hasClass('mkb-disabled')) {
                return;
            }

            var originalBtnLabel = $btn.html();

            $btn.attr('disabled', 'disabled');
            $btn.text('Please wait, updating...');

            ui.fetch({
                action: 'mkb_update_permissions',
                role: $form.find('[name="mkb_edited_role"]').val(),
                caps: caps
            }).always(function(response) {
                if (response.status == 1) {
                    // error
                    $btn.text(originalBtnLabel).attr('disabled', false);
                    ui.handleErrors(response);
                } else {
                    // success
                    toastr.success('Permissions updated, reloading page');

                    window.location.reload();
                }

            }).fail(function() {
                toastr.error('Some error happened, try to refresh page');
            });
        });
    }

    function init() {
        setupPermissions();

        toastr.options.positionClass = "toast-top-right";
        toastr.options.timeOut = 5000;
        toastr.options.showDuration = 200;
    }

    $(document).ready(init);
})(jQuery);