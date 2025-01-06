<?php

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Order;

class View extends \M2E\Kaufland\Controller\Adminhtml\Kaufland\AbstractOrder
{
    /** @var \M2E\Kaufland\Helper\Data\GlobalData */
    private $globalData;
    private \M2E\Kaufland\Model\Order\Repository $repository;

    public function __construct(
        \M2E\Kaufland\Helper\Data\GlobalData $globalData,
        \M2E\Kaufland\Model\Order\Repository $repository
    ) {
        parent::__construct();

        $this->globalData = $globalData;
        $this->repository = $repository;
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $order = $this->repository->get((int)$id);

        $this->globalData->setValue('order', $order);

        $this->addContent(
            $this->getLayout()->createBlock(
                \M2E\Kaufland\Block\Adminhtml\Kaufland\Order\View::class
            )
        );

        $this->init();
        $this->getResultPage()->getConfig()->getTitle()->prepend(__('View Order Details'));

        return $this->getResult();
    }
}
