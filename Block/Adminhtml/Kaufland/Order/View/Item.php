<?php

namespace M2E\Kaufland\Block\Adminhtml\Kaufland\Order\View;

use M2E\Kaufland\Block\Adminhtml\Magento\Grid\AbstractGrid;
use M2E\Kaufland\Model\ResourceModel\Order\Item as OrderItem;

class Item extends AbstractGrid
{
    protected \Magento\Catalog\Model\Product $productModel;
    protected \Magento\Tax\Model\Calculation $taxCalculator;
    private \M2E\Kaufland\Model\Order $order;
    private \M2E\Kaufland\Model\Currency $currency;
    private \M2E\Kaufland\Model\Listing\Other\Repository $otherRepository;
    private \M2E\Kaufland\Model\Order\Item\Repository $orderItemRepository;

    public function __construct(
        \M2E\Kaufland\Model\Order\Item\Repository $orderItemRepository,
        \M2E\Kaufland\Model\Currency $currency,
        \Magento\Catalog\Model\Product $productModel,
        \Magento\Tax\Model\Calculation $taxCalculator,
        \M2E\Kaufland\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \M2E\Kaufland\Model\Order $order,
        \M2E\Kaufland\Model\Listing\Other\Repository $otherRepository,
        array $data = []
    ) {
        $this->productModel = $productModel;
        $this->taxCalculator = $taxCalculator;
        $this->order = $order;
        $this->currency = $currency;
        $this->otherRepository = $otherRepository;
        $this->orderItemRepository = $orderItemRepository;
        parent::__construct($context, $backendHelper, $data);
    }

    public function _construct()
    {
        parent::_construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('KauflandOrderViewItem');
        // ---------------------------------------

        // Set default values
        // ---------------------------------------
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setPagerVisibility(false);
        $this->setFilterVisibility(false);
        $this->setUseAjax(true);
        $this->_defaultLimit = 200;
        // ---------------------------------------
    }

    protected function _prepareCollection()
    {
        $collection = $this->orderItemRepository->getGroupOrderItems($this->order->getId());
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('products', [
            'header' => __('Product'),
            'align' => 'left',
            'width' => '*',
            'index' => 'product_id',
            'frame_callback' => [$this, 'callbackColumnProduct'],
        ]);

        $this->addColumn('stock_availability', [
            'header' => __('Stock Availability'),
            'width' => '100px',
            'filter' => false,
            'sortable' => false,
            'frame_callback' => [$this, 'callbackColumnIsInStock'],
        ]);

        $this->addColumn('original_price', [
            'header' => __('Original Price'),
            'align' => 'left',
            'width' => '80px',
            'filter' => false,
            'sortable' => false,
            'frame_callback' => [$this, 'callbackColumnOriginalPrice'],
        ]);

        $this->addColumn('sale_price', [
            'header' => __('Price'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'sale_price',
            'filter' => false,
            'sortable' => false,
            'frame_callback' => [$this, 'callbackColumnPrice'],
        ]);

        $this->addColumn('qty_sold', [
            'header' => __('QTY'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'qty_purchased',
            'filter' => false,
            'sortable' => false,
            'frame_callback' => [$this, 'callbackColumnQty'],
        ]);

        $this->addColumn('tax_percent', [
            'header' => __('Tax Percent'),
            'align' => 'left',
            'width' => '80px',
            'filter' => false,
            'sortable' => false,
            'frame_callback' => [$this, 'callbackColumnTaxPercent'],
        ]);

        $this->addColumn('row_total', [
            'header' => __('Row Total'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'sale_price',
            'filter' => false,
            'sortable' => false,
            'frame_callback' => [$this, 'callbackColumnRowTotal'],
        ]);

        return parent::_prepareColumns();
    }

    /**
     * @param string $value
     * @param \M2E\Kaufland\Model\Order\Item $row
     * @param \Magento\Backend\Block\Widget\Grid\Column\Extended $column
     * @param bool $isExport
     *
     * @return string
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    public function callbackColumnProduct($value, \M2E\Kaufland\Model\Order\Item $row, $column, $isExport)
    {
        $productLink = '';
        if ($row->getMagentoProductId()) {
            $productUrl = $this->getUrl('catalog/product/edit', [
                'id' => $row->getMagentoProductId(),
                'store' => $row->getOrder()->getStoreId(),
            ]);
            $productLink = sprintf(
                '<a href="%s" target="_blank">%s</a>',
                $productUrl,
                __('View')
            );
        }

        $editLink = '';
        if (!$row->getMagentoProductId()) {
            $onclick = "OrderEditItemObj.edit('{$this->getId()}', '{$row->getOrderItemsIds()}')";
            $editLink = sprintf(
                '<a class="gray" href="javascript:void(0);" onclick="%s">%s</a>',
                $onclick,
                __('Link to Magento Product')
            );
        }

        if ($row->getMagentoProductId() && $row->getMagentoProduct()->isProductWithVariations()) {
            $onclick = "OrderEditItemObj.edit('{$this->getId()}', '{$row->getOrderItemsIds()}',)";
            $editLink = sprintf(
                '<a class="gray" href="javascript:void(0);" onclick="%s">%s</a>',
                $onclick,
                __('Set Options')
            ) . '&nbsp;|&nbsp;';
        }

        $discardLink = '';
        if ($row->getMagentoProductId()) {
            $onclick = "OrderEditItemObj.unassignProduct('{$this->getId()}', '{$row->getOrderItemsIds()}')";
            $discardLink = sprintf(
                '<a class="gray" href="javascript:void(0);" onclick="%s">%s</a>',
                $onclick,
                __('Unlink')
            );
        }

        $titleLine = sprintf(
            '<p><strong>%s</strong></p>',
            \M2E\Core\Helper\Data::escapeHtml($row->getTitle())
        );

        if ($row->getMagentoProduct()) {
            $skuLine = sprintf(
                '<p><strong>%s:</strong> %s</p>',
                __('SKU'),
                \M2E\Core\Helper\Data::escapeHtml($row->getMagentoProduct()->getSku())
            );
        } else {
            $skuLine = '';
        }

        $kauflandProduct = $row->getKauflandProduct($row->getKauflandProductId(), $row->getOfferId(), $row->getStorefrontId());
        $unmanagedProduct = $this->otherRepository->getByOfferIdAndStorefrontId($row->getOfferId(), $row->getStorefrontId());

        if ($kauflandProduct) {
            $unitIdLine = sprintf(
                '<p><strong>%s:</strong> %s</p>',
                __('Unit ID'),
                \M2E\Core\Helper\Data::escapeHtml($kauflandProduct->getUnitId())
            );
        } elseif ($unmanagedProduct) {
            $unitIdLine = sprintf(
                '<p><strong>%s:</strong> %s</p>',
                __('Unit ID'),
                \M2E\Core\Helper\Data::escapeHtml($unmanagedProduct->getUnitId())
            );
        } else {
            $unitIdLine = '';
        }

        $actionLine = sprintf(
            '<div style="float: left;">%s</div><div style="float: right;">%s%s</div>',
            $productLink,
            $editLink,
            $discardLink
        );

        return $titleLine . $skuLine . $unitIdLine . $actionLine;
    }

    public function callbackColumnIsInStock($value, \M2E\Kaufland\Model\Order\Item $row, $column, $isExport)
    {
        if (!$row->isMagentoProductExists()) {
            return '<span style="color: red;">' . __('Product Not Found') . '</span>';
        }

        if ($row->getMagentoProduct() === null) {
            return __('N/A');
        }

        if (!$row->getMagentoProduct()->isStockAvailability()) {
            return '<span style="color: red;">' . __('Out Of Stock') . '</span>';
        }

        return __('In Stock');
    }

    public function callbackColumnOriginalPrice($value, \M2E\Kaufland\Model\Order\Item $row, $column, $isExport)
    {
        $formattedPrice = __('N/A');

        $product = $row->getProduct();

        if ($product) {
            /** @var \M2E\Kaufland\Model\Magento\Product $magentoProduct */
            $magentoProduct = $this->modelFactory->getObject('Magento\Product');
            $magentoProduct->setProduct($product);

            if ($magentoProduct->isGroupedType()) {
                $associatedProducts = $row->getAssociatedProducts();
                $price = $this->productModel
                    ->load(array_shift($associatedProducts))
                    ->getPrice();

                $formattedPrice = $this->order->getStore()->getCurrentCurrency()->format($price);
            } else {
                $formattedPrice = $this->order->getStore()
                                              ->getCurrentCurrency()
                                              ->format($row->getProduct()->getPrice());
            }
        }

        return $formattedPrice;
    }

    public function callbackColumnPrice($value, \M2E\Kaufland\Model\Order\Item $row, $column, $isExport)
    {
        return $this->currency->formatPrice(
            $this->order->getCurrency(),
            (float)$value
        );
    }

    public function callbackColumnRowTotal($value, \M2E\Kaufland\Model\Order\Item $row, $column, $isExport)
    {
        $countItem = $row->getData('total_qty');
        $rowTotal = (float)$value * $countItem;

        return $this->currency->formatPrice(
            $this->order->getCurrency(),
            $rowTotal
        );
    }

    public function callbackColumnQty($value, \M2E\Kaufland\Model\Order\Item $row, $column, $isExport)
    {
        return $row->getData('total_qty');
    }

    public function callbackColumnTaxPercent($value, \M2E\Kaufland\Model\Order\Item $row, $column, $isExport)
    {
        $taxDetails = $row->getTaxDetails();
        if ($taxDetails === []) {
            return '0%';
        }

        return sprintf('%s%%', $taxDetails['percent']);
    }

    public function getRowUrl($item)
    {
        return '';
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/orderItemGrid', ['_current' => true]);
    }
}
