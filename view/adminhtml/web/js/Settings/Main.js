define([
    'Kaufland/Common'
], function () {
    window.KauflandSettingsMain = Class.create(Common, {

        identifier_code_mode_change: function () {
            var self = KauflandSettingsMainObj;

            $('identifier_code_custom_attribute').value = '';
            if (this.value == Kaufland.php.constant('M2E_Kaufland_Helper_Component_Kaufland_Configuration::IDENTIFIER_CODE_MODE_CUSTOM_ATTRIBUTE')) {
                self.updateHiddenValue(this, $('identifier_code_custom_attribute'));
            }
        },
    })
})
