<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Product\Action\Async\Processing;

class Initiator implements \M2E\Kaufland\Model\Processing\SingleInitiatorInterface
{
    private \M2E\Core\Model\Connector\CommandProcessingInterface $command;
    private Params $params;

    public function __construct(
        \M2E\Core\Model\Connector\CommandProcessingInterface $command,
        Params $params
    ) {
        $this->command = $command;
        $this->params = $params;
    }

    public function getInitCommand(): \M2E\Core\Model\Connector\CommandProcessingInterface
    {
        return $this->command;
    }

    public function generateProcessParams(): array
    {
        return $this->params->toArray();
    }

    public function getResultHandlerNick(): string
    {
        return ResultHandler::NICK;
    }

    public function initLock(\M2E\Kaufland\Model\Processing\LockManager $lockManager): void
    {
        // Lock will be acquired in the Start action.
    }
}
