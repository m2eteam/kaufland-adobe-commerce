<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\Stop;

class Response extends \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\AbstractResponse
{
    use \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\ResponseUnitTrait;

    public function processSuccess(array $response, array $responseParams = []): void
    {
        $data = [
            'status' => \M2E\Kaufland\Model\Product::STATUS_INACTIVE,
        ];

        $this->getListingProduct()->addData($data);

        if ($this->getListingProduct()->isIncomplete()) {
            $this->getListingProduct()->makeComplete();
        }

        $this->getListingProduct()->save();
    }
}
