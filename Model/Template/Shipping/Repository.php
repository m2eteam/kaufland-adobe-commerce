<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Template\Shipping;

class Repository
{
    private \M2E\Kaufland\Model\ResourceModel\Template\Shipping $resource;
    private \M2E\Kaufland\Model\ResourceModel\Template\Shipping\CollectionFactory $collectionFactory;
    private \M2E\Kaufland\Model\Template\ShippingFactory $shippingFactory;

    public function __construct(
        \M2E\Kaufland\Model\ResourceModel\Template\Shipping $resource,
        \M2E\Kaufland\Model\ResourceModel\Template\Shipping\CollectionFactory $collectionFactory,
        \M2E\Kaufland\Model\Template\ShippingFactory $shippingFactory
    ) {
        $this->resource = $resource;
        $this->collectionFactory = $collectionFactory;
        $this->shippingFactory = $shippingFactory;
    }

    public function find(int $id): ?\M2E\Kaufland\Model\Template\Shipping
    {
        $model = $this->shippingFactory->create();
        $this->resource->load($model, $id);

        if ($model->isObjectNew()) {
            return null;
        }

        return $model;
    }

    /**
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    public function get(int $id): \M2E\Kaufland\Model\Template\Shipping
    {
        $template = $this->find($id);
        if ($template === null) {
            throw new \M2E\Kaufland\Model\Exception\Logic('Shipping not found');
        }

        return $template;
    }

    public function delete(\M2E\Kaufland\Model\Template\Shipping $template)
    {
        $template->delete();
    }
}
