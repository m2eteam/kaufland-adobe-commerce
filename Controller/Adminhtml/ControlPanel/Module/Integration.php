<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\ControlPanel\Module;

use M2E\Kaufland\Controller\Adminhtml\Context;
use M2E\Kaufland\Controller\Adminhtml\ControlPanel\AbstractCommand;
use M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type;

class Integration extends AbstractCommand
{
    private \Magento\Framework\Data\Form\FormKey $formKey;

    /** @var \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\Relist\RequestFactory */
    private Type\Relist\RequestFactory $relistRequestFactory;
    private \M2E\Kaufland\Model\Product\Repository $productRepository;
    private \M2E\Kaufland\Model\Product\ActionCalculator $actionCalculator;
    /** @var \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\Stop\RequestFactory */
    private Type\Stop\RequestFactory $stopRequestFactory;
    /**
     * @var \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\ListUnit\RequestFactory
     */
    private Type\ListUnit\RequestFactory $listUnitRequestFactory;
    /**
     * @var \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\ReviseUnit\RequestFactory
     */
    private Type\ReviseUnit\RequestFactory $reviseUnitRequestFactory;
    /**
     * @var \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\ListProduct\RequestFactory
     */
    private Type\ListProduct\RequestFactory $listProductRequestFactory;
    /**
     * @var \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\ReviseProduct\RequestFactory
     */
    private Type\ReviseProduct\RequestFactory $reviseProductRequestFactory;
    private \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\LogBufferFactory $logBufferFactory;

    public function __construct(
        \Magento\Framework\Data\Form\FormKey $formKey,
        \M2E\Kaufland\Helper\View\ControlPanel $controlPanelHelper,
        \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\ListUnit\RequestFactory $listUnitRequestFactory,
        \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\ReviseUnit\RequestFactory $reviseUnitRequestFactory,
        \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\ListProduct\RequestFactory $listProductRequestFactory,
        \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\ReviseProduct\RequestFactory $reviseProductRequestFactory,
        \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\Relist\RequestFactory $relistRequestFactory,
        \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\Stop\RequestFactory $stopRequestFactory,
        \M2E\Kaufland\Model\Product\Repository $productRepository,
        \M2E\Kaufland\Model\Product\ActionCalculator $actionCalculator,
        \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\LogBufferFactory $logBufferFactory,
        Context $context
    ) {
        parent::__construct($controlPanelHelper, $context);
        $this->formKey = $formKey;
        $this->relistRequestFactory = $relistRequestFactory;
        $this->productRepository = $productRepository;
        $this->actionCalculator = $actionCalculator;
        $this->stopRequestFactory = $stopRequestFactory;
        $this->listUnitRequestFactory = $listUnitRequestFactory;
        $this->reviseUnitRequestFactory = $reviseUnitRequestFactory;
        $this->listProductRequestFactory = $listProductRequestFactory;
        $this->reviseProductRequestFactory = $reviseProductRequestFactory;
        $this->logBufferFactory = $logBufferFactory;
    }

    /**
     * @title "Print Request Data"
     * @description "Calculate Allowed Action for Listing Product"
     */
    public function getRequestDataAction()
    {
        $httpRequest = $this->getRequest();

        $listingProductId = $httpRequest->getParam('listing_product_id', null);
        if ($listingProductId !== null) {
            $listingProductId = (int)$listingProductId;
        }

        $form = $this->printFormForCalculateAction($listingProductId);
        $html = "<div style='padding: 20px;background:#d3d3d3;position:sticky;top:0;width:100vw'>$form</div>";

        if ($httpRequest->getParam('print')) {
            try {
                $listingProduct = $this->productRepository->get((int)$listingProductId);
                $actions = $this->actionCalculator->calculate(
                    $listingProduct,
                    true,
                );

                $currentStatusTitle = \M2E\Kaufland\Model\Product::getStatusTitle($listingProduct->getStatus());

                $productSku = $listingProduct->getMagentoProduct()->getSku();

                $listingTitle = $listingProduct->getListing()->getTitle();

                <<<HTML
<style>
    table {
      border-collapse: collapse;
      width: 100%;
    }

    td, th {
      border: 1px solid #dddddd;
      text-align: left;
      padding: 8px;
    }

    tr:nth-child(even) {
      background-color: #f2f2f2;
    }

</style>
<table>
    <tr>
        <td>Listing</td>
        <td>$listingTitle</td>
    </tr>
    <tr>
        <td>Product (SKU)</td>
        <td>$productSku</td>
    </tr>
    <tr>
        <td>Current Product Status</td>
        <td>$currentStatusTitle</td>
    </tr>
</table>
HTML;
                foreach ($actions as $action) {
                    $html .= '<div>' . $this->printProductInfo($listingProduct, $action) . '</div>';
                }
            } catch (\Throwable $exception) {
                $html .= sprintf(
                    '<div style="margin: 20px 0">%s</div>',
                    $exception->getMessage()
                );
            }
        }

        return $html;
    }

    private function printFormForCalculateAction(?int $listingProductId): string
    {
        $formKey = $this->formKey->getFormKey();
        $actionUrl = $this->getUrl('*/*/*', ['action' => 'getRequestData']);

        return <<<HTML
<form style="margin: 0; font-size: 16px" method="get" enctype="multipart/form-data" action="$actionUrl">
    <input name="form_key" value="$formKey" type="hidden" />
    <input name="print" value="1" type="hidden" />

    <label style="display: inline-block;">
        Listing Product ID:
        <input name="listing_product_id" style="width: 200px;" required value="$listingProductId">
    </label>
    <div style="margin: 10px 0 0 0;">
        <button type="submit">Calculate Allowed Action</button>
    </div>
</form>
HTML;
    }

    private function printProductInfo(
        \M2E\Kaufland\Model\Product $listingProduct,
        \M2E\Kaufland\Model\Product\Action $action
    ): ?string {
        $calculateAction = 'Nothing';
        if ($action->isActionList()) {
            if ($listingProduct->isListableAsProduct()) {
                $calculateAction = 'List_Product';
                $request = $this->listProductRequestFactory->create(
                    $listingProduct,
                    $action->getConfigurator(),
                    $this->logBufferFactory->create()
                );
                $printResult = $this->printRequestData($request);
            } else {
                $calculateAction = 'List_Unit';
                $request = $this->listUnitRequestFactory->create(
                    $listingProduct,
                    $action->getConfigurator(),
                    $this->logBufferFactory->create()
                );
                $printResult = $this->printRequestData($request);
            }
        } elseif ($action->isActionRevise()) {
            $calculateAction = sprintf(
                'Revise Unit (Reason (%s))',
                implode(' | ', $action->getConfigurator()->getAllowedDataTypes()),
            );
            $request = $this->reviseUnitRequestFactory->create(
                $listingProduct,
                $action->getConfigurator(),
                $this->logBufferFactory->create()
            );
            $printResult = $this->printRequestData($request);
        } elseif ($action->isActionReviseProduct()) {
            $calculateAction = sprintf(
                'Revise Product (Reason (%s))',
                implode(' | ', $action->getConfigurator()->getAllowedDataTypes()),
            );
            $request = $this->reviseProductRequestFactory->create(
                $listingProduct,
                $action->getConfigurator(),
                $this->logBufferFactory->create()
            );
            $printResult = $this->printRequestData($request);
        } elseif ($action->isActionStop()) {
            $calculateAction = 'Stop';
            $request = $this->stopRequestFactory->create(
                $listingProduct,
                $action->getConfigurator(),
                $this->logBufferFactory->create()
            );
            $printResult = $this->printRequestData($request);
        } elseif ($action->isActionRelist()) {
            $calculateAction = 'Relist';
            $request = $this->relistRequestFactory->create(
                $listingProduct,
                $action->getConfigurator(),
                $this->logBufferFactory->create()
            );
            $printResult = $this->printRequestData($request);
        } else {
            $printResult = 'Nothing action allowed.';
        }

        return <<<HTML
<style>
    table {
      border-collapse: collapse;
      width: 100%;
    }

    td, th {
      border: 1px solid #dddddd;
      text-align: left;
      padding: 8px;
    }

    tr:nth-child(even) {
      background-color: #f2f2f2;
    }

</style>
<table>
    <tr>
        <td>Calculate Action</td>
        <td>$calculateAction</td>
    <tr>
        <td>Request Data</td>
        <td>$printResult</td>
    </tr>
</table>
HTML;
    }

    private function printRequestData(
        \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\AbstractRequest $request
    ): string {
        return sprintf(
            '<pre>%s</pre>',
            htmlspecialchars(
                json_encode(
                    $request->getRequestData(),
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
                ),
                ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401,
            ),
        );
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
