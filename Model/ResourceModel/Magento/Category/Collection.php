<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\ResourceModel\Magento\Category;

use Magento\Catalog\Api\Data\CategoryAttributeInterface;

/**
 * Class \M2E\Kaufland\Model\ResourceModel\Magento\Category\Collection
 */
class Collection extends \Magento\Catalog\Model\ResourceModel\Category\Collection
{
    protected \M2E\Core\Helper\Magento\Staging $helperStaging;

    public function __construct(
        \M2E\Core\Helper\Magento\Staging $helperStaging,
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Eav\Model\EntityFactory $eavEntityFactory,
        \Magento\Eav\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ?\Magento\Framework\DB\Adapter\AdapterInterface $connection = null
    ) {
        $this->helperStaging = $helperStaging;

        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $eavConfig,
            $resource,
            $eavEntityFactory,
            $resourceHelper,
            $universalFactory,
            $storeManager,
            $connection
        );
    }

    //########################################

    /**
     * Compatibility with Magento Enterprise (Staging modules) - entity_id column issue
     */
    public function joinTable($table, $bind, $fields = null, $cond = null, $joinType = 'inner')
    {
        $helper = $this->helperStaging;

        if (
            $helper->isInstalled() &&
            $helper->isStagedTable($table, CategoryAttributeInterface::ENTITY_TYPE_CODE) &&
            strpos($bind, 'entity_id') !== false
        ) {
            $bind = str_replace(
                'entity_id',
                $helper->getTableLinkField(CategoryAttributeInterface::ENTITY_TYPE_CODE),
                $bind
            );
        }

        return parent::joinTable($table, $bind, $fields, $cond, $joinType);
    }

    /**
     * Compatibility with Magento Enterprise (Staging modules) - entity_id column issue
     */
    public function joinAttribute($alias, $attribute, $bind, $filter = null, $joinType = 'inner', $storeId = null)
    {
        $helper = $this->helperStaging;

        if ($helper->isInstalled() && is_string($attribute) && is_string($bind)) {
            $attrArr = explode('/', $attribute);
            if (CategoryAttributeInterface::ENTITY_TYPE_CODE == $attrArr[0] && $bind == 'entity_id') {
                $bind = $helper->getTableLinkField(CategoryAttributeInterface::ENTITY_TYPE_CODE);
            }
        }

        return parent::joinAttribute($alias, $attribute, $bind, $filter, $joinType, $storeId);
    }

    //########################################
}
