<?php

declare(strict_types=1);

namespace M2E\Kaufland\Block\Adminhtml\Order\Note;

class Popup extends \M2E\Kaufland\Block\Adminhtml\Magento\AbstractContainer
{
    private ?int $orderId;
    private ?\M2E\Kaufland\Model\Order\Note $note;

    public function __construct(
        ?int $orderId,
        ?\M2E\Kaufland\Model\Order\Note $note,
        \M2E\Kaufland\Block\Adminhtml\Magento\Context\Widget $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->orderId = $orderId;
        $this->note = $note;
    }

    public function _construct()
    {
        parent::_construct();

        $this->setTemplate('order/note.phtml');
    }

    public function hasOrderId(): bool
    {
        return $this->orderId !== null;
    }

    public function getOrderId(): ?int
    {
        return $this->orderId;
    }

    public function hasNote(): bool
    {
        return $this->note !== null;
    }

    public function getNote(): ?\M2E\Kaufland\Model\Order\Note
    {
        return $this->note;
    }
}
