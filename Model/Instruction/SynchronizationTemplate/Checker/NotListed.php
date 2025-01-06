<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Instruction\SynchronizationTemplate\Checker;

class NotListed extends \M2E\Kaufland\Model\Instruction\SynchronizationTemplate\Checker\AbstractChecker
{
    private \M2E\Kaufland\Model\ScheduledAction\CreateService $scheduledActionCreate;
    private \M2E\Kaufland\Model\ScheduledAction\Repository $scheduledActionRepository;
    private \M2E\Kaufland\Model\Product\ActionCalculator $actionCalculator;

    public function __construct(
        \M2E\Kaufland\Model\Product\ActionCalculator $actionCalculator,
        \M2E\Kaufland\Model\ScheduledAction\CreateService $scheduledActionCreate,
        \M2E\Kaufland\Model\ScheduledAction\Repository $scheduledActionRepository,
        \M2E\Kaufland\Model\Instruction\Handler\Input $input
    ) {
        parent::__construct($input);

        $this->scheduledActionCreate = $scheduledActionCreate;
        $this->scheduledActionRepository = $scheduledActionRepository;
        $this->actionCalculator = $actionCalculator;
    }

    public function isAllowed(): bool
    {
        if (!parent::isAllowed()) {
            return false;
        }

        return $this->input->getListingProduct()->isListable();
    }

    public function process(array $params = []): void
    {
        $product = $this->getInput()->getListingProduct();

        $calculateResult = $this->actionCalculator->calculateToList($product);
        if (!$calculateResult->isActionList()) {
            $this->tryRemoveExistScheduledAction();

            return;
        }

        if (
            $this->getInput()->getScheduledAction() !== null
            && $this->getInput()->getScheduledAction()->isActionTypeList()
        ) {
            return;
        }

        $this->scheduledActionCreate->create(
            $this->input->getListingProduct(),
            $product->isListableAsProduct()
                ? \M2E\Kaufland\Model\Product::ACTION_LIST_PRODUCT
                : \M2E\Kaufland\Model\Product::ACTION_LIST_UNIT,
            ['params' => $params],
            $calculateResult->getConfigurator()->getAllowedDataTypes(),
            false,
            $calculateResult->getConfigurator()
        );
    }

    private function tryRemoveExistScheduledAction(): void
    {
        if ($this->getInput()->getScheduledAction() === null) {
            return;
        }

        if ($this->getInput()->getScheduledAction()->isForce()) {
            return;
        }

        $this->scheduledActionRepository->remove($this->getInput()->getScheduledAction());
    }
}
