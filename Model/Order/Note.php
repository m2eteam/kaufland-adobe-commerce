<?php

namespace M2E\Kaufland\Model\Order;

class Note extends \M2E\Kaufland\Model\ActiveRecord\AbstractModel
{
    private ?\M2E\Kaufland\Model\Order $order = null;

    private \M2E\Kaufland\Model\ResourceModel\Order $orderResource;
    private \M2E\Kaufland\Model\OrderFactory $orderFactory;
    private \M2E\Kaufland\Model\Magento\Order\Updater $orderUpdater;

    public function __construct(
        \M2E\Kaufland\Model\Magento\Order\Updater $orderUpdater,
        \M2E\Kaufland\Model\ResourceModel\Order $orderResource,
        \M2E\Kaufland\Model\OrderFactory $orderFactory,
        \M2E\Kaufland\Model\Factory $modelFactory,
        \M2E\Kaufland\Model\ActiveRecord\Factory $activeRecordFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $modelFactory,
            $activeRecordFactory,
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
        $this->orderResource = $orderResource;
        $this->orderFactory = $orderFactory;
        $this->orderUpdater = $orderUpdater;
    }

    public function _construct()
    {
        parent::_construct();
        $this->_init(\M2E\Kaufland\Model\ResourceModel\Order\Note::class);
    }

    public function getNote()
    {
        return $this->getData('note');
    }

    public function getOrderId()
    {
        return $this->getData('order_id');
    }

    public function afterDelete(): self
    {
        $comment = __('Custom Note for the corresponding Kaufland order was deleted.');
        $this->updateMagentoOrderComments($comment);

        return parent::afterDelete();
    }

    public function afterSave(): self
    {
        $comment = __(
            'Custom Note was added to the corresponding Kaufland order: %note.',
            ['note' => $this->getNote()]
        );

        if ($this->getOrigData('id') !== null) {
            $comment = __(
                'Custom Note for the corresponding Kaufland order was updated: %note.',
                ['note' => $this->getNote()]
            );
        }

        $this->updateMagentoOrderComments($comment);

        return parent::afterSave();
    }

    protected function updateMagentoOrderComments(string $comment): void
    {
        $magentoOrderModel = $this->findOrder()->getMagentoOrder();

        if ($magentoOrderModel === null) {
            return;
        }

        $orderUpdater = $this->orderUpdater;
        $orderUpdater->setMagentoOrder($magentoOrderModel);
        $orderUpdater->updateComments($comment);
        $orderUpdater->finishUpdate();
    }

    public function findOrder(): ?\M2E\Kaufland\Model\Order
    {
        if ($this->order === null) {
            $order = $this->orderFactory->create();
            $this->orderResource->load($order, $this->getOrderId());

            $this->order = $order;
        }

        return $this->order;
    }

    //########################################
}
