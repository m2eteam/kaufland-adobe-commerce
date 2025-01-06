<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Manual\Schedule;

use M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Manual\Result;

abstract class AbstractSchedule extends \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Manual\AbstractManual
{
    private \M2E\Kaufland\Model\ScheduledAction\CreateService $scheduledActionCreateService;

    public function __construct(
        \M2E\Kaufland\Model\ScheduledAction\CreateService $scheduledActionCreateService,
        \M2E\Kaufland\Model\Product\ActionCalculator $calculator,
        \M2E\Kaufland\Model\Listing\LogService $listingLogService,
        \M2E\Kaufland\Model\Product\LockRepository $lockRepository
    ) {
        parent::__construct($calculator, $listingLogService, $lockRepository);
        $this->scheduledActionCreateService = $scheduledActionCreateService;
    }

    protected function processAction(array $actions, array $params): Result
    {
        foreach ($actions as $action) {
            $this->createScheduleAction(
                $action,
                $params,
                $this->scheduledActionCreateService,
            );
        }

        return Result::createSuccess($this->getLogActionId());
    }

    protected function createScheduleAction(
        \M2E\Kaufland\Model\Product\Action $action,
        array $params,
        \M2E\Kaufland\Model\ScheduledAction\CreateService $createService
    ): void {
        $params['status_changer'] = \M2E\Kaufland\Model\Product::STATUS_CHANGER_USER;

        $scheduledActionParams = [
            'params' => $params,
        ];

        $scheduledAction = $action->getAction();
        if ($action->isActionList() && $action->getProduct()->isListableAsProduct()) {
            $scheduledAction = \M2E\Kaufland\Model\Product::ACTION_LIST_PRODUCT;
        }

        $createService->create(
            $action->getProduct(),
            $scheduledAction,
            $scheduledActionParams,
            $action->getConfigurator()->getAllowedDataTypes(),
            true,
            $action->getConfigurator()
        );
    }
}
