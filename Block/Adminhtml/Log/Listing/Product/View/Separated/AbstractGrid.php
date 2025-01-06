<?php

namespace M2E\Kaufland\Block\Adminhtml\Log\Listing\Product\View\Separated;

use M2E\Kaufland\Block\Adminhtml\Log\Listing\View;

abstract class AbstractGrid extends \M2E\Kaufland\Block\Adminhtml\Log\Listing\Product\AbstractGrid
{
    private \M2E\Kaufland\Model\ResourceModel\Listing\Log\CollectionFactory $listingLogCollectionFactory;

    public function __construct(
        \M2E\Kaufland\Model\ResourceModel\Listing\Log\CollectionFactory $listingLogCollectionFactory,
        \M2E\Kaufland\Model\ResourceModel\Account $accountResource,
        \M2E\Kaufland\Model\Config\Manager $config,
        \M2E\Kaufland\Model\ResourceModel\Collection\WrapperFactory $wrapperCollectionFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \M2E\Kaufland\Helper\View $viewHelper,
        \M2E\Kaufland\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \M2E\Core\Helper\Data $dataHelper,
        array $data = []
    ) {
        $this->listingLogCollectionFactory = $listingLogCollectionFactory;
        parent::__construct(
            $accountResource,
            $config,
            $wrapperCollectionFactory,
            $resourceConnection,
            $viewHelper,
            $context,
            $backendHelper,
            $dataHelper,
            $data,
        );
    }

    protected function getViewMode()
    {
        return View\Switcher::VIEW_MODE_SEPARATED;
    }

    protected function _prepareCollection()
    {
        $collection = $this->listingLogCollectionFactory->create();

        $this->applyFilters($collection);

        $isNeedCombine = $this->isNeedCombineMessages();

        if ($isNeedCombine) {
            $collection->getSelect()->columns(
                ['main_table.create_date' => new \Zend_Db_Expr('MAX(main_table.create_date)')]
            );
            $collection->getSelect()->group(['main_table.listing_product_id', 'main_table.description']);
        }

        $this->setCollection($collection);

        $result = parent::_prepareCollection();

        if ($isNeedCombine) {
            $this->prepareMessageCount($collection);
        }

        return $result;
    }
}
