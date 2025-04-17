<?php

namespace M2E\Kaufland\Block\Adminhtml\Kaufland\Account\Edit\Tabs;

class General extends \M2E\Kaufland\Block\Adminhtml\Magento\Form\AbstractForm
{
    private \M2E\Kaufland\Model\Account $account;

    public function __construct(
        \M2E\Kaufland\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \M2E\Kaufland\Model\Account $account,
        array $data = []
    ) {
        $this->account = $account;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    // ----------------------------------------

    protected function _prepareForm()
    {
        $form = $this->_formFactory->create();

        $content = __(
            'This Page shows the Environment for your %channel_title Account and details of the ' .
            'authorisation for %extension_title to connect to your %channel_title Account.<br/><br/>',
            [
                'channel_title' => \M2E\Kaufland\Helper\Module::getChannelTitle(),
                'extension_title' => \M2E\Kaufland\Helper\Module::getExtensionTitle(),
            ]
        );

        $form->addField(
            'kaufland_accounts_general',
            self::HELP_BLOCK,
            [
                'content' => $content,
            ],
        );

        $fieldset = $form->addFieldset(
            'general',
            [
                'legend' => __('General'),
                'collapsable' => false,
            ],
        );

        $fieldset->addField(
            'title',
            'text',
            [
                'name' => 'title',
                'class' => 'kaufland-account-title',
                'label' => __('Title'),
                'value' => $this->account->getTitle(),
                'tooltip' => __(
                    'Title or Identifier of %channel_title Account for your internal use.',
                    [
                        'channel_title' => \M2E\Kaufland\Helper\Module::getChannelTitle(),
                    ]
                ),
            ],
        );

        $fieldset = $form->addFieldset(
            'access_details',
            [
                'legend' => __('Access Details'),
                'collapsable' => false,
            ],
        );

        $button = $this->getLayout()->createBlock(\M2E\Kaufland\Block\Adminhtml\Magento\Button::class)->addData(
            [
                'label' => __('Update Access Data'),
                'onclick' => 'KauflandAccountObj.openAccessDataPopup(\'' .
                    $this->getUrl(
                        '*/kaufland_account/updateCredentials',
                        ['id' => $this->getRequest()->getParam('id')],
                    ) . '\'
                )',
                'class' => 'check kaufland_check_button primary',
            ],
        );

        $fieldset->addField(
            'update_access_data_container',
            'label',
            [
                'label' => '',
                'after_element_html' => $button->toHtml(),
            ],
        );

        $this->setForm($form);

        $id = $this->getRequest()->getParam('id');
        $this->js->add("Kaufland.formData.id = '$id';");

        $this->js->add(
            <<<JS
    require([
        'Kaufland/Kaufland/Account'
    ], function(){
        window.KauflandAccountObj = new KauflandAccount('{$id}');
        KauflandAccountObj.initObservers();
    });
JS,
        );

        return parent::_prepareForm();
    }
}
