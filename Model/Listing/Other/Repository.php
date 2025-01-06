<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Listing\Other;

use M2E\Kaufland\Model\ResourceModel\Listing\Other as ListingOtherResource;
use M2E\Kaufland\Model\ResourceModel\ExternalChange as ExternalChangeResource;
use Magento\Ui\Component\MassAction\Filter as MassActionFilter;

class Repository
{
    private \M2E\Kaufland\Model\ResourceModel\Listing\Other\CollectionFactory $collectionFactory;
    private \M2E\Kaufland\Model\ResourceModel\Listing\Other $resource;
    private \M2E\Kaufland\Model\Listing\OtherFactory $objectFactory;
    private \M2E\Kaufland\Model\ResourceModel\ExternalChange $externalChangeResource;
    private \M2E\Kaufland\Helper\Module\Database\Structure $dbStructureHelper;

    public function __construct(
        \M2E\Kaufland\Model\ResourceModel\Listing\Other\CollectionFactory $collectionFactory,
        \M2E\Kaufland\Model\ResourceModel\Listing\Other $resource,
        \M2E\Kaufland\Model\ResourceModel\ExternalChange $externalChangeResource,
        \M2E\Kaufland\Model\Listing\OtherFactory $objectFactory,
        \M2E\Kaufland\Helper\Module\Database\Structure $dbStructureHelper
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->resource = $resource;
        $this->objectFactory = $objectFactory;
        $this->externalChangeResource = $externalChangeResource;
        $this->dbStructureHelper = $dbStructureHelper;
    }

    public function createCollection(): \M2E\Kaufland\Model\ResourceModel\Listing\Other\Collection
    {
        return $this->collectionFactory->create();
    }

    /**
     * @param \M2E\Kaufland\Model\Listing\Other[] $data
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function create(\M2E\Kaufland\Model\Listing\Other $other)
    {
        $this->resource->save($other);
    }

    public function save(\M2E\Kaufland\Model\Listing\Other $listingOther): void
    {
        $this->resource->save($listingOther);
    }

    /**
     * @throws \M2E\Kaufland\Model\Exception
     */
    public function get(int $id): \M2E\Kaufland\Model\Listing\Other
    {
        $obj = $this->objectFactory->create();
        $this->resource->load($obj, $id);

        if ($obj->isObjectNew()) {
            throw new \M2E\Kaufland\Model\Exception("Object by id $id not found.");
        }

        return $obj;
    }

    public function remove(\M2E\Kaufland\Model\Listing\Other $other): void
    {
        $this->resource->delete($other);
    }

    /**
     * @return \M2E\Kaufland\Model\Listing\Other[]
     */
    public function findByIds(array $ids): array
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(
            ListingOtherResource::COLUMN_ID,
            ['in' => $ids],
        );

        return array_values($collection->getItems());
    }

    /**
     * @param int $id
     *
     * @return \M2E\Kaufland\Model\Listing\Other|null
     */
    public function findById(int $id): ?\M2E\Kaufland\Model\Listing\Other
    {
        $obj = $this->objectFactory->create();
        $this->resource->load($obj, $id);

        if ($obj->isObjectNew()) {
            return null;
        }

        return $obj;
    }

    public function findByMagentoProductId(int $magentoProductId): array
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(
            ListingOtherResource::COLUMN_MAGENTO_PRODUCT_ID,
            ['eq' => $magentoProductId]
        );

        return array_values($collection->getItems());
    }

    public function findByOfferIds(array $offerIds, int $accountId, int $storefrontId): array
    {
        $collection = $this->collectionFactory->create();
        $collection
            ->addFieldToFilter(
                \M2E\Kaufland\Model\ResourceModel\Listing\Other::COLUMN_OFFER_ID,
                ['in' => $offerIds],
            )
            ->addFieldToFilter(ListingOtherResource::COLUMN_ACCOUNT_ID, $accountId)
            ->addFieldToFilter(ListingOtherResource::COLUMN_STOREFRONT_ID, $storefrontId);

        return array_values($collection->getItems());
    }

    public function removeByAccountId(int $accountId)
    {
        $collection = $this->collectionFactory->create();
        $collection->getConnection()->delete(
            $collection->getMainTable(),
            ['account_id = ?' => $accountId],
        );
    }

    /**
     * @param string $offerId
     * @param int $storefrontId
     *
     * @return \M2E\Kaufland\Model\Listing\Other|null
     */
    public function getByOfferIdAndStorefrontId(string $offerId, int $storefrontId): ?\M2E\Kaufland\Model\Listing\Other
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(ListingOtherResource::COLUMN_OFFER_ID, $offerId);
        $collection->addFieldToFilter(ListingOtherResource::COLUMN_STOREFRONT_ID, $storefrontId);

        $item = $collection->getFirstItem();
        if (!$item->getId()) {
            return null;
        }

        return $item;
    }

    /**
     * @param int $accountId
     * @param int $storefrontId
     *
     * @return \M2E\Kaufland\Model\Listing\Other[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function findRemovedFromChannel(int $accountId, int $storefrontId): array
    {
        $joinConditions = [
            sprintf(
                '`ec`.%s = `main_table`.%s',
                ExternalChangeResource::COLUMN_UNIT_ID,
                ListingOtherResource::COLUMN_UNIT_ID,
            ),
            sprintf(
                '`ec`.%s = `main_table`.%s',
                ExternalChangeResource::COLUMN_ACCOUNT_ID,
                ListingOtherResource::COLUMN_ACCOUNT_ID,
            ),
            sprintf(
                '`ec`.%s = `main_table`.%s',
                ExternalChangeResource::COLUMN_STOREFRONT_ID,
                ListingOtherResource::COLUMN_STOREFRONT_ID,
            ),
        ];

        $collection = $this->collectionFactory->create();
        $collection->joinLeft(
            [
                'ec' => $this->externalChangeResource->getMainTable(),
            ],
            implode(' AND ', $joinConditions),
            [],
        );

        $collection
            ->addFieldToFilter(sprintf('main_table.%s', ListingOtherResource::COLUMN_ACCOUNT_ID), $accountId)
            ->addFieldToFilter(sprintf('main_table.%s', ListingOtherResource::COLUMN_STOREFRONT_ID), $storefrontId)
            ->addFieldToFilter('ec.id', ['null' => true]);

        return array_values($collection->getItems());
    }

    /**
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     *
     * @return \M2E\Kaufland\Model\Listing\Other[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function findForMovingByMassActionSelectedProducts(MassActionFilter $filter): array
    {
        $collection = $this->collectionFactory->create();
        $filter->getCollection($collection);

        $collection->addFieldToFilter(
            ListingOtherResource::COLUMN_MAGENTO_PRODUCT_ID,
            ['notnull' => true]
        );

        return array_values($collection->getItems());
    }

    /**
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     *
     * @return \M2E\Kaufland\Model\Listing\Other[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function findForAutoMappingByMassActionSelectedProducts(MassActionFilter $filter): array
    {
        $collection = $this->collectionFactory->create();
        $filter->getCollection($collection);

        $collection->addFieldToFilter(
            ListingOtherResource::COLUMN_MAGENTO_PRODUCT_ID,
            ['null' => true]
        );

        return array_values($collection->getItems());
    }

    /**
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     *
     * @return \M2E\Kaufland\Model\Listing\Other[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function findForUnmappingByMassActionSelectedProducts(MassActionFilter $filter): array
    {
        $collection = $this->collectionFactory->create();
        $filter->getCollection($collection);

        $collection->addFieldToFilter(
            ListingOtherResource::COLUMN_MAGENTO_PRODUCT_ID,
            ['notnull' => true]
        );

        return array_values($collection->getItems());
    }

    /**
     * @param array $ids
     *
     * @return array|bool
     * @throws \Zend_Db_Statement_Exception
     */
    public function findPrepareMoveToListingByIds(array $ids)
    {
        $listingOtherCollection = $this->collectionFactory->create();
        $listingOtherCollection->addFieldToFilter('id', ['in' => $ids]);
        $listingOtherCollection->addFieldToFilter(
            \M2E\Kaufland\Model\ResourceModel\Listing\Other::COLUMN_MAGENTO_PRODUCT_ID,
            ['notnull' => true]
        );

        $listingOtherCollection->getSelect()->join(
            ['cpe' => $this->dbStructureHelper->getTableNameWithPrefix('catalog_product_entity')],
            'magento_product_id = cpe.entity_id'
        );

        return $listingOtherCollection
            ->getSelect()
            ->reset(\Magento\Framework\DB\Select::COLUMNS)
            ->group(['account_id', 'storefront_id'])
            ->columns(['account_id', 'storefront_id'])
            ->query()
            ->fetch();
    }

    public function isExistForAccountId(int $accountId): bool
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(\M2E\Kaufland\Model\ResourceModel\Listing\Other::COLUMN_ACCOUNT_ID, $accountId);

        return (int)$collection->getSize() > 0;
    }
}
