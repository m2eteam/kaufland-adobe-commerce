<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Listing;

use M2E\Kaufland\Model\Product;

class AddProductsService
{
    private Product\Repository $listingProductRepository;
    private \M2E\Kaufland\Model\InstructionService $instructionService;
    private \M2E\Kaufland\Model\ProductFactory $listingProductFactory;
    private \M2E\Kaufland\Model\Listing\LogService $listingLogService;
    private \M2E\Kaufland\Model\Magento\Product\CacheFactory $magentoProductFactory;
    /**
     * @var \M2E\Kaufland\Model\Listing\Other\DeleteService
     */
    private Other\DeleteService $unmanagedProductDeleteService;

    public function __construct(
        Product\Repository $listingProductRepository,
        \M2E\Kaufland\Model\InstructionService $instructionService,
        \M2E\Kaufland\Model\ProductFactory $listingProductFactory,
        \M2E\Kaufland\Model\Listing\LogService $listingLogService,
        \M2E\Kaufland\Model\Magento\Product\CacheFactory $magentoProductFactory,
        \M2E\Kaufland\Model\Listing\Other\DeleteService $unmanagedProductDeleteService
    ) {
        $this->listingProductRepository = $listingProductRepository;
        $this->instructionService = $instructionService;
        $this->listingProductFactory = $listingProductFactory;
        $this->listingLogService = $listingLogService;
        $this->magentoProductFactory = $magentoProductFactory;
        $this->unmanagedProductDeleteService = $unmanagedProductDeleteService;
    }

    public function addProduct(
        \M2E\Kaufland\Model\Listing $listing,
        int $magentoProductId,
        ?\M2E\Kaufland\Model\Category\Dictionary $categoryTemplate,
        ?string $kauflandProductId,
        int $initiator = \M2E\Core\Helper\Data::INITIATOR_UNKNOWN,
        ?\M2E\Kaufland\Model\Listing\Other $unmanagedProduct = null
    ): ?Product {
        $this->checkSupportedMagentoType($magentoProductId);

        $listingProduct = $this->findExistProduct($listing, $magentoProductId);
        if ($listingProduct) {
            return null;
        }

        $listingProduct = $this->listingProductFactory->create();
        $listingProduct->init($listing->getId(), $magentoProductId, $kauflandProductId, $kauflandProductId === null);

        if ($unmanagedProduct !== null) {
            $listingProduct->fillFromUnmanagedProduct($unmanagedProduct);
        }

        if ($categoryTemplate !== null) {
            $listingProduct->setTemplateCategoryId($categoryTemplate->getId());
            $listingProduct->setOnlineCategoryData($categoryTemplate->getPath());
        }

        $this->listingProductRepository->create($listingProduct);

        $logMessage = (string)__('Product was Added');
        $logAction = \M2E\Kaufland\Model\Listing\Log::ACTION_ADD_PRODUCT_TO_LISTING;

        if (!empty($unmanagedProduct)) {
            $logMessage = (string)__('Item was Moved');
            $logAction = \M2E\Kaufland\Model\Listing\Log::ACTION_MOVE_FROM_OTHER_LISTING;
        }

        // Add message for listing log
        // ---------------------------------------
        $this->listingLogService->addProduct(
            $listingProduct,
            $initiator,
            $logAction,
            null,
            $logMessage,
            \M2E\Kaufland\Model\Log\AbstractModel::TYPE_INFO,
        );
        // ---------------------------------------

        $this->instructionService->create(
            (int)$listingProduct->getId(),
            \M2E\Kaufland\Model\Listing::INSTRUCTION_TYPE_PRODUCT_ADDED,
            \M2E\Kaufland\Model\Listing::INSTRUCTION_INITIATOR_ADDING_PRODUCT,
            70,
        );

        return $listingProduct;
    }

    public function addFromUnmanaged(
        \M2E\Kaufland\Model\Listing $listing,
        \M2E\Kaufland\Model\Listing\Other $unmanagedProduct,
        ?\M2E\Kaufland\Model\Category\Dictionary $categoryTemplate,
        int $initiator
    ): ?Product {
        if (!$unmanagedProduct->getMagentoProductId()) {
            return null;
        }

        if (
            $listing->getAccount()->getId() !== $unmanagedProduct->getAccount()->getId()
            || $listing->getStorefront()->getId() !== $unmanagedProduct->getStorefront()->getId()
        ) {
            return null;
        }

        $magentoProductId = $unmanagedProduct->getMagentoProductId();

        $listingProduct = $this->addProduct(
            $listing,
            $magentoProductId,
            $categoryTemplate,
            $unmanagedProduct->getKauflandProductId(),
            $initiator,
            $unmanagedProduct
        );

        if ($listingProduct === null) {
            return null;
        }

        $this->unmanagedProductDeleteService->process($unmanagedProduct);

        $this->instructionService->create(
            $listingProduct->getId(),
            \M2E\Kaufland\Model\Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_OTHER,
            \M2E\Kaufland\Model\Listing::INSTRUCTION_INITIATOR_MOVING_PRODUCT_FROM_OTHER,
            20,
        );

        return $listingProduct;
    }

    /**
     * @param \M2E\Kaufland\Model\Product $listingProduct
     * @param \M2E\Kaufland\Model\Listing $targetListing
     * @param \M2E\Kaufland\Model\Listing $sourceListing
     *
     * @return bool
     * @throws \Exception
     */
    public function addProductFromListing(
        \M2E\Kaufland\Model\Product $listingProduct,
        \M2E\Kaufland\Model\Listing $targetListing,
        \M2E\Kaufland\Model\Listing $sourceListing
    ): bool {
        if ($this->findExistProduct($targetListing, $listingProduct->getMagentoProductId()) !== null) {
            $this->listingLogService->addProduct(
                $listingProduct,
                \M2E\Core\Helper\Data::INITIATOR_USER,
                \M2E\Kaufland\Model\Listing\Log::ACTION_MOVE_TO_LISTING,
                null,
                (string)__('The Product was not moved because it already exists in the selected Listing'),
                \M2E\Kaufland\Model\Log\AbstractModel::TYPE_ERROR,
            );

            return false;
        }

        $listingProduct->changeListing($targetListing);
        if ($listingProduct->isStatusNotListed()) {
            $listingProduct->resetKauflandOfferId();
        }

        $this->listingProductRepository->save($listingProduct);

        $logMessage = (string)__(
            'Item was moved from Listing %previous_listing_name.',
            [
                'previous_listing_name' => $sourceListing->getTitle()
            ],
        );

        $this->listingLogService->addProduct(
            $listingProduct,
            \M2E\Core\Helper\Data::INITIATOR_USER,
            \M2E\Kaufland\Model\Listing\Log::ACTION_MOVE_TO_LISTING,
            null,
            $logMessage,
            \M2E\Kaufland\Model\Log\AbstractModel::TYPE_INFO,
        );

        $logMessage = (string)__(
            'Product %product_title was moved to Listing %current_listing_name',
            [
                'product_title' => $listingProduct->getMagentoProduct()->getName(),
                'current_listing_name' => $targetListing->getTitle(),
            ],
        );

        $this->listingLogService->addListing(
            $sourceListing,
            \M2E\Core\Helper\Data::INITIATOR_USER,
            \M2E\Kaufland\Model\Listing\Log::ACTION_MOVE_TO_LISTING,
            null,
            $logMessage,
            \M2E\Kaufland\Model\Log\AbstractModel::TYPE_INFO,
        );

        $this->instructionService->create(
            $listingProduct->getId(),
            \M2E\Kaufland\Model\Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_LISTING,
            \M2E\Kaufland\Model\Listing::INSTRUCTION_INITIATOR_MOVING_PRODUCT_FROM_LISTING,
            20
        );

        return true;
    }

    private function findExistProduct(\M2E\Kaufland\Model\Listing $listing, int $magentoProductId): ?\M2E\Kaufland\Model\Product
    {
        return $this->listingProductRepository->findByListingAndMagentoProductId($listing, $magentoProductId);
    }

    private function isSupportedMagentoProductType(\M2E\Kaufland\Model\Magento\Product\Cache $ourMagentoProduct): bool
    {
        return $ourMagentoProduct->isSimpleType();
    }

    private function checkSupportedMagentoType(int $magentoProductId): void
    {
        $ourMagentoProduct = $this->magentoProductFactory->create()->setProductId($magentoProductId);
        if (!$this->isSupportedMagentoProductType($ourMagentoProduct)) {
            throw new \M2E\Kaufland\Model\Exception\Logic(
                (string)__(
                    sprintf('Unsupported magento product type - %s', $ourMagentoProduct->getTypeId()),
                ),
            );
        }
    }
}
