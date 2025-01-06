<?php

declare(strict_types=1);

namespace M2E\Kaufland\Block\Adminhtml\Wizard\InstallationKaufland;

abstract class Installation extends \M2E\Kaufland\Block\Adminhtml\Wizard\Installation
{
    protected function _construct()
    {
        parent::_construct();

        $this->updateButton('continue', 'onclick', 'InstallationWizardObj.continueStep();');
    }

    protected function _toHtml()
    {
        $this->js->add(
            <<<JS
    require([
        'Kaufland/Wizard/InstallationKaufland',
    ], function(){
        window.InstallationWizardObj = new WizardInstallationKaufland();
    });
JS
        );

        return parent::_toHtml();
    }
}
