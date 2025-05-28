<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Category\Tree;

class Manager
{
    private Repository $categoryTreeRepository;
    private \M2E\Kaufland\Model\Channel\Category\Retriever $connectionProcessor;
    private \M2E\Kaufland\Model\Category\TreeFactory $categoryFactory;
    private \M2E\Kaufland\Model\Registry\Manager $registry;
    private \M2E\Kaufland\Model\Category\Tree\DeleteService $categoryTreeDeleteService;

    public function __construct(
        \M2E\Kaufland\Model\Category\Tree\Repository $treeRepository,
        \M2E\Kaufland\Model\Registry\Manager $registry,
        \M2E\Kaufland\Model\Category\TreeFactory $categoryFactory,
        \M2E\Kaufland\Model\Channel\Category\Retriever $connectionProcessor,
        \M2E\Kaufland\Model\Category\Tree\DeleteService $categoryTreeDeleteService
    ) {
        $this->categoryTreeRepository = $treeRepository;
        $this->connectionProcessor = $connectionProcessor;
        $this->categoryFactory = $categoryFactory;
        $this->registry = $registry;
        $this->categoryTreeDeleteService = $categoryTreeDeleteService;
    }

    /**
     * @param \M2E\Kaufland\Model\Storefront $storefront
     * @param int|null $categoryId
     *
     * @return \M2E\Kaufland\Model\Category\Tree[]
     * @throws \Exception
     */
    public function getCategories(
        \M2E\Kaufland\Model\Storefront $storefront,
        ?int $categoryId
    ): array {
        if (!$storefront->isLoaded()) {
            throw new \M2E\Kaufland\Model\Exception\Logic('Storefront must be loaded');
        }

        if ($this->isNeedSync($storefront)) {
            $this->syncCategories($storefront);
        }

        if ($categoryId === null) {
            return $this->categoryTreeRepository->getRootCategories($storefront->getId());
        }

        return $this->categoryTreeRepository->getChildCategories($storefront->getId(), $categoryId);
    }

    private function syncCategories(\M2E\Kaufland\Model\Storefront $storefront)
    {
        $response = $this->connectionProcessor->process($storefront);

        $categories = [];
        foreach ($response->getCategories() as $category) {
            $categories[] = $this->categoryFactory->create()->create(
                $storefront->getId(),
                $category->getId(),
                $category->getParentId(),
                $category->getName(),
            );
        }

        $this->categoryTreeDeleteService->deleteByStorefront($storefront);
        $this->categoryTreeRepository->batchInsert($categories);

        $this->setLastSyncDate($storefront, \M2E\Core\Helper\Date::createCurrentGmt());
    }

    private function isNeedSync(\M2E\Kaufland\Model\Storefront $storefront): bool
    {
        return $this->getLastSyncDate($storefront) === null;
    }

    private function getLastSyncDate(\M2E\Kaufland\Model\Storefront $storefront): ?\DateTime
    {
        $date = $this->registry->getValue(
            self::prepareRegistryKey($storefront)
        );

        if ($date === null) {
            return null;
        }

        return \M2E\Core\Helper\Date::createDateGmt($date);
    }

    private function setLastSyncDate(\M2E\Kaufland\Model\Storefront $storefront, \DateTime $dateTime): void
    {
        $this->registry->setValue(
            self::prepareRegistryKey($storefront),
            $dateTime->format('Y-m-d H:i:s')
        );
    }

    public static function prepareRegistryKey(\M2E\Kaufland\Model\Storefront $storefront): string
    {
        return sprintf('/category/tree/last_sync_date/kaufland_storefront_id/%d', $storefront->getId());
    }
}
