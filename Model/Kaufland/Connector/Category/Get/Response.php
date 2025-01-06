<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Connector\Category\Get;

class Response
{
    /** @var \M2E\Kaufland\Model\Kaufland\Connector\Category\Category[] */
    private array $categories = [];

    public function addCategory(
        \M2E\Kaufland\Model\Kaufland\Connector\Category\Category $category
    ): void {
        $this->categories[] = $category;
    }

    /**
     * @return \M2E\Kaufland\Model\Kaufland\Connector\Category\Category[]
     */
    public function getCategories(): array
    {
        return $this->categories;
    }
}
