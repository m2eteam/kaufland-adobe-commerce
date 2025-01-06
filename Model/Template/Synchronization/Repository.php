<?php

namespace M2E\Kaufland\Model\Template\Synchronization;

class Repository
{
    private \M2E\Kaufland\Model\ResourceModel\Template\Synchronization $resource;
    private \M2E\Kaufland\Model\ResourceModel\Template\Synchronization\CollectionFactory $collectionFactory;
    private \M2E\Kaufland\Model\Template\SynchronizationFactory $synchronizationFactory;

    public function __construct(
        \M2E\Kaufland\Model\ResourceModel\Template\Synchronization $resource,
        \M2E\Kaufland\Model\ResourceModel\Template\Synchronization\CollectionFactory $collectionFactory,
        \M2E\Kaufland\Model\Template\SynchronizationFactory $synchronizationFactory
    ) {
        $this->resource = $resource;
        $this->collectionFactory = $collectionFactory;
        $this->synchronizationFactory = $synchronizationFactory;
    }

    public function find(int $id): ?\M2E\Kaufland\Model\Template\Synchronization
    {
        $model = $this->synchronizationFactory->create();
        $this->resource->load($model, $id);

        if ($model->isObjectNew()) {
            return null;
        }

        return $model;
    }

    /**
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    public function get(int $id): \M2E\Kaufland\Model\Template\Synchronization
    {
        $template = $this->find($id);
        if ($template === null) {
            throw new \M2E\Kaufland\Model\Exception\Logic('Synchronization policy does not exist.');
        }

        return $template;
    }

    public function delete(\M2E\Kaufland\Model\Template\Synchronization $template)
    {
        $template->delete();
    }

    public function getAll(): array
    {
        $collection = $this->collectionFactory->create();

        return array_values($collection->getItems());
    }
}
