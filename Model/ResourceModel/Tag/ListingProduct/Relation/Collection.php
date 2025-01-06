<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\ResourceModel\Tag\ListingProduct\Relation;

use M2E\Kaufland\Model\ResourceModel\Tag\ListingProduct\Relation as ResourceModel;
use M2E\Kaufland\Model\Tag\ListingProduct\Relation;

class Collection extends \M2E\Kaufland\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    /**
     * @inerhitDoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(Relation::class, ResourceModel::class);
    }
}
