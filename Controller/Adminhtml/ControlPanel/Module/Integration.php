<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\ControlPanel\Module;

use M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type;

class Integration extends \M2E\Kaufland\Controller\Adminhtml\ControlPanel\AbstractCommand
{
    private \Magento\Framework\Data\Form\FormKey $formKey;
    private \M2E\Kaufland\Model\ControlPanel\Module\Integration\RequestData $requestData;

    public function __construct(
        \Magento\Framework\Data\Form\FormKey $formKey,
        \M2E\Kaufland\Model\ControlPanel\Module\Integration\RequestData $requestData,
        \M2E\Kaufland\Helper\View\ControlPanel $controlPanelHelper,
        \M2E\Kaufland\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($controlPanelHelper, $context);

        $this->formKey = $formKey;
        $this->requestData = $requestData;
    }

    /**
     * @title "Print Request Data"
     * @description "Calculate Allowed Action for Listing Product"
     */
    public function getRequestDataAction()
    {
        return $this->requestData->execute($this->getRequest());
    }

    /**
     * @title "Build Order Quote"
     * @description "Print Order Quote Data"
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \M2E\Kaufland\Model\Exception
     * @throws \Exception
     */
    public function getPrintOrderQuoteDataAction()
    {
        $isPrint = (bool)$this->getRequest()->getParam('print');
        $orderId = $this->getRequest()->getParam('order_id');

        $buildResultHtml = '';
        if ($isPrint && $orderId) {
            $orderResource = $this->_objectManager->create(\M2E\Kaufland\Model\ResourceModel\Order::class);
            $order = $this->_objectManager->create(\M2E\Kaufland\Model\Order::class);

            $orderResource->load($order, (int)$orderId);

            if (!$order->getId()) {
                $this->getMessageManager()->addErrorMessage('Unable to load order instance.');

                return $this->_redirect($this->controlPanelHelper->getPageModuleTabUrl());
            }

            // Store must be initialized before products
            // ---------------------------------------
            $order->associateWithStore();
            $order->associateItemsWithProducts();
            // ---------------------------------------

            $proxy = $order->getProxy()->setStore($order->getStore());

            $magentoQuoteBuilder = $this
                ->_objectManager
                ->create(\M2E\Kaufland\Model\Magento\Quote\Builder::class, ['proxyOrder' => $proxy]);

            $magentoQuoteManager = $this
                ->_objectManager
                ->create(\M2E\Kaufland\Model\Magento\Quote\Manager::class);

            $quote = $magentoQuoteBuilder->build();

            $shippingAddressData = $quote->getShippingAddress()->getData();
            unset(
                $shippingAddressData['cached_items_all'],
                $shippingAddressData['cached_items_nominal'],
                $shippingAddressData['cached_items_nonnominal'],
            );
            $billingAddressData = $quote->getBillingAddress()->getData();
            unset(
                $billingAddressData['cached_items_all'],
                $billingAddressData['cached_items_nominal'],
                $billingAddressData['cached_items_nonnominal'],
            );
            $quoteData = $quote->getData();
            unset(
                $quoteData['items'],
                $quoteData['extension_attributes'],
            );

            $items = [];
            foreach ($quote->getAllItems() as $item) {
                $items[] = $item->getData();
            }

            $magentoQuoteManager->save($quote->setIsActive(false));

            $buildResultHtml = json_encode(
                json_decode(
                    json_encode([
                        'Grand Total' => $quote->getGrandTotal(),
                        'Shipping Amount' => $quote->getShippingAddress()->getShippingAmount(),
                        'Quote Data' => $quoteData,
                        'Shipping Address Data' => $shippingAddressData,
                        'Billing Address Data' => $billingAddressData,
                        'Items' => $items,
                    ]),
                    true,
                ),
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
            );
        }

        $formKey = $this->formKey->getFormKey();
        $actionUrl = $this->getUrl('*/*/*', ['action' => 'getPrintOrderQuoteData']);

        $formHtml = <<<HTML
<form method="get" enctype="multipart/form-data" action="$actionUrl">
    <input name="form_key" value="{$formKey}" type="hidden" />
    <input name="print" value="1" type="hidden" />
    <div>
        <label>Order ID: </label>
        <input name="order_id" value="$orderId" required>
        <button type="submit">Build</button>
    </div>
</form>
HTML;
        $resultHtml = $formHtml;
        if ($buildResultHtml !== '') {
            $resultHtml .= "<h3>Result</h3><div><pre>$buildResultHtml</pre></div>";
        }

        return $resultHtml;
    }
}
