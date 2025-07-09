<?php

namespace M2E\Kaufland\Block\Adminhtml\Kaufland\Listing\View\Settings;

use M2E\Kaufland\Model\ResourceModel\Category\Dictionary as CategoryDictionaryResource;
use M2E\Kaufland\Model\ResourceModel\Product as ListingProductResource;

class Grid extends \M2E\Kaufland\Block\Adminhtml\Listing\View\AbstractGrid
{
    private \M2E\Kaufland\Model\ResourceModel\Magento\Product\CollectionFactory $magentoProductCollectionFactory;
    private \M2E\Kaufland\Helper\Data\Session $sessionDataHelper;
    private ListingProductResource $listingProductResource;
    private \M2E\Core\Helper\Url $urlHelper;
    private \M2E\Kaufland\Model\Magento\ProductFactory $magentoProductFactory;
    private CategoryDictionaryResource $categoryDictionaryResource;

    public function __construct(
        CategoryDictionaryResource $categoryDictionaryResource,
        \M2E\Kaufland\Model\Magento\ProductFactory $magentoProductFactory,
        ListingProductResource $listingProductResource,
        \M2E\Kaufland\Model\ResourceModel\Magento\Product\CollectionFactory $magentoProductCollectionFactory,
        \M2E\Kaufland\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \M2E\Kaufland\Helper\Data\Session $sessionDataHelper,
        \M2E\Kaufland\Helper\Data $dataHelper,
        \M2E\Core\Helper\Url $urlHelper,
        \M2E\Kaufland\Helper\Data\GlobalData $globalDataHelper,
        array $data = []
    ) {
        $this->magentoProductCollectionFactory = $magentoProductCollectionFactory;
        $this->urlHelper = $urlHelper;
        $this->sessionDataHelper = $sessionDataHelper;
        $this->listingProductResource = $listingProductResource;
        $this->magentoProductFactory = $magentoProductFactory;
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

        $this->setId('kauflandListingViewGrid' . $this->listing->getId());

        $this->css->addFile('kaufland/template.css');
        $this->css->addFile('listing/grid.css');

        $this->showAdvancedFilterProductsOption = false;
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareCollection(): Grid
    {
        $collection = $this->magentoProductCollectionFactory->create();

        $collection->setListingProductModeOn();
        $collection->setStoreId($this->listing->getStoreId());

        $collection->addAttributeToSelect('sku');
        $collection->addAttributeToSelect('name');

        $lpTable = $this->listingProductResource->getMainTable();
        $collection->joinTable(
            ['lp' => $lpTable],
            sprintf('%s = entity_id', ListingProductResource::COLUMN_MAGENTO_PRODUCT_ID),
            [
                'id' => ListingProductResource::COLUMN_ID,
                'status' => ListingProductResource::COLUMN_STATUS,
                'kaufland_product_id' => ListingProductResource::COLUMN_KAUFLAND_PRODUCT_ID,
                'is_kaufland_product_creator' => ListingProductResource::COLUMN_IS_KAUFLAND_PRODUCT_CREATOR,
                'additional_data' => ListingProductResource::COLUMN_ADDITIONAL_DATA,
                'available_qty' => ListingProductResource::COLUMN_ONLINE_QTY,
                'online_current_price' => ListingProductResource::COLUMN_ONLINE_PRICE,
                'online_title' => ListingProductResource::COLUMN_ONLINE_TITLE,
                'template_category_id' => ListingProductResource::COLUMN_TEMPLATE_CATEGORY_ID,
            ],
            sprintf(
                '{{table}}.%s = %s',
                ListingProductResource::COLUMN_LISTING_ID,
                $this->listing->getId()
            )
        );

        $categoryDictionaryTableName = $this->categoryDictionaryResource->getMainTable();
        $collection->joinTable(
            ['cd' => $categoryDictionaryTableName],
            sprintf('%s = template_category_id', CategoryDictionaryResource::COLUMN_ID),
            [
                'categories_data' => CategoryDictionaryResource::COLUMN_PATH,
                'online_category_id' => CategoryDictionaryResource::COLUMN_CATEGORY_ID,
            ],
            null,
            'left'
        );

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @throws \Exception
     */
    protected function _prepareColumns(): Grid
    {
        $this->addColumn(
            'product_id',
            [
                'header' => __('Product ID'),
                'align' => 'right',
                'width' => '100px',
                'type' => 'number',
                'index' => 'entity_id',
                'store_id' => $this->listing->getStoreId(),
                'renderer' => \M2E\Kaufland\Block\Adminhtml\Magento\Grid\Column\Renderer\ProductId::class,
            ]
        );

        $this->addColumn(
            'name',
            [
                'header' => __('Product Title / Product SKU'),
                'align' => 'left',
                'type' => 'text',
                'index' => 'name',
                'escape' => false,
                'frame_callback' => [$this, 'callbackColumnTitle'],
                'filter_condition_callback' => [$this, 'callbackFilterTitle'],
            ]
        );

        $this->addColumn(
            'category',
            [
                'header' => __(
                    '%channel_title Category',
                    ['channel_title' => \M2E\Kaufland\Helper\Module::getChannelTitle()]
                ),
                'align' => 'left',
                'type' => 'text',
                'frame_callback' => [$this, 'callbackColumnCategory'],
                'filter_condition_callback' => [$this, 'callbackFilterCategory'],
            ]
        );

        $this->addColumn('actions', [
            'header' => $this->__('Actions'),
            'align' => 'left',
            'type' => 'action',
            'index' => 'actions',
            'filter' => false,
            'sortable' => false,
            'renderer' => \M2E\Kaufland\Block\Adminhtml\Magento\Grid\Column\Renderer\Action::class,
            'field' => 'id',
            'group_order' => $this->getGroupOrder(),
            'actions' => [$this, 'callbackColumnActionsItems'],
        ]);

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->setMassactionIdFieldOnlyIndexValue(true);

        $this->getMassactionBlock()->setGroups([
            'edit_categories_settings' => $this->__('Edit Category Settings'),
            'other' => $this->__('Other'),
        ]);

        $this->getMassactionBlock()->addItem('editCategorySettings', [
            'label' => $this->__('Categories & Specifics'),
            'url' => '',
        ], 'edit_categories_settings');

        $this->getMassactionBlock()->addItem('moving', [
            'label' => $this->__('Move Item(s) to Another Listing'),
            'url' => '',
        ], 'other');

        return $this;
    }

    public function callbackColumnTitle($value, $row, $column, $isExport): string
    {
        $value = '<span>' . \M2E\Core\Helper\Data::escapeHtml($value) . '</span>';

        $sku = $row->getData('sku');
        if ($sku === null) {
            $sku = $this->magentoProductFactory
                ->create()
                ->setProductId($row->getData('entity_id'))
                ->getSku();
        }

        $value .= '<br/><strong>' . __('SKU') . ':</strong>&nbsp;';
        $value .= \M2E\Core\Helper\Data::escapeHtml($sku);

        return $value;
    }

    public function callbackColumnCategory($value, $row, $column, $isExport): string
    {
        $categoryTitle = $row->getData('categories_data');

        return <<<HTML
    <div>
        <p style="padding: 2px 0 0 10px">{$categoryTitle}</p>
    </div>
HTML;
    }

    public function callbackFilterTitle($collection, $column)
    {
        $inputValue = $column->getFilter()->getValue();

        if ($inputValue !== null) {
            $fieldsToFilter = [
                ['attribute' => 'sku', 'like' => '%' . $inputValue . '%'],
                ['attribute' => 'name', 'like' => '%' . $inputValue . '%'],
            ];

            $collection->addFieldToFilter($fieldsToFilter);
        }
    }

    public function callbackFilterCategory($collection, $column)
    {
        $filter = $column->getFilter();
        if ($value = $filter->getValue()) {
            $collection->getSelect()->where('categories_data LIKE ?', '%' . $value . '%');
        }
    }

    public function getGridUrl(): string
    {
        return $this->getUrl('*/kaufland_listing/view', ['_current' => true]);
    }

    public function getRowUrl($item): bool
    {
        return false;
    }

    protected function getGroupOrder(): array
    {
        return [
            'edit_categories_settings' => $this->__('Edit Category Settings'),
        ];
    }

    public function callbackColumnActionsItems($row): array
    {
        $actions = [
            'editCategories' => [
                'caption' => $this->__('Categories & Attributes'),
                'group' => 'edit_categories_settings',
                'field' => 'id',
            ],
        ];

        $actions['editCategories']['onclick_action'] = "KauflandListingViewSettingsGridObj.actions['editCategorySettingsAction']";

        return $actions;
    }

    protected function _toHtml(): string
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->js->add(
                <<<JS
            KauflandListingViewSettingsGridObj.afterInitPage();
JS
            );

            return parent::_toHtml();
        }

        $helper = $this->dataHelper;

        // ---------------------------------------
        $this->jsPhp->addConstants(
            \M2E\Kaufland\Helper\Data::getClassConstants(\M2E\Kaufland\Model\Template\Manager::class)
        );
        // ---------------------------------------

        // ---------------------------------------
        $this->jsUrl->addUrls($helper->getControllerActions('Kaufland\Listing', ['_current' => true]));
        $this->jsUrl->addUrls(
            $helper->getControllerActions('Listing\Product\Category\Settings', ['_current' => true])
        );

        $this->jsUrl->add(
            $this->getUrl('*/kaufland_log_listing_product/index', [
                \M2E\Kaufland\Block\Adminhtml\Log\Listing\Product\AbstractGrid::LISTING_ID_FIELD =>
                    $this->listing->getId(),
            ]),
            'kaufland_log_listing_product/index'
        );
        $this->jsUrl->add(
            $this->getUrl('*/kaufland_log_listing_product/index', [
                \M2E\Kaufland\Block\Adminhtml\Log\Listing\Product\AbstractGrid::LISTING_ID_FIELD =>
                    $this->listing->getId(),
                'back' => $this->urlHelper->makeBackUrlParam(
                    '*/kaufland_listing/view',
                    ['id' => $this->listing->getId()]
                ),
            ]),
            'logViewUrl'
        );

        $this->jsUrl->add($this->getUrl('*/listing/getErrorsSummary'), 'getErrorsSummary');

        $this->jsUrl->add($this->getUrl('*/listing_moving/moveToListingGrid'), 'moveToListingGridHtml');
        $this->jsUrl->add($this->getUrl('*/listing_moving/prepareMoveToListing'), 'prepareData');
        $this->jsUrl->add($this->getUrl('*/listing_moving/moveToListing'), 'moveToListing');

        // ---------------------------------------

        $taskCompletedWarningMessage = '"%task_title%" Task has completed with warnings.'
            . ' <a target="_blank" href="%url%">View Log</a> for details.';

        $taskCompletedErrorMessage = '"%task_title%" Task has completed with errors. '
            . ' <a target="_blank" href="%url%">View Log</a> for details.';

        //------------------------------
        $this->jsTranslator->addTranslations([
            'Category Settings' => __('Category Settings'),
            'task_completed_message' => __('Task completed. Please wait ...'),
            'task_completed_success_message' => __('"%task_title%" Task has completed.'),
            'sending_data_message' => __(
                'Sending %product_title% Product(s) data on %channel_title.',
                [
                    'channel_title' => \M2E\Kaufland\Helper\Module::getChannelTitle(),
                ]
            ),
            'View Full Product Log.' => __('View Full Product Log.'),
            'Please select Items.' => __('Please select Items.'),
            'Please select Action.' => __('Please select Action.'),
            'popup_title' => __(
                'Moving %channel_title Items',
                ['channel_title' => \M2E\Kaufland\Helper\Module::getChannelTitle()]
            ),
            'task_completed_warning_message' => __($taskCompletedWarningMessage),
            'task_completed_error_message' => __($taskCompletedErrorMessage),
        ]);

        $temp = $this->sessionDataHelper->getValue('products_ids_for_list', true);
        $productsIdsForList = empty($temp) ? '' : $temp;

        $ignoreListing = $this->listing->getId();

        $this->js->add(
            <<<JS
    Kaufland.productsIdsForList = '{$productsIdsForList}';
    Kaufland.customData.gridId = '{$this->getId()}';
    Kaufland.customData.ignoreListing = '{$ignoreListing}';
JS
        );

        $this->js->addOnReadyJs(
            <<<JS
    require([
        'Kaufland/Kaufland/Listing/View/Settings/Grid',
        'Kaufland/Listing/Wizard/Category'
    ], function(){

        window.KauflandListingViewSettingsGridObj = new KauflandListingViewSettingsGrid(
            '{$this->getId()}',
            '{$this->listing->getId()}',
            '{$this->listing->getAccountId()}',
            '{$this->listing->getStorefrontId()}'
        );
        KauflandListingViewSettingsGridObj.afterInitPage();
        KauflandListingViewSettingsGridObj.movingHandler.setProgressBar('listing_view_progress_bar');
        KauflandListingViewSettingsGridObj.movingHandler.setGridWrapper('listing_view_content_container');


        window.KauflandListingCategoryObj = new KauflandListingCategory(KauflandListingViewSettingsGridObj);
    });
JS
        );

        return parent::_toHtml();
    }
}
