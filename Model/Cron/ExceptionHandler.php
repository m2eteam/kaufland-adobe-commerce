<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Cron;

class ExceptionHandler
{
    /** @var \M2E\Kaufland\Model\Cron\OperationHistory */
    private OperationHistory $operationHistory;
    private \M2E\Kaufland\Model\Synchronization\LogService $syncLog;
    private \M2E\Kaufland\Helper\Module\Exception $exceptionHelper;

    public function __construct(
        \M2E\Kaufland\Model\Cron\OperationHistory $operationHistory,
        \M2E\Kaufland\Model\Synchronization\LogService $syncLog,
        \M2E\Kaufland\Helper\Module\Exception $exceptionHelper
    ) {
        $this->operationHistory = $operationHistory;
        $this->syncLog = $syncLog;
        $this->exceptionHelper = $exceptionHelper;
    }

    public function processTaskAccountException(string $message, $file, $line, $trace = null): void
    {
        $this->operationHistory->addContentData(
            'exceptions',
            [
                'message' => $message,
                'file' => $file,
                'line' => $line,
                'trace' => $trace,
            ]
        );

        $this->syncLog->add(
            $message,
            \M2E\Kaufland\Model\Log\AbstractModel::TYPE_ERROR
        );
    }

    public function processTaskException(\Throwable $exception): void
    {
        $this->operationHistory->addContentData(
            'exceptions',
            [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ]
        );

        $this->syncLog->addFromException($exception);

        $this->exceptionHelper->process($exception);
    }
}
