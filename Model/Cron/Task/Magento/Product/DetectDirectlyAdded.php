<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Cron\Task\Magento\Product;

class DetectDirectlyAdded implements \M2E\Core\Model\Cron\TaskHandlerInterface
{
    public const NICK = 'magento/product/detect_directly_added';
    private const REGISTRY_KEY = '/magento/product/detect_directly_added/last_magento_product_id/';

    private \M2E\Kaufland\Model\Registry\Manager $registryManager;
    private \M2E\Kaufland\Model\ResourceModel\Magento\Product\CollectionFactory $magentoProductCollectionFactory;
    private \M2E\Kaufland\Model\Listing\Auto\Actions\Mode\Factory $listingAutoActionsModeFactory;

    public function __construct(
        \M2E\Kaufland\Model\Registry\Manager $registryManager,
        \M2E\Kaufland\Model\ResourceModel\Magento\Product\CollectionFactory $magentoProductCollectionFactory,
        \M2E\Kaufland\Model\Listing\Auto\Actions\Mode\Factory $listingAutoActionsModeFactory
    ) {
        $this->registryManager = $registryManager;
        $this->magentoProductCollectionFactory = $magentoProductCollectionFactory;
        $this->listingAutoActionsModeFactory = $listingAutoActionsModeFactory;
    }

    public function process($context): void
    {
        if ($this->getLastProcessedMagentoProductId() === null) {
            $this->setLastProcessedMagentoProductId($this->getLastMagentoProductId());
        }

        $magentoProducts = $this->getProducts();
        if (empty($magentoProducts)) {
            return;
        }

        foreach ($magentoProducts as $magentoProduct) {
            $this->processCategoriesActions($magentoProduct);
            $this->processGlobalActions($magentoProduct);
            $this->processWebsiteActions($magentoProduct);
        }

        $lastMagentoProduct = array_pop($magentoProducts);
        $this->setLastProcessedMagentoProductId((int)$lastMagentoProduct->getId());
    }

    private function getLastProcessedMagentoProductId(): ?int
    {
        $value = $this->registryManager->getValue(self::REGISTRY_KEY);
        if (empty($value)) {
            return null;
        }

        return (int)$value;
    }

    private function setLastProcessedMagentoProductId(int $lastMagentoProductId): void
    {
        $this->registryManager->setValue(self::REGISTRY_KEY, (string)$lastMagentoProductId);
    }

    private function getLastMagentoProductId(): int
    {
        $collection = $this->magentoProductCollectionFactory->create();
        $collection->getSelect()->order('entity_id DESC')->limit(1);

        return (int)$collection->getLastItem()->getId();
    }

    /**
     * @return \Magento\Catalog\Model\Product[]
     */
    private function getProducts(): array
    {
        $collection = $this->magentoProductCollectionFactory->create();

        $collection->addFieldToFilter(
            'entity_id',
            ['gt' => $this->getLastProcessedMagentoProductId()]
        );
        $collection->addAttributeToSelect('visibility');
        $collection->setOrder('entity_id', 'asc');
        $collection->getSelect()->limit(100);

        return array_values($collection->getItems());
    }

    private function processCategoriesActions(\Magento\Catalog\Model\Product $magentoProduct): void
    {
        $productCategories = $magentoProduct->getCategoryIds();

        $categoriesByWebsite = [
            0 => $productCategories, // website for admin values
        ];

        foreach ($magentoProduct->getWebsiteIds() as $websiteId) {
            $categoriesByWebsite[$websiteId] = $productCategories;
        }

        $autoActionsCategory = $this->listingAutoActionsModeFactory->createCategoryMode($magentoProduct);
        foreach ($categoriesByWebsite as $websiteId => $categoryIds) {
            $autoActionsCategory->synchWithAddedCategoryId($websiteId, $categoryIds);
        }
    }

    private function processGlobalActions(\Magento\Catalog\Model\Product $magentoProduct): void
    {
        $globalMode = $this->listingAutoActionsModeFactory->createGlobalMode($magentoProduct);
        $globalMode->synch();
    }

    private function processWebsiteActions(\Magento\Catalog\Model\Product $magentoProduct): void
    {
        $websiteMode = $this->listingAutoActionsModeFactory->createWebsiteMode($magentoProduct);

        $websiteIds = $magentoProduct->getWebsiteIds();
        $websiteIds[] = 0;

        foreach ($websiteIds as $websiteId) {
            $websiteMode->synchWithAddedWebsiteId($websiteId);
        }
    }
}
