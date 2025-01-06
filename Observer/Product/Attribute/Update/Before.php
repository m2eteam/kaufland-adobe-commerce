<?php

namespace M2E\Kaufland\Observer\Product\Attribute\Update;

class Before extends \M2E\Kaufland\Observer\AbstractObserver
{
    /** @var \Magento\Framework\ObjectManagerInterface */
    protected $objectManager;

    public function __construct(
        \M2E\Kaufland\Model\ActiveRecord\Factory $activeRecordFactory,
        \M2E\Kaufland\Model\Factory $modelFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        parent::__construct($activeRecordFactory, $modelFactory);
        $this->objectManager = $objectManager;
    }

    protected function process(): void
    {
        $changedProductsIds = $this->getEventObserver()->getData('product_ids');
        if (empty($changedProductsIds)) {
            return;
        }

        /** @var \M2E\Kaufland\PublicServices\Product\SqlChange $changesModel */
        $changesModel = $this->objectManager->get(\M2E\Kaufland\PublicServices\Product\SqlChange::class);

        foreach ($changedProductsIds as $productId) {
            $changesModel->markProductChanged($productId);
        }

        $changesModel->applyChanges();
    }
}
