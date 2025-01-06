<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\ExternalChange;

class Repository
{
    private \M2E\Kaufland\Model\ResourceModel\ExternalChange $resource;

    public function __construct(
        \M2E\Kaufland\Model\ResourceModel\ExternalChange $resource
    ) {
        $this->resource = $resource;
    }

    public function create(\M2E\Kaufland\Model\ExternalChange $externalChanges): void
    {
        $this->resource->save($externalChanges);
    }

    public function remove(\M2E\Kaufland\Model\ExternalChange $externalChanges): void
    {
        $this->resource->delete($externalChanges);
    }

    public function removeAllByAccountAndStorefront(int $accountId, int $storefrontId): void
    {
        $this->resource->getConnection()->delete(
            $this->resource->getMainTable(),
            [
                \M2E\Kaufland\Model\ResourceModel\ExternalChange::COLUMN_ACCOUNT_ID . ' = ?' => $accountId,
                \M2E\Kaufland\Model\ResourceModel\ExternalChange::COLUMN_STOREFRONT_ID . ' = ?' => $storefrontId,
            ]
        );
    }
}
