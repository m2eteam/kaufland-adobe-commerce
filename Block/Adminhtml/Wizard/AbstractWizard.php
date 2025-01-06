<?php

declare(strict_types=1);

namespace M2E\Kaufland\Block\Adminhtml\Wizard;

use M2E\Kaufland\Block\Adminhtml\Magento\AbstractContainer;

abstract class AbstractWizard extends AbstractContainer
{
    /** @var \M2E\Kaufland\Helper\Data */
    private $dataHelper;
    /** @var \M2E\Kaufland\Helper\Module\Wizard */
    private $wizardHelper;

    public function __construct(
        \M2E\Kaufland\Helper\Data $dataHelper,
        \M2E\Kaufland\Helper\Module\Wizard $wizardHelper,
        \M2E\Kaufland\Block\Adminhtml\Magento\Context\Widget $context,
        array $data = []
    ) {
        $this->dataHelper = $dataHelper;
        $this->wizardHelper = $wizardHelper;
        parent::__construct($context, $data);
    }

    protected function _prepareLayout()
    {
        $this->css->addFile('wizard.css');

        return parent::_prepareLayout();
    }

    protected function _beforeToHtml()
    {
        $this->jsPhp->addConstants(
            \M2E\Kaufland\Helper\Data::getClassConstants(\M2E\Kaufland\Helper\Module\Wizard::class)
        );

        $this->jsUrl->addUrls(
            [
                'setStep' => $this->getUrl('*/wizard_' . $this->getNick() . '/setStep'),
                'setStatus' => $this->getUrl('*/wizard_' . $this->getNick() . '/setStatus'),
            ]
        );

        $this->jsTranslator->addTranslations(
            [
                'Step' => __('Step'),
                'Completed' => __('Completed'),
            ]
        );

        $step = $this->wizardHelper->getStep($this->getNick());
        $steps = \M2E\Core\Helper\Json::encode(
            $this->wizardHelper->getWizard($this->getNick())->getSteps()
        );
        $status = $this->wizardHelper->getStatus($this->getNick());

        $this->js->add(
            <<<JS
    require([
        'Kaufland/Wizard',
    ], function(){
        window.WizardObj = new Wizard('{$status}', '{$step}');
        WizardObj.steps.all = {$steps};
    });
JS
        );

        return parent::_beforeToHtml();
    }
}
