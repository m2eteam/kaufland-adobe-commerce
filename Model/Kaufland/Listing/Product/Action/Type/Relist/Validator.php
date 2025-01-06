<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\Relist;

class Validator extends \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\AbstractValidator
{
    public function validate(): bool
    {
        if (!$this->getListingProduct()->isRelistable()) {
            $this->addMessage('The Item either is Listed, or not Listed yet or not available');

            return false;
        }

        if (!$this->validatePrice()) {
            return false;
        }

        return true;
    }
}
