<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Category\Dictionary;

class DeleteService
{
    private \Magento\Framework\App\ResourceConnection $resource;
    private \M2E\Kaufland\Model\ResourceModel\Category\Dictionary\CollectionFactory $dictionaryCollectionFactory;
    private \M2E\Kaufland\Model\ResourceModel\Category\Attribute $categoryAttributeResource;
    private \M2E\Kaufland\Model\ResourceModel\Category\Dictionary $categoryDictionaryResource;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \M2E\Kaufland\Model\ResourceModel\Category\Dictionary\CollectionFactory $dictionaryCollectionFactory,
        \M2E\Kaufland\Model\ResourceModel\Category\Attribute $categoryAttributeResource,
        \M2E\Kaufland\Model\ResourceModel\Category\Dictionary $categoryDictionaryResource
    ) {
        $this->resource = $resource;
        $this->dictionaryCollectionFactory = $dictionaryCollectionFactory;
        $this->categoryAttributeResource = $categoryAttributeResource;
        $this->categoryDictionaryResource = $categoryDictionaryResource;
    }

    public function deleteByStorefront(\M2E\Kaufland\Model\Storefront $storefront)
    {
        $connection = $this->resource->getConnection();
        $transaction = $connection->beginTransaction();

        try {
            $this->removeRelatedAttributesByStorefrontId($storefront);
            $connection = $this->resource->getConnection();
            $connection->delete(
                $this->categoryDictionaryResource->getMainTable(),
                [\M2E\Kaufland\Model\ResourceModel\Category\Dictionary::COLUMN_STOREFRONT_ID . ' = ?' => $storefront->getId()]
            );
        } catch (\Throwable $exception) {
            $transaction->rollBack();
        }

        $transaction->commit();
    }

    private function removeRelatedAttributesByStorefrontId(\M2E\Kaufland\Model\Storefront $storefront)
    {
        $dictionaryCollection = $this->dictionaryCollectionFactory->create();
        $dictionaryCollection->addFieldToFilter(
            \M2E\Kaufland\Model\ResourceModel\Category\Dictionary::COLUMN_STOREFRONT_ID,
            ['eq' => $storefront->getId()]
        );

        $select = $dictionaryCollection->getSelect();

        $select->reset(\Magento\Framework\DB\Select::COLUMNS);
        $select->columns(\M2E\Kaufland\Model\ResourceModel\Category\Dictionary::COLUMN_ID);

        $connection = $this->resource->getConnection();
        $connection->delete(
            $this->categoryAttributeResource->getMainTable(),
            [
                sprintf(
                    '%s IN (?)',
                    \M2E\Kaufland\Model\ResourceModel\Category\Attribute::COLUMN_CATEGORY_DICTIONARY_ID
                ) => $dictionaryCollection->getSelect(),
            ]
        );
    }
}
