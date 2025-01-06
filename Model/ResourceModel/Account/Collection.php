<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\ResourceModel\Account;

/**
 * @method \M2E\Kaufland\Model\Account[] getItems()
 * @method \M2E\Kaufland\Model\Account getFirstItem()
 */
class Collection extends \M2E\Kaufland\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    public function _construct(): void
    {
        parent::_construct();

        $this->_init(
            \M2E\Kaufland\Model\Account::class,
            \M2E\Kaufland\Model\ResourceModel\Account::class
        );
    }
}
