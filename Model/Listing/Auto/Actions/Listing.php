<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Listing\Auto\Actions;

use M2E\Kaufland\Model\Product;

abstract class Listing
{
    public const INSTRUCTION_TYPE_STOP = 'auto_actions_stop';
    public const INSTRUCTION_TYPE_STOP_AND_REMOVE = 'auto_actions_stop_and_remove';

    public const INSTRUCTION_INITIATOR = 'auto_actions';

    private \M2E\Kaufland\Model\Listing $listing;
    private \M2E\Kaufland\Helper\Module\Exception $exceptionHelper;
    private \M2E\Kaufland\Model\Listing\LogService $logService;
    private \M2E\Kaufland\Model\InstructionService $instructionService;

    public function __construct(
        \M2E\Kaufland\Model\Listing $listing,
        \M2E\Kaufland\Model\ActiveRecord\Factory $activeRecordFactory,
        \M2E\Kaufland\Helper\Module\Exception $exceptionHelper,
        \M2E\Kaufland\Model\Listing\LogService $logService,
        \M2E\Kaufland\Model\InstructionService $instructionService
    ) {
        $this->listing = $listing;
        $this->exceptionHelper = $exceptionHelper;
        $this->logService = $logService;
        $this->instructionService = $instructionService;
    }

    public function deleteProduct(\Magento\Catalog\Model\Product $product, int $deletingMode): void
    {
        if ($deletingMode == \M2E\Kaufland\Model\Listing::DELETING_MODE_NONE) {
            return;
        }

        $listingsProducts = $this->getListingProductsForDelete((int)$product->getEntityId());
        if (count($listingsProducts) <= 0) {
            return;
        }

        foreach ($listingsProducts as $listingProduct) {
            if (!($listingProduct instanceof \M2E\Kaufland\Model\Product)) {
                return;
            }

            if ($deletingMode == \M2E\Kaufland\Model\Listing::DELETING_MODE_STOP && !$listingProduct->isStoppable()) {
                continue;
            }

            try {
                $instructionType = self::INSTRUCTION_TYPE_STOP;

                if ($deletingMode == \M2E\Kaufland\Model\Listing::DELETING_MODE_STOP_REMOVE) {
                    $instructionType = self::INSTRUCTION_TYPE_STOP_AND_REMOVE;
                }

                $this->instructionService->create(
                    $listingProduct->getId(),
                    $instructionType,
                    self::INSTRUCTION_INITIATOR,
                    $listingProduct->isStoppable() ? 60 : 0
                );
            } catch (\Exception $exception) {
                $this->exceptionHelper->process($exception);
            }
        }
    }

    abstract public function getListingProductsForDelete(int $magentoProductId): array;

    abstract public function addProductByCategoryGroup(
        \Magento\Catalog\Model\Product $product,
        \M2E\Kaufland\Model\Listing\Auto\Category\Group $categoryGroup
    ): void;

    abstract public function addProductByGlobalListing(
        \Magento\Catalog\Model\Product $product,
        \M2E\Kaufland\Model\Listing $listing
    ): void;

    abstract public function addProductByWebsiteListing(
        \Magento\Catalog\Model\Product $product,
        \M2E\Kaufland\Model\Listing $listing
    ): void;

    protected function logAddedToMagentoProduct(Product $listingProduct)
    {
        $this->logService->addProduct(
            $listingProduct,
            \M2E\Core\Helper\Data::INITIATOR_UNKNOWN,
            \M2E\Kaufland\Model\Listing\Log::ACTION_ADD_PRODUCT_TO_MAGENTO,
            null,
            'Product was Added',
            \M2E\Kaufland\Model\Log\AbstractModel::TYPE_INFO
        );
    }

    /**
     * @return \M2E\Kaufland\Model\Listing
     */
    protected function getListing(): \M2E\Kaufland\Model\Listing
    {
        return $this->listing;
    }
}
