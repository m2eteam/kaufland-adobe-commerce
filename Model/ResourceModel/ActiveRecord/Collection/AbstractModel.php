<?php

namespace M2E\Kaufland\Model\ResourceModel\ActiveRecord\Collection;

use Magento\Catalog\Api\Data\CategoryAttributeInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;

abstract class AbstractModel extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /** @var \M2E\Kaufland\Model\ActiveRecord\Factory */
    protected $activeRecordFactory;

    //########################################

    public function __construct(
        \M2E\Kaufland\Model\ActiveRecord\Factory $activeRecordFactory,
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        ?\Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        ?\Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->activeRecordFactory = $activeRecordFactory;
    }

    protected function _toOptionArray($valueField = 'id', $labelField = 'title', $additional = []) // @codingStandardsIgnoreLine
    {
        return parent::_toOptionArray($valueField, $labelField, $additional);
    }

    protected function _toOptionHash($valueField = 'id', $labelField = 'title') // @codingStandardsIgnoreLine
    {
        return parent::_toOptionHash($valueField, $labelField);
    }

    //########################################

    public function joinLeft($name, $cond, $cols = '*', $schema = null)
    {
        $cond = $this->replaceJoinCondition($name, $cond);
        $this->getSelect()->joinLeft($name, $cond, $cols, $schema);
    }

    public function joinInner($name, $cond, $cols = '*', $schema = null)
    {
        $cond = $this->replaceJoinCondition($name, $cond);
        $this->getSelect()->joinInner($name, $cond, $cols, $schema);
    }

    /**
     * Compatibility with Magento Enterprise (Staging modules) - entity_id column issue
     */
    private function replaceJoinCondition($table, $cond)
    {
        /** @var \M2E\Core\Helper\Magento\Staging $helper */
        $helper = \Magento\Framework\App\ObjectManager::getInstance()->get(
            \M2E\Core\Helper\Magento\Staging::class
        );

        if (
            $helper->isInstalled() && $helper->isStagedTable($table) &&
            strpos($cond, 'entity_id') !== false
        ) {
            $linkField = $helper->isStagedTable($table, ProductAttributeInterface::ENTITY_TYPE_CODE)
                ? $helper->getTableLinkField(ProductAttributeInterface::ENTITY_TYPE_CODE)
                : $helper->getTableLinkField(CategoryAttributeInterface::ENTITY_TYPE_CODE);

            $cond = str_replace('entity_id', $linkField, $cond);
        }

        return $cond;
    }
}
