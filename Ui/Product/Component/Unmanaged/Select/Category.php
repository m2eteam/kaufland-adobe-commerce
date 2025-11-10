<?php

declare(strict_types=1);

namespace M2E\Kaufland\Ui\Product\Component\Unmanaged\Select;

use Magento\Framework\Data\OptionSourceInterface;

class Category implements OptionSourceInterface
{
    private \M2E\Kaufland\Model\Listing\Other\Repository $repository;

    public function __construct(\M2E\Kaufland\Model\Listing\Other\Repository $repository)
    {
        $this->repository = $repository;
    }

    public function toOptionArray(): array
    {
        $options = [];
        foreach ($this->repository->getDistinctCategories() as $category) {
            $options[] = [
                'value' => $category['id'],
                'label' => $this->formatLabel($category['path'], $category['id']),
            ];
        }

        return $options;
    }

    private function formatLabel(string $path, int $categoryId): string
    {
        $parts = array_map('trim', explode('>', $path));

        $shortPath = $path;
        if (count($parts) > 2) {
            $shortPath = sprintf('%s > ... > %s', reset($parts), end($parts));
        }

        return sprintf(
            '%s (%s)',
            $shortPath,
            $categoryId
        );
    }
}
