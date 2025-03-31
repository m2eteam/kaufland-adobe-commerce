<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\Relist;

class Response extends \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\ReviseUnit\Response
{
    use \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\ResponseUnitTrait;

    private \M2E\Kaufland\Model\Product\Repository $repository;

    public function __construct(\M2E\Kaufland\Model\Product\Repository $repository)
    {
        parent::__construct($repository);
        $this->repository = $repository;
    }

    public function processSuccess(array $response, array $responseParams = []): void
    {
        parent::processSuccess($response, $responseParams);

        $this->getListingProduct()
             ->setStatusListed($this->getStatusChanger())
             ->removeBlockingByError();

        $this->repository->save($this->getListingProduct());
    }
}
