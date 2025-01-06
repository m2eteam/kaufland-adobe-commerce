<?php

namespace M2E\Kaufland\Block\Adminhtml\Account;

class Switcher extends \M2E\Kaufland\Block\Adminhtml\Switcher
{
    /** @var string */
    protected $paramName = 'account';
    private \M2E\Kaufland\Model\ResourceModel\Account\CollectionFactory $accountCollectionFactory;

    public function __construct(
        \M2E\Kaufland\Model\ResourceModel\Account\CollectionFactory $accountCollectionFactory,
        \M2E\Kaufland\Block\Adminhtml\Magento\Context\Template $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->accountCollectionFactory = $accountCollectionFactory;
    }

    public function getLabel(): string
    {
        return (string)__('Account');
    }

    protected function loadItems()
    {
        $collection = $this->accountCollectionFactory->create();

        $collection->setOrder('title', 'ASC');

        if (!$collection->getSize()) {
            $this->items = [];

            return;
        }

        if ($collection->getSize() < 2) {
            $this->hasDefaultOption = false;
            $this->setIsDisabled();
        }

        $items = [];

        /** @var \M2E\Kaufland\Model\Account $account */
        foreach ($collection->getItems() as $account) {
            $accountTitle = $this->filterManager->truncate(
                $account->getTitle(),
                ['length' => 15]
            );

            $items['accounts']['value'][] = [
                'value' => $account->getId(),
                'label' => $accountTitle,
            ];
        }

        $this->items = $items;
    }

    private function setIsDisabled(): void
    {
        $this->setData('is_disabled', true);
    }
}
