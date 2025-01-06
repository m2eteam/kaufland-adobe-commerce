<?php

namespace M2E\Kaufland\Model\Template\SellingFormat;

class Repository
{
    private \M2E\Kaufland\Model\ResourceModel\Template\SellingFormat $resource;
    private \M2E\Kaufland\Model\ResourceModel\Template\SellingFormat\CollectionFactory $collectionFactory;
    private \M2E\Kaufland\Model\Template\SellingFormatFactory $sellingFormatFactory;

    public function __construct(
        \M2E\Kaufland\Model\ResourceModel\Template\SellingFormat $resource,
        \M2E\Kaufland\Model\ResourceModel\Template\SellingFormat\CollectionFactory $collectionFactory,
        \M2E\Kaufland\Model\Template\SellingFormatFactory $sellingFormatFactory
    ) {
        $this->resource = $resource;
        $this->collectionFactory = $collectionFactory;
        $this->sellingFormatFactory = $sellingFormatFactory;
    }

    public function find(int $id): ?\M2E\Kaufland\Model\Template\SellingFormat
    {
        $model = $this->sellingFormatFactory->create();
        $this->resource->load($model, $id);

        if ($model->isObjectNew()) {
            return null;
        }

        return $model;
    }

    /**
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    public function get(int $id): \M2E\Kaufland\Model\Template\SellingFormat
    {
        $template = $this->find($id);
        if ($template === null) {
            throw new \M2E\Kaufland\Model\Exception\Logic('Synchronization not found');
        }

        return $template;
    }

    public function delete(\M2E\Kaufland\Model\Template\SellingFormat $template)
    {
        $template->delete();
    }

    public function getAll(): array
    {
        $collection = $this->collectionFactory->create();

        return array_values($collection->getItems());
    }
}
