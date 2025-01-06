<?php

namespace M2E\Kaufland\Observer\Indexes;

class Disable extends \M2E\Kaufland\Observer\AbstractObserver
{
    /** @var \M2E\Kaufland\Model\Magento\Product\Index */
    private $productIndex;
    private \M2E\Core\Helper\Magento $helperMagento;

    public function __construct(
        \M2E\Kaufland\Model\Magento\Product\Index $productIndex,
        \M2E\Core\Helper\Magento $helperMagento,
        \M2E\Kaufland\Model\ActiveRecord\Factory $activeRecordFactory,
        \M2E\Kaufland\Model\Factory $modelFactory
    ) {
        $this->productIndex = $productIndex;
        $this->helperMagento = $helperMagento;
        parent::__construct($activeRecordFactory, $modelFactory);
    }

    protected function process(): void
    {
        if ($this->helperMagento->isMSISupportingVersion()) {
            return;
        }

        if (!$this->productIndex->isIndexManagementEnabled()) {
            return;
        }

        foreach ($this->productIndex->getIndexes() as $code) {
            if ($this->productIndex->disableReindex($code)) {
                $this->productIndex->rememberDisabledIndex($code);
            }
        }
    }
}
