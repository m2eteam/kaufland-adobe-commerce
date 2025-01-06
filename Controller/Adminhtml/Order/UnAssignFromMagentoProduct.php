<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Order;

use M2E\Kaufland\Controller\Adminhtml\AbstractOrder;

class UnAssignFromMagentoProduct extends AbstractOrder
{
    private \M2E\Kaufland\Model\Order\Item\Repository $orderItemRepository;
    private \M2E\Kaufland\Model\Order\Item\ProductAssignService $productAssignService;

    public function __construct(
        \M2E\Kaufland\Model\Order\Item\Repository $orderItemRepository,
        \M2E\Kaufland\Model\Order\Item\ProductAssignService $productAssignService,
        $context = null
    ) {
        parent::__construct($context);
        $this->orderItemRepository = $orderItemRepository;
        $this->productAssignService = $productAssignService;
    }

    /**
     * @throws \M2E\Kaufland\Model\Exception\Logic
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $orderItemIds = explode(',', $this->getRequest()->getParam('order_item_ids'));
        $orderItems = $this->orderItemRepository->findOrderItemsByIds($orderItemIds);

        if (empty($orderItems)) {
            $this->setJsonContent([
                'error' => __('Please specify Required Options.'),
            ]);

            return $this->getResult();
        }

        $this->productAssignService->unAssign($orderItems);

        $this->setJsonContent([
            'success' => __('Item was Unlinked.'),
        ]);

        return $this->getResult();
    }
}
