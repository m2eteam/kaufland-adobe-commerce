<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Category\Search;

class ResultItem
{
    public int $categoryId;
    public string $path;

    public function __construct(
        int $categoryId,
        string $path
    ) {
        $this->categoryId = $categoryId;
        $this->path = $path;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->categoryId,
            'path' => $this->path
        ];
    }
}
