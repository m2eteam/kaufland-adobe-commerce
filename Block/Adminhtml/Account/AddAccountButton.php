<?php

declare(strict_types=1);

namespace M2E\Kaufland\Block\Adminhtml\Account;

class AddAccountButton implements \Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface
{
    private \Magento\Backend\Model\UrlInterface $urlBuilder;

    public function __construct(
        \Magento\Backend\Model\UrlInterface $urlBuilder
    ) {
        $this->urlBuilder = $urlBuilder;
    }

    public function getButtonData()
    {
        return [
            'label' => __('Add Account'),
            'class' => 'action-primary action-btn',
            'on_click' => '',
            'sort_order' => 4,
            'data_attribute' => [
                'mage-init' => [
                    'Kaufland/Account/AddButton' => [
                        'urlCreate' => $this->urlBuilder->getUrl('*/kaufland_account/create'),
                    ],
                ],
            ],
        ];
    }
}
