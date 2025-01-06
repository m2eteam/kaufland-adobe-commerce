<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Connector\Attribute;

class Attribute
{
    private int $id;
    private string $nick;
    private string $title;
    private string $description;
    private string $type;
    private bool $isRequired;
    private bool $isMultipleSelected;
    private array $options = [];

    public function __construct(
        int $id,
        string $nick,
        string $title,
        string $description,
        string $type,
        bool $isRequired,
        bool $isMultipleSelected
    ) {
        $this->id = $id;
        $this->nick = $nick;
        $this->title = $title;
        $this->description = $description;
        $this->type = $type;
        $this->isRequired = $isRequired;
        $this->isMultipleSelected = $isMultipleSelected;
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
     * @return list<array{id:string, name:string}>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    public function addOptions(string $value, string $label): void
    {
        $this->options[] = [
            'value' => $value,
            'label' => $label,
        ];
    }
}
