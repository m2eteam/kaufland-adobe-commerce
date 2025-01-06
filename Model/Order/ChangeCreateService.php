<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Order;

use M2E\Kaufland\Model\Order\Change;
use M2E\Kaufland\Model\Order\ChangeFactory;

class ChangeCreateService
{
    /** @var \M2E\Kaufland\Model\Order\Change\Repository */
    private Change\Repository $repository;
    /** @var \M2E\Kaufland\Model\Order\ChangeFactory */
    private ChangeFactory $factory;

    public function __construct(
        \M2E\Kaufland\Model\Order\Change\Repository $repository,
        ChangeFactory $factory
    ) {
        $this->repository = $repository;
        $this->factory = $factory;
    }

    public function create(int $orderId, string $action, ?int $creatorType, array $params): Change
    {
        if (!in_array($action, [Change::ACTION_UPDATE_SHIPPING, Change::ACTION_CANCEL, Change::ACTION_SEND_INVOICE])) {
            throw new \InvalidArgumentException('Action is invalid.');
        }

        $hash = $this->generateHash($orderId, $action, $params);

        $change = $this->repository->findExist($orderId, $action, $hash);
        if ($change !== null) {
            return $change;
        }

        $change = $this->factory->create();
        $change->init($orderId, $action, $creatorType, $params, $hash);

        $this->repository->create($change);

        return $change;
    }

    private function generateHash(int $orderId, string $action, array $params): string
    {
        return sha1($orderId . '-' . $action . '-' . json_encode($params, JSON_THROW_ON_ERROR));
    }
}
