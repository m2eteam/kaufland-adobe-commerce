<?php

declare(strict_types=1);

namespace M2E\Kaufland\Ui\Product\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use M2E\Kaufland\Model\Product\Ui\RuntimeStorage;

class OnlinePrice extends Column
{
    private RuntimeStorage $productUiRuntimeStorage;
    private CurrencyInterface $localeCurrency;

    public function __construct(
        RuntimeStorage $productUiRuntimeStorage,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        CurrencyInterface $localeCurrency,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->productUiRuntimeStorage = $productUiRuntimeStorage;
        $this->localeCurrency = $localeCurrency;
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

            if ($product->isStatusNotListed() && empty($row['product_online_price'])) {
                $row['product_online_price'] = sprintf('<span style="color: gray;">%s</span>', __('Not Listed'));

                continue;
            }

            $currencyCode = $product->getListing()->getStorefront()->getCurrencyCode();

            $onlinePrice = $this->localeCurrency->getCurrency($currencyCode)
                                                ->toCurrency($product->getOnlineCurrentPrice());
            if ($product->isStatusInactive()) {
                $row['product_online_price'] =  sprintf(
                    '<span style="color: gray;">%s</span>',
                    $onlinePrice
                );
            } else {
                $row['product_online_price'] = $onlinePrice;
            }
        }

        return $dataSource;
    }
}
