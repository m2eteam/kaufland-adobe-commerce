<?php

declare(strict_types=1);

namespace M2E\Kaufland\Ui\Select;

use Magento\Framework\Data\OptionSourceInterface;
use M2E\Kaufland\Model\Account\Repository;

class Account implements OptionSourceInterface
{
    private Repository $repository;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    public function toOptionArray(): array
    {
        $options = [];

        foreach ($this->repository->getAll() as $account) {
            $options[] = [
                'label' => $account->getTitle(),
                'value' => $account->getId(),
            ];
        }

        return $options;
    }
}
