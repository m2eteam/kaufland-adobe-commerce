<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Product\Action\Type\Relist;

class Validator extends \M2E\Kaufland\Model\Product\Action\Type\AbstractValidator
{
    public function validate(): bool
    {
        if (!$this->getListingProduct()->isRelistable()) {
            $this->addMessage(
                new \M2E\Kaufland\Model\Product\Action\Validator\ValidatorMessage(
                    'The Item either is Listed, or not Listed yet or not available',
                    \M2E\Kaufland\Model\Tag\ValidatorIssues::NOT_USER_ERROR
                )
            );

            return false;
        }

        if (!$this->validatePrice()) {
            return false;
        }

        return true;
    }
}
