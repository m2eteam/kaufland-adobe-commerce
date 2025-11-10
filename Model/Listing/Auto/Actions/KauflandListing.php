<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Listing\Auto\Actions;

class KauflandListing extends \M2E\Kaufland\Model\Listing\Auto\Actions\Listing
{
    private \M2E\Kaufland\Model\Listing\AddProductsService $addProductsService;
    private \M2E\Kaufland\Model\Magento\ProductFactory $magentoProductFactory;
    private \M2E\Kaufland\Model\Category\Dictionary\Repository $categoryDictionaryRepository;
    private \M2E\Kaufland\Model\Product\Repository $productRepository;

    public function __construct(
        \M2E\Kaufland\Model\Listing\AddProductsService $addProductsService,
        \M2E\Kaufland\Model\Magento\ProductFactory $magentoProductFactory,
        \M2E\Kaufland\Model\Category\Dictionary\Repository $categoryDictionaryRepository,
        \M2E\Kaufland\Model\Product\Repository $productRepository,
        \M2E\Kaufland\Model\Listing $listing,
        \M2E\Kaufland\Model\ActiveRecord\Factory $activeRecordFactory,
        \M2E\Kaufland\Helper\Module\Exception $exceptionHelper,
        \M2E\Kaufland\Model\Listing\LogService $logService,
        \M2E\Kaufland\Model\InstructionService $instructionService
    ) {
        parent::__construct(
            $listing,
            $activeRecordFactory,
            $exceptionHelper,
            $logService,
            $instructionService
        );
        $this->addProductsService = $addProductsService;
        $this->magentoProductFactory = $magentoProductFactory;
        $this->categoryDictionaryRepository = $categoryDictionaryRepository;
        $this->productRepository = $productRepository;
    }

    /**
     * @throws \M2E\Kaufland\Model\Exception\Logic
     * @throws \Zend_Db_Select_Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getListingProductsForDelete(int $magentoProductId): array
    {
        return $this->productRepository
            ->getItemsByMagentoProductId(
                $magentoProductId,
                [],
                [
                    \M2E\Kaufland\Model\ResourceModel\Product::COLUMN_LISTING_ID => $this->getListing()->getId(),
                ]
            );
    }

    /**
     * @throws \M2E\Kaufland\Model\Listing\Exception\MagentoProductNotFoundException
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    public function addProductByCategoryGroup(
        \Magento\Catalog\Model\Product $product,
        \M2E\Kaufland\Model\Listing\Auto\Category\Group $categoryGroup
    ): void {
        $addedProduct = $this->addProductsService->addProduct(
            $this->getListing(),
            $this->convertMagentoProductToM2eMagentoProduct($product),
            $this->getCategoryByStorefrontAndId(
                $this->getListing()->getStorefrontId(),
                $categoryGroup->getAddingTemplateCategoryId()
            ),
            null,
            \M2E\Core\Helper\Data::INITIATOR_EXTENSION
        );

        if ($addedProduct === null) {
            return;
        }

        $this->addAdditionalData($addedProduct, [
            'reason' => __METHOD__,
            'rule_id' => $categoryGroup->getId(),
            'rule_title' => $categoryGroup->getTitle(),
        ]);
        $this->logAddedToMagentoProduct($addedProduct);
    }

    /**
     * @throws \M2E\Kaufland\Model\Listing\Exception\MagentoProductNotFoundException
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    public function addProductByGlobalListing(
        \Magento\Catalog\Model\Product $product,
        \M2E\Kaufland\Model\Listing $listing
    ): void {
        $addedProduct = $this->addProductsService->addProduct(
            $this->getListing(),
            $this->convertMagentoProductToM2eMagentoProduct($product),
            $this->getCategoryByStorefrontAndId(
                $this->getListing()->getStorefrontId(),
                $this->getListing()->getAutoGlobalAddingTemplateCategoryId()
            ),
            null,
            \M2E\Core\Helper\Data::INITIATOR_EXTENSION
        );

        if ($addedProduct === null) {
            return;
        }

        $this->addAdditionalData($addedProduct, [
            'reason' => __METHOD__,
        ]);
        $this->logAddedToMagentoProduct($addedProduct);
    }

    /**
     * @throws \M2E\Kaufland\Model\Listing\Exception\MagentoProductNotFoundException
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    public function addProductByWebsiteListing(
        \Magento\Catalog\Model\Product $product,
        \M2E\Kaufland\Model\Listing $listing
    ): void {
        $addedProduct = $this->addProductsService->addProduct(
            $this->getListing(),
            $this->convertMagentoProductToM2eMagentoProduct($product),
            $this->getCategoryByStorefrontAndId(
                $this->getListing()->getStorefrontId(),
                $this->getListing()->getAutoWebsiteAddingTemplateCategoryId()
            ),
            null,
            \M2E\Core\Helper\Data::INITIATOR_EXTENSION
        );

        if ($addedProduct === null) {
            return;
        }

        $this->addAdditionalData($addedProduct, [
            'reason' => __METHOD__,
        ]);
        $this->logAddedToMagentoProduct($addedProduct);
    }

    private function convertMagentoProductToM2eMagentoProduct(
        \Magento\Catalog\Model\Product $magentoProduct
    ): \M2E\Kaufland\Model\Magento\Product {
        return $this->magentoProductFactory
            ->createByProductId((int)$magentoProduct->getEntityId());
    }

    private function getCategoryByStorefrontAndId(
        int $storefrontId,
        int $categoryId
    ): \M2E\Kaufland\Model\Category\Dictionary {
        $category = $this->categoryDictionaryRepository
            ->findByStorefrontAndCategoryId($storefrontId, $categoryId);

        if ($category === null) {
            $message = sprintf(
                'Category (ID: %s) not found for Storefront (ID: %s).',
                $storefrontId,
                $categoryId
            );

            throw new \M2E\Kaufland\Model\Exception\Logic($message);
        }

        return $category;
    }

    private function addAdditionalData(\M2E\Kaufland\Model\Product $listingProduct, array $additionalData)
    {
        $listingProduct->setAdditionalData($additionalData);
        $this->productRepository->save($listingProduct);
    }
}
