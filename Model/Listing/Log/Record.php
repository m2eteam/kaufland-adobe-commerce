<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Listing\Log;

class Record
{
    private string $message;
    private int $type;

    private function __construct(
        string $message,
        int $type
    ) {
        $this->message = $message;
        $this->type = $type;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getType(): int
    {
        return $this->type;
    }

    // ----------------------------------------

    public static function createInfo(string $message): self
    {
        return new self($message, \M2E\Kaufland\Model\Log\AbstractModel::TYPE_INFO);
    }

    public static function createSuccess(string $message): self
    {
        return new self($message, \M2E\Kaufland\Model\Log\AbstractModel::TYPE_SUCCESS);
    }

    public static function createWarning(string $message): self
    {
        return new self($message, \M2E\Kaufland\Model\Log\AbstractModel::TYPE_WARNING);
    }

    public static function createError(string $message): self
    {
        return new self($message, \M2E\Kaufland\Model\Log\AbstractModel::TYPE_ERROR);
    }
}
