<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\ResourceModel\Account\Grid;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection implements
    \Magento\Framework\Api\Search\SearchResultInterface
{
    use \M2E\Kaufland\Model\ResourceModel\SearchResultTrait;

    public function _construct(): void
    {
        $this->_init(
            \Magento\Framework\View\Element\UiComponent\DataProvider\Document::class,
            \M2E\Kaufland\Model\ResourceModel\Account::class,
        );
    }
}
