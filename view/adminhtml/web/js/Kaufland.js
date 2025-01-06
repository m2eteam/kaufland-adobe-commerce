define([
    'jquery',
    'Kaufland/Url',
    'Kaufland/Php',
    'Kaufland/Translator',
    'Kaufland/Common',
    'prototype',
    'Kaufland/Plugin/BlockNotice',
    'Kaufland/Plugin/Prototype/Event.Simulate',
    'Kaufland/Plugin/Fieldset',
    'M2ECore/Plugin/Validator',
    'Kaufland/General/PhpFunctions',
    'mage/loader_old'
], function (jQuery, Url, Php, Translator) {

    jQuery('body').loader();

    Ajax.Responders.register({
        onException: function (event, error) {
            console.error(error);
        }
    });

    return {
        url: Url,
        php: Php,
        translator: Translator
    };

});
