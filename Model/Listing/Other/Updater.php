<?php

namespace M2E\Kaufland\Model\Listing\Other;

class Updater
{
    private Repository $unmanagedRepository;
    private \M2E\Kaufland\Model\Listing\Other\MappingService $mappingService;
    private \M2E\Kaufland\Model\Account $account;
    private \M2E\Kaufland\Model\Product\Repository $listingProductRepository;
    private \M2E\Kaufland\Model\Listing\OtherFactory $otherFactory;

    private \M2E\Kaufland\Model\Storefront $storefront;

    public function __construct(
        \M2E\Kaufland\Model\Account $account,
        \M2E\Kaufland\Model\Storefront $storefront,
        \M2E\Kaufland\Model\Listing\OtherFactory $otherFactory,
        \M2E\Kaufland\Model\Listing\Other\Repository $unmanagedRepository,
        \M2E\Kaufland\Model\Product\Repository $listingProductRepository,
        \M2E\Kaufland\Model\Listing\Other\MappingService $mappingService
    ) {
        $this->unmanagedRepository = $unmanagedRepository;
        $this->mappingService = $mappingService;
        $this->listingProductRepository = $listingProductRepository;
        $this->otherFactory = $otherFactory;
        $this->account = $account;
        $this->storefront = $storefront;
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \M2E\Kaufland\Model\Exception
     */
    public function process(KauflandProductCollection $kauflandProductCollection): ?KauflandProductCollection
    {
        if ($kauflandProductCollection->empty()) {
            return null;
        }

        $existInListingCollection = $this->removeExistInListingProduct($kauflandProductCollection);

        $this->processExist($kauflandProductCollection);
        $unmanagedItems = $this->processNew($kauflandProductCollection);

        // remove not exist

        $this->autoMapping($unmanagedItems);

        return $existInListingCollection;
    }

    private function removeExistInListingProduct(KauflandProductCollection $collection): KauflandProductCollection
    {
        $existInListingCollection = new \M2E\Kaufland\Model\Listing\Other\KauflandProductCollection();
        if ($collection->empty()) {
            return $existInListingCollection;
        }

        $existed = $this->listingProductRepository->findByKauflandOfferIds(
            $collection->getOfferIds(),
            $this->account->getId(),
            $this->storefront->getId()
        );

        foreach ($existed as $product) {
            $existInListingCollection->add($collection->get($product->getKauflandOfferId()));

            $collection->remove($product->getKauflandOfferId());
        }

        return $existInListingCollection;
    }

    private function processExist(KauflandProductCollection $collection): void
    {
        if ($collection->empty()) {
            return;
        }

        $existProducts = $this->unmanagedRepository->findByOfferIds(
            $collection->getOfferIds(),
            $this->account->getId(),
            $this->storefront->getId(),
        );

        foreach ($existProducts as $existProduct) {
            if (!$collection->has($existProduct->getOfferId())) {
                continue;
            }

            $new = $collection->get($existProduct->getOfferId());

            $collection->remove($existProduct->getOfferId());

            if ($existProduct->getTitle() !== $new->getTitle()) {
                $existProduct->setTitle($new->getTitle());
            }

            if ($existProduct->getQty() !== $new->getQty()) {
                $existProduct->setQty($new->getQty());
            }

            if ($existProduct->getPrice() !== $new->getPrice()) {
                $existProduct->setPrice($new->getPrice());
            }

            if ($existProduct->getStatus() !== $new->getStatus()) {
                $existProduct->setStatus($new->getStatus());
            }

            $this->unmanagedRepository->save($existProduct);
        }
    }

    private function processNew(KauflandProductCollection $collection): array
    {
        $result = [];
        foreach ($collection->getAll() as $item) {
            $other = $this->otherFactory->create();
            $other->init(
                $this->account,
                $this->storefront,
                $item->getUnitId(),
                $item->getOfferId(),
                $item->getProductId(),
                $item->getStatus(),
                $item->getTitle(),
                $item->getEans(),
                $item->getCurrencyCode(),
                $item->getPrice(),
                $item->getQty(),
                $item->getMainPicture(),
                $item->getCategoryId(),
                $item->getCategoryTitle(),
                $item->getFulfilledByMerchant(),
                $item->getWarehouseId(),
                $item->getShippingGroupId(),
                $item->getCondition(),
                $item->getHandlingTime()
            );

            $this->unmanagedRepository->create($other);

            $result[] = $other;
        }

        return $result;
    }

    /**
     * @param \M2E\Kaufland\Model\Listing\Other[] $unmanagedItems
     *
     * @return \M2E\Kaufland\Model\Listing\Other[]
     * @throws \M2E\Kaufland\Model\Exception
     */
    private function autoMapping(array $otherListings): void
    {
        $this->mappingService->autoMapOtherListingsProducts($otherListings);
    }
}
