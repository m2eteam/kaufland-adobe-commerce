<?php

namespace M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Manual;

class Result
{
    /** @var int */
    private $logActionId;
    /** @var string */
    private $status;

    public function __construct(string $status, int $logActionId)
    {
        $this->logActionId = $logActionId;
        $this->status = $status;
    }

    public function isSuccess(): bool
    {
        return $this->status === 'success';
    }

    public function isWarning(): bool
    {
        return $this->status === 'warning';
    }

    public function isError(): bool
    {
        return $this->status === 'error';
    }

    public function getLogActionId(): int
    {
        return $this->logActionId;
    }

    // ----------------------------------------

    public static function createSuccess(int $logAction): self
    {
        return new self('success', $logAction);
    }

    public static function createWarning(int $logAction): self
    {
        return new self('warning', $logAction);
    }

    public static function createError(int $logAction): self
    {
        return new self('error', $logAction);
    }
}
