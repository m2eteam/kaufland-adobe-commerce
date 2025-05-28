<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Template\Description;

use M2E\Kaufland\Controller\Adminhtml\Kaufland\Template\AbstractDescription;

class GetRandomMagentoProductId extends AbstractDescription
{
    private \M2E\Kaufland\Model\Product\Repository $productRepository;

    public function __construct(
        \Magento\Framework\HTTP\PhpEnvironment\Request $phpEnvironmentRequest,
        \Magento\Catalog\Model\Product $productModel,
        \M2E\Kaufland\Model\Template\Manager $templateManager,
        \M2E\Kaufland\Model\Product\Repository $productRepository
    ) {
        parent::__construct(
            $phpEnvironmentRequest,
            $productModel,
            $templateManager
        );

        $this->productRepository = $productRepository;
    }

    public function execute()
    {
        $storeId = (int)$this->getRequest()->getPost('store_id', \Magento\Store\Model\Store::DEFAULT_STORE_ID);
        $productId = $this->getProductIdFromListing($storeId) ?? $this->getProductIdFromMagento();

        if ($productId) {
            $this->setJsonContent([
                'success' => true,
                'product_id' => $productId,
            ]);
        } else {
            $this->setJsonContent([
                'success' => false,
                'message' => __('You don\'t have any products in Magento catalog.'),
            ]);
        }

        return $this->getResult();
    }

    private function getProductIdFromListing(int $storeId): ?int
    {
        $listingProductCollection = $this->productRepository->getProductCollectionByStoreId($storeId);
        $collectionSize = $listingProductCollection->getSize();

        if ($collectionSize == 0) {
            return null;
        }

        $listingProductCollection
            ->getSelect()
            ->reset(\Magento\Framework\DB\Select::COLUMNS)
            ->columns(\M2E\Kaufland\Model\ResourceModel\Product::COLUMN_MAGENTO_PRODUCT_ID)
            ->limit(1, $this->calculateOffset($collectionSize));

        $listingProduct = $listingProductCollection->getFirstItem();

        return $listingProduct->getMagentoProductId();
    }

    private function getProductIdFromMagento(): ?int
    {
        $productCollection = $this->productModel->getCollection();
        $collectionSize = $productCollection->getSize();

        if ($collectionSize == 0) {
            return null;
        }

        $productCollection
            ->getSelect()
            ->reset(\Magento\Framework\DB\Select::COLUMNS)
            ->columns('entity_id')
            ->limit(1, $this->calculateOffset($collectionSize));

        /** @var \Magento\Catalog\Model\Product $product */
        $product = $productCollection->getFirstItem();

        return (int)$product->getEntityId();
    }

    private function calculateOffset(int $collectionSize): int
    {
        return rand(0, $collectionSize - 1);
    }
}
