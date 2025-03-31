<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Instruction\SynchronizationTemplate;

class Handler implements \M2E\Kaufland\Model\Instruction\Handler\HandlerInterface
{
    /** @var \M2E\Kaufland\Model\Instruction\SynchronizationTemplate\Checker\CheckerFactory */
    private Checker\CheckerFactory $checkerFactory;
    private \M2E\Kaufland\Model\ScheduledAction\Repository $scheduledActionRepository;

    public function __construct(
        Checker\CheckerFactory $checkerFactory,
        \M2E\Kaufland\Model\ScheduledAction\Repository $scheduledActionRepository
    ) {
        $this->checkerFactory = $checkerFactory;
        $this->scheduledActionRepository = $scheduledActionRepository;
    }

    public function process(\M2E\Kaufland\Model\Instruction\Handler\Input $input): void
    {
        $scheduledAction = $this->scheduledActionRepository
            ->findByListingProductId($input->getListingProduct()->getId());

        if ($scheduledAction !== null) {
            $input->setScheduledAction($scheduledAction);
        }

        foreach ($this->getAllCheckers() as $checkerClassName) {
            $checkerModel = $this->checkerFactory->create($checkerClassName, $input);

            if (!$checkerModel->isAllowed()) {
                continue;
            }

            $checkerModel->process();
        }
    }

    /**
     * @return string[]
     */
    protected function getAllCheckers(): array
    {
        return [
            Checker\NotListed::class,
            Checker\Active::class,
            Checker\UnitInactive::class
        ];
    }
}
