<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Listing\Auto\Actions\Mode;

class DuplicateProducts
{
    private DuplicateProducts\Repository $duplicateProductsRepository;
    private \M2E\Kaufland\Model\Listing\Log\Factory $logFactory;
    private \M2E\Kaufland\Model\Listing\LogService $logService;
    private \M2E\Kaufland\Model\Listing\Log\Repository $logRepository;

    public function __construct(
        DuplicateProducts\Repository $duplicateProductsRepository,
        \M2E\Kaufland\Model\Listing\Log\Factory $logFactory,
        \M2E\Kaufland\Model\Listing\LogService $logService,
        \M2E\Kaufland\Model\Listing\Log\Repository $logRepository
    ) {
        $this->duplicateProductsRepository = $duplicateProductsRepository;
        $this->logFactory = $logFactory;
        $this->logService = $logService;
        $this->logRepository = $logRepository;
    }

    public function checkDuplicateListingProduct(
        \M2E\Kaufland\Model\Listing $listing,
        \Magento\Catalog\Model\Product $magentoProduct
    ): bool {
        $listingProductIdsArr = $this->duplicateProductsRepository
            ->getListingProductIds($listing, $magentoProduct);

        if ($listingProductIdsArr === []) {
            return false;
        }

        foreach ($listingProductIdsArr as $listingProductId) {
            $this->addLog(
                $listingProductId,
                $listing,
                $magentoProduct
            );
        }

        return true;
    }

    private function addLog(
        int $listingProductId,
        \M2E\Kaufland\Model\Listing $listing,
        \Magento\Catalog\Model\Product $magentoProduct
    ): void {
        $message = 'Product was not added since the item is already presented in another Listing related to ' .
            'the Channel account and marketplace.';

        $logModel = $this->logFactory->create();
        $logModel->setData([
            \M2E\Kaufland\Model\ResourceModel\Listing\Log::COLUMN_ACCOUNT_ID => $listing->getAccountId(),
            \M2E\Kaufland\Model\ResourceModel\Listing\Log::COLUMN_STOREFRONT_ID => $listing->getStorefrontId(),
            \M2E\Kaufland\Model\ResourceModel\Listing\Log::COLUMN_LISTING_ID => $listing->getId(),
            \M2E\Kaufland\Model\ResourceModel\Listing\Log::COLUMN_PRODUCT_ID => $magentoProduct->getId(),
            \M2E\Kaufland\Model\ResourceModel\Listing\Log::COLUMN_LISTING_PRODUCT_ID => $listingProductId,
            \M2E\Kaufland\Model\ResourceModel\Listing\Log::COLUMN_LISTING_TITLE => $listing->getTitle(),
            \M2E\Kaufland\Model\ResourceModel\Listing\Log::COLUMN_PRODUCT_TITLE => $magentoProduct->getName(),
            \M2E\Kaufland\Model\ResourceModel\Listing\Log::COLUMN_ACTION_ID => $this->logService->getNextActionId(),
            \M2E\Kaufland\Model\ResourceModel\Listing\Log::COLUMN_ACTION => \M2E\Kaufland\Model\Listing\Log::ACTION_ADD_PRODUCT_TO_LISTING,
            \M2E\Kaufland\Model\ResourceModel\Listing\Log::COLUMN_INITIATOR => \M2E\Core\Helper\Data::INITIATOR_EXTENSION,
            \M2E\Kaufland\Model\ResourceModel\Listing\Log::COLUMN_TYPE => \M2E\Kaufland\Model\Log\AbstractModel::TYPE_INFO,
            \M2E\Kaufland\Model\ResourceModel\Listing\Log::COLUMN_DESCRIPTION => $message,
        ]);

        $this->logRepository->create($logModel);
    }
}
