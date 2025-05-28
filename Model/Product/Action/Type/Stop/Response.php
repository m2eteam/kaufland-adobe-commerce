<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Product\Action\Type\Stop;

class Response extends \M2E\Kaufland\Model\Product\Action\Type\AbstractResponse
{
    use \M2E\Kaufland\Model\Product\Action\Type\ResponseUnitTrait;

    public function processSuccess(array $response, array $responseParams = []): void
    {
        $this->getListingProduct()->setStatusInactive($this->getStatusChanger());

        if ($this->getListingProduct()->isIncomplete()) {
            $this->getListingProduct()->makeComplete();
        }

        $this->getListingProduct()->save();
    }
}
