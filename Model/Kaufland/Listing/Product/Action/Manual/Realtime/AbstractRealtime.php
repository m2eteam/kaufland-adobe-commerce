<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Manual\Realtime;

abstract class AbstractRealtime extends \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Manual\AbstractManual
{
    public function __construct(
        \M2E\Kaufland\Model\Product\ActionCalculator $calculator,
        \M2E\Kaufland\Model\Listing\LogService $listingLogService,
        \M2E\Kaufland\Model\Product\LockRepository $lockRepository
    ) {
        parent::__construct($calculator, $listingLogService, $lockRepository);
    }
}
