<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Product;

class Action
{
    private const ACTION_NOTHING = 0;

    private int $action;
    private \M2E\Kaufland\Model\Product $product;
    private Action\Configurator $configurator;

    private function __construct(
        int $action,
        \M2E\Kaufland\Model\Product $product,
        Action\Configurator $configurator
    ) {
        $this->product = $product;
        $this->configurator = $configurator;
        $this->action = $action;
    }

    public function getProduct(): \M2E\Kaufland\Model\Product
    {
        return $this->product;
    }

    public function getAction(): int
    {
        return $this->action;
    }

    public function getConfigurator(): Action\Configurator
    {
        return $this->configurator;
    }

    public function isActionList(): bool
    {
        return $this->action === \M2E\Kaufland\Model\Product::ACTION_LIST_UNIT;
    }

    public function isActionListProduct(): bool
    {
        return $this->action === \M2E\Kaufland\Model\Product::ACTION_LIST_PRODUCT;
    }

    public function isActionRevise(): bool
    {
        return $this->action === \M2E\Kaufland\Model\Product::ACTION_REVISE_UNIT;
    }

    public function isActionReviseProduct(): bool
    {
        return $this->action === \M2E\Kaufland\Model\Product::ACTION_REVISE_PRODUCT;
    }

    public function isActionStop(): bool
    {
        return $this->action === \M2E\Kaufland\Model\Product::ACTION_STOP_UNIT;
    }

    public function isActionRelist(): bool
    {
        return $this->action === \M2E\Kaufland\Model\Product::ACTION_RELIST_UNIT;
    }

    public function isActionNothing(): bool
    {
        return $this->action === self::ACTION_NOTHING;
    }

    // ----------------------------------------

    public static function createNothing(\M2E\Kaufland\Model\Product $product): self
    {
        return new self(
            self::ACTION_NOTHING,
            $product,
            new Action\Configurator(),
        );
    }

    public static function createList(
        \M2E\Kaufland\Model\Product $product,
        Action\Configurator $configurator
    ): self {
        return new self(
            \M2E\Kaufland\Model\Product::ACTION_LIST_UNIT,
            $product,
            $configurator,
        );
    }

    public static function createRelist(
        \M2E\Kaufland\Model\Product $product,
        Action\Configurator $configurator
    ): self {
        return new self(
            \M2E\Kaufland\Model\Product::ACTION_RELIST_UNIT,
            $product,
            $configurator,
        );
    }

    public static function createReviseUnit(
        \M2E\Kaufland\Model\Product $product,
        Action\Configurator $configurator
    ): self {
        return new self(
            \M2E\Kaufland\Model\Product::ACTION_REVISE_UNIT,
            $product,
            $configurator,
        );
    }

    public static function createReviseProduct(
        \M2E\Kaufland\Model\Product $product,
        Action\Configurator $configurator
    ): self {
        return new self(
            \M2E\Kaufland\Model\Product::ACTION_REVISE_PRODUCT,
            $product,
            $configurator,
        );
    }

    public static function createStop(
        \M2E\Kaufland\Model\Product $product
    ): self {
        return new self(
            \M2E\Kaufland\Model\Product::ACTION_STOP_UNIT,
            $product,
            new Action\Configurator(),
        );
    }

    public static function createDelete(
        \M2E\Kaufland\Model\Product $product
    ): self {
        return new self(
            \M2E\Kaufland\Model\Product::ACTION_DELETE_UNIT,
            $product,
            new Action\Configurator(),
        );
    }
}
