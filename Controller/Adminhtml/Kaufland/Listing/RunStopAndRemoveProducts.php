<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Listing;

use M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Manual;

class RunStopAndRemoveProducts extends \M2E\Kaufland\Controller\Adminhtml\Kaufland\Listing\AbstractAction
{
    private Manual\Realtime\StopAndRemoveAction $realtimeStopAndRemoveAction;
    private Manual\Schedule\StopAndRemoveAction $scheduledStopAndRemoveAction;

    public function __construct(
        Manual\Realtime\StopAndRemoveAction $realtimeStopAndRemoveAction,
        Manual\Schedule\StopAndRemoveAction $scheduledStopAndRemoveAction,
        \M2E\Kaufland\Model\ResourceModel\Product\CollectionFactory $listingProductCollectionFactory,
        \M2E\Kaufland\Model\Listing\LogService $listingLogService
    ) {
        parent::__construct(
            $listingProductCollectionFactory,
            $listingLogService,
        );
        $this->realtimeStopAndRemoveAction = $realtimeStopAndRemoveAction;
        $this->scheduledStopAndRemoveAction = $scheduledStopAndRemoveAction;
    }

    public function execute()
    {
        if ($this->isRealtimeProcess()) {
            return $this->processRealtime(
                $this->realtimeStopAndRemoveAction,
                ['remove' => true],
            );
        }

        return $this->createScheduleAction(
            $this->scheduledStopAndRemoveAction,
            ['remove' => true],
        );
    }
}
