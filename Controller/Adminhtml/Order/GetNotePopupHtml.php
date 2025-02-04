<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Order;

use M2E\Kaufland\Controller\Adminhtml\AbstractOrder;

class GetNotePopupHtml extends AbstractOrder
{
    private \M2E\Kaufland\Model\Order\Note\Repository $repository;

    public function __construct(\M2E\Kaufland\Model\Order\Note\Repository $repository)
    {
        parent::__construct();
        $this->repository = $repository;
    }

    public function execute()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        if ($orderId !== null) {
            $orderId = (int)$orderId;
        }

        $noteId = $this->getRequest()->getParam('note_id');

        $note = null;
        if ($noteId !== null) {
            $note = $this->repository->get((int)$noteId);
        }

        $grid = $this->getLayout()->createBlock(
            \M2E\Kaufland\Block\Adminhtml\Order\Note\Popup::class,
            '',
            ['note' => $note, 'orderId' => $orderId],
        );

        $this->setAjaxContent($grid->toHtml());

        return $this->getResult();
    }
}
