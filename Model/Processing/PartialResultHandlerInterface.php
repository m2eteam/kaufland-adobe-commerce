<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Processing;

interface PartialResultHandlerInterface extends SingleResultHandlerInterface
{
    public function processPartialResult(array $partialData): void;
}
