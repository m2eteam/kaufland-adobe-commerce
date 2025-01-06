<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Order;

class Cancel
{
    private \M2E\Kaufland\Model\Order\ChangeCreateService $changeCreateService;

    public function __construct(
        \M2E\Kaufland\Model\Order\ChangeCreateService $changeCreateService
    ) {
        $this->changeCreateService = $changeCreateService;
    }

    /**
     * @param \M2E\Kaufland\Model\Order $order
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @param int $initiator
     *
     * @return void
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    public function process(
        \M2E\Kaufland\Model\Order $order,
        \Magento\Sales\Model\Order\Creditmemo $creditmemo,
        int $initiator
    ): void {
        \M2E\Core\Helper\Data::validateInitiator($initiator);

        if (!$order->canCancel()) {
            return;
        }

        if (!$order->getAccount()->getOrdersSettings()->isOrderCancelOrRefundOnChannelEnabled()) {
            return;
        }

        $items = [];
        foreach ($creditmemo->getItems() as $creditmemoItem) {
            $kauflandOrderItems = $this->loadItems($creditmemoItem, $order);
            if (empty($kauflandOrderItems)) {
                continue;
            }

            array_push($items, ...$kauflandOrderItems);
        }

        if (empty($items)) {
            return;
        }

        $params = [];
        /** @var \M2E\Kaufland\Model\Order\Item $item */
        foreach ($items as $item) {
            $params['items'][] = [
                'id' => $item->getId(),
            ];
        }

        $this->changeCreateService->create(
            (int)$order->getId(),
            \M2E\Kaufland\Model\Order\Change::ACTION_CANCEL,
            $initiator,
            $params
        );
    }

    /**
     * @param \Magento\Sales\Model\Order\Creditmemo\Item $creditmemoItem
     * @param \M2E\Kaufland\Model\Order $order
     *
     * @return \M2E\Kaufland\Model\Order\Item[]
     */
    private function loadItems(
        \Magento\Sales\Model\Order\Creditmemo\Item $creditmemoItem,
        \M2E\Kaufland\Model\Order $order
    ): array {
        $magentoProductId = (int)$creditmemoItem->getProductId();
        $qty = (int)$creditmemoItem->getQty();

        $result = [];
        foreach ($order->getItems() as $item) {
            if ($qty === 0) {
                break;
            }

            if ($magentoProductId !== $item->getMagentoProductId()) {
                continue;
            }

            $result[] = $item;
            $qty--;
        }

        return $result;
    }
}
