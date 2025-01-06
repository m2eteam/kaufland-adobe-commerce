<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Processing;

interface SingleResultHandlerInterface
{
    public function initialize(array $params): void;

    /**
     * @param array $resultData
     * @param \M2E\Core\Model\Response\Message[] $messages
     *
     * @return void
     */
    public function processSuccess(array $resultData, array $messages): void;

    public function processExpire(): void;

    public function clearLock(LockManager $lockManager): void;
}
