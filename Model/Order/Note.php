<?php

namespace M2E\Kaufland\Model\Order;

class Note extends \M2E\Kaufland\Model\ActiveRecord\AbstractModel
{
    public function _construct(): void
    {
        parent::_construct();
        $this->_init(\M2E\Kaufland\Model\ResourceModel\Order\Note::class);
    }

    public function init(int $orderId, string $note): self
    {
        $this->setData(\M2E\Kaufland\Model\ResourceModel\Order\Note::COLUMN_ORDER_ID, $orderId)
             ->setNote($note);

        return $this;
    }

    public function getOrderId(): int
    {
        return (int)$this->getData(\M2E\Kaufland\Model\ResourceModel\Order\Note::COLUMN_ORDER_ID);
    }

    public function setNote(string $note): self
    {
        $this->setData(\M2E\Kaufland\Model\ResourceModel\Order\Note::COLUMN_NOTE, trim($note));

        return $this;
    }

    public function getNote(): string
    {
        return (string)$this->getData(\M2E\Kaufland\Model\ResourceModel\Order\Note::COLUMN_NOTE);
    }
}
