<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Category\Dictionary;

abstract class AbstractAttribute
{
    private int $id;
    private string $nick;
    private string $title;
    private string $description;
    private string $type;
    private bool $isRequired;
    private bool $isMultipleSelected;
    /** @var \M2E\Kaufland\Model\Category\Dictionary\Attribute\Value[] */
    private array $recommendedValuers;

    public function __construct(
        int $id,
        string $nick,
        string $title,
        string $description,
        string $type,
        bool $isRequired,
        bool $isMultipleSelected,
        array $recommendedValuers = []
    ) {
        $this->id = $id;
        $this->nick = $nick;
        $this->title = $title;
        $this->description = $description;
        $this->type = $type;
        $this->isRequired = $isRequired;
        $this->isMultipleSelected = $isMultipleSelected;
        $this->recommendedValuers = $recommendedValuers;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getNick(): string
    {
        return $this->nick;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function isRequired(): bool
    {
        return $this->isRequired;
    }

    public function isMultipleSelected(): bool
    {
        return $this->isMultipleSelected;
    }

    /**
     * @return array|\M2E\Kaufland\Model\Category\Dictionary\Attribute\Option[]
     */
    public function getOptions(): array
    {
        return $this->recommendedValuers;
    }
}
