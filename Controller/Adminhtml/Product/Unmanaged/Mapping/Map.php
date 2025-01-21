<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Product\Unmanaged\Mapping;

class Map extends \M2E\Kaufland\Controller\Adminhtml\AbstractListing
{
    private \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory;
    private \M2E\Kaufland\Model\Listing\Other\Repository $listingOtherRepository;
    private \M2E\Kaufland\Model\Listing\Other\MappingService $mappingService;

    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \M2E\Kaufland\Model\Listing\Other\Repository $listingOtherRepository,
        \M2E\Kaufland\Model\Listing\Other\MappingService $mappingService,
        \M2E\Kaufland\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($context);

        $this->productCollectionFactory = $productCollectionFactory;
        $this->listingOtherRepository = $listingOtherRepository;
        $this->mappingService = $mappingService;
    }

    public function execute()
    {
        $productId = $this->getRequest()->getParam('product_id'); // Magento
        $productOtherId = (int)$this->getRequest()->getParam('other_product_id');
        $accountId = (int)$this->getRequest()->getParam('account_id');

        if (!$productId || !$productOtherId) {
            $this->getMessageManager()->addErrorMessage('Params not valid.');

            return $this->_redirect('*/product_grid/unmanaged/', ['account' => $accountId]);
        }

        $collection = $this->productCollectionFactory->create();
        $collection->addFieldToFilter('entity_id', $productId);

        $magentoCatalogProductModel = $collection->getFirstItem();
        if ($magentoCatalogProductModel->isEmpty()) {
            $this->getMessageManager()->addErrorMessage('Params not valid.');

            return $this->_redirect('*/product_grid/unmanaged/', ['account' => $accountId]);
        }

        $productId = $magentoCatalogProductModel->getId();

        $productOtherInstance = $this->listingOtherRepository->get($productOtherId);

        $this->mappingService->mapProduct($productOtherInstance, (int)$productId);

        return $this->_redirect('*/product_grid/unmanaged/', ['account' => $accountId]);
    }
}
