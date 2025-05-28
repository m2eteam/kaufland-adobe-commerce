<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Listing;

use M2E\Kaufland\Model\ResourceModel\Listing as ListingResource;
use M2E\Kaufland\Model\Template\Description;
use M2E\Kaufland\Model\Template\SellingFormat;
use M2E\Kaufland\Model\Template\Shipping;
use M2E\Kaufland\Model\Template\Synchronization;

class UpdateService
{
    private \M2E\Kaufland\Model\Listing\SnapshotBuilderFactory $listingSnapshotBuilderFactory;
    private \M2E\Kaufland\Model\Listing\Repository $listingRepository;
    private \M2E\Kaufland\Model\Listing\AffectedListingsProductsFactory $affectedListingsProductsFactory;
    private SellingFormat\Repository $sellingFormatTemplateRepository;
    private SellingFormat\SnapshotBuilderFactory $sellingFormatSnapshotBuilderFactory;
    private SellingFormat\DiffFactory $sellingFormatDiffFactory;
    private SellingFormat\ChangeProcessorFactory $sellingFormatChangeProcessorFactory;
    private Synchronization\Repository $synchronizationTemplateRepository;
    private Synchronization\SnapshotBuilderFactory $synchronizationSnapshotBuilderFactory;
    private Synchronization\DiffFactory $synchronizationDiffFactory;
    private Synchronization\ChangeProcessorFactory $synchronizationChangeProcessorFactory;
    /** @var \M2E\Kaufland\Model\Template\Shipping\Repository */
    private Shipping\Repository $shippingTemplateRepository;
    /** @var \M2E\Kaufland\Model\Template\Shipping\SnapshotBuilderFactory */
    private Shipping\SnapshotBuilderFactory $shippingSnapshotBuilderFactory;
    /** @var \M2E\Kaufland\Model\Template\Shipping\DiffFactory */
    private Shipping\DiffFactory $shippingDiffFactory;
    /** @var \M2E\Kaufland\Model\Template\Shipping\ChangeProcessorFactory */
    private Shipping\ChangeProcessorFactory $shippingChangeProcessorFactory;
    /**
     * @var \M2E\Kaufland\Model\Template\Description\Repository
     */
    private Description\Repository $descriptionTemplateRepository;
    /**
     * @var \M2E\Kaufland\Model\Template\Description\SnapshotBuilderFactory
     */
    private Description\SnapshotBuilderFactory $descriptionSnapshotBuilderFactory;
    /**
     * @var \M2E\Kaufland\Model\Template\Description\DiffFactory
     */
    private Description\DiffFactory $descriptionDiffFactory;
    /**
     * @var \M2E\Kaufland\Model\Template\Description\ChangeProcessorFactory
     */
    private Description\ChangeProcessorFactory $descriptionChangeProcessorFactory;
    private \M2E\Kaufland\Model\Product\Repository $productRepository;

    public function __construct(
        \M2E\Kaufland\Model\Product\Repository $productRepository,
        \M2E\Kaufland\Model\Listing\Repository $listingRepository,
        \M2E\Kaufland\Model\Listing\SnapshotBuilderFactory $listingSnapshotBuilderFactory,
        \M2E\Kaufland\Model\Listing\AffectedListingsProductsFactory $affectedListingsProductsFactory,
        SellingFormat\Repository $sellingFormatTemplateRepository,
        SellingFormat\SnapshotBuilderFactory $sellingFormatSnapshotBuilderFactory,
        SellingFormat\DiffFactory $sellingFormatDiffFactory,
        SellingFormat\ChangeProcessorFactory $sellingFormatChangeProcessorFactory,
        Synchronization\Repository $synchronizationTemplateRepository,
        Synchronization\SnapshotBuilderFactory $synchronizationSnapshotBuilderFactory,
        Synchronization\DiffFactory $synchronizationDiffFactory,
        Synchronization\ChangeProcessorFactory $synchronizationChangeProcessorFactory,
        Shipping\Repository $shippingTemplateRepository,
        Shipping\SnapshotBuilderFactory $shippingSnapshotBuilderFactory,
        Shipping\DiffFactory $shippingDiffFactory,
        Shipping\ChangeProcessorFactory $shippingChangeProcessorFactory,
        Description\Repository $descriptionTemplateRepository,
        Description\SnapshotBuilderFactory $descriptionSnapshotBuilderFactory,
        Description\DiffFactory $descriptionDiffFactory,
        Description\ChangeProcessorFactory $descriptionChangeProcessorFactory
    ) {
        $this->listingSnapshotBuilderFactory = $listingSnapshotBuilderFactory;
        $this->listingRepository = $listingRepository;
        $this->affectedListingsProductsFactory = $affectedListingsProductsFactory;
        $this->sellingFormatTemplateRepository = $sellingFormatTemplateRepository;
        $this->sellingFormatSnapshotBuilderFactory = $sellingFormatSnapshotBuilderFactory;
        $this->sellingFormatDiffFactory = $sellingFormatDiffFactory;
        $this->sellingFormatChangeProcessorFactory = $sellingFormatChangeProcessorFactory;
        $this->synchronizationTemplateRepository = $synchronizationTemplateRepository;
        $this->synchronizationSnapshotBuilderFactory = $synchronizationSnapshotBuilderFactory;
        $this->synchronizationDiffFactory = $synchronizationDiffFactory;
        $this->synchronizationChangeProcessorFactory = $synchronizationChangeProcessorFactory;
        $this->shippingTemplateRepository = $shippingTemplateRepository;
        $this->shippingSnapshotBuilderFactory = $shippingSnapshotBuilderFactory;
        $this->shippingDiffFactory = $shippingDiffFactory;
        $this->shippingChangeProcessorFactory = $shippingChangeProcessorFactory;
        $this->descriptionTemplateRepository = $descriptionTemplateRepository;
        $this->descriptionSnapshotBuilderFactory = $descriptionSnapshotBuilderFactory;
        $this->descriptionDiffFactory = $descriptionDiffFactory;
        $this->descriptionChangeProcessorFactory = $descriptionChangeProcessorFactory;
        $this->productRepository = $productRepository;
    }

    /**
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    public function update(\M2E\Kaufland\Model\Listing $listing, array $post)
    {
        $isNeedProcessChangesSellingFormatTemplate = false;
        $isNeedProcessChangesSynchronizationTemplate = false;
        $isNeedProcessChangesShippingTemplate = false;
        $isNeedProcessChangesDescriptionTemplate = false;
        $isNeedProcessChangesConditionValue = false;
        $isNeedProcessChangesSkuSettings = false;

        $oldListingSnapshot = $this->makeListingSnapshot($listing);

        $settingsData = $post['sku_settings'];

        $skuSettings = $this->createSkuSettings($settingsData);
        $listingSkuSettings = $listing->getSkuSettings();

        if (!\M2E\Kaufland\Model\Listing\Settings\Sku::isEqual($listingSkuSettings, $skuSettings)) {
            $isNeedProcessChangesSkuSettings = true;
        }

        $newTemplateSellingFormatId = $post[ListingResource::COLUMN_TEMPLATE_SELLING_FORMAT_ID] ?? null;
        if (
            $newTemplateSellingFormatId !== null
            && $listing->getTemplateSellingFormatId() !== (int)$newTemplateSellingFormatId
        ) {
            $listing->setTemplateSellingFormatId((int)$newTemplateSellingFormatId);
            $isNeedProcessChangesSellingFormatTemplate = true;
        }

        $newTemplateSynchronizationId = $post[ListingResource::COLUMN_TEMPLATE_SYNCHRONIZATION_ID] ?? null;
        if (
            $newTemplateSynchronizationId !== null
            && $listing->getTemplateSynchronizationId() !== (int)$newTemplateSynchronizationId
        ) {
            $listing->setTemplateSynchronizationId((int)$newTemplateSynchronizationId);
            $isNeedProcessChangesSynchronizationTemplate = true;
        }

        $newTemplateShippingId = $post[ListingResource::COLUMN_TEMPLATE_SHIPPING_ID] ?? null;
        if (
            $newTemplateShippingId !== null
            && $listing->getTemplateShippingId() !== (int)$newTemplateShippingId
        ) {
            $listing->setTemplateShippingId((int)$newTemplateShippingId);
            $isNeedProcessChangesShippingTemplate = true;
        }

        $newTemplateDescriptionId = $post[ListingResource::COLUMN_TEMPLATE_DESCRIPTION_ID] ?? null;
        if (
            $newTemplateDescriptionId !== null
            && $listing->getTemplateDescriptionId() !== (int)$newTemplateDescriptionId
        ) {
            $listing->setTemplateDescriptionId((int)$newTemplateDescriptionId);
            $isNeedProcessChangesDescriptionTemplate = true;
        }

        $newConditionValue = $post[ListingResource::COLUMN_CONDITION_VALUE] ?? null;
        if (
            $newConditionValue !== null
            && $listing->getConditionValue() !== $newConditionValue
        ) {
            $listing->setConditionValue($newConditionValue);
            $isNeedProcessChangesConditionValue = true;
        }

        if (
            $isNeedProcessChangesSellingFormatTemplate === false
            && $isNeedProcessChangesSynchronizationTemplate === false
            && $isNeedProcessChangesShippingTemplate === false
            && $isNeedProcessChangesDescriptionTemplate === false
            && $isNeedProcessChangesConditionValue === false
            && $isNeedProcessChangesSkuSettings === false
        ) {
            return;
        }

        $listing->setSkuSettings($skuSettings);
        $this->listingRepository->save($listing);

        if ($isNeedProcessChangesSkuSettings) {
            $this->processChangeSkuSettings($listing);
        }

        $newListingSnapshot = $this->makeListingSnapshot($listing);

        $affectedListingsProducts = $this->affectedListingsProductsFactory->create();
        $affectedListingsProducts->setModel($listing);

        if ($isNeedProcessChangesSellingFormatTemplate) {
            $this->processChangeSellingFormatTemplate(
                (int)$oldListingSnapshot[ListingResource::COLUMN_TEMPLATE_SELLING_FORMAT_ID],
                (int)$newListingSnapshot[ListingResource::COLUMN_TEMPLATE_SELLING_FORMAT_ID],
                $affectedListingsProducts
            );
        }

        if ($isNeedProcessChangesSynchronizationTemplate) {
            $this->processChangeSynchronizationTemplate(
                (int)$oldListingSnapshot[ListingResource::COLUMN_TEMPLATE_SYNCHRONIZATION_ID],
                (int)$newListingSnapshot[ListingResource::COLUMN_TEMPLATE_SYNCHRONIZATION_ID],
                $affectedListingsProducts
            );
        }

        if ($isNeedProcessChangesShippingTemplate) {
            $this->processChangeShippingTemplate(
                (int)$oldListingSnapshot[ListingResource::COLUMN_TEMPLATE_SHIPPING_ID],
                (int)$newListingSnapshot[ListingResource::COLUMN_TEMPLATE_SHIPPING_ID],
                $affectedListingsProducts
            );
        }

        if ($isNeedProcessChangesDescriptionTemplate) {
            $this->processChangeDescriptionTemplate(
                (int)$oldListingSnapshot[ListingResource::COLUMN_TEMPLATE_DESCRIPTION_ID],
                (int)$newListingSnapshot[ListingResource::COLUMN_TEMPLATE_DESCRIPTION_ID],
                $affectedListingsProducts
            );
        }
    }

    private function createSkuSettings(array $settingsData): \M2E\Kaufland\Model\Listing\Settings\Sku
    {
        $skuSettings = new \M2E\Kaufland\Model\Listing\Settings\Sku();
        return $skuSettings
            ->createWithSkuMode((int)$settingsData['sku_mode'])
            ->createWithSkuCustomAttribute($settingsData['sku_custom_attribute'])
            ->createWithSkuModificationMode((int)$settingsData['sku_modification_mode'])
            ->createWithSkuModificationCustomValue($settingsData['sku_modification_custom_value'])
            ->createWithGenerateSkuMode((int)$settingsData['generate_sku_mode']);
    }

    private function makeListingSnapshot(\M2E\Kaufland\Model\Listing $listing)
    {
        $snapshotBuilder = $this->listingSnapshotBuilderFactory->create();
        $snapshotBuilder->setModel($listing);

        return $snapshotBuilder->getSnapshot();
    }

    private function processChangeSkuSettings(\M2E\Kaufland\Model\Listing $listing): void
    {
        $this->productRepository->cleanOfferIdForNotListed($listing->getId());
    }

    /**
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    private function processChangeSellingFormatTemplate(
        int $oldId,
        int $newId,
        \M2E\Kaufland\Model\Listing\AffectedListingsProducts $affectedListingsProducts
    ) {
        $oldTemplate = $this->sellingFormatTemplateRepository->get($oldId);
        $newTemplate = $this->sellingFormatTemplateRepository->get($newId);

        $oldTemplateData = $this->makeSellingFormatTemplateSnapshot($oldTemplate);
        $newTemplateData = $this->makeSellingFormatTemplateSnapshot($newTemplate);

        $diff = $this->sellingFormatDiffFactory->create();
        $diff->setOldSnapshot($oldTemplateData);
        $diff->setNewSnapshot($newTemplateData);

        $changeProcessor = $this->sellingFormatChangeProcessorFactory->create();

        $affectedProducts = $affectedListingsProducts->getObjectsData(
            ['id', 'status'],
            ['template' => \M2E\Kaufland\Model\Template\Manager::TEMPLATE_SELLING_FORMAT]
        );
        $changeProcessor->process($diff, $affectedProducts);
    }

    private function makeSellingFormatTemplateSnapshot(SellingFormat $sellingFormatTemplate)
    {
        $snapshotBuilder = $this->sellingFormatSnapshotBuilderFactory->create();
        $snapshotBuilder->setModel($sellingFormatTemplate);

        return $snapshotBuilder->getSnapshot();
    }

    /**
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    private function processChangeSynchronizationTemplate(
        int $oldId,
        int $newId,
        \M2E\Kaufland\Model\Listing\AffectedListingsProducts $affectedListingsProducts
    ) {
        $oldTemplate = $this->synchronizationTemplateRepository->get($oldId);
        $newTemplate = $this->synchronizationTemplateRepository->get($newId);

        $oldTemplateData = $this->makeSynchronizationTemplateSnapshot($oldTemplate);
        $newTemplateData = $this->makeSynchronizationTemplateSnapshot($newTemplate);

        $diff = $this->synchronizationDiffFactory->create();
        $diff->setOldSnapshot($oldTemplateData);
        $diff->setNewSnapshot($newTemplateData);

        $changeProcessor = $this->synchronizationChangeProcessorFactory->create();

        $affectedProducts = $affectedListingsProducts->getObjectsData(
            ['id', 'status'],
            ['template' => \M2E\Kaufland\Model\Template\Manager::TEMPLATE_SYNCHRONIZATION]
        );
        $changeProcessor->process($diff, $affectedProducts);
    }

    private function processChangeShippingTemplate(
        int $oldId,
        int $newId,
        \M2E\Kaufland\Model\Listing\AffectedListingsProducts $affectedListingsProducts
    ) {
        $oldTemplate = $this->shippingTemplateRepository->find($oldId);
        $newTemplate = $this->shippingTemplateRepository->get($newId);

        if ($oldTemplate === null) {
            $oldTemplateData = [];
        } else {
            $oldTemplateData = $this->makeShippingTemplateSnapshot($oldTemplate);
        }

        $newTemplateData = $this->makeShippingTemplateSnapshot($newTemplate);

        $diff = $this->shippingDiffFactory->create();
        $diff->setOldSnapshot($oldTemplateData);
        $diff->setNewSnapshot($newTemplateData);

        $changeProcessor = $this->shippingChangeProcessorFactory->create();

        $affectedProducts = $affectedListingsProducts->getObjectsData(
            ['id', 'status'],
            ['template' => \M2E\Kaufland\Model\Template\Manager::TEMPLATE_SHIPPING]
        );
        $changeProcessor->process($diff, $affectedProducts);
    }

    private function processChangeDescriptionTemplate(
        int $oldId,
        int $newId,
        \M2E\Kaufland\Model\Listing\AffectedListingsProducts $affectedListingsProducts
    ) {
        $oldTemplate = $this->descriptionTemplateRepository->find($oldId);
        $newTemplate = $this->descriptionTemplateRepository->get($newId);

        if ($oldTemplate === null) {
            $oldTemplateData = [];
        } else {
            $oldTemplateData = $this->makeDescriptionTemplateSnapshot($oldTemplate);
        }

        $newTemplateData = $this->makeDescriptionTemplateSnapshot($newTemplate);

        $diff = $this->descriptionDiffFactory->create();
        $diff->setOldSnapshot($oldTemplateData);
        $diff->setNewSnapshot($newTemplateData);

        $changeProcessor = $this->descriptionChangeProcessorFactory->create();

        $affectedProducts = $affectedListingsProducts->getObjectsData(
            ['id', 'status'],
            ['template' => \M2E\Kaufland\Model\Template\Manager::TEMPLATE_DESCRIPTION]
        );
        $changeProcessor->process($diff, $affectedProducts);
    }

    private function makeSynchronizationTemplateSnapshot(Synchronization $synchronizationTemplate)
    {
        $snapshotBuilder = $this->synchronizationSnapshotBuilderFactory->create();
        $snapshotBuilder->setModel($synchronizationTemplate);

        return $snapshotBuilder->getSnapshot();
    }

    private function makeShippingTemplateSnapshot(Shipping $shippingTemplate)
    {
        $snapshotBuilder = $this->shippingSnapshotBuilderFactory->create();
        $snapshotBuilder->setModel($shippingTemplate);

        return $snapshotBuilder->getSnapshot();
    }

    private function makeDescriptionTemplateSnapshot(Description $descriptionTemplate)
    {
        $snapshotBuilder = $this->descriptionSnapshotBuilderFactory->create();
        $snapshotBuilder->setModel($descriptionTemplate);

        return $snapshotBuilder->getSnapshot();
    }
}
