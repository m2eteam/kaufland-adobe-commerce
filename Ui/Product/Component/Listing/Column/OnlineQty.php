<?php

declare(strict_types=1);

namespace M2E\Kaufland\Ui\Product\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use M2E\Kaufland\Model\Product\Ui\RuntimeStorage;

class OnlineQty extends Column
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

            $style = $product->getOnlineQty() > 0 ? 'text-decoration: line-through;' : '';
            if ($product->isStatusNotListed() && empty($row['product_online_qty'])) {
                $row['product_online_qty'] = sprintf(
                    '<span style="color: gray">%s</span>',
                    __('Not Listed')
                );
            } elseif ($product->isStatusInactive()) {
                $row['product_online_qty'] = sprintf(
                    '<span style="color: gray; %s">%s</span>',
                    $style,
                    $product->getOnlineQty()
                );
            } else {
                $row['product_online_qty'] = $product->getOnlineQty();
            }
        }

        return $dataSource;
    }
}
