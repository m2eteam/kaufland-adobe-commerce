<?php

declare(strict_types=1);

namespace M2E\Kaufland\Ui\Product\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use M2E\Kaufland\Helper\Module\Configuration;
use M2E\Kaufland\Model\Magento\ProductFactory;
use Magento\Framework\View\Element\UiComponentFactory;

class MagentoProductId extends Column
{
    private UrlInterface $url;
    private Configuration $moduleConfiguration;
    private ProductFactory $magentoProductFactory;

    public function __construct(
        Configuration $moduleConfiguration,
        ProductFactory $magentoProductFactory,
        UrlInterface $url,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->moduleConfiguration = $moduleConfiguration;
        $this->url = $url;
        $this->magentoProductFactory = $magentoProductFactory;
    }

    public function prepareDataSource(array $dataSource): array
    {
        if (empty($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$row) {
            if (empty($row['entity_id'])) {
                $row['entity_id'] = __('N/A');
                continue;
            }

            $storeId = (int)$row['listing_store_id'];
            $magentoProductId = (int)$row['entity_id'];
            $magentoProductUrl = $this->generateMagentoProductUrl(
                $magentoProductId,
                $storeId,
            );

            $withoutImageHtml = sprintf(
                '<a href="%s" target="_blank">%s</a>',
                $magentoProductUrl,
                $row['entity_id']
            );

            $row['entity_id'] = $withoutImageHtml;
            if (!$this->moduleConfiguration->getViewShowProductsThumbnailsMode()) {
                continue;
            }

            $magentoProduct = $this->magentoProductFactory->createByProductId($magentoProductId);
            $magentoProduct->setStoreId($storeId);

            $thumbnail = $magentoProduct->getThumbnailImage();
            if ($thumbnail === null) {
                continue;
            }

            $row['entity_id'] = <<<HTML
<a href="{$magentoProductUrl}" target="_blank">
    {$magentoProductId}
    <div style="margin-top: 5px"><img style="max-width: 100px; max-height: 100px;" src="{$thumbnail->getUrl()}" /></div>
</a>
HTML;
        }

        return $dataSource;
    }

    private function generateMagentoProductUrl(int $entityId, int $storeId): string
    {
        return $this->url->getUrl('catalog/product/edit', ['id' => $entityId, 'store' => $storeId]);
    }
}
