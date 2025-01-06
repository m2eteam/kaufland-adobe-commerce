<?php

declare(strict_types=1);

namespace M2E\Kaufland\Ui\Product\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use M2E\Kaufland\Model\Product\Ui\RuntimeStorage;
use M2E\Core\Helper\Url;
use M2E\Core\Helper\Magento\Assets;

class GoToListing extends Column
{
    private RuntimeStorage $productUiRuntimeStorage;
    private Url $urlHelper;
    private Assets $magentoAssets;

    public function __construct(
        RuntimeStorage $productUiRuntimeStorage,
        Url $urlHelper,
        Assets $magentoAssets,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->productUiRuntimeStorage = $productUiRuntimeStorage;
        $this->urlHelper = $urlHelper;
        $this->magentoAssets = $magentoAssets;
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

            $urlData = [
                'back' => $this->urlHelper->makeBackUrlParam('m2e_kaufland/product_grid/allItems'),
                'id' => $product->getListingId(),
                'view_mode' => \M2E\Kaufland\Block\Adminhtml\Kaufland\Listing\View\Switcher::VIEW_MODE_KAUFLAND,
            ];

            $filters = [
                'product_id' => [
                    'from' => $product->getMagentoProductId(),
                    'to' => $product->getMagentoProductId(),
                ],
            ];

            $image = sprintf(
                '<img src="%s" />',
                $this->magentoAssets->getViewFileUrl('M2E_Core::images/goto_listing.png'),
            );

            $html = sprintf(
                '<div style="float:right; margin:5px 15px 0 0;"><a title="%s" target="_blank" href="%s">%s</a></div>',
                __('Go to Listing'),
                $this->urlHelper->getUrlWithFilter('m2e_kaufland/kaufland_listing/view/', $filters, $urlData),
                $image,
            );

            $row['go_to_listing'] = $html;
        }

        return $dataSource;
    }
}
