<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\ResourceModel\Instruction;

class Collection extends \M2E\Kaufland\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    public function _construct(): void
    {
        $this->_init(
            \M2E\Kaufland\Model\Instruction::class,
            \M2E\Kaufland\Model\ResourceModel\Instruction::class
        );
    }
}
