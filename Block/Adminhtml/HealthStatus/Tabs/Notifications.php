<?php

namespace M2E\Kaufland\Block\Adminhtml\HealthStatus\Tabs;

use M2E\Kaufland\Model\HealthStatus\Notification\Settings;
use M2E\Kaufland\Model\HealthStatus\Task\Result;

class Notifications extends \M2E\Kaufland\Block\Adminhtml\Magento\Form\AbstractForm
{
    /** @var \Magento\Backend\Model\Auth */
    protected $auth;

    /** @var \M2E\Core\Helper\Data */
    private $dataHelper;

    public function __construct(
        \Magento\Backend\Model\Auth $auth,
        \M2E\Kaufland\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \M2E\Core\Helper\Data $dataHelper,
        array $data = []
    ) {
        $this->auth = $auth;
        $this->dataHelper = $dataHelper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    //########################################

    protected function _prepareForm()
    {
        $notificationSettings = $this->modelFactory->getObject('HealthStatus_Notification_Settings');

        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'action' => $this->getUrl('*/*/save'),
                    'method' => 'post',
                ],
            ]
        );

        $form->addField(
            'health_status_notification_help_block',
            self::HELP_BLOCK,
            [
                'content' => $this->__(
                    'You can specify how M2E Kaufland should notify you about Health Status of your M2E Kaufland by selecting:
<ul>
    <li>
        <b>Do Not Notify</b> - no notification required;
    </li>
    <li>
        <b>On each Extension Page (default)</b> - notification will be shown on each page of M2E Kaufland Module;
    </li>
    <li>
        <b>On each Magento Page</b> - notification will be shown on each page of Magento;
    </li>
    <li>
        <b>As Magento System Notification</b> - notification will be shown via Magento global messages system;
    </li>
    <li>
        <b>Send me an eMail</b> - notification will be sent you to the provided email.
    </li>
</ul>
Also, you can select a minimal Notifications Level:
<ul>
    <li>
        <b>Critical/Error (default)</b> - notification will arise only for critical issue and error;
    </li>
    <li>
        <b>Warning</b> - notification will arise once the error or warning occur;
    </li>
    <li>
        <b>Notice</b> - notification will arise in case the error, warning or notice occur.
    </li>
</ul>'
                ),
            ]
        );

        $fieldSet = $form->addFieldset(
            'notification_field_set',
            ['legend' => false, 'collabsable' => false]
        );

        $fieldSet->addField(
            'notification_mode',
            self::SELECT,
            [
                'name' => 'notification_mode',
                'label' => $this->__('Notify Me'),
                'values' => [
                    [
                        'value' => Settings::MODE_DISABLED,
                        'label' => $this->__('Do Not Notify'),
                    ],
                    [
                        'value' => Settings::MODE_EXTENSION_PAGES,
                        'label' => $this->__('On each Extension Page'),
                    ],
                    [
                        'value' => Settings::MODE_MAGENTO_PAGES,
                        'label' => $this->__('On each Magento Page'),
                    ],
                    [
                        'value' => Settings::MODE_MAGENTO_SYSTEM_NOTIFICATION,
                        'label' => $this->__('As Magento System Notification'),
                    ],
                    [
                        'value' => Settings::MODE_EMAIL,
                        'label' => $this->__('Send me an Email'),
                    ],
                ],
                'value' => $notificationSettings->getMode(),
            ]
        );

        $email = $notificationSettings->getEmail();
        empty($email) && $email = $this->auth->getUser()->getEmail();

        $fieldSet->addField(
            'notification_email',
            'text',
            [
                'container_id' => 'notification_email_value_container',
                'name' => 'notification_email',
                'label' => $this->__('Email'),
                'value' => $email,
                'class' => 'Kaufland-validate-email',
                'required' => true,
            ]
        );

        $fieldSet->addField(
            'notification_level',
            self::SELECT,
            [
                'name' => 'notification_level',
                'label' => $this->__('Notification Level'),
                'values' => [
                    [
                        'value' => Result::STATE_CRITICAL,
                        'label' => $this->__('Critical / Error'),
                    ],
                    [
                        'value' => Result::STATE_WARNING,
                        'label' => $this->__('Warning'),
                    ],
                    [
                        'value' => Result::STATE_NOTICE,
                        'label' => $this->__('Notice'),
                    ],
                ],
                'value' => $notificationSettings->getLevel(),
            ]
        );
        //------------------------------------

        $button = $this->getLayout()->createBlock(
            \M2E\Kaufland\Block\Adminhtml\Magento\Button::class,
            '',
            [
                'data' => [
                    'id' => 'submit_button',
                    'label' => $this->__('Save'),
                    'onclick' => 'HealthStatusObj.saveClick()',
                    'class' => 'action-primary',
                ],
            ]
        );

        $fieldSet->addField(
            'submit_button_container',
            self::CUSTOM_CONTAINER,
            [
                'text' => $button->toHtml(),
            ]
        );
        //------------------------------------

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    //########################################

    protected function _beforeToHtml()
    {
        $this->jsUrl->add($this->getUrl('*/*/save'), 'formSubmit');

        $this->jsPhp->addConstants(
            \M2E\Kaufland\Helper\Data::getClassConstants(\M2E\Kaufland\Model\HealthStatus\Notification\Settings::class)
        );

        $this->js->addRequireJs(
            ['hS' => 'Kaufland/HealthStatus'],
            <<<JS

        window.HealthStatusObj = new HealthStatus();
JS
        );

        return parent::_beforeToHtml();
    }

    //########################################
}
