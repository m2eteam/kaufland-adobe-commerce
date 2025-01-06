<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Listing\Wizard\Step;

interface BackHandlerInterface
{
    public function process(\M2E\Kaufland\Model\Listing\Wizard\Manager $manager): void;
}
