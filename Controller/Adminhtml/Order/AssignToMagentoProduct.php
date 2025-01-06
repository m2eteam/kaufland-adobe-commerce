<?php

namespace M2E\Kaufland\Controller\Adminhtml\Order;

class AssignToMagentoProduct extends \M2E\Kaufland\Controller\Adminhtml\AbstractOrder
{
    public const MAPPING_PRODUCT = 'product';
    public const MAPPING_OPTIONS = 'options';

    private \M2E\Kaufland\Helper\Data\GlobalData $globalData;
    private \M2E\Kaufland\Model\Order\ItemFactory $orderItemFactory;
    private \M2E\Kaufland\Model\ResourceModel\Order\Item $orderItemResource;

    public function __construct(
        \M2E\Kaufland\Model\Order\ItemFactory $orderItemFactory,
        \M2E\Kaufland\Model\ResourceModel\Order\Item $orderItemResource,
        \M2E\Kaufland\Helper\Data\GlobalData $globalData,
        \M2E\Kaufland\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($context);

        $this->globalData = $globalData;
        $this->orderItemFactory = $orderItemFactory;
        $this->orderItemResource = $orderItemResource;
    }

    public function execute()
    {
        $orderItemId = $this->getRequest()->getParam('order_item_id');

        $orderItem = $this->orderItemFactory->create();
        $this->orderItemResource->load($orderItem, $orderItemId);

        if ($orderItem->isObjectNew()) {
            $this->setJsonContent([
                'error' => __('Order Item does not exist.'),
            ]);

            return $this->getResult();
        }

        $this->globalData->setValue('order_item', $orderItem);

        if (
            $orderItem->getMagentoProductId() === null
            || !$orderItem->getMagentoProduct()->exists()
        ) {
            $block = $this
                ->getLayout()
                ->createBlock(\M2E\Kaufland\Block\Adminhtml\Order\Item\Product\Mapping::class);

            $this->setJsonContent([
                'title' => __('Linking Product "%title"', ['title' => $orderItem->getTitle()]),
                'html' => $block->toHtml(),
                'type' => self::MAPPING_PRODUCT,
            ]);

            return $this->getResult();
        }

        $this->setJsonContent([
            'error' => __('Product does not have Required Options.'),
        ]);

        return $this->getResult();
    }
}
