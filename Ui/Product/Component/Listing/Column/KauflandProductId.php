<?php

declare(strict_types=1);

namespace M2E\Kaufland\Ui\Product\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use M2E\Kaufland\Model\Product\Ui\RuntimeStorage;

class KauflandProductId extends Column
{
    private RuntimeStorage $productUiRuntimeStorage;

    public function __construct(
        RuntimeStorage $productUiRuntimeStorage,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->productUiRuntimeStorage = $productUiRuntimeStorage;
    }

    public function prepareDataSource(array $dataSource): array
    {
        if (empty($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$row) {
            $product = $this->productUiRuntimeStorage->findProduct((int)$row['product_id']);
            if (empty($product)) {
                continue;
            }

            $row['product_kaufland_product_id'] = __('N/A');

            $kauflandProductId = $product->getKauflandProductId();

            if ($product->isStatusNotListed() && !$kauflandProductId) {
                $row['product_kaufland_product_id'] = sprintf('<span style="color: gray;">%s</span>', __('Not Listed'));

                continue;
            }

            if ($kauflandProductId === '') {
                continue;
            }
            try {
                $storefrontCode =  $product->getListing()->getStorefront()->getStorefrontCode();
            } catch (\M2E\Kaufland\Model\Exception\Logic $e) {
                continue;
            }

            $url = 'https://www.kaufland.' . $storefrontCode . '/product/' . $kauflandProductId;

            $row['product_kaufland_product_id'] =  sprintf('<a href="%s" target="_blank">%s</a>', $url, $kauflandProductId);
        }

        return $dataSource;
    }
}
