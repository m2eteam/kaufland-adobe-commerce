<?php

declare(strict_types=1);

namespace M2E\Kaufland\Ui\Product\Component\Unmanaged\Column;

use Magento\Framework\Locale\CurrencyInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Price extends Column
{
    private CurrencyInterface $localeCurrency;
    private \M2E\Kaufland\Model\Storefront\Repository $storefrontRepository;

    public function __construct(
        \M2E\Kaufland\Model\Storefront\Repository $storefrontRepository,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        CurrencyInterface $localeCurrency,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->localeCurrency = $localeCurrency;
        $this->storefrontRepository = $storefrontRepository;
    }

    public function prepareDataSource(array $dataSource): array
    {
        if (empty($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$row) {
            $price = $row['price'];

            try {
                $currencyCode = $this->storefrontRepository->get((int)$row['storefront_id'])->getCurrencyCode();
            } catch (\M2E\Kaufland\Model\Exception\Logic $e) {
                continue;
            }

            $price = $this->localeCurrency->getCurrency($currencyCode)->toCurrency($price);

            $row['price'] = $price;
        }

        return $dataSource;
    }
}
