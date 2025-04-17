<?php

declare(strict_types=1);

namespace M2E\Kaufland\Block\Adminhtml\Listing\Wizard\Product\SearchChannelIdStep;

use M2E\Kaufland\Model\ResourceModel\Listing\Wizard\Product as WizardProductResource;

class Grid extends \M2E\Kaufland\Block\Adminhtml\Magento\Grid\AbstractGrid
{
    private \M2E\Kaufland\Model\Listing $listing;
    private \M2E\Kaufland\Model\Listing\Wizard\Manager $wizardManager;
    private \M2E\Kaufland\Model\ResourceModel\Magento\Product\CollectionFactory $magentoProductCollectionFactory;

    private \M2E\Kaufland\Model\ResourceModel\Listing\Wizard\Product $wizardProductResource;
    private \M2E\Kaufland\Model\Magento\ProductFactory $magentoProductFactory;
    private \M2E\Kaufland\Helper\Component\Kaufland\Configuration $configuration;
    private \Magento\Catalog\Api\ProductRepositoryInterface $productRepository;
    private \Magento\Framework\Message\ManagerInterface $messageManager;

    public function __construct(
        \M2E\Kaufland\Model\Listing $listing,
        \M2E\Kaufland\Model\Listing\Wizard\Manager $wizardManager,
        \M2E\Kaufland\Model\ResourceModel\Magento\Product\CollectionFactory $magentoProductCollectionFactory,
        \M2E\Kaufland\Model\ResourceModel\Listing\Wizard\Product $wizardProductResource,
        \M2E\Kaufland\Model\Magento\ProductFactory $magentoProductFactory,
        \M2E\Kaufland\Helper\Component\Kaufland\Configuration $configuration,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \M2E\Kaufland\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->magentoProductCollectionFactory = $magentoProductCollectionFactory;
        $this->listing = $listing;
        $this->wizardManager = $wizardManager;
        $this->wizardProductResource = $wizardProductResource;
        $this->magentoProductFactory = $magentoProductFactory;
        $this->configuration = $configuration;
        $this->productRepository = $productRepository;
        $this->messageManager = $messageManager;
        parent::__construct($context, $backendHelper, $data);
    }

    public function _construct(): void
    {
        parent::_construct();

        $this->setId('SearchKauflandProductIdForListingProductsGrid' . $this->listing->getId());
    }

    protected function _prepareCollection()
    {
        $listingProductsIds = $this->wizardManager->getProductsIds();

        $collection = $this->magentoProductCollectionFactory->create();
        $collection->setListingProductModeOn();
        $collection->setStoreId($this->listing->getStoreId());

        $collection->addAttributeToSelect('sku');
        $collection->addAttributeToSelect('name');

        $wizardProductTableName = $this->wizardProductResource->getMainTable();
        $collection->joinTable(
            ['lp' => $wizardProductTableName],
            sprintf('%s = entity_id', WizardProductResource::COLUMN_MAGENTO_PRODUCT_ID),
            [
                'id' => WizardProductResource::COLUMN_ID,
                'status_search' => WizardProductResource::COLUMN_PRODUCT_ID_SEARCH_STATUS,
                'kaufland_product_id' => WizardProductResource::COLUMN_KAUFLAND_PRODUCT_ID,
            ],
            '{{table}}.wizard_id=' . $this->wizardManager->getWizardId(),
        );

        $collection->getSelect()->where('lp.magento_product_id IN (?)', $listingProductsIds);

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('entity_id', [
            'header' => __('Product ID'),
            'align' => 'right',
            'width' => '100px',
            'type' => 'number',
            'index' => 'entity_id',
            'store_id' => $this->listing->getStoreId(),
            'renderer' => \M2E\Kaufland\Block\Adminhtml\Magento\Grid\Column\Renderer\ProductId::class,
        ]);

        $this->addColumn('name', [
            'header' => __('Product Title / Product SKU'),
            'align' => 'left',
            'type' => 'text',
            'index' => 'name',
            'filter_index' => 'name',
            'escape' => false,
            'frame_callback' => [$this, 'callbackColumnProductTitle'],
            'filter_condition_callback' => [$this, 'callbackFilterTitle'],
        ]);

        $this->addColumn('kaufland_product_id', [
            'header' => __('%channel_title Product ID', ['channel_title' => \M2E\Kaufland\Helper\Module::getChannelTitle()]),
            'align' => 'left',
            'type' => 'text',
            'index' => 'kaufland_product_id',
            'escape' => false,
            'filter_index' => 'kaufland_product_id',
            'frame_callback' => [$this, 'callbackColumnKauflandProductId'],
        ]);

        $this->addColumn('settings', [
            'header' => __('Search Values'),
            'align' => 'left',
            'filter' => false,
            'sortable' => false,
            'type' => 'text',
            'index' => 'id',
            'frame_callback' => [$this, 'callbackColumnSearchValues'],
        ]);

        $this->addColumn('status_search', [
            'header' => __('Search Status'),
            'index' => 'status_search',
            'filter_index' => 'status_search',
            'sortable' => false,
            'type' => 'options',
            'options' => [
                \M2E\Kaufland\Model\Product::SEARCH_STATUS_NONE => __('None'),
                \M2E\Kaufland\Model\Product::SEARCH_STATUS_COMPLETED => __('Completed'),
            ],
            'frame_callback' => [$this, 'callbackColumnStatus'],
        ]);

        return parent::_prepareColumns();
    }

    public function callbackColumnProductTitle($productTitle, $row, $column, $isExport)
    {
        $productTitle = $this->_escaper->escapeHtml($productTitle);

        $value = '<span>' . $productTitle . '</span>';

        $tempSku = $row->getData('sku');
        if ($tempSku === null) {
            $tempSku = $this->magentoProductFactory->create()
                                                   ->setProductId($row->getData('entity_id'))
                                                   ->getSku();
        }

        $value .= '<br/><strong>' . __('SKU') .
            ':</strong> ' . $this->_escaper->escapeHtml($tempSku) . '<br/>';

        return $value;
    }

    public function callbackColumnKauflandProductId($kauflandProductId, $row, $column, $isExport)
    {
        $isSearchCompleted = ((int)$row['status_search']) === \M2E\Kaufland\Model\Product::SEARCH_STATUS_COMPLETED;
        if ($isSearchCompleted) {
            if (empty($kauflandProductId)) {
                return __('Not Found');
            }

            return $row->getData('kaufland_product_id');
        }

        if (empty($kauflandProductId)) {
            return __('Searching...');
        }

        return $row->getData('kaufland_product_id');
    }

    public function callbackColumnSearchValues($value, $row, $column, $isExport)
    {
        $eanAttributeCode = $this->configuration->getIdentifierCodeCustomAttribute();
        if (!$eanAttributeCode) {
            return __('Not Set');
        }

        $magentoProduct = $this->productRepository->get($row->getData('sku'));
        $eanAttributeCode = $this->configuration->getIdentifierCodeCustomAttribute();
        $searchValue = $magentoProduct->getCustomAttribute($eanAttributeCode);
        if ($searchValue) {
            $searchValue = $this->_escaper->escapeHtml($searchValue->getValue());
        } else {
            $searchValue = __('Not Set');
        }

        return '<strong>' . __('EAN') . ':</strong>' . ' ' . $searchValue;
    }

    public function callbackColumnStatus($value, $row, $column, $isExport)
    {
        $html = '';
        switch ($row->getData('status_search')) {
            case \M2E\Kaufland\Model\Product::SEARCH_STATUS_NONE:
                $html .= '<span style="color: gray;">' . __('None') . '</span>';
                break;

            case \M2E\Kaufland\Model\Product::SEARCH_STATUS_COMPLETED:
                $html .= '<span style="color: green;">' . __('Completed') . '</span>';

                break;
        }

        return $html;
    }

    protected function callbackFilterTitle($collection, $column): void
    {
        $value = $column->getFilter()->getValue();

        if ($value === null) {
            return;
        }

        $collection->addFieldToFilter(
            [
                ['attribute' => 'sku', 'like' => '%' . $value . '%'],
                ['attribute' => 'name', 'like' => '%' . $value . '%'],
            ]
        );
    }
}
