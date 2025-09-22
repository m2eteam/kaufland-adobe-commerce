<?php

declare(strict_types=1);

namespace M2E\Kaufland\Block\Adminhtml\Kaufland\Template\Category;

use M2E\Kaufland\Model\Category\Dictionary;
use M2E\Kaufland\Model\ResourceModel\Category\Dictionary\CollectionFactory as DictionaryCollectionFactory;

class Grid extends \M2E\Kaufland\Block\Adminhtml\Magento\Grid\AbstractGrid
{
    private \M2E\Kaufland\Model\ResourceModel\Storefront $storefrontResource;
    private DictionaryCollectionFactory $categoryDictionaryCollectionFactory;
    private \M2E\Kaufland\Model\ResourceModel\Storefront\CollectionFactory $storefrontCollectionFactory;
    private \M2E\Kaufland\Model\ResourceModel\Product $productResource;
    private \M2E\Core\Ui\AppliedFilters\Manager $appliedFiltersManager;

    public function __construct(
        \M2E\Kaufland\Model\ResourceModel\Product $productResource,
        \M2E\Core\Ui\AppliedFilters\Manager $appliedFiltersManager,
        \M2E\Kaufland\Model\ResourceModel\Storefront $storefrontResource,
        DictionaryCollectionFactory $categoryDictionaryCollectionFactory,
        \M2E\Kaufland\Model\ResourceModel\Storefront\CollectionFactory $storefrontCollectionFactory,
        \M2E\Kaufland\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->storefrontResource = $storefrontResource;
        $this->categoryDictionaryCollectionFactory = $categoryDictionaryCollectionFactory;
        $this->storefrontCollectionFactory = $storefrontCollectionFactory;

        parent::__construct($context, $backendHelper, $data);
        $this->productResource = $productResource;
        $this->appliedFiltersManager = $appliedFiltersManager;
    }

    public function _construct()
    {
        parent::_construct();

        $this->setId('kauflandTemplateCategoryGrid');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setDefaultSort('id');
        $this->setDefaultDir('asc');
    }

    protected function _prepareCollection()
    {
        $collection = $this->categoryDictionaryCollectionFactory->create();
        $collection->join(
            ['storefront' => $this->storefrontResource->getMainTable()],
            'main_table.storefront_id=storefront.id',
            [
                'storefront_id' => 'storefront.id',
            ]
        );

        $collection->getSelect()->where(
            'main_table.state != ?',
            Dictionary::DRAFT_STATE
        );

        $collection->getSelect()->columns('storefront.storefront_code');

        $collection->joinLeft(
            ['products' => $this->createProductCountJoinTable()],
            'template_category_id = main_table.id',
            ['product_count' => 'count']
        );

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'category_id',
            [
                'header' => __('Category ID'),
                'align' => 'center',
                'type' => 'text',
                'index' => 'category_id',
            ]
        );

        $this->addColumn(
            'path',
            [
                'header' => __('Title'),
                'align' => 'left',
                'type' => 'text',
                'escape' => true,
                'index' => 'path',
                'filter_condition_callback' => [$this, 'callbackFilterPath'],
            ]
        );

        $this->addColumn(
            'storefront_code',
            [
                'header' => __('Storefront'),
                'align' => 'left',
                'type' => 'options',
                'width' => '100px',
                'index' => 'storefront_code',
                'frame_callback' => [$this, 'callbackColumnStorefrontTitle'],
                'filter_condition_callback' => [$this, 'callbackFilterStorefront'],
                'options' => $this->getStorefrontIdOptions(),
            ]
        );

        $this->addColumn(
            'product_count',
            [
                'header' => __('Products'),
                'align' => 'center',
                'type' => 'number',
                'index' => 'product_count',
                'filter_index' => 'products.count',
                'frame_callback' => [$this, 'callbackColumnProductCount'],
            ]
        );

        $this->addColumn(
            'total_attributes',
            [
                'header' => __('Attributes: Total'),
                'align' => 'left',
                'type' => 'text',
                'width' => '100px',
                'index' => 'total_product_attributes',
                'filter' => false,
            ]
        );

        $this->addColumn(
            'used_attributes',
            [
                'header' => __('Attributes: Used'),
                'align' => 'left',
                'type' => 'text',
                'width' => '100px',
                'index' => 'used_product_attributes',
                'filter' => false,
            ]
        );

        $this->addColumn(
            'actions',
            [
                'header' => __('Actions'),
                'align' => 'left',
                'width' => '70px',
                'type' => 'action',
                'index' => 'actions',
                'filter' => false,
                'sortable' => false,
                'renderer' => \M2E\Kaufland\Block\Adminhtml\Magento\Grid\Column\Renderer\Action::class,
                'actions' => [
                    [
                        'caption' => __('Edit'),
                        'url' => [
                            'base' => '*/kaufland_category/view',
                            'params' => [
                                'dictionary_id' => '$id',
                            ],
                        ],
                        'field' => 'id',
                    ],
                ],
            ]
        );

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('main_table.id');
        $this->getMassactionBlock()->setFormFieldName('ids');

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Remove'),
                'url' => $this->getUrl('*/kaufland_category/delete'),
                'confirm' => __('Are you sure?'),
            ]
        );

        return parent::_prepareMassaction();
    }

    protected function callbackFilterPath($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        if ($value == null) {
            return;
        }

        $collection->getSelect()->where('main_table.path LIKE ?', '%' . $value . '%');
    }

    private function getStorefrontIdOptions(): array
    {
        $collection = $this->storefrontCollectionFactory->create();
        $options = [];
        /** @var \M2E\Kaufland\Model\Storefront $item */
        foreach ($collection as $item) {
            $options[$item->getId()] = $item->getTitle();
        }

        return $options;
    }

    public function callbackColumnStorefrontTitle($value, $row, $column, $isExport)
    {
        $title = $row->getStorefront()->getTitle();

        return $title;
    }

    public function callbackColumnProductCount($value, $row, $column, $isExport): string
    {
        if (empty($value)) {
            return '0';
        }

        $appliedFiltersBuilder = new \M2E\Core\Ui\AppliedFilters\Builder();
        $appliedFiltersBuilder->addSelectFilter('product_template_category_id', [$row->getId()]);

        $url = $this->appliedFiltersManager->createUrlWithAppliedFilters(
            '*/product_grid/allItems',
            $appliedFiltersBuilder->build()
        );

        return sprintf('<a href="%s" target="_blank">%s</a>', $url, $value);
    }

    protected function callbackFilterStorefront($collection, $column): void
    {
        $value = $column->getFilter()->getValue();
        if ($value == null) {
            return;
        }

        $collection->getSelect()->where('main_table.storefront_id = ?', $value);
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', ['_current' => true]);
    }

    public function getRowUrl($item)
    {
        return false;
    }

    private function createProductCountJoinTable(): \Magento\Framework\DB\Select
    {
        return $this->productResource
            ->getConnection()
            ->select()
            ->from(
                ['temp' => $this->productResource->getMainTable()],
                [
                    'template_category_id' => $this->productResource::COLUMN_TEMPLATE_CATEGORY_ID,
                    'count' => new \Zend_Db_Expr('COUNT(*)'),
                ]
            )
            ->group($this->productResource::COLUMN_TEMPLATE_CATEGORY_ID);
    }
}
