<?php

namespace M2E\Kaufland\Block\Adminhtml\Magento\Grid\Column\Renderer;

class ProductId extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Number
{
    use \M2E\Kaufland\Block\Adminhtml\Traits\BlockTrait;

    protected \M2E\Kaufland\Model\Factory $modelFactory;
    private \M2E\Kaufland\Helper\Module\Configuration $moduleConfiguration;
    private \M2E\Kaufland\Model\Magento\ProductFactory $magentoProductFactory;

    public function __construct(
        \M2E\Kaufland\Model\Magento\ProductFactory $magentoProductFactory,
        \M2E\Kaufland\Helper\Module\Configuration $moduleConfiguration,
        \Magento\Backend\Block\Context $context,
        \M2E\Kaufland\Model\Factory $modelFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->modelFactory = $modelFactory;
        $this->moduleConfiguration = $moduleConfiguration;
        $this->magentoProductFactory = $magentoProductFactory;
    }

    public function render(\Magento\Framework\DataObject $row)
    {
        $productId = $this->_getValue($row);

        if ($productId === null) {
            return __('N/A');
        }

        if ($this->getColumn()->getData('store_id') !== null) {
            $storeId = (int)$this->getColumn()->getData('store_id');
        } elseif ($row->getData('store_id') !== null) {
            $storeId = (int)$row->getData('store_id');
        } else {
            $storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        }

        $url = $this->getUrl('catalog/product/edit', ['id' => $productId, 'store' => $storeId]);
        $withoutImageHtml = sprintf(
            '<a href="%s" target="_blank">%s</a>',
            $url,
            $productId
        );

        if (!$this->moduleConfiguration->getViewShowProductsThumbnailsMode()) {
            return $withoutImageHtml;
        }

        $magentoProduct = $this->magentoProductFactory->create();
        $magentoProduct->setProductId($productId);
        $magentoProduct->setStoreId($storeId);

        $thumbnail = $magentoProduct->getThumbnailImage();
        if ($thumbnail === null) {
            return $withoutImageHtml;
        }

        return <<<HTML
<a href="{$url}" target="_blank">
    {$productId}
    <div style="margin-top: 5px"><img style="max-width: 100px; max-height: 100px;" src="{$thumbnail->getUrl()}" /></div>
</a>
HTML;
    }

    public function renderExport(\Magento\Framework\DataObject $row)
    {
        return $this->_getValue($row) ?? '';
    }
}
