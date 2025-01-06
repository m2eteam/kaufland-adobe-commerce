<?php

namespace M2E\Kaufland\Observer;

use M2E\Kaufland\Observer\AbstractObserver;

class Creditmemo extends AbstractObserver
{
    private \M2E\Kaufland\Model\Order\Repository $repository;
    private \M2E\Kaufland\Model\Order\Cancel $cancel;

    public function __construct(
        \M2E\Kaufland\Model\Order\Repository $repository,
        \M2E\Kaufland\Model\Order\Cancel $cancel,
        \M2E\Kaufland\Model\ActiveRecord\Factory $activeRecordFactory,
        \M2E\Kaufland\Model\Factory $modelFactory
    ) {
        parent::__construct($activeRecordFactory, $modelFactory);
        $this->repository = $repository;
        $this->cancel = $cancel;
    }

    protected function process(): void
    {
        /** @var \Magento\Sales\Model\Order\Creditmemo $creditmemo */
        $creditmemo = $this->getEvent()->getCreditmemo();
        $magentoOrderId = (int)$creditmemo->getOrderId();

        $order = $this->repository->findByMagentoOrderId($magentoOrderId);
        if ($order === null) {
            return;
        }

        $this->cancel->process($order, $creditmemo, \M2E\Core\Helper\Data::INITIATOR_USER);
    }
}
