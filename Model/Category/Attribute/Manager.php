<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Category\Attribute;

class Manager
{
    private \M2E\Kaufland\Model\Category\Dictionary\Repository $categoryDictionaryRepository;
    private \M2E\Kaufland\Model\Category\Attribute\Repository $categoryAttributeRepository;
    private \Magento\Framework\App\ResourceConnection $resource;
    private \M2E\Kaufland\Model\Kaufland\Template\Category\SnapshotBuilderFactory $snapshotBuilderFactory;
    private \M2E\Kaufland\Model\Kaufland\Template\Category\DiffFactory $diffFactory;
    private \M2E\Kaufland\Model\Kaufland\Template\Category\ChangeProcessorFactory $changeProcessorFactory;
    private \M2E\Kaufland\Model\Kaufland\Template\Category\AffectedListingsProductsFactory $affectedListingsProductsFactory;

    public function __construct(
        \M2E\Kaufland\Model\Category\Dictionary\Repository $categoryDictionaryRepository,
        \M2E\Kaufland\Model\Category\Attribute\Repository $categoryAttributeRepository,
        \Magento\Framework\App\ResourceConnection $resource,
        \M2E\Kaufland\Model\Kaufland\Template\Category\SnapshotBuilderFactory $snapshotBuilderFactory,
        \M2E\Kaufland\Model\Kaufland\Template\Category\DiffFactory $diffFactory,
        \M2E\Kaufland\Model\Kaufland\Template\Category\ChangeProcessorFactory $changeProcessorFactory,
        \M2E\Kaufland\Model\Kaufland\Template\Category\AffectedListingsProductsFactory $affectedListingsProductsFactory
    ) {
        $this->categoryDictionaryRepository = $categoryDictionaryRepository;
        $this->categoryAttributeRepository = $categoryAttributeRepository;
        $this->resource = $resource;
        $this->snapshotBuilderFactory = $snapshotBuilderFactory;
        $this->diffFactory = $diffFactory;
        $this->changeProcessorFactory = $changeProcessorFactory;
        $this->affectedListingsProductsFactory = $affectedListingsProductsFactory;
    }

    /**
     * @param \M2E\Kaufland\Model\Category\Attribute[] $attributes
     * @param \M2E\Kaufland\Model\Category\Dictionary $dictionary
     *
     * @return void
     * @throws \Exception
     */
    public function createOrUpdateAttributes(
        array $attributes,
        \M2E\Kaufland\Model\Category\Dictionary $dictionary
    ): void {
        $attributesSortedById = [];
        $countOfUsedAttributes = 0;

        foreach ($attributes as $attribute) {
            $attributesSortedById[$attribute->getAttributeId()] = $attribute;
            if (
                !empty($attribute->getCustomValue())
                || !empty($attribute->getCustomAttributeValue())
                || !empty($attribute->getRecommendedValue())
            ) {
                $countOfUsedAttributes++;
            }
        }

        $transaction = $this->resource->getConnection()->beginTransaction();
        try {
            $oldSnapshot = $this->getSnapshot($dictionary);

            $existedAttributes = $this->categoryAttributeRepository
                ->findByDictionaryId($dictionary->getId());

            foreach ($existedAttributes as $existedAttribute) {
                $inputAttribute = $attributesSortedById[$existedAttribute->getAttributeId()] ?? null;
                if ($inputAttribute === null) {
                    continue;
                }

                $this->updateAttribute($existedAttribute, $inputAttribute);
                unset($attributesSortedById[$existedAttribute->getAttributeId()]);
            }

            foreach ($attributesSortedById as $attribute) {
                $this->createAttribute($attribute);
            }

            $newSnapshot = $this->getSnapshot($dictionary);

            $this->addInstruction($dictionary, $oldSnapshot, $newSnapshot);

            $dictionary->setUsedProductAttributes($countOfUsedAttributes);
            $dictionary->installStateSaved();
            $this->categoryDictionaryRepository->save($dictionary);
        } catch (\Throwable $exception) {
            $transaction->rollBack();
            throw $exception;
        }

        $transaction->commit();
    }

    private function updateAttribute(
        \M2E\Kaufland\Model\Category\Attribute $existedAttribute,
        \M2E\Kaufland\Model\Category\Attribute $inputAttribute
    ) {
        $existedAttribute->setCategoryDictionaryId($inputAttribute->getCategoryDictionaryId());
        $existedAttribute->setAttributeType($inputAttribute->getAttributeType());
        $existedAttribute->setAttributeId($inputAttribute->getAttributeId());
        $existedAttribute->setAttributeTitle($inputAttribute->getAttributeTitle());
        $existedAttribute->setAttributeNick($inputAttribute->getAttributeNick());
        $existedAttribute->setAttributeDescription($inputAttribute->getAttributeDescription());
        $existedAttribute->setValueMode($inputAttribute->getValueMode());
        $existedAttribute->setRecommendedValue($inputAttribute->getRecommendedValue());
        $existedAttribute->setCustomValue($inputAttribute->getCustomValue());
        $existedAttribute->setCustomAttributeValue($inputAttribute->getCustomAttributeValue());

        $this->categoryAttributeRepository->save($existedAttribute);
    }

    private function createAttribute(\M2E\Kaufland\Model\Category\Attribute $attribute)
    {
        $this->categoryAttributeRepository->create($attribute);
    }

    private function getSnapshot(\M2E\Kaufland\Model\Category\Dictionary $dictionary): array
    {
        return $this->snapshotBuilderFactory
            ->create()
            ->setModel($dictionary)
            ->getSnapshot();
    }

    private function addInstruction(
        \M2E\Kaufland\Model\Category\Dictionary $dictionary,
        array $oldSnapshot,
        array $newSnapshot
    ): void {
        $diff = $this->diffFactory->create();
        $diff->setOldSnapshot($oldSnapshot);
        $diff->setNewSnapshot($newSnapshot);

        $affectedListingsProducts = $this->affectedListingsProductsFactory->create();
        $affectedListingsProducts->setModel($dictionary);

        $changeProcessor = $this->changeProcessorFactory->create();
        $changeProcessor->process(
            $diff,
            $affectedListingsProducts->getObjectsData(['id', 'status'])
        );
    }
}
