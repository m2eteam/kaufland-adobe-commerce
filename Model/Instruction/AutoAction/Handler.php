<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Instruction\AutoAction;

class Handler implements \M2E\Kaufland\Model\Instruction\Handler\HandlerInterface
{
    private \M2E\Kaufland\Model\ScheduledAction\CreateService $scheduledActionCreateService;
    private \M2E\Kaufland\Model\Product\RemoveHandler $productRemoveHandler;

    public function __construct(
        \M2E\Kaufland\Model\ScheduledAction\CreateService $scheduledActionCreateService,
        \M2E\Kaufland\Model\Product\RemoveHandler $productRemoveHandler
    ) {
        $this->scheduledActionCreateService = $scheduledActionCreateService;
        $this->productRemoveHandler = $productRemoveHandler;
    }

    public function process(\M2E\Kaufland\Model\Instruction\Handler\Input $input)
    {
        if (!$this->hasAllowedInstruction($input)) {
            return;
        }

        $listingProduct = $input->getListingProduct();
        $params = [];
        if ($this->hasStopAndRemoveInstruction($input)) {
            if (!$listingProduct->isStoppable()) {
                $this->productRemoveHandler->process($listingProduct);

                return;
            }

            $params['remove'] = true;
        }

        $this->scheduledActionCreateService->create(
            $listingProduct,
            \M2E\Kaufland\Model\Product::ACTION_STOP_UNIT,
            \M2E\Kaufland\Model\Product::STATUS_CHANGER_SYNCH,
            ['params' => $params],
            [],
            true
        );
    }

    private function hasAllowedInstruction(\M2E\Kaufland\Model\Instruction\Handler\Input $input): bool
    {
        $instructions = [
            \M2E\Kaufland\Model\Listing\Auto\Actions\Listing::INSTRUCTION_TYPE_STOP,
            \M2E\Kaufland\Model\Listing\Auto\Actions\Listing::INSTRUCTION_TYPE_STOP_AND_REMOVE,
        ];

        return $input->hasInstructionWithTypes($instructions);
    }

    private function hasStopAndRemoveInstruction(\M2E\Kaufland\Model\Instruction\Handler\Input $input): bool
    {
        $instructions = [
            \M2E\Kaufland\Model\Listing\Auto\Actions\Listing::INSTRUCTION_TYPE_STOP_AND_REMOVE,
        ];

        return $input->hasInstructionWithTypes($instructions);
    }
}
