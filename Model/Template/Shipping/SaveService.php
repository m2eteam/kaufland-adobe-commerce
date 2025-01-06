<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Template\Shipping;

use M2E\Kaufland\Model\Template\Shipping;

class SaveService
{
    private \M2E\Kaufland\Model\Template\ShippingFactory $shippingFactory;
    private Shipping\Repository $shippingRepository;
    private Shipping\SnapshotBuilderFactory $shippingSnapshotBuilderFactory;
    private Shipping\BuilderFactory $builderFactory;
    private Shipping\DiffFactory $diffFactory;
    private Shipping\AffectedListingsProductsFactory $affectedProductsFactory;
    private Shipping\ChangeProcessorFactory $changeProcessorFactory;

    public function __construct(
        \M2E\Kaufland\Model\Template\ShippingFactory $shippingFactory,
        Shipping\ChangeProcessorFactory $changeProcessorFactory,
        Shipping\AffectedListingsProductsFactory $affectedProductsFactory,
        Shipping\DiffFactory $diffFactory,
        Shipping\BuilderFactory $builderFactory,
        Shipping\Repository $shippingRepository,
        Shipping\SnapshotBuilderFactory $shippingSnapshotBuilderFactory
    ) {
        $this->shippingFactory = $shippingFactory;
        $this->changeProcessorFactory = $changeProcessorFactory;
        $this->affectedProductsFactory = $affectedProductsFactory;
        $this->diffFactory = $diffFactory;
        $this->builderFactory = $builderFactory;
        $this->shippingRepository = $shippingRepository;
        $this->shippingSnapshotBuilderFactory = $shippingSnapshotBuilderFactory;
    }

    public function save(array $data)
    {
        $templateModel = $this->shippingFactory->create();

        if (empty($data['id'])) {
            $oldData = [];
        } else {
            $templateModel = $this->shippingRepository->get((int)$data['id']);
            $oldData = $this->makeSnapshot($templateModel);
        }

        $templateBuilder = $this->builderFactory->create();

        $template = $templateBuilder->build($templateModel, $data);

        $snapshotBuilder = $this->shippingSnapshotBuilderFactory->create();
        $snapshotBuilder->setModel($template);

        $newData = $this->makeSnapshot($template);

        $diff = $this->diffFactory->create();

        $diff->setNewSnapshot($newData);
        $diff->setOldSnapshot($oldData);

        $affectedListingsProducts = $this->affectedProductsFactory->create();
        $affectedListingsProducts->setModel($template);

        $changeProcessor = $this->changeProcessorFactory->create();
        $templateId = $template->getId();

        $changeProcessor->process(
            $diff,
            $affectedListingsProducts->getObjectsDataByTemplateShippingId(['id', 'status'], $templateId)
        );

        return $template;
    }

    private function makeSnapshot($model)
    {
        $snapshotBuilder = $this->shippingSnapshotBuilderFactory->create();
        $snapshotBuilder->setModel($model);

        return $snapshotBuilder->getSnapshot();
    }
}
