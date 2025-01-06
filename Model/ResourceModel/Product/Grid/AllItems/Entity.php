<?php

namespace M2E\Kaufland\Model\ResourceModel\Product\Grid\AllItems;

use Magento\Framework\View\Element\UiComponent\DataProvider\Document;

class Entity extends Document
{
    public function getIdFieldName()
    {
        return 'entity_id';
    }

    public function isVisibleInSiteVisibility()
    {
        return false;
    }

    public function getProductId(): int
    {
        return (int)$this->getData(Collection::PRIMARY_COLUMN);
    }
}
