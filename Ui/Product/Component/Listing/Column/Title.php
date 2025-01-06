<?php

declare(strict_types=1);

namespace M2E\Kaufland\Ui\Product\Component\Listing\Column;

use M2E\Kaufland\Model\Storefront\Repository;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use M2E\Core\Helper\Url;
use M2E\Kaufland\Model\Product\Ui\RuntimeStorage;

class Title extends Column
{
    private Url $urlHelper;
    private RuntimeStorage $productUiRuntimeStorage;
    private Repository $storefrontRepository;

    public function __construct(
        Repository $storefrontRepository,
        Url $urlHelper,
        RuntimeStorage $productUiRuntimeStorage,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->urlHelper = $urlHelper;
        $this->storefrontRepository = $storefrontRepository;
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

            $productTitle = $product->getOnlineTitle();
            if (empty($productTitle)) {
                $productTitle = $row['name'] ?? '--';
            }

            $html = sprintf('<p>%s</p>', $productTitle);

            $html .= $this->renderLine(
                (string)__('Listing'),
                sprintf(
                    '<a href="%s" target="_blank">%s</a>',
                    $this->getListingLink($product->getListingId()),
                    $row['listing_title']
                )
            );

            $html .= $this->renderLine(
                (string)__('Account'),
                sprintf(
                    '%s',
                    $row['account_title']
                )
            );

            $html .= $this->renderLine(
                (string)__('Storefront'),
                sprintf(
                    '%s',
                    $this->getStorefrontTitle((int)$row['listing_storefront_id'])
                )
            );

            $html .= $this->renderLine((string)__('SKU'), $row['sku']);

            $row['column_title'] = $html;
        }

        return $dataSource;
    }

    private function renderLine(string $label, string $value): string
    {
        return sprintf('<p style="margin: 0"><strong>%s:</strong> %s</p>', $label, $value);
    }

    private function getListingLink(int $listingId): string
    {
        $params = [
            'back' => $this->urlHelper->makeBackUrlParam('m2e_kaufland/product_grid/allItems'),
            'id' => $listingId,
            'view_mode' => \M2E\Kaufland\Block\Adminhtml\Kaufland\Listing\View\Switcher::VIEW_MODE_KAUFLAND,
        ];

        $filters = [];

        return $this->urlHelper->getUrlWithFilter('m2e_kaufland/kaufland_listing/view', $filters, $params);
    }

    private function getStorefrontTitle(int $storefrontId): string
    {
        return $this->storefrontRepository->get($storefrontId)->getTitle();
    }
}
