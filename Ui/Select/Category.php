<?php

declare(strict_types=1);

namespace M2E\Kaufland\Ui\Select;

use Magento\Framework\Data\OptionSourceInterface;
use M2E\Kaufland\Model\Category\Dictionary\Repository;

class Category implements OptionSourceInterface
{
    private Repository $repository;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    public function toOptionArray(): array
    {
        $options = [];
        foreach ($this->repository->getAllItems() as $dictionary) {
            $options[] = [
                'label' => $dictionary->getPath(),
                'value' => $dictionary->getId(),
            ];
        }

        return $options;
    }
}
