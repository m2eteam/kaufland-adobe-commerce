<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Listing\Product\Action;

class LogBuffer
{
    /** @var \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\LogRecord[] */
    private array $logs = [];

    public function addSuccess(string $successfulMessage): void
    {
        $this->logs[] = new LogRecord($successfulMessage, \M2E\Core\Model\Response\Message::TYPE_SUCCESS);
    }

    public function addWarning(string $successfulMessage): void
    {
        $this->logs[] = new LogRecord($successfulMessage, \M2E\Core\Model\Response\Message::TYPE_WARNING);
    }

    public function addFail(string $successfulMessage): void
    {
        $this->logs[] = new LogRecord($successfulMessage, \M2E\Core\Model\Response\Message::TYPE_ERROR);
    }

    /**
     * @return \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\LogRecord[]
     */
    public function getLogs(): array
    {
        return $this->logs;
    }

    public function getWarningMessages(): array
    {
        return array_map(
            static fn(LogRecord $log) => $log->getMessage(),
            array_filter(
                $this->logs,
                static fn(LogRecord $log) => $log->getSeverity() === \M2E\Core\Model\Response\Message::TYPE_WARNING
            )
        );
    }
}
