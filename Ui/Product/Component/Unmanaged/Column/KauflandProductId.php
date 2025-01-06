<?php

declare(strict_types=1);

namespace M2E\Kaufland\Ui\Product\Component\Unmanaged\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class KauflandProductId extends Column
{
    private \M2E\Kaufland\Model\Storefront\Repository $storefrontRepository;

    public function __construct(
        \M2E\Kaufland\Model\Storefront\Repository $storefrontRepository,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->storefrontRepository = $storefrontRepository;
    }

    public function prepareDataSource(array $dataSource): array
    {
        if (empty($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$row) {
            $kauflandProductId = $row['kaufland_product_id'];

            try {
                $storefrontCode = $this->storefrontRepository->get((int)$row['storefront_id'])->getStorefrontCode();
            } catch (\M2E\Kaufland\Model\Exception\Logic $e) {
                continue;
            }

            $url = 'https://www.kaufland.' . $storefrontCode . '/product/' . $kauflandProductId;

            $row['kaufland_product_id'] =  sprintf('<a href="%s" target="_blank">%s</a>', $url, $kauflandProductId);
        }

        return $dataSource;
    }
}
