<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Product\Action\Type\Stop;

class Response extends \M2E\Kaufland\Model\Product\Action\Type\AbstractResponse
{
    use \M2E\Kaufland\Model\Product\Action\Type\ResponseUnitTrait;

    private \M2E\Kaufland\Model\Product\RemoveHandler $removeHandler;

    public function __construct(\M2E\Kaufland\Model\Product\RemoveHandler $removeHandler)
    {
        $this->removeHandler = $removeHandler;
    }

    public function processSuccess(array $response, array $responseParams = []): void
    {
        if ($this->getParams()['remove'] ?? false) {
            $this->removeHandler->process($this->getListingProduct());
        }

        $this->getListingProduct()->setStatusInactive($this->getStatusChanger());

        if ($this->getListingProduct()->isIncomplete()) {
            $this->getListingProduct()->makeComplete();
        }

        $this->getListingProduct()->save();
    }
}
