<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Listing\Other;

use M2E\Kaufland\Model\Magento\Product as ProductModel;

class MappingService
{
    private \Magento\Catalog\Model\ProductFactory $productFactory;
    private \M2E\Kaufland\Model\Listing\Other\Repository $listingOtherRepository;

    public function __construct(
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \M2E\Kaufland\Model\Listing\Other\Repository $listingOtherRepository
    ) {
        $this->productFactory = $productFactory;
        $this->listingOtherRepository = $listingOtherRepository;
    }

    public function mapProduct(\M2E\Kaufland\Model\Listing\Other $other, int $magentoProductId): void
    {
        if ($other->getMagentoProductId() !== null) {
            throw new \RuntimeException('Product already mapped.');
        }

        $other->mapMagentoProduct($magentoProductId);

        $this->listingOtherRepository->save($other);
    }

    /**
     * @param \M2E\Kaufland\Model\Listing\Other[] $otherListings
     *
     * @return void
     */
    public function unMap(array $otherListings): void
    {
        foreach ($otherListings as $otherListing) {
            if ($otherListing->getMagentoProductId() === null) {
                continue;
            }

            $otherListing->unmapMagentoProduct();

            $this->listingOtherRepository->save($otherListing);
        }
    }

    /**
     * @param \M2E\Kaufland\Model\Listing\Other[] $otherListings
     * @throws \M2E\Kaufland\Model\Exception
     */
    public function autoMapOtherListingsProducts(array $otherListings): bool
    {
        $otherListingsFiltered = [];

        foreach ($otherListings as $otherListing) {
            if ($otherListing->getMagentoProductId()) {
                continue;
            }

            $otherListingsFiltered[] = $otherListing;
        }

        if (count($otherListingsFiltered) <= 0) {
            return false;
        }

        $result = true;
        foreach ($otherListingsFiltered as $otherListing) {
            if (!$this->autoMapOtherListingProduct($otherListing)) {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * @throws \M2E\Kaufland\Model\Exception
     */
    private function autoMapOtherListingProduct(\M2E\Kaufland\Model\Listing\Other $otherListing): bool
    {
        if ($otherListing->getMagentoProductId()) {
            return false;
        }

        if (!$otherListing->getAccount()->getUnmanagedListingSettings()->isMappingEnabled()) {
            return false;
        }

        $magentoProductId = $this->findMagentoProductId($otherListing);

        if ($magentoProductId === null) {
            return false;
        }

        $otherListing->mapMagentoProduct($magentoProductId);

        $this->listingOtherRepository->save($otherListing);

        return true;
    }

    /**
     * @throws \M2E\Kaufland\Model\Exception
     */
    private function findMagentoProductId(\M2E\Kaufland\Model\Listing\Other $otherListing): ?int
    {
        $mappingTypes = $otherListing->getAccount()->getUnmanagedListingSettings()->getMappingTypesByPriority();
        foreach ($mappingTypes as $type) {
            $magentoProductId = null;

            if ($type === \M2E\Kaufland\Model\Account\Settings\UnmanagedListings::MAPPING_TYPE_BY_SKU) {
                $magentoProductId = $this->getSkuMappedMagentoProductId($otherListing);
            }

            if ($type === \M2E\Kaufland\Model\Account\Settings\UnmanagedListings::MAPPING_TYPE_BY_EAN) {
                $magentoProductId = $this->getEanMappedMagentoProductId($otherListing);
            }

            if ($type === \M2E\Kaufland\Model\Account\Settings\UnmanagedListings::MAPPING_TYPE_BY_ITEM_ID) {
                $magentoProductId = $this->getItemIdMappedMagentoProductId($otherListing);
            }

            if ($magentoProductId === null) {
                continue;
            }

            return $magentoProductId;
        }

        return null;
    }

    /**
     * @throws \M2E\Kaufland\Model\Exception
     */
    private function getSkuMappedMagentoProductId(\M2E\Kaufland\Model\Listing\Other $otherListing): ?int
    {
        $temp = $otherListing->getOfferId();

        if (empty($temp)) {
            return null;
        }

        $settings = $otherListing->getAccount()->getUnmanagedListingSettings();

        if ($settings->isMappingBySkuModeByProductId()) {
            $productId = trim($otherListing->getOfferId());

            if (!ctype_digit($productId) || (int)$productId <= 0) {
                return null;
            }

            $product = $this->productFactory->create()->load($productId);

            if (
                $product->getId()
                && $this->isMagentoProductTypeAllowed($product->getTypeId())
            ) {
                return (int)$product->getId();
            }

            return null;
        }

        $attributeCode = null;

        if ($settings->isMappingBySkuModeBySku()) {
            $attributeCode = 'sku';
        }

        if ($settings->isMappingBySkuModeByAttribute()) {
            $attributeCode = $settings->getMappingAttributeBySku();
        }

        if ($attributeCode === null) {
            return null;
        }

        $storeId = $otherListing->getRelatedStoreId();
        $attributeValue = trim($otherListing->getOfferId());

        $productObj = $this->productFactory->create()->setStoreId($storeId);
        $productObj = $productObj->loadByAttribute($attributeCode, $attributeValue);

        if (
            $productObj
            && $productObj->getId()
            && $this->isMagentoProductTypeAllowed($productObj->getTypeId())
        ) {
            return (int)$productObj->getId();
        }

        return null;
    }

    /**
     * @throws \M2E\Kaufland\Model\Exception
     */
    private function getEanMappedMagentoProductId(\M2E\Kaufland\Model\Listing\Other $otherListing): ?int
    {
        $temp = $otherListing->getEans();

        if (empty($temp)) {
            return null;
        }

        $settings = $otherListing->getAccount()->getUnmanagedListingSettings();

        $attributeCode = null;

        if ($settings->isMappingByEanModeByAttribute()) {
            $attributeCode = $settings->getMappingAttributeByEan();
        }

        if ($attributeCode === null) {
            return null;
        }

        $storeId = $otherListing->getRelatedStoreId();
        $attributeValue = array_unique($otherListing->getEans());

        $productObj = $this->productFactory->create()->setStoreId($storeId);
        $productObj = $productObj->loadByAttribute($attributeCode, $attributeValue[0]);

        if (
            $productObj
            && $productObj->getId()
            && $this->isMagentoProductTypeAllowed($productObj->getTypeId())
        ) {
            return (int)$productObj->getId();
        }

        return null;
    }

    private function getItemIdMappedMagentoProductId(\M2E\Kaufland\Model\Listing\Other $otherListing): ?int
    {
        $temp = $otherListing->getKauflandProductId();

        if (empty($temp)) {
            return null;
        }

        $settings = $otherListing->getAccount()->getUnmanagedListingSettings();

        $attributeCode = null;

        if ($settings->isMappingByItemIdEnabled()) {
            $attributeCode = $settings->getMappingAttributeByItemId();
        }

        if ($attributeCode === null) {
            return null;
        }

        $storeId = $otherListing->getRelatedStoreId();
        $attributeValue = $otherListing->getKauflandProductId();

        $productObj = $this->productFactory->create()->setStoreId($storeId);
        $productObj = $productObj->loadByAttribute($attributeCode, $attributeValue);

        if (
            $productObj
            && $productObj->getId()
        ) {
            return (int)$productObj->getId();
        }

        return null;
    }

    /**
     * @throws \M2E\Kaufland\Model\Exception
     */
    private function isMagentoProductTypeAllowed($type): bool
    {
        $allowedTypes = [
            ProductModel::TYPE_SIMPLE_ORIGIN,
            ProductModel::TYPE_VIRTUAL_ORIGIN,
        ];

        return in_array($type, $allowedTypes);
    }
}
