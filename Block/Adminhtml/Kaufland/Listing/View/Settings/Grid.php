<?php

namespace M2E\Kaufland\Block\Adminhtml\Kaufland\Listing\View\Settings;

use M2E\Kaufland\Block\Adminhtml\Kaufland\Listing\View\Settings\Grid\Column\Filter\PolicySettings
    as PolicySettingsFilter;
use M2E\Kaufland\Model\ResourceModel\Product as ListingProductResource;
use M2E\Kaufland\Model\ResourceModel\Template\SellingFormat as SellingFormatResource;
use M2E\Kaufland\Model\ResourceModel\Template\Synchronization as SynchronizationResource;
use M2E\Kaufland\Model\Kaufland\Template\Manager;

class Grid extends \M2E\Kaufland\Block\Adminhtml\Listing\View\AbstractGrid
{
    private \M2E\Kaufland\Model\ResourceModel\Magento\Product\CollectionFactory $magentoProductCollectionFactory;
    private \M2E\Kaufland\Helper\Data\Session $sessionDataHelper;
    private ListingProductResource $listingProductResource;
    private \M2E\Core\Helper\Url $urlHelper;
    private \M2E\Kaufland\Model\Magento\ProductFactory $magentoProductFactory;
    private SellingFormatResource $sellingFormatResource;
    private SynchronizationResource $synchronizationResource;
    private \M2E\Kaufland\Model\Template\SellingFormat\Repository $sellingFormatRepository;
    private \M2E\Kaufland\Model\Template\Synchronization\Repository $synchronizationRepository;
    private \M2E\Kaufland\Model\Template\Description\Repository $descriptionRepository;

    public function __construct(
        \M2E\Kaufland\Model\Template\SellingFormat\Repository $sellingFormatRepository,
        \M2E\Kaufland\Model\Template\Synchronization\Repository $synchronizationRepository,
        \M2E\Kaufland\Model\Template\Description\Repository $descriptionRepository,
        SellingFormatResource $sellingFormatResource,
        SynchronizationResource $synchronizationResource,
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
        $this->sellingFormatResource = $sellingFormatResource;
        $this->synchronizationResource = $synchronizationResource;
        $this->sellingFormatRepository = $sellingFormatRepository;
        $this->synchronizationRepository = $synchronizationRepository;
        $this->descriptionRepository = $descriptionRepository;
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
                'is_kaufland_product_creator' => ListingProductResource::COLUMN_IS_KAUFLAND_PRODUCT_CREATOR,
                'additional_data' => ListingProductResource::COLUMN_ADDITIONAL_DATA,
                'available_qty' => ListingProductResource::COLUMN_ONLINE_QTY,
                'online_category_id' => ListingProductResource::COLUMN_ONLINE_CATEGORY_ID,
                'online_categories_data' => ListingProductResource::COLUMN_ONLINE_CATEGORIES_DATA,
                'online_current_price' => ListingProductResource::COLUMN_ONLINE_PRICE,
                'template_selling_format_mode' => ListingProductResource::COLUMN_TEMPLATE_SELLING_FORMAT_MODE,
                'template_synchronization_mode' => ListingProductResource::COLUMN_TEMPLATE_SYNCHRONIZATION_MODE,
                'template_selling_format_id' => ListingProductResource::COLUMN_TEMPLATE_SELLING_FORMAT_ID,
                'template_synchronization_id' => ListingProductResource::COLUMN_TEMPLATE_SYNCHRONIZATION_ID,
            ],
            sprintf(
                '{{table}}.%s = %s',
                ListingProductResource::COLUMN_LISTING_ID,
                $this->listing->getId()
            )
        );

        $templateSellingFormatTableName = $this->sellingFormatResource->getMainTable();
        $templateSynchronizationTableName = $this->synchronizationResource->getMainTable();
        $collection
            ->joinTable(
                ['tsf' => $templateSellingFormatTableName],
                sprintf('%s = template_selling_format_id', SellingFormatResource::COLUMN_ID),
                ['selling_policy_title' => SellingFormatResource::COLUMN_TITLE],
                null,
                'left'
            )
            ->joinTable(
                ['ts' => $templateSynchronizationTableName],
                sprintf('%s = template_synchronization_id', SynchronizationResource::COLUMN_ID),
                ['synchronization_policy_title' => SynchronizationResource::COLUMN_TITLE],
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
                'header' => __('Kaufland Category'),
                'align' => 'left',
                'type' => 'text',
                'frame_callback' => [$this, 'callbackColumnCategory'],
                'filter_condition_callback' => [$this, 'callbackFilterCategory'],
            ]
        );

        $this->addColumn(
            'setting',
            [
                'index' => 'name',
                'header' => __('Listing Policies Overrides'),
                'align' => 'left',
                'type' => 'text',
                'sortable' => false,
                'filter' => PolicySettingsFilter::class,
                'frame_callback' => [$this, 'callbackColumnSetting'],
                'filter_condition_callback' => [$this, 'callbackFilterSetting'],
                'column_css_class' => 'listing-grid-column-setting',
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
        ]);

        $this->getMassactionBlock()->addItem('editCategorySettings', [
            'label' => $this->__('Categories & Specifics'),
            'url' => '',
        ], 'edit_categories_settings');

        $this->getMassactionBlock()->setGroups([
            'other' => $this->__('Other'),
        ]);

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
        $categoryTitle = $row->getData('online_categories_data');

        return <<<HTML
    <div>
        <p style="padding: 2px 0 0 10px">{$categoryTitle}</p>
    </div>
HTML;
    }

    public function callbackColumnSetting($value, $row, $column, $isExport): string
    {
        $templatesNames = [
            Manager::TEMPLATE_SELLING_FORMAT => __('Selling'),
            Manager::TEMPLATE_SYNCHRONIZATION => __('Synchronization'),
            Manager::TEMPLATE_DESCRIPTION => __('Description'),
        ];

        // ---------------------------------------

        $modes = array_keys($templatesNames);
        $listingSettings = array_filter($modes, function ($templateNick) use ($row) {
            $templateMode = $row->getData('template_' . $templateNick . '_mode');

            return $templateMode == Manager::MODE_PARENT;
        });

        if (count($listingSettings) === count($templatesNames)) {
            return __('Use from Listing Settings');
        }

        // ---------------------------------------

        $html = '';
        foreach ($templatesNames as $templateNick => $templateTitle) {
            $templateMode = $row->getData('template_' . $templateNick . '_mode');

            if ($templateMode == Manager::MODE_PARENT) {
                continue;
            }

            $templateLink = '';
            if ($templateMode == Manager::MODE_CUSTOM) {
                $templateLink = '<span>' . __('Custom Settings') . '</span>';
            } elseif ($templateMode == Manager::MODE_TEMPLATE) {
                $id = (int)$row->getData('template_' . $templateNick . '_id');

                $url = $this->getUrl('kaufland/kaufland_template/edit', [
                    'id' => $id,
                    'nick' => $templateNick,
                ]);

                $objTitle = '';
                if ($templateNick === Manager::TEMPLATE_SELLING_FORMAT) {
                    $objTitle = $this->sellingFormatRepository->get($id)->getTitle();
                } elseif ($templateNick === Manager::TEMPLATE_DESCRIPTION) {
                    $objTitle = $this->descriptionRepository->get($id)->getTitle();
                } else {
                    $objTitle = $this->synchronizationRepository->get($id)->getTitle();
                }

                $templateLink = '<a href="' . $url . '" target="_blank">' . $objTitle . '</a>';
            }

            $html .= "<div style='padding: 2px 0 0 0px'>
                                    <strong>$templateTitle:</strong>
                                    <span style='padding: 0 0px 0 5px'>$templateLink</span>
                               </div>";
        }

        return $html;
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
            $collection->getSelect()->where('online_categories_data LIKE ?', '%' . $value . '%');
        }
    }

    public function callbackFilterSetting($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        $inputValue = null;

        if (is_array($value) && isset($value['input'])) {
            $inputValue = $value['input'];
        } elseif (is_string($value)) {
            $inputValue = $value;
        }

        if ($inputValue !== null) {
            /** @var \M2E\Kaufland\Model\ResourceModel\Magento\Product\Collection $collection */
            $collection->addAttributeToFilter(
                [
                    ['attribute' => 'selling_policy_title', 'like' => '%' . $inputValue . '%'],
                    ['attribute' => 'synchronization_policy_title', 'like' => '%' . $inputValue . '%'],
                ]
            );
        }

        if (isset($value['select'])) {
            switch ($value['select']) {
                case Manager::MODE_PARENT:
                    // no policy overrides

                    $collection->addAttributeToFilter(
                        'template_selling_format_mode',
                        ['eq' => Manager::MODE_PARENT]
                    );
                    $collection->addAttributeToFilter(
                        'template_synchronization_mode',
                        ['eq' => Manager::MODE_PARENT]
                    );
                    break;
                case Manager::MODE_TEMPLATE:
                case Manager::MODE_CUSTOM:
                    // policy templates and custom settings
                    $collection->addAttributeToFilter(
                        [
                            [
                                'attribute' => 'template_selling_format_mode',
                                'eq' => (int)$value['select'],
                            ],
                            [
                                'attribute' => 'template_synchronization_mode',
                                'eq' => (int)$value['select'],
                            ],
                        ]
                    );
                    break;
            }
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
            \M2E\Kaufland\Helper\Data::getClassConstants(\M2E\Kaufland\Model\Kaufland\Template\Manager::class)
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

        $this->jsUrl->add(
            $this->getUrl('*/kaufland_template/editListingProductsPolicy'),
            'kaufland_template/editListingProductsPolicy'
        );
        $this->jsUrl->add(
            $this->getUrl('*/kaufland_template/saveListingProductsPolicy'),
            'kaufland_template/saveListingProductsPolicy'
        );

        // ---------------------------------------

        $taskCompletedWarningMessage = '"%task_title%" Task has completed with warnings.'
            . ' <a target="_blank" href="%url%">View Log</a> for details.';

        $taskCompletedErrorMessage = '"%task_title%" Task has completed with errors. '
            . ' <a target="_blank" href="%url%">View Log</a> for details.';

        //------------------------------
        $this->jsTranslator->addTranslations([
            'Edit Selling Policy Setting' => __('Edit Selling Policy Setting'),
            'Edit Synchronization Policy Setting' => __('Edit Synchronization Policy Setting'),
            'Edit Settings' => __('Edit Settings'),
            'For' => __('For'),
            'Category Settings' => __('Category Settings'),
            'Specifics' => __('Specifics'),
            'task_completed_message' => __('Task completed. Please wait ...'),
            'task_completed_success_message' => __('"%task_title%" Task has completed.'),
            'sending_data_message' => __('Sending %product_title% Product(s) data on Kaufland.'),
            'View Full Product Log.' => __('View Full Product Log.'),
            'The Listing was locked by another process. Please try again later.' =>
                __('The Listing was locked by another process. Please try again later.'),
            'Listing is empty.' => __('Listing is empty.'),
            'Please select Items.' => __('Please select Items.'),
            'Please select Action.' => __('Please select Action.'),
            'popup_title' => __('Moving Kaufland Items'),
            'task_completed_warning_message' => __($taskCompletedWarningMessage),
            'task_completed_error_message' => __($taskCompletedErrorMessage),
            'Add New Listing' => __('Add New Listing'),
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
