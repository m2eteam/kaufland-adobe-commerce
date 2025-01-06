<?php

declare(strict_types=1);

namespace M2E\Kaufland\Ui\Select;

use Magento\Framework\Data\OptionSourceInterface;
use M2E\Kaufland\Model\Template\Synchronization\Repository;

class PolicySync implements OptionSourceInterface
{
    private Repository $repository;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    public function toOptionArray(): array
    {
        $options = [];
        foreach ($this->repository->getAll() as $policy) {
            $options[] = [
                'label' => $policy->getTitle(),
                'value' => $policy->getId(),
            ];
        }

        return $options;
    }
}
