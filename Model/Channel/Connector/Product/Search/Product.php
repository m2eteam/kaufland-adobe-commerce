<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Channel\Connector\Product\Search;

class Product
{
    private string $id;
    private array $eans;
    private string $categoryTitle;
    private int $categoryId;

    public function __construct(
        string $id,
        array $eans,
        string $categoryTitle,
        int $categoryId
    ) {
        $this->id = $id;
        $this->eans = $eans;
        $this->categoryTitle = $categoryTitle;
        $this->categoryId = $categoryId;
    }

    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string[]
     */
    public function getEans(): array
    {
        return $this->eans;
    }

    public function getCategoryTitle(): string
    {
        return $this->categoryTitle;
    }

    public function getCategoryId(): int
    {
        return $this->categoryId;
    }
}
