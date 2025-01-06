<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\ResourceModel\Template\Synchronization;

class Collection extends \M2E\Kaufland\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    public function _construct(): void
    {
        parent::_construct();
        $this->_init(
            \M2E\Kaufland\Model\Template\Synchronization::class,
            \M2E\Kaufland\Model\ResourceModel\Template\Synchronization::class
        );
    }
}