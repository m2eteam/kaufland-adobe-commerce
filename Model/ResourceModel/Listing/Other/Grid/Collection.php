<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\ResourceModel\Listing\Other\Grid;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection implements
    \Magento\Framework\Api\Search\SearchResultInterface
{
    use \M2E\Kaufland\Model\ResourceModel\SearchResultTrait;

    protected $_idFieldName = 'id';
    private \M2E\Kaufland\Model\ResourceModel\Storefront $storefrontResource;

    public function __construct(
        \M2E\Kaufland\Model\ResourceModel\Storefront $storefrontResource,
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        ?\Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        ?\Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );
        $this->storefrontResource = $storefrontResource;
    }

    public function _construct(): void
    {
        $this->_init(
            \Magento\Framework\View\Element\UiComponent\DataProvider\Document::class,
            \M2E\Kaufland\Model\ResourceModel\Listing\Other::class,
        );
    }

    /**
     * @psalm-suppress ParamNameMismatch
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field === 'account') {
            $field = 'main_table.account_id';
        }

        if ($field === 'linked') {
            $this->buildFilterByLinked($condition);

            return $this;
        }

        if ($field === 'storefront_code') {
            $this->buildFilterByStorefrontCode($condition);

            return $this;
        }

        parent::addFieldToFilter($field, $condition);

        return $this;
    }

    private function buildFilterByLinked($condition): void
    {
        $conditionValue = (int)$condition['eq'];
        $column = \M2E\Kaufland\Model\ResourceModel\Listing\Other::COLUMN_MAGENTO_PRODUCT_ID;

        if ($conditionValue === \M2E\Kaufland\Ui\Select\YesNoAnyOption::OPTION_YES) {
            $this->getSelect()->where(sprintf('%s IS NOT NULL', $column));
        } elseif ($conditionValue === \M2E\Kaufland\Ui\Select\YesNoAnyOption::OPTION_NO) {
            $this->getSelect()->where(sprintf('%s IS NULL', $column));
        }
    }

    private function buildFilterByStorefrontCode($condition): void
    {
        $this->join(
            ['storefront' => $this->storefrontResource->getMainTable()],
            sprintf(
                'main_table.%s = storefront.%s',
                \M2E\Kaufland\Model\ResourceModel\Listing\Other::COLUMN_STOREFRONT_ID,
                \M2E\Kaufland\Model\ResourceModel\Storefront::COLUMN_ID,
            ),
            [\M2E\Kaufland\Model\ResourceModel\Storefront::COLUMN_STOREFRONT_CODE]
        );

        $this
            ->getSelect()
            ->where(
                sprintf(
                    'storefront.%s IN (?)',
                    \M2E\Kaufland\Model\ResourceModel\Storefront::COLUMN_STOREFRONT_CODE
                ),
                $condition['in']
            );
    }
}
