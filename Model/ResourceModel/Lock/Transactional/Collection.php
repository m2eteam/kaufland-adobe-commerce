<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\ResourceModel\Lock\Transactional;

class Collection extends \M2E\Kaufland\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    public function _construct(): void
    {
        $this->_init(
            \M2E\Kaufland\Model\Lock\Transactional::class,
            \M2E\Kaufland\Model\ResourceModel\Lock\Transactional::class
        );
    }
}
