<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Listing;

class RunStopProducts extends \M2E\Kaufland\Controller\Adminhtml\Kaufland\Listing\AbstractAction
{
    private \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Manual\Realtime\StopAction $realtimeStopAction;
    private \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Manual\Schedule\StopAction $scheduledStopAction;

    public function __construct(
        \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Manual\Realtime\StopAction $realtimeStopAction,
        \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Manual\Schedule\StopAction $scheduledStopAction,
        \M2E\Kaufland\Model\ResourceModel\Product\CollectionFactory $listingProductCollectionFactory,
        \M2E\Kaufland\Model\Listing\LogService $listingLogService
    ) {
        parent::__construct(
            $listingProductCollectionFactory,
            $listingLogService,
        );
        $this->realtimeStopAction = $realtimeStopAction;
        $this->scheduledStopAction = $scheduledStopAction;
    }

    public function execute()
    {
        if ($this->isRealtimeProcess()) {
            return $this->processRealtime(
                $this->realtimeStopAction,
            );
        }

        return $this->createScheduleAction(
            $this->scheduledStopAction,
        );
    }
}
