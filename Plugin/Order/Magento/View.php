<?php

namespace M2E\Kaufland\Plugin\Order\Magento;

class View extends \M2E\Kaufland\Plugin\AbstractPlugin
{
    private \M2E\Kaufland\Model\Order\Repository $repository;

    public function __construct(
        \M2E\Kaufland\Model\Order\Repository $repository
    ) {
        $this->repository = $repository;
    }

    /**
     * @throws \M2E\Kaufland\Model\Exception
     */
    public function aroundSetLayout(
        \Magento\Framework\View\Element\AbstractBlock $interceptor,
        \Closure $callback,
        ...$arguments
    ) {
        if (!($interceptor instanceof \Magento\Sales\Block\Adminhtml\Order\View)) {
            return $callback(...$arguments);
        }

        return $this->execute('setLayout', $interceptor, $callback, $arguments);
    }

    protected function processSetLayout($interceptor, \Closure $callback, array $arguments)
    {
        /** @var \Magento\Sales\Block\Adminhtml\Order\View $interceptor */
        $magentoOrderId = $interceptor->getRequest()->getParam('order_id');
        if (empty($magentoOrderId)) {
            return $callback(...$arguments);
        }

        $order = $this->findOrder((int)$magentoOrderId);
        if ($order === null) {
            return $callback(...$arguments);
        }

        $buttonUrl = $interceptor->getUrl(
            'm2e_kaufland/kaufland_order/view',
            ['id' => $order->getId()]
        );

        $interceptor->addButton(
            'go_to_kauflnd_order',
            [
                'label' => __('Show Kaufland Order'),
                'onclick' => "setLocation('$buttonUrl')",
            ],
            0,
            -1
        );

        return $callback(...$arguments);
    }

    //########################################

    private function findOrder(int $magentoOrderId): ?\M2E\Kaufland\Model\Order
    {
        try {
            $order = $this->repository->findByMagentoOrderId($magentoOrderId);

            if ($order === null) {
                return null;
            }
        } catch (\Throwable $exception) {
            return null;
        }

        return $order;
    }
}
