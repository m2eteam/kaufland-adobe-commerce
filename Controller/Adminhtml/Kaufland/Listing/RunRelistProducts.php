<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Listing;

class RunRelistProducts extends \M2E\Kaufland\Controller\Adminhtml\Kaufland\Listing\AbstractAction
{
    private \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Manual\Realtime\RelistAction $realtimeRelistAction;
    private \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Manual\Schedule\RelistAction $scheduledRelistAction;

    public function __construct(
        \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Manual\Realtime\RelistAction $realtimeRelistAction,
        \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Manual\Schedule\RelistAction $scheduledRelistAction,
        \M2E\Kaufland\Model\ResourceModel\Product\CollectionFactory $listingProductCollectionFactory,
        \M2E\Kaufland\Model\Listing\LogService $listingLogService
    ) {
        parent::__construct(
            $listingProductCollectionFactory,
            $listingLogService,
        );
        $this->realtimeRelistAction = $realtimeRelistAction;
        $this->scheduledRelistAction = $scheduledRelistAction;
    }

    public function execute()
    {
        if ($this->isRealtimeProcess()) {
            return $this->processRealtime(
                $this->realtimeRelistAction,
            );
        }

        return $this->createScheduleAction(
            $this->scheduledRelistAction,
        );
    }
}
