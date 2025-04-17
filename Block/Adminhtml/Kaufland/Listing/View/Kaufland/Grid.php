<?php

namespace M2E\Kaufland\Block\Adminhtml\Kaufland\Listing\View\Kaufland;

use M2E\Kaufland\Model\ResourceModel\Product as ListingProductResource;
use M2E\Kaufland\Model\ResourceModel\Category\Dictionary as CategoryDictionaryResource;
use M2E\Kaufland\Model\Product;

class Grid extends \M2E\Kaufland\Block\Adminhtml\Listing\View\AbstractGrid
{
    private const STATUS_INCOMPLETE = 'Incomplete';

    private \M2E\Kaufland\Model\ResourceModel\Magento\Product\CollectionFactory $magentoProductCollectionFactory;
    private \M2E\Kaufland\Helper\Data\Session $sessionDataHelper;
    private CategoryDictionaryResource $categoryDictionaryResource;
    private ListingProductResource $listingProductResource;
    private \M2E\Core\Helper\Url $urlHelper;

    public function __construct(
        ListingProductResource $listingProductResource,
        CategoryDictionaryResource $categoryDictionaryResource,
        \M2E\Kaufland\Model\ResourceModel\Magento\Product\CollectionFactory $magentoProductCollectionFactory,
        \M2E\Kaufland\Helper\Data\Session $sessionDataHelper,
        \M2E\Kaufland\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \M2E\Kaufland\Helper\Data $dataHelper,
        \M2E\Core\Helper\Url $urlHelper,
        \M2E\Kaufland\Helper\Data\GlobalData $globalDataHelper,
        \M2E\Kaufland\Model\Currency $currency,
        array $data = []
    ) {
        $this->magentoProductCollectionFactory = $magentoProductCollectionFactory;
        $this->sessionDataHelper = $sessionDataHelper;
        $this->listingProductResource = $listingProductResource;
        $this->urlHelper = $urlHelper;
        $this->categoryDictionaryResource = $categoryDictionaryResource;

        parent::__construct(
            $context,
            $backendHelper,
            $dataHelper,
            $globalDataHelper,
            $sessionDataHelper,
            $data
        );
    }

    public function _construct()
    {
        parent::_construct();

        $this->setDefaultSort(false);

        $this->setId('kauflandListingViewGrid' . $this->listing->getId());

        $this->showAdvancedFilterProductsOption = false;
    }

    protected function _setCollectionOrder($column)
    {
        $collection = $this->getCollection();
        if ($collection) {
            $columnIndex = $column->getFilterIndex() ? $column->getFilterIndex() : $column->getIndex();
            $collection->getSelect()->order($columnIndex . ' ' . strtoupper($column->getDir()));
        }

        return $this;
    }

    protected function _prepareCollection()
    {
        $listingData = $this->listing->getData();

        $collection = $this->magentoProductCollectionFactory->create();
        $collection->setListingProductModeOn();
        $collection->setStoreId($this->listing->getStoreId());

        $collection->addAttributeToSelect('sku');
        $collection->addAttributeToSelect('name');

        $listingProductTableName = $this->listingProductResource->getMainTable();
        $collection->joinTable(
            ['lp' => $listingProductTableName],
            sprintf('%s = entity_id', ListingProductResource::COLUMN_MAGENTO_PRODUCT_ID),
            [
                'id' => ListingProductResource::COLUMN_ID,
                'status' => ListingProductResource::COLUMN_STATUS,
                'kaufland_product_id' => ListingProductResource::COLUMN_KAUFLAND_PRODUCT_ID,
                'offer_id' => ListingProductResource::COLUMN_OFFER_ID,
                'additional_data' => ListingProductResource::COLUMN_ADDITIONAL_DATA,
                'online_qty' => ListingProductResource::COLUMN_ONLINE_QTY,
                'online_price' => ListingProductResource::COLUMN_ONLINE_PRICE,
                'online_title' => ListingProductResource::COLUMN_ONLINE_TITLE,
                'unit_id' => ListingProductResource::COLUMN_UNIT_ID,
                'is_kaufland_product_creator' => ListingProductResource::COLUMN_IS_KAUFLAND_PRODUCT_CREATOR,
                'channel_product_empty_attributes' => ListingProductResource::COLUMN_CHANNEL_PRODUCT_EMPTY_ATTRIBUTES,
                'is_incomplete' => ListingProductResource::COLUMN_IS_INCOMPLETE,
                'template_category_id' => ListingProductResource::COLUMN_TEMPLATE_CATEGORY_ID,
            ],
            '{{table}}.listing_id=' . (int)$listingData['id']
        );

        $categoryDictionaryTableName = $this->categoryDictionaryResource->getMainTable();
        $collection
            ->joinTable(
                ['cd' => $categoryDictionaryTableName],
                sprintf('%s = template_category_id', CategoryDictionaryResource::COLUMN_ID),
                [
                    'categories_data' => CategoryDictionaryResource::COLUMN_PATH,
                    'online_category_id' => CategoryDictionaryResource::COLUMN_CATEGORY_ID,
                    'online_category_path' => CategoryDictionaryResource::COLUMN_PATH,
                ],
                null,
                'left'
            );

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addExportType('*/*/exportCsvListingGrid', __('CSV'));

        $this->addColumn('product_id', [
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
            'header_export' => __('Product SKU'),
            'align' => 'left',
            'type' => 'text',
            'index' => 'offer_id',
            'escape' => false,
            'frame_callback' => [$this, 'callbackColumnTitle'],
            'filter_condition_callback' => [$this, 'callbackFilterTitle'],
        ]);

        $this->addColumn('kaufland_product_id', [
            'header' => __('%channel_title Product ID', ['channel_title' => \M2E\Kaufland\Helper\Module::getChannelTitle()]),
            'align' => 'left',
            'width' => '100px',
            'type' => 'text',
            'index' => 'kaufland_product_id',
            'account_id' => $this->listing->getAccountId(),
            'storefront_id' => $this->listing->getStorefront(),
            'filter' => \M2E\Kaufland\Block\Adminhtml\Grid\Column\Filter\ListingProductId::class,
            'renderer' => \M2E\Kaufland\Block\Adminhtml\Kaufland\Grid\Column\Renderer\KauflandProductId::class,
            'renderer_options' => ['storefront_code' => $this->listing->getStorefront()->getStorefrontCode()],
            'filter_condition_callback' => [$this, 'callbackFilterIsProductCreator'],
        ]);

        $this->addColumn(
            'online_qty',
            [
                'header' => __('Available QTY'),
                'align' => 'right',
                'width' => '50px',
                'type' => 'number',
                'index' => 'online_qty',
                'sortable' => true,
                'filter_index' => 'online_qty',
                'frame_callback' => [$this, 'callbackColumnQty'],
            ]
        );

        $this->addColumn(
            'price',
            [
                'header' => __('Price'),
                'align' => 'right',
                'width' => '50px',
                'type' => 'currency',
                'currency_code' => $this->listing->getStorefront()->getCurrencyCode(),
                'rate' => 1,
                'index' => 'online_price',
                'filter_index' => 'online_price',
                'frame_callback' => [$this, 'callbackColumnPrice'],
            ]
        );

        $statusColumn = [
            'header' => __('Status'),
            'width' => '100px',
            'index' => 'status',
            'filter_index' => 'status',
            'type' => 'options',
            'sortable' => false,
            'options' => [
                Product::STATUS_NOT_LISTED => Product::getStatusTitle(Product::STATUS_NOT_LISTED),
                Product::STATUS_LISTED => Product::getStatusTitle(Product::STATUS_LISTED),
                Product::STATUS_INACTIVE => Product::getStatusTitle(Product::STATUS_INACTIVE),
                self::STATUS_INCOMPLETE => Product::getIncompleteStatusTitle(),
            ],
            'showLogIcon' => true,
            'renderer' => \M2E\Kaufland\Block\Adminhtml\Kaufland\Grid\Column\Renderer\Status::class,
            'filter_condition_callback' => [$this, 'callbackFilterStatus'],
        ];

        $this->addColumn('status', $statusColumn);

        return parent::_prepareColumns();
    }

    protected function callbackFilterIsProductCreator($collection, $column)
    {
        $inputValue = $column->getFilter()->getValue('input');
        if ($inputValue !== null) {
            $collection->addFieldToFilter('kaufland_product_id', ['like' => '%' . $inputValue . '%']);
        }

        $selectValue = $column->getFilter()->getValue('select');
        if ($selectValue !== null) {
            $collection->addFieldToFilter(
                ListingProductResource::COLUMN_IS_KAUFLAND_PRODUCT_CREATOR,
                $selectValue
            );
        }
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->setMassactionIdFieldOnlyIndexValue(true);

        // Configure groups
        // ---------------------------------------

        $groups = [
            'actions' => __('Listing Actions'),
            'other' => __('Other'),
        ];

        $this->getMassactionBlock()->setGroups($groups);

        // Set mass-action
        // ---------------------------------------

        $this->getMassactionBlock()->addItem('list', [
            'label' => __(
                'List Item(s) on %channel_title',
                ['channel_title' => \M2E\Kaufland\Helper\Module::getChannelTitle()]
            ),
            'url' => '',
        ], 'actions');

        $this->getMassactionBlock()->addItem('revise', [
            'label' => __(
                'Revise Item(s) on %channel_title',
                ['channel_title' => \M2E\Kaufland\Helper\Module::getChannelTitle()]
            ),
            'url' => '',
        ], 'actions');

        $this->getMassactionBlock()->addItem('relist', [
            'label' => __(
                'Relist Item(s) on %channel_title',
                ['channel_title' => \M2E\Kaufland\Helper\Module::getChannelTitle()]
            ),
            'url' => '',
        ], 'actions');

        $this->getMassactionBlock()->addItem('stop', [
            'label' => __(
                'Stop Item(s) on %channel_title',
                ['channel_title' => \M2E\Kaufland\Helper\Module::getChannelTitle()]
            ),
            'url' => '',
        ], 'actions');

        $this->getMassactionBlock()->addItem('stopAndRemove', [
            'label' => __(
                'Remove from %channel_title / Remove from Listing',
                [
                    'channel_title' => \M2E\Kaufland\Helper\Module::getChannelTitle(),
                ]
            ),
            'url' => '',
        ], 'actions');

        // ---------------------------------------

        return parent::_prepareMassaction();
    }

    public function callbackColumnQty($value, $row, $column, $isExport)
    {
        if ((int)$row['status'] === \M2E\Kaufland\Model\Product::STATUS_NOT_LISTED) {
            return sprintf(
                '<span style="color: gray">%s</span>',
                __('Not Listed')
            );
        }

        if ((int)$row['status'] === \M2E\Kaufland\Model\Product::STATUS_INACTIVE) {
            return '<span style="color: red">0</span>';
        }

        if ($value <= 0) {
            return 0;
        }

        return (int)$value;
    }

    public function callbackColumnTitle($value, $row, $column, $isExport)
    {
        $title = $row->getName();

        $onlineTitle = $row->getData('online_title');
        !empty($onlineTitle) && $title = $onlineTitle;

        $title = \M2E\Core\Helper\Data::escapeHtml($title);

        $valueHtml = '<span class="product-title-value">' . $title . '</span>';

        $sku = $row->getData('sku');

        if ($row->getData('sku') === null) {
            $sku = $this->modelFactory->getObject('Magento\Product')
                                      ->setProductId($row->getData('entity_id'))
                                      ->getSku();
        }

        $unitId = $row->getData('unit_id');

        if ($isExport) {
            return \M2E\Core\Helper\Data::escapeHtml($sku);
        }

        $valueHtml .= '<br/>' .
            '<strong>' . __('SKU') . ':</strong>&nbsp;' .
            \M2E\Core\Helper\Data::escapeHtml($sku);

        if ($unitId) {
            $valueHtml .= '<br/>' .
                '<strong>' . __('Unit ID') . ':</strong>&nbsp;' .
                \M2E\Core\Helper\Data::escapeHtml($unitId);
        }

        if ($categoryId = $row->getData(\M2E\Kaufland\Model\ResourceModel\Product::COLUMN_ONLINE_CATEGORY_ID)) {
            $categoryPath = $row->getData('online_category_path');
            $categoryInfo = sprintf('%s (%s)', $categoryPath, $categoryId);
            $valueHtml .= '<br/><br/>' .
                '<strong>' . __('Category') . ':</strong>&nbsp;' .
                \M2E\Core\Helper\Data::escapeHtml($categoryInfo);
        }

        return $valueHtml;
    }

    protected function callbackFilterTitle($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $collection->addFieldToFilter(
            [
                ['attribute' => 'sku', 'like' => '%' . $value . '%'],
                ['attribute' => 'name', 'like' => '%' . $value . '%'],
            ]
        );
    }

    public function callbackColumnPrice($value, $row, $column, $isExport)
    {
        $productStatus = $row->getData('status');

        if ((int)$productStatus === \M2E\Kaufland\Model\Product::STATUS_NOT_LISTED) {
            return sprintf(
                '<span style="color: gray;">%s</span>',
                __('Not Listed')
            );
        }

        return $value;
    }

    protected function callbackFilterStatus($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        $index = $column->getIndex();

        if ($value == null) {
            return;
        }

        if ($value === self::STATUS_INCOMPLETE) {
            $collection->addFieldToFilter(ListingProductResource::COLUMN_IS_INCOMPLETE, 1);
        } else {
            if (is_array($value) && isset($value['value'])) {
                $collection->addFieldToFilter($index, (int)$value['value']);
                $collection->addFieldToFilter(ListingProductResource::COLUMN_IS_INCOMPLETE, 0);
            } else {
                if (!is_array($value) && $value !== null) {
                    $collection->addFieldToFilter($index, (int)$value);
                    $collection->addFieldToFilter(ListingProductResource::COLUMN_IS_INCOMPLETE, 0);
                }
            }
        }
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/kaufland_listing/view', ['_current' => true]);
    }

    public function getRowUrl($item)
    {
        return false;
    }

    public function getTooltipHtml($content, $id = '', $customClasses = [])
    {
        return <<<HTML
<div id="{$id}" class="Kaufland-field-tooltip admin__field-tooltip">
    <a class="admin__field-tooltip-action" href="javascript://"></a>
    <div class="admin__field-tooltip-content" style="">
        {$content}
    </div>
</div>
HTML;
    }

    protected function _toHtml()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->js->add(
                <<<JS
                KauflandListingViewKauflandGridObj.afterInitPage();
JS
            );

            return parent::_toHtml();
        }

        $temp = $this->sessionDataHelper->getValue('products_ids_for_list', true);
        $productsIdsForList = empty($temp) ? '' : $temp;

        $gridId = 'KauflandListingViewGrid' . $this->listing['id'];
        $ignoreListing = $this->listing['id'];

        $this->jsUrl->addUrls([
            'runListProducts' => $this->getUrl('*/kaufland_listing/runListProducts'),
            'runRelistProducts' => $this->getUrl('*/kaufland_listing/runRelistProducts'),
            'runReviseProducts' => $this->getUrl('*/kaufland_listing/runReviseProducts'),
            'runStopProducts' => $this->getUrl('*/kaufland_listing/runStopProducts'),
            'runStopAndRemoveProducts' => $this->getUrl('*/kaufland_listing/runStopAndRemoveProducts'),
            'previewItems' => $this->getUrl('*/kaufland_listing/previewItems'),
        ]);

        $this->jsUrl->add(
            $this->getUrl('*/kaufland_listing/saveCategoryTemplate', [
                'listing_id' => $this->listing['id'],
            ]),
            'kaufland_listing/saveCategoryTemplate'
        );

        $this->jsUrl->add(
            $this->getUrl('*/kaufland_log_listing_product/index'),
            'kaufland_log_listing_product/index'
        );

        $this->jsUrl->add(
            $this->getUrl('*/kaufland_log_listing_product/index', [
                \M2E\Kaufland\Block\Adminhtml\Log\Listing\Product\AbstractGrid::LISTING_ID_FIELD =>
                    $this->listing['id'],
                'back' => $this->urlHelper->makeBackUrlParam(
                    '*/kaufland_listing/view',
                    ['id' => $this->listing['id']]
                ),
            ]),
            'logViewUrl'
        );
        $this->jsUrl->add($this->getUrl('*/listing/getErrorsSummary'), 'getErrorsSummary');

        $this->jsUrl->add(
            $this->getUrl('*/kaufland_listing_moving/moveToListingGrid'),
            'kaufland_listing_moving/moveToListingGrid'
        );

        $this->jsUrl->add(
            $this->getUrl('*/kaufland_listing/getListingProductBids'),
            'kaufland_listing/getListingProductBids'
        );

        $taskCompletedWarningMessage = '"%task_title%" task has completed with warnings. ';
        $taskCompletedWarningMessage .= '<a target="_blank" href="%url%">View Log</a> for details.';

        $taskCompletedErrorMessage = '"%task_title%" task has completed with errors. ';
        $taskCompletedErrorMessage .= '<a target="_blank" href="%url%">View Log</a> for details.';

        $channelTitle = \M2E\Kaufland\Helper\Module::getChannelTitle();

        $this->jsTranslator->addTranslations([
            'task_completed_message' => __('Task completed. Please wait ...'),

            'task_completed_success_message' => __('"%task_title%" task has completed.'),

            'task_completed_warning_message' => __($taskCompletedWarningMessage),
            'task_completed_error_message' => __($taskCompletedErrorMessage),

            'sending_data_message' => __(
                'Sending %product_title% Product(s) data on %channel_title.',
                [
                    'channel_title' => $channelTitle,
                ]
            ),

            'View Full Product Log' => __('View Full Product Log.'),

            'The Listing was locked by another process. Please try again later.' =>
                __('The Listing was locked by another process. Please try again later.'),

            'Listing is empty.' => __('Listing is empty.'),

            'listing_all_items_message' => __(
                'Listing All Items On %channel_title',
                ['channel_title' => $channelTitle]
            ),
            'listing_selected_items_message' => __(
                'Listing Selected Items On %channel_title',
                ['channel_title' => $channelTitle]
            ),
            'revising_selected_items_message' => __(
                'Revising Selected Items On %channel_title',
                ['channel_title' => $channelTitle]
            ),
            'relisting_selected_items_message' => __(
                'Relisting Selected Items On %channel_title',
                ['channel_title' => $channelTitle]
            ),
            'stopping_selected_items_message' => __(
                'Stopping Selected Items On %channel_title',
                ['channel_title' => $channelTitle]
            ),
            'stopping_and_removing_selected_items_message' => __(
                'Stopping On %channel_title And Removing From Listing Selected Items',
                ['channel_title' => $channelTitle]
            ),
            'removing_selected_items_message' => __('Removing From Listing Selected Items'),

            'Please select the Products you want to perform the Action on.' =>
                __('Please select the Products you want to perform the Action on.'),

            'Please select Action.' => __('Please select Action.'),

            'Specifics' => __('Specifics'),
        ]);

        $this->js->add(
            <<<JS
    Kaufland.productsIdsForList = '{$productsIdsForList}';
    Kaufland.customData.gridId = '{$gridId}';
    Kaufland.customData.ignoreListing = '{$ignoreListing}';
JS
        );

        $this->js->addOnReadyJs(
            <<<JS
    require([
        'Kaufland/Kaufland/Listing/View/Kaufland/Grid',
        'Kaufland/Kaufland/Listing/VariationProductManage'
    ], function(){

        window.KauflandListingViewKauflandGridObj = new KauflandListingViewKauflandGrid(
            '{$this->getId()}',
            {$this->listing['id']}
        );
        KauflandListingViewKauflandGridObj.afterInitPage();

        KauflandListingViewKauflandGridObj.actionHandler.setProgressBar('listing_view_progress_bar');
        KauflandListingViewKauflandGridObj.actionHandler.setGridWrapper('listing_view_content_container');

        if (Kaufland.productsIdsForList) {
            KauflandListingViewKauflandGridObj.getGridMassActionObj().checkedString = Kaufland.productsIdsForList;
            KauflandListingViewKauflandGridObj.actionHandler.listAction();
        }
    });
JS
        );

        return parent::_toHtml();
    }
}
