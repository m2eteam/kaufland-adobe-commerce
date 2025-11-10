<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Listing\Auto\Actions;

use Magento\Catalog\Model\Product\Visibility as ProductVisibility;

class CategoryMode
{
    private \Magento\Catalog\Model\Product $magentoProduct;
    private \M2E\Kaufland\Model\Listing\Auto\Actions\ListingFactory $autoActionsListingFactory;
    private \M2E\Kaufland\Model\Listing\Auto\Actions\Mode\DuplicateProducts $duplicateProducts;
    private \M2E\Kaufland\Model\Listing\Auto\Actions\Mode\Category\Repository $repository;

    public function __construct(
        \Magento\Catalog\Model\Product $magentoProduct,
        \M2E\Kaufland\Model\Listing\Auto\Actions\ListingFactory $autoActionsListingFactory,
        \M2E\Kaufland\Model\Listing\Auto\Actions\Mode\DuplicateProducts $duplicateProducts,
        \M2E\Kaufland\Model\Listing\Auto\Actions\Mode\Category\Repository $repository
    ) {
        $this->magentoProduct = $magentoProduct;
        $this->autoActionsListingFactory = $autoActionsListingFactory;
        $this->duplicateProducts = $duplicateProducts;
        $this->repository = $repository;
    }

    /**
     * @throws \M2E\Kaufland\Model\Exception\Logic
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function synchWithAddedCategoryId(int $websiteId, array $idsOfAddedCategories): void
    {
        if (empty($idsOfAddedCategories)) {
            return;
        }

        $groupSet = $this->repository
            ->getGroupSet($idsOfAddedCategories);
        if ($groupSet->isEmpty()) {
            return;
        }

        $groupSet = $this->filterGroupsFromSetByWebsite($websiteId, $groupSet);
        $groupSet = $this->excludeGroupsWithDuplicateProductsFromSet($groupSet);

        foreach ($groupSet->getGroups() as $group) {
            $listing = $this->repository->getLoadedListing($group->getListingId());
            $autoCategoryGroups = $this->repository->getAutoCategoryGroups($group);
            foreach ($autoCategoryGroups as $autoCategoryGroup) {
                if ($autoCategoryGroup->isAddingModeNone()) {
                    continue;
                }

                if (
                    !$autoCategoryGroup->isAddingAddNotVisibleYes()
                    && $this->magentoProduct->getVisibility() == ProductVisibility::VISIBILITY_NOT_VISIBLE
                ) {
                    continue;
                }

                $autoActionListing = $this->autoActionsListingFactory->create($listing);
                $autoActionListing->addProductByCategoryGroup(
                    $this->magentoProduct,
                    $autoCategoryGroup
                );
            }
        }
    }

    /**
     * @param Mode\Category\GroupSet $collection
     *
     * @return Mode\Category\GroupSet
     */
    private function excludeGroupsWithDuplicateProductsFromSet(
        Mode\Category\GroupSet $collection
    ): Mode\Category\GroupSet {
        return $collection->filter(function (Mode\Category\Group $group) {
            $listing = $this->repository->getLoadedListing($group->getListingId());

            return !$this->duplicateProducts->checkDuplicateListingProduct($listing, $this->magentoProduct);
        });
    }

    /**
     * @param int $websiteId
     * @param array $idsOfRemovedCategories
     *
     * @return void
     * @throws \M2E\Kaufland\Model\Exception\Logic
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function synchWithDeletedCategoryId(int $websiteId, array $idsOfRemovedCategories): void
    {
        if (empty($idsOfRemovedCategories)) {
            return;
        }

        $groupSet = $this->repository
            ->getGroupSet($idsOfRemovedCategories, $this->magentoProduct);
        if ($groupSet->isEmpty()) {
            return;
        }

        $idsOfCategoriesThatStayInMagentoProduct = $this->magentoProduct->getCategoryIds();
        $groupSet = $groupSet
            ->excludeGroupsThatContainsCategoryIds($idsOfCategoriesThatStayInMagentoProduct);
        $groupSet = $this
            ->filterGroupsFromSetByWebsite($websiteId, $groupSet);

        foreach ($groupSet->getGroups() as $group) {
            $listing = $this->repository->getLoadedListing($group->getListingId());
            $autoCategoryGroups = $this->repository->getAutoCategoryGroups($group);
            foreach ($autoCategoryGroups as $autoCategoryGroup) {
                if ($autoCategoryGroup->isDeletingModeNone()) {
                    continue;
                }
                $autoActionListing = $this->autoActionsListingFactory->create($listing);
                $autoActionListing->deleteProduct($this->magentoProduct, $autoCategoryGroup->getDeletingMode());
            }
        }
    }

    /**
     * @param int $websiteId
     * @param Mode\Category\GroupSet $groupSet
     *
     * @return Mode\Category\GroupSet
     */
    private function filterGroupsFromSetByWebsite(
        int $websiteId,
        Mode\Category\GroupSet $groupSet
    ): Mode\Category\GroupSet {
        return $groupSet->filter(function (Mode\Category\Group $group) use ($websiteId) {
            $listingId = $group->getListingId();
            $listing = $this->repository->getLoadedListing($listingId);
            $storeWebsiteId = $this->repository->getStoreWebsiteIdByListingId($listingId);

            return $storeWebsiteId === $websiteId
                && $listing->isAutoModeCategory();
        });
    }
}
