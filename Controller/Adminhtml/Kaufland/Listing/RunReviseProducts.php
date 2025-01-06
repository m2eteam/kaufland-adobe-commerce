<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Listing;

class RunReviseProducts extends \M2E\Kaufland\Controller\Adminhtml\Kaufland\Listing\AbstractAction
{
    private \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Manual\Realtime\ReviseAction $realtimeReviseAction;
    private \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Manual\Schedule\ReviseAction $scheduleReviseAction;

    public function __construct(
        \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Manual\Realtime\ReviseAction $realtimeReviseAction,
        \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Manual\Schedule\ReviseAction $scheduleReviseAction,
        \M2E\Kaufland\Model\ResourceModel\Product\CollectionFactory $listingProductCollectionFactory,
        \M2E\Kaufland\Model\Listing\LogService $listingLogService
    ) {
        parent::__construct(
            $listingProductCollectionFactory,
            $listingLogService,
        );
        $this->realtimeReviseAction = $realtimeReviseAction;
        $this->scheduleReviseAction = $scheduleReviseAction;
    }

    public function execute()
    {
        if ($this->isRealtimeProcess()) {
            return $this->processRealtime(
                $this->realtimeReviseAction,
            );
        }

        return $this->createScheduleAction(
            $this->scheduleReviseAction,
        );
    }
}
