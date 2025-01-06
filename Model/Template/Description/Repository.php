<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Template\Description;

class Repository
{
    private \M2E\Kaufland\Model\ResourceModel\Template\Description $resource;
    private \M2E\Kaufland\Model\Template\DescriptionFactory $descriptionFactory;
    private \M2E\Kaufland\Model\ResourceModel\Template\Description\CollectionFactory $collectionFactory;

    public function __construct(
        \M2E\Kaufland\Model\ResourceModel\Template\Description $resource,
        \M2E\Kaufland\Model\ResourceModel\Template\Description\CollectionFactory $collectionFactory,
        \M2E\Kaufland\Model\Template\DescriptionFactory $descriptionFactory
    ) {
        $this->resource = $resource;
        $this->descriptionFactory = $descriptionFactory;
        $this->collectionFactory = $collectionFactory;
    }

    public function find(int $id): ?\M2E\Kaufland\Model\Template\Description
    {
        $model = $this->descriptionFactory->create();
        $this->resource->load($model, $id);

        if ($model->isObjectNew()) {
            return null;
        }

        return $model;
    }

    /**
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    public function get(int $id): \M2E\Kaufland\Model\Template\Description
    {
        $template = $this->find($id);
        if ($template === null) {
            throw new \M2E\Kaufland\Model\Exception\Logic('Description not found');
        }

        return $template;
    }

    public function delete(\M2E\Kaufland\Model\Template\Description $template): void
    {
        $this->resource->delete($template);
    }

    public function create(\M2E\Kaufland\Model\Template\Description $template): void
    {
        $this->resource->save($template);
    }

    public function save(\M2E\Kaufland\Model\Template\Description $template): void
    {
        $this->resource->save($template);
    }

    /**
     * @return \M2E\Kaufland\Model\Template\Description[]
     */
    public function getAll(): array
    {
        $collection = $this->collectionFactory->create();

        return array_values($collection->getItems());
    }
}
