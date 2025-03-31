<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\ControlPanel\Module\Integration;

use M2E\Kaufland\Model\Kaufland\Listing\Product\Action;
use M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type;

class RequestData
{
    private const PARAM_PRODUCT_MAGENTO_SKU = 'listing_product_magento_sku';
    private const PARAM_CALCULATOR_ACTION = 'calculator_action';
    private const PARAM_PRINT = 'print';

    private \M2E\Kaufland\Model\Product\Repository $productRepository;
    private \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\Relist\RequestFactory $relistRequestFactory;
    private \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\Stop\RequestFactory $stopRequestFactory;
    private \M2E\Kaufland\Model\Product\ActionCalculator $actionCalculator;
    private \Magento\Framework\Data\Form\FormKey $formKey;
    private \Magento\Framework\UrlInterface $url;
    private \Magento\Framework\Escaper $escaper;
    private \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\LogBufferFactory $logBufferFactory;
    private Type\ListProduct\RequestFactory $listProductRequestFactory;
    private Type\ListUnit\RequestFactory $listUnitRequestFactory;
    private Type\ReviseUnit\RequestFactory $reviseUnitRequestFactory;
    private Type\ReviseProduct\RequestFactory $reviseProductRequestFactory;

    public function __construct(
        \M2E\Kaufland\Model\Product\Repository $productRepository,
        \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\Relist\RequestFactory $relistRequestFactory,
        \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\Stop\RequestFactory $stopRequestFactory,
        \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\ListProduct\RequestFactory $listProductRequestFactory,
        \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\ListUnit\RequestFactory $listUnitRequestFactory,
        \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\ReviseUnit\RequestFactory $reviseUnitRequestFactory,
        \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\ReviseProduct\RequestFactory $reviseProductRequestFactory,
        \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\LogBufferFactory $logBufferFactory,
        \M2E\Kaufland\Model\Product\ActionCalculator $actionCalculator,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\Escaper $escaper
    ) {
        $this->productRepository = $productRepository;
        $this->relistRequestFactory = $relistRequestFactory;
        $this->stopRequestFactory = $stopRequestFactory;
        $this->actionCalculator = $actionCalculator;
        $this->formKey = $formKey;
        $this->url = $url;
        $this->escaper = $escaper;
        $this->logBufferFactory = $logBufferFactory;
        $this->listProductRequestFactory = $listProductRequestFactory;
        $this->listUnitRequestFactory = $listUnitRequestFactory;
        $this->reviseUnitRequestFactory = $reviseUnitRequestFactory;
        $this->reviseProductRequestFactory = $reviseProductRequestFactory;
    }

    /**
     * @title "Print Request Data"
     * @description "Calculate Allowed Action for Listing Product"
     */
    public function execute(\Magento\Framework\App\RequestInterface $request): string
    {
        $productMagentoSku = $request->getParam(self::PARAM_PRODUCT_MAGENTO_SKU, '');
        $calculatorAction = $request->getParam(self::PARAM_CALCULATOR_ACTION, 'auto');

        $body = $this->printFormForCalculateAction($productMagentoSku, $calculatorAction);

        if ($request->getParam(self::PARAM_PRINT)) {
            try {
                $listingProducts = $this->productRepository->findProductsByMagentoSku($productMagentoSku);

                foreach ($listingProducts as $listingProduct) {
                    if ($calculatorAction === 'list') {
                        $action = \M2E\Kaufland\Model\Product\Action::createList(
                            $listingProduct,
                            (new Action\Configurator())->enableAll()
                        );
                    } elseif ($calculatorAction === 'revise_product') {
                        $action = \M2E\Kaufland\Model\Product\Action::createReviseProduct(
                            $listingProduct,
                            (new Action\Configurator())->enableAll()
                        );
                    } elseif ($calculatorAction === 'revise_unit') {
                        $action = \M2E\Kaufland\Model\Product\Action::createReviseUnit(
                            $listingProduct,
                            (new Action\Configurator())->enableAll()
                        );
                    } elseif ($calculatorAction === 'relist') {
                        $action = \M2E\Kaufland\Model\Product\Action::createRelist(
                            $listingProduct,
                            (new Action\Configurator())->enableAll()
                        );
                    } elseif ($calculatorAction === 'stop') {
                        $action = \M2E\Kaufland\Model\Product\Action::createStop(
                            $listingProduct,
                        );
                    } else {
                        $action = $this->actionCalculator->calculate(
                            $listingProduct,
                            true,
                            \M2E\Kaufland\Model\Product::STATUS_CHANGER_USER,
                        );
                        $action = reset($action);
                    }

                    $body .= '<div>' . $this->printProductInfo($listingProduct, $action) . '</div>';
                }
            } catch (\Throwable $exception) {
                $body .= sprintf(
                    '<div style="margin: 20px 0">%s</div>',
                    $exception->getMessage()
                );
            }
        }

        return $this->renderHtml($body);
    }
    private function printFormForCalculateAction(string $productMagentoSku = '', string $selectedAction = 'auto'): string
    {
        $formKey = $this->formKey->getFormKey();
        $actionUrl = $this->url->getUrl('*/*/*', ['action' => 'getRequestData']);

        $actionsList = [
            ['value' => 'auto', 'label' => 'Auto'],
            ['value' => 'list', 'label' => 'List'],
            ['value' => 'revise_product', 'label' => 'Revise Product'],
            ['value' => 'revise_unit', 'label' => 'Revise Unit'],
            ['value' => 'relist', 'label' => 'Relist'],
            ['value' => 'stop', 'label' => 'Stop'],
        ];

        $actionsOptions = '';
        foreach ($actionsList as $action) {
            $actionsOptions .= sprintf(
                '<option value="%s" %s>%s</option>',
                $action['value'],
                $selectedAction === $action['value'] ? 'selected' : '',
                $action['label']
            );
        }

        return <<<HTML
<div class="sticky-form-wrapper">
    <form method="get" enctype="multipart/form-data" action="$actionUrl">
        <input name="form_key" value="$formKey" type="hidden" />
        <input name="print" value="1" type="hidden" />

        <div class="form-row">
            <label for="product_id">Magento Product Sku:</label>
            <input id="product_id" name="listing_product_magento_sku" required value="$productMagentoSku">
        </div>
        <div class="form-row">
            <label for="calculator_action">Action:</label>
            <select id="calculator_action" name="calculator_action">$actionsOptions</select>
        </div>
        <div class="form-row">
            <button class="run" type="submit">Run</button>
        </div>
    </form>
</div>
HTML;
    }

    private function printProductInfo(
        \M2E\Kaufland\Model\Product $listingProduct,
        \M2E\Kaufland\Model\Product\Action $action
    ): ?string {
        if ($action->isActionList()) {
            if ($listingProduct->isListableAsProduct()) {
                $calculateAction = 'List Product';
                $request = $this->listProductRequestFactory->create(
                    $listingProduct,
                    $action->getConfigurator(),
                    $this->logBufferFactory->create()
                );
            } else {
                $calculateAction = 'List Unit';
                $request = $this->listUnitRequestFactory->create(
                    $listingProduct,
                    $action->getConfigurator(),
                    $this->logBufferFactory->create()
                );
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
        } elseif ($action->isActionStop()) {
            $calculateAction = 'Stop';
            $request = $this->stopRequestFactory->create(
                $listingProduct,
                $action->getConfigurator(),
                $this->logBufferFactory->create()
            );
        } elseif ($action->isActionRelist()) {
            $calculateAction = 'Relist';
            $request = $this->relistRequestFactory->create(
                $listingProduct,
                $action->getConfigurator(),
                $this->logBufferFactory->create()
            );
        } else {
            $request = null;
            $calculateAction = 'Nothing';
        }

        $requestData = $request === null
            ? 'Nothing action allowed.'
            : $this->printCodeBlock($request->getRequestData());

        $requestMetaData = $request === null
            ? 'Nothing action allowed.'
            : $this->printCodeBlock($request->getMetaData());

        $currentStatusTitle = \M2E\Kaufland\Model\Product::getStatusTitle($listingProduct->getStatus());
        $productSku = $listingProduct->getMagentoProduct()->getSku();
        $listingTitle = $listingProduct->getListing()->getTitle();

        return <<<HTML
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
    <tr>
        <td>Calculate Action</td>
        <td>$calculateAction</td>
    </tr>
    <tr>
        <td>Request Data</td>
        <td>$requestData</td>
    </tr>
    <tr>
        <td>Request MetaData</td>
        <td>$requestMetaData</td>
    </tr>
</table>
HTML;
    }

    private function printCodeBlock(array $data): string
    {
        return sprintf(
            '<pre class="white-space_pre-wrap">%s</pre>',
            $this->escaper->escapeHtml(
                json_encode(
                    $data,
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
                ),
                ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401,
            ),
        );
    }

    private function renderHtml(string $body): string
    {
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
  <head>
    <title>Kaufland Module Tools | Print Request Data</title>
    <style>
    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    .sticky-form-wrapper {
        background: #d3d3d3;
        position: sticky;
        top: 0;
        width: 100%
    }

    form {
        padding: 10px;
        font-size: 16px;
        position: relative
    }

    .form-row:not(:last-child) {
        margin-bottom: 10px
    }

    .form-row label {
        display: inline-block;
        min-width: 100px
    }

    .form-row input, .form-row select {
        min-width: 200px
    }

    button.run {
        padding: 7px 15px; font-weight: 700
    }

    table {
      border-collapse: collapse;
      width: 100%;
    }

    td:first-child {
        width: 200px;
    }

    .white-space_pre-wrap {
        white-space: pre-wrap;
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
  </head>
  <body>$body</body>
</html>
HTML;
    }
}
