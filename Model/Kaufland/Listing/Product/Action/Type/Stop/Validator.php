<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\Stop;

class Validator extends \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\AbstractValidator
{
    public function validate(): bool
    {
        if (!$this->getListingProduct()->isStoppable()) {
            $this->addMessage('Item is not Listed or not available');

            return false;
        }

        return true;
    }
}
