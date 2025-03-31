<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Instruction\SynchronizationTemplate\Checker;

abstract class AbstractChecker
{
    protected \M2E\Kaufland\Model\Instruction\Handler\Input $input;

    public function __construct(
        \M2E\Kaufland\Model\Instruction\Handler\Input $input
    ) {
        $this->input = $input;
    }

    public function isAllowed(): bool
    {
        $listingProduct = $this->getInput()->getListingProduct();

        if (!$listingProduct->getMagentoProduct()->exists()) {
            return false;
        }

        if ($listingProduct->hasBlockingByError()) {
            return false;
        }

        return true;
    }

    abstract public function process();

    // ----------------------------------------

    protected function getInput(): \M2E\Kaufland\Model\Instruction\Handler\Input
    {
        return $this->input;
    }
}
