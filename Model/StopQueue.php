<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model;

use M2E\Kaufland\Model\ResourceModel\StopQueue as ResourceModel;

class StopQueue extends \M2E\Kaufland\Model\ActiveRecord\AbstractModel
{
    public function _construct(): void
    {
        parent::_construct();
        $this->_init(ResourceModel::class);
    }

    public function create(string $account, string $storefrontCode, int $unitId): self
    {
        $this->setRequestData($account, $storefrontCode, $unitId);

        return $this;
    }

    public function setAsProcessed(): void
    {
        $this->setData(ResourceModel::COLUMN_IS_PROCESSED, 1);
    }

    public function getRequestData(): array
    {
        $data = $this->getData(ResourceModel::COLUMN_REQUEST_DATA);
        if ($data === null) {
            return [];
        }

        $data = json_decode($data, true);

        return [
            'account' => $data['account'],
            'storefront' => $data['storefront'],
            'unit_id' => $data['unit_id'],
        ];
    }

    private function setRequestData(string $account, string $storefrontCode, int $unitId): void
    {
        $this->setData(
            ResourceModel::COLUMN_REQUEST_DATA,
            json_encode([
                'account' => $account,
                'storefront' => $storefrontCode,
                'unit_id' => $unitId,
            ], JSON_THROW_ON_ERROR)
        );
    }
}
