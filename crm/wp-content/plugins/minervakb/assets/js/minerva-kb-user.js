/**
 * Project: Minerva KB
 * Copyright: 2015-2016 @KonstruktStudio
 */
(function($) {
    'use strict';

    var GLOBAL_DATA = window.MinervaKB;
    var ui = window.MinervaUI;

    var $userSettingsContainer = $('.js-mkb-user-settings');

    function init() {
        if (!$userSettingsContainer.length) {
            return;
        }

        // ui.setupColorPickers($userSettingsContainer);
        // ui.setupIconSelect($userSettingsContainer);
        // ui.setupImageSelect($userSettingsContainer);
        // ui.setupPageSelect($userSettingsContainer);
        // ui.setupRolesSelector($userSettingsContainer);
        ui.setupMediaUpload($userSettingsContainer);
    }

    $(document).ready(init);
})(jQuery);