<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\ScheduledAction;

use M2E\Kaufland\Model\Product\Action\Configurator;
use M2E\Kaufland\Model\ResourceModel\ScheduledAction\Collection as ScheduledActionCollection;
use M2E\Kaufland\Model\ResourceModel\ScheduledAction\CollectionFactory as ScheduledActionCollectionFactory;

class Processor
{
    private const LIST_PRIORITY = 25;
    private const RELIST_PRIORITY = 125;
    private const STOP_PRIORITY = 1000;
    private const REVISE_QTY_PRIORITY = 500;
    private const REVISE_PRICE_PRIORITY = 250;
    private const REVISE_PARTS_PRIORITY = 50;
    private const REVISE_SHIPPING_PRIORITY = 50;
    private const REVISE_TITLE_PRIORITY = 50;
    private const REVISE_DESCRIPTION_PRIORITY = 50;
    private const REVISE_IMAGES_PRIORITY = 50;
    private const REVISE_CATEGORIES_PRIORITY = 50;

    private \M2E\Kaufland\Model\Product\Action\ConfiguratorFactory $configuratorFactory;
    private \M2E\Kaufland\Model\Config\Manager $config;
    private \Magento\Framework\App\ResourceConnection $resourceConnection;
    private ScheduledActionCollectionFactory $scheduledActionCollectionFactory;
    private \M2E\Kaufland\Helper\Module\Exception $exceptionHelper;
    private \M2E\Kaufland\Model\Product\Action\Dispatcher $actionDispatcher;
    /** @var \M2E\Kaufland\Model\ScheduledAction\Repository */
    private Repository $scheduledActionRepository;
    private \M2E\Kaufland\Model\Product\Action\Async\DispatcherAsync $actionDispatcherAsync;

    public function __construct(
        \M2E\Kaufland\Model\ScheduledAction\Repository $scheduledActionRepository,
        \M2E\Kaufland\Model\Product\Action\ConfiguratorFactory $configuratorFactory,
        \M2E\Kaufland\Model\Config\Manager $config,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        ScheduledActionCollectionFactory $scheduledActionCollectionFactory,
        \M2E\Kaufland\Helper\Module\Exception $exceptionHelper,
        \M2E\Kaufland\Model\Product\Action\Dispatcher $actionDispatcher,
        \M2E\Kaufland\Model\Product\Action\Async\DispatcherAsync $actionDispatcherAsync
    ) {
        $this->configuratorFactory = $configuratorFactory;
        $this->config = $config;
        $this->resourceConnection = $resourceConnection;
        $this->scheduledActionCollectionFactory = $scheduledActionCollectionFactory;
        $this->exceptionHelper = $exceptionHelper;
        $this->actionDispatcher = $actionDispatcher;
        $this->scheduledActionRepository = $scheduledActionRepository;
        $this->actionDispatcherAsync = $actionDispatcherAsync;
    }

    public function process(): void
    {
        $limit = $this->calculateActionsCountLimit();
        if ($limit === 0) {
            return;
        }

        $scheduledActions = $this->getScheduledActionsForProcessing($limit);
        if (empty($scheduledActions)) {
            return;
        }

        foreach ($scheduledActions as $scheduledAction) {
            try {
                $listingProduct = $scheduledAction->getListingProduct();
                $additionalData = $scheduledAction->getAdditionalData();
                $statusChanger = $scheduledAction->getStatusChanger();
            } catch (\M2E\Kaufland\Model\Exception\Logic $e) {
                $this->exceptionHelper->process($e);

                $this->scheduledActionRepository->remove($scheduledAction);

                continue;
            }

            $params = $additionalData['params'] ?? [];

            $packageCollection = new \M2E\Kaufland\Model\Product\Action\PackageCollection();
            $packageCollection->add($listingProduct, $scheduledAction->getConfigurator());

            if ($scheduledAction->getActionType() === \M2E\Kaufland\Model\Product::ACTION_LIST_PRODUCT) {
                $this->actionDispatcherAsync->processList($listingProduct, $params, $statusChanger);
                $this->scheduledActionRepository->remove($scheduledAction);
                continue;
            }

            if ($scheduledAction->getActionType() === \M2E\Kaufland\Model\Product::ACTION_REVISE_PRODUCT) {
                $this->actionDispatcherAsync->processRevise($listingProduct, $params, $statusChanger);
                $this->scheduledActionRepository->remove($scheduledAction);
                continue;
            }

            $this->actionDispatcher->process(
                $scheduledAction->getActionType(),
                $packageCollection,
                $params,
                $statusChanger
            );

            $this->scheduledActionRepository->remove($scheduledAction);
        }
    }

    private function calculateActionsCountLimit(): int
    {
        $maxAllowedActionsCount = (int)$this->config->getGroupValue(
            '/listing/product/scheduled_actions/',
            'max_prepared_actions_count'
        );

        if ($maxAllowedActionsCount <= 0) {
            return 0;
        }

        return $maxAllowedActionsCount;
    }

    /**
     * @return \M2E\Kaufland\Model\ScheduledAction[]
     * @throws \Zend_Db_Select_Exception
     */
    private function getScheduledActionsForProcessing(int $limit): array
    {
        $connection = $this->resourceConnection->getConnection();

        $unionSelect = $connection->select()->union([
            $this->getUnitListScheduledActionsPreparedCollection()->getSelect(),
            $this->getProductListScheduledActionsPreparedCollection()->getSelect(),
            $this->getRelistScheduledActionsPreparedCollection()->getSelect(),
            $this->getReviseQtyScheduledActionsPreparedCollection()->getSelect(),
            $this->getRevisePriceScheduledActionsPreparedCollection()->getSelect(),
            $this->getRevisePartsScheduledActionsPreparedCollection()->getSelect(),
            $this->getReviseShippingScheduledActionsPreparedCollection()->getSelect(),
            $this->getReviseTitleScheduledActionsPreparedCollection()->getSelect(),
            $this->getReviseDescriptionScheduledActionsPreparedCollection()->getSelect(),
            $this->getReviseImagesScheduledActionsPreparedCollection()->getSelect(),
            $this->getReviseCategoriesScheduledActionsPreparedCollection()->getSelect(),
            $this->getStopScheduledActionsPreparedCollection()->getSelect(),
            $this->getDeleteScheduledActionsPreparedCollection()->getSelect(),
        ]);

        $unionSelect->order(['coefficient DESC']);
        $unionSelect->order(['create_date ASC']);

        $unionSelect->distinct(true);
        $unionSelect->limit($limit);

        $scheduledActionsData = $unionSelect->query()->fetchAll();
        if (empty($scheduledActionsData)) {
            return [];
        }

        $scheduledActionsIds = [];
        foreach ($scheduledActionsData as $scheduledActionData) {
            $scheduledActionsIds[] = $scheduledActionData['id'];
        }

        return $this->scheduledActionRepository->getByIds($scheduledActionsIds);
    }

    // ---------------------------------------

    private function getUnitListScheduledActionsPreparedCollection(): ScheduledActionCollection
    {
        return $this->scheduledActionCollectionFactory->create()->getScheduledActionsPreparedCollection(
            self::LIST_PRIORITY,
            \M2E\Kaufland\Model\Product::ACTION_LIST_UNIT
        );
    }

    private function getProductListScheduledActionsPreparedCollection(): ScheduledActionCollection
    {
        return $this->scheduledActionCollectionFactory->create()->getScheduledActionsPreparedCollection(
            self::LIST_PRIORITY,
            \M2E\Kaufland\Model\Product::ACTION_LIST_PRODUCT
        );
    }

    private function getRelistScheduledActionsPreparedCollection(): ScheduledActionCollection
    {
        $collection = $this->scheduledActionCollectionFactory->create();

        $collection->getScheduledActionsPreparedCollection(
            self::RELIST_PRIORITY,
            \M2E\Kaufland\Model\Product::ACTION_RELIST_UNIT
        );

        return $collection;
    }

    private function getReviseQtyScheduledActionsPreparedCollection(): ScheduledActionCollection
    {
        $collection = $this->scheduledActionCollectionFactory->create();

        $collection->getScheduledActionsPreparedCollection(
            self::REVISE_QTY_PRIORITY,
            \M2E\Kaufland\Model\Product::ACTION_REVISE_UNIT
        );
        $collection->addTagFilter('qty');

        return $collection;
    }

    private function getRevisePriceScheduledActionsPreparedCollection(): ScheduledActionCollection
    {
        $collection = $this->scheduledActionCollectionFactory->create();

        $collection->getScheduledActionsPreparedCollection(
            self::REVISE_PRICE_PRIORITY,
            \M2E\Kaufland\Model\Product::ACTION_REVISE_UNIT
        );
        $collection->addTagFilter('price');

        return $collection;
    }

    private function getRevisePartsScheduledActionsPreparedCollection(): ScheduledActionCollection
    {
        $collection = $this->scheduledActionCollectionFactory->create();

        $collection->getScheduledActionsPreparedCollection(
            self::REVISE_PARTS_PRIORITY,
            \M2E\Kaufland\Model\Product::ACTION_REVISE_UNIT
        );
        $collection->addTagFilter('parts');

        return $collection;
    }

    private function getReviseShippingScheduledActionsPreparedCollection(): ScheduledActionCollection
    {
        $collection = $this->scheduledActionCollectionFactory->create();

        $collection->getScheduledActionsPreparedCollection(
            self::REVISE_SHIPPING_PRIORITY,
            \M2E\Kaufland\Model\Product::ACTION_REVISE_UNIT
        );
        $collection->addTagFilter('shipping');

        return $collection;
    }

    private function getReviseTitleScheduledActionsPreparedCollection(): ScheduledActionCollection
    {
        $collection = $this->scheduledActionCollectionFactory->create();

        $collection->getScheduledActionsPreparedCollection(
            self::REVISE_TITLE_PRIORITY,
            \M2E\Kaufland\Model\Product::ACTION_REVISE_PRODUCT
        );
        $collection->addTagFilter(Configurator::DATA_TYPE_TITLE);

        return $collection;
    }

    private function getReviseDescriptionScheduledActionsPreparedCollection(): ScheduledActionCollection
    {
        $collection = $this->scheduledActionCollectionFactory->create();

        $collection->getScheduledActionsPreparedCollection(
            self::REVISE_DESCRIPTION_PRIORITY,
            \M2E\Kaufland\Model\Product::ACTION_REVISE_PRODUCT
        );
        $collection->addTagFilter(Configurator::DATA_TYPE_DESCRIPTION);

        return $collection;
    }

    private function getReviseImagesScheduledActionsPreparedCollection(): ScheduledActionCollection
    {
        $collection = $this->scheduledActionCollectionFactory->create();

        $collection->getScheduledActionsPreparedCollection(
            self::REVISE_IMAGES_PRIORITY,
            \M2E\Kaufland\Model\Product::ACTION_REVISE_PRODUCT
        );
        $collection->addTagFilter(Configurator::DATA_TYPE_IMAGES);

        return $collection;
    }

    private function getReviseCategoriesScheduledActionsPreparedCollection(): ScheduledActionCollection
    {
        $collection = $this->scheduledActionCollectionFactory->create();

        $collection->getScheduledActionsPreparedCollection(
            self::REVISE_CATEGORIES_PRIORITY,
            \M2E\Kaufland\Model\Product::ACTION_REVISE_PRODUCT
        );
        $collection->addTagFilter(Configurator::DATA_TYPE_CATEGORIES);

        return $collection;
    }

    private function getStopScheduledActionsPreparedCollection(): ScheduledActionCollection
    {
        return $this->scheduledActionCollectionFactory
            ->create()
            ->getScheduledActionsPreparedCollection(
                self::STOP_PRIORITY,
                \M2E\Kaufland\Model\Product::ACTION_STOP_UNIT
            );
    }

    private function getDeleteScheduledActionsPreparedCollection(): ScheduledActionCollection
    {
        return $this->scheduledActionCollectionFactory
            ->create()
            ->getScheduledActionsPreparedCollection(
                self::STOP_PRIORITY,
                \M2E\Kaufland\Model\Product::ACTION_DELETE_UNIT
            );
    }
}
