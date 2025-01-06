<?php

declare(strict_types=1);

namespace M2E\Kaufland\Block\Adminhtml\System\Config\Sections\License;

class Change extends \M2E\Kaufland\Block\Adminhtml\System\Config\Sections
{
    private \M2E\Core\Model\LicenseService $licenseService;

    public function __construct(
        \M2E\Core\Model\LicenseService $licenseService,
        \M2E\Kaufland\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        $this->licenseService = $licenseService;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm()
    {
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'method' => 'post',
                    'action' => 'javascript:void(0)',
                ],
            ]
        );

        $fieldSet = $form->addFieldset('change_license', ['legend' => '', 'collapsable' => false]);

        $key = \M2E\Core\Helper\Data::escapeHtml($this->licenseService->get()->getKey());
        $fieldSet->addField(
            'new_license_key',
            'text',
            [
                'name' => 'new_license_key',
                'label' => __('New License Key'),
                'title' => __('New License Key'),
                'value' => $key,
                'required' => true,
            ]
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
