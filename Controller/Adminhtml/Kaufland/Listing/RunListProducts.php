<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Listing;

class RunListProducts extends \M2E\Kaufland\Controller\Adminhtml\Kaufland\Listing\AbstractAction
{
    private \M2E\Kaufland\Model\Product\Action\Manual\Realtime\ListAction $realtimeListAction;
    private \M2E\Kaufland\Model\Product\Action\Manual\Schedule\ListAction $scheduledListAction;

    public function __construct(
        \M2E\Kaufland\Model\Product\Action\Manual\Realtime\ListAction $realtimeListAction,
        \M2E\Kaufland\Model\Product\Action\Manual\Schedule\ListAction $scheduledListAction,
        \M2E\Kaufland\Model\ResourceModel\Product\CollectionFactory $listingProductCollectionFactory,
        \M2E\Kaufland\Model\Listing\LogService $listingLogService
    ) {
        parent::__construct(
            $listingProductCollectionFactory,
            $listingLogService,
        );
        $this->realtimeListAction = $realtimeListAction;
        $this->scheduledListAction = $scheduledListAction;
    }

    public function execute()
    {
        if ($this->isRealtimeProcess()) {
            return $this->processRealtime(
                $this->realtimeListAction,
            );
        }

        return $this->createScheduleAction(
            $this->scheduledListAction,
        );
    }
}
