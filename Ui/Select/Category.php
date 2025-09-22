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
                'label' => $this->formatLabel($dictionary),
                'value' => $dictionary->getId(),
            ];
        }

        return $options;
    }

    private function formatLabel(\M2E\Kaufland\Model\Category\Dictionary $dictionary): string
    {
        $path = $dictionary->getPath();
        $parts = array_map('trim', explode('>', $path));

        $shortPath = $path;
        if (count($parts) > 2) {
            $shortPath = sprintf('%s > ... > %s', reset($parts), end($parts));
        }

        return sprintf(
            '%s (%s)',
            $shortPath,
            $dictionary->getCategoryId()
        );
    }
}
