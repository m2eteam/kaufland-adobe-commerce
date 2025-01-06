<?php

namespace M2E\Kaufland\Model\Order;

use M2E\Kaufland\Model\ResourceModel\Order\Change as ChangeResource;

class Change extends \M2E\Kaufland\Model\ActiveRecord\AbstractModel
{
    public const ACTION_UPDATE_SHIPPING = 'update_shipping';
    public const ACTION_CANCEL = 'cancel';
    public const ACTION_SEND_INVOICE = 'send_invoice';

    public const MAX_ALLOWED_PROCESSING_ATTEMPTS = 3;

    public function _construct(): void
    {
        parent::_construct();
        $this->_init(ChangeResource::class);
    }

    public function init(int $orderId, string $action, $creatorType, array $params, string $hash): void
    {
        if (!in_array($action, self::getAllowedActions())) {
            throw new \InvalidArgumentException('Action is invalid.');
        }

        $this->addData([
            ChangeResource::COLUMN_ORDER_ID => $orderId,
            ChangeResource::COLUMN_ACTION => $action,
            ChangeResource::COLUMN_CREATOR_TYPE => $creatorType,
            'component' => '',
            ChangeResource::COLUMN_HASH => $hash,
        ])
        ->setParams($params);
    }

    public function getOrderId(): int
    {
        return (int)$this->getData(ChangeResource::COLUMN_ORDER_ID);
    }

    public function isShippingUpdateAction(): bool
    {
        return $this->getAction() === self::ACTION_UPDATE_SHIPPING;
    }

    public function getAction(): string
    {
        return (string)$this->getData(ChangeResource::COLUMN_ACTION);
    }

    public function getCreatorType(): int
    {
        return (int)$this->getData(ChangeResource::COLUMN_CREATOR_TYPE);
    }

    public function setParams(array $params): self
    {
        $this->setData(ChangeResource::COLUMN_PARAMS, json_encode($params));

        return $this;
    }

    public function getParams(): array
    {
        $params = $this->getData(ChangeResource::COLUMN_PARAMS);
        if (empty($params)) {
            return [];
        }

        return json_decode($params, true);
    }

    public function getHash(): string
    {
        return (string)$this->getData(ChangeResource::COLUMN_HASH);
    }

    private static function getAllowedActions(): array
    {
        return [
            self::ACTION_UPDATE_SHIPPING,
            self::ACTION_CANCEL,
            self::ACTION_SEND_INVOICE,
        ];
    }
}
