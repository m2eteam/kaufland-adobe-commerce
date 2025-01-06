<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Instruction\Handler;

use M2E\Kaufland\Model\Product;

class Input
{
    private Product $product;

    /** @var \M2E\Kaufland\Model\Instruction[] */
    private array $instructions = [];

    private \M2E\Kaufland\Model\ScheduledAction $scheduledAction;

    /**
     * @param \M2E\Kaufland\Model\Instruction[] $instructions
     */
    public function __construct(
        Product $product,
        array $instructions
    ) {
        $this->product = $product;
        $this->instructions = $instructions;
    }

    public function setScheduledAction(\M2E\Kaufland\Model\ScheduledAction $scheduledAction): void
    {
        $this->scheduledAction = $scheduledAction;
    }

    public function getScheduledAction(): ?\M2E\Kaufland\Model\ScheduledAction
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        return $this->scheduledAction ?? null;
    }

    public function getListingProduct(): Product
    {
        return $this->product;
    }

    /**
     * @return \M2E\Kaufland\Model\Instruction[]
     */
    public function getInstructions(): array
    {
        return $this->instructions;
    }

    /**
     * @return string[]
     */
    public function getUniqueInstructionTypes(): array
    {
        $types = [];

        foreach ($this->getInstructions() as $instruction) {
            $types[] = $instruction->getType();
        }

        return array_unique($types);
    }

    public function hasInstructionWithType(string $instructionType): bool
    {
        return in_array($instructionType, $this->getUniqueInstructionTypes(), true);
    }

    public function hasInstructionWithTypes(array $instructionTypes): bool
    {
        return count(array_intersect($this->getUniqueInstructionTypes(), $instructionTypes)) > 0;
    }
}
