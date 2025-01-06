<?php

declare(strict_types=1);

namespace M2E\Kaufland\Block\Adminhtml\Wizard\InstallationKaufland\Installation\Settings;

use M2E\Kaufland\Block\Adminhtml\Magento\Form\AbstractForm;

class Content extends AbstractForm
{
    public function _construct()
    {
        parent::_construct();
        $this->setId('wizardInstallationSettings');
    }

    protected function _prepareLayout()
    {
        $string = __('In this section, you can provide various Kaufland Marketplace settings, such as Product Identifier configurations, to optimize your marketplace presence.');
        $settings = __('Anytime you can change these settings under');
        $path = __('Kaufland > Configuration > Settings');

        $content = $string . '<br><br>' . $settings . ' <b>' . $path . '</b>';
        $this->getLayout()->getBlock('wizard.help.block')->setContent($content);

        parent::_prepareLayout();
    }

    protected function _prepareForm()
    {
        $settings = $this
            ->getLayout()
            ->createBlock(\M2E\Kaufland\Block\Adminhtml\Settings\Tabs\Main::class);

        $settings->toHtml();
        $form = $settings->getForm();

        $form->setData([
            'id' => 'edit_form',
            'method' => 'post',
        ]);

        $form->setUseContainer(true);
        $this->setForm($form);
    }
}
