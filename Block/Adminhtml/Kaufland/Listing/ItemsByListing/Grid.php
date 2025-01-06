<?php

namespace M2E\Kaufland\Block\Adminhtml\Kaufland\Listing\ItemsByListing;

use M2E\Kaufland\Block\Adminhtml\Widget\Grid\Column\Extended\Rewrite;

class Grid extends \M2E\Kaufland\Block\Adminhtml\Listing\Grid
{
    private \M2E\Kaufland\Model\ResourceModel\Account $accountResource;
    private \M2E\Kaufland\Model\ResourceModel\Product $listingProductResource;
    private \M2E\Core\Helper\Url $urlHelper;

    private \M2E\Kaufland\Model\ResourceModel\Listing\CollectionFactory $listingCollectionFactory;
    private \M2E\Kaufland\Model\Storefront\Repository $storefrontRepository;

    public function __construct(
        \M2E\Kaufland\Helper\View $viewHelper,
        \M2E\Kaufland\Model\ResourceModel\Product $listingProductResource,
        \M2E\Kaufland\Model\ResourceModel\Account $accountResource,
        \M2E\Kaufland\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \M2E\Kaufland\Helper\Data $dataHelper,
        \M2E\Core\Helper\Url $urlHelper,
        \M2E\Kaufland\Model\ResourceModel\Listing\CollectionFactory $listingCollectionFactory,
        \M2E\Kaufland\Model\Storefront\Repository $storefrontRepository,
        array $data = []
    ) {
        parent::__construct($urlHelper, $viewHelper, $context, $backendHelper, $dataHelper, $data);

        $this->urlHelper = $urlHelper;
        $this->listingCollectionFactory = $listingCollectionFactory;
        $this->listingProductResource = $listingProductResource;
        $this->accountResource = $accountResource;
        $this->storefrontRepository = $storefrontRepository;
    }

    public function _construct(): void
    {
        parent::_construct();
        $this->setId('KauflandListingItemsByListingGrid');
    }

    /**
     * @ingeritdoc
     */
    public function getRowUrl($item)
    {
        return $this->getUrl(
            '*/kaufland_listing/view',
            [
                'id' => $item->getId(),
                'back' => $this->getBackUrl(),
            ]
        );
    }

    /**
     * @return string
     */
    private function getBackUrl(): string
    {
        return $this->urlHelper->makeBackUrlParam('*/kaufland_listing/index');
    }

    /**
     * @return \M2E\Kaufland\Block\Adminhtml\Kaufland\Listing\ItemsByListing\Grid
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareCollection()
    {
        $collection = $this->listingCollectionFactory->create();
        $collection->getSelect()->join(
            ['account' => $this->accountResource->getMainTable()],
            'account.id = main_table.account_id',
            ['account_title' => 'title']
        );

        $select = $collection->getConnection()->select();
        $select->from(['lp' => $this->listingProductResource->getMainTable()], [
            'listing_id' => 'listing_id',
            'products_total_count' => new \Zend_Db_Expr('COUNT(lp.id)'),
            'products_active_count' => new \Zend_Db_Expr('COUNT(IF(lp.status = 2, lp.id, NULL))'),
            'products_inactive_count' => new \Zend_Db_Expr('COUNT(IF(lp.status != 2, lp.id, NULL))'),
        ]);
        $select->group('lp.listing_id');

        $collection->getSelect()->joinLeft(
            ['t' => $select],
            'main_table.id=t.listing_id',
            [
                'products_total_count' => 'products_total_count',
                'products_active_count' => 'products_active_count',
                'products_inactive_count' => 'products_inactive_count',
            ]
        );

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @ingeritdoc
     */
    protected function _prepareLayout()
    {
        $this->css->addFile('listing/grid.css');

        return parent::_prepareLayout();
    }

    /**
     * @return array[]
     */
    protected function getColumnActionsItems()
    {
        $backUrl = $this->getBackUrl();

        return [
            'manageProducts' => [
                'caption' => __('Manage'),
                'group' => 'products_actions',
                'field' => 'id',
                'url' => [
                    'base' => '*/kaufland_listing/view',
                    'params' => [
                        'id' => $this->getId(),
                        'back' => $backUrl,
                    ],
                ],
            ],

            'editTitle' => [
                'caption' => __('Title'),
                'group' => 'edit_actions',
                'field' => 'id',
                'onclick_action' => 'EditListingTitleObj.openPopup',
            ],

            'editStoreView' => [
                'caption' => __('Store View'),
                'group' => 'edit_actions',
                'field' => 'id',
                'onclick_action' => 'EditListingStoreViewObj.openPopup',
            ],

            'editConfiguration' => [
                'caption' => __('Configuration'),
                'group' => 'edit_actions',
                'field' => 'id',
                'url' => [
                    'base' => '*/kaufland_listing/edit',
                    'params' => ['back' => $backUrl],
                ],
            ],

            'viewLogs' => [
                'caption' => __('Logs & Events'),
                'group' => 'other',
                'field' => \M2E\Kaufland\Block\Adminhtml\Log\Listing\Product\AbstractGrid::LISTING_ID_FIELD,
                'url' => [
                    'base' => '*/kaufland_log_listing_product/index',
                ],
            ],

            'clearLogs' => [
                'caption' => __('Clear Log'),
                'confirm' => __('Are you sure?'),
                'group' => 'other',
                'field' => 'id',
                'url' => [
                    'base' => '*/listing/clearLog',
                    'params' => [
                        'back' => $backUrl,
                    ],
                ],
            ],

            'delete' => [
                'caption' => __('Delete Listing'),
                'confirm' => __('Are you sure?'),
                'group' => 'other',
                'field' => 'id',
                'url' => [
                    'base' => '*/kaufland_listing/delete',
                    'params' => ['id' => $this->getId()],
                ],
            ],
        ];
    }

    /**
     * editPartsCompatibilityMode has to be not accessible for not Multi Motors marketplaces
     * @return $this
     */
    protected function _prepareColumns()
    {
        $result = parent::_prepareColumns();

        $this->getColumn('actions')->setData(
            'renderer',
            \M2E\Kaufland\Block\Adminhtml\Kaufland\Listing\Grid\Column\Renderer\Action::class
        );

        return $result;
    }

    /**
     * @param string $value
     * @param \M2E\Kaufland\Model\Listing $row
     * @param Rewrite $column
     * @param bool $isExport
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function callbackColumnTitle($value, $row, $column, $isExport)
    {
        $title = \M2E\Core\Helper\Data::escapeHtml($value);

        $value = <<<HTML
<span id="listing_title_{$row->getId()}">
    {$title}
</span>
HTML;

        $accountTitle = $row->getData('account_title');
        $storefrontId = $row->getData('storefront_id');
        if ($storefrontId) {
            $storefront =  $this->storefrontRepository->get($row->getData('storefront_id'));
            $storefrontTitle = $storefront->getTitle();
        } else {
            $storefrontTitle = "";
        }

        $storeModel = $this->_storeManager->getStore($row->getStoreId());
        $storeView = $this->_storeManager->getWebsite($storeModel->getWebsiteId())->getName();
        if (strtolower($storeView) !== 'admin') {
            $storeView .= ' > ' . $this->_storeManager->getGroup($storeModel->getStoreGroupId())->getName();
            $storeView .= ' > ' . $storeModel->getName();
        } else {
            $storeView = __('Admin (Default Values)');
        }

        $account = __('Account');
        $storefront = __('Storefront');
        $store = __('Magento Store View');

        $value .= <<<HTML
<div>
    <span style="font-weight: bold">{$account}</span>: <span style="color: #505050">{$accountTitle}</span><br/>
    <span style="font-weight: bold">{$storefront}</span>: <span style="color: #505050">{$storefrontTitle}</span><br/>
    <span style="font-weight: bold">{$store}</span>: <span style="color: #505050">{$storeView}</span>
</div>
HTML;

        return $value;
    }

    /**
     * @ingeritdoc
     */
    protected function _toHtml()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            return parent::_toHtml();
        }

        $this->jsUrl->addUrls(
            array_merge(
                $this->dataHelper->getControllerActions('Kaufland\Listing'),
                $this->dataHelper->getControllerActions('Kaufland_Log_Listing_Product'),
                $this->dataHelper->getControllerActions('Kaufland\Template')
            )
        );

        $this->jsUrl->add($this->getUrl('*/listing/edit'), 'listing/edit');

        $this->jsUrl->add($this->getUrl('*/kaufland_listing_edit/selectStoreView'), 'listing/selectStoreView');
        $this->jsUrl->add($this->getUrl('*/kaufland_listing_edit/saveStoreView'), 'listing/saveStoreView');

        $this->jsTranslator->add('Edit Listing Title', __('Edit Listing Title'));
        $this->jsTranslator->add('Edit Listing Store View', __('Edit Listing Store View'));
        $this->jsTranslator->add('Listing Title', __('Listing Title'));
        $this->jsTranslator->add(
            'The specified Title is already used for other Listing. Listing Title must be unique.',
            __(
                'The specified Title is already used for other Listing. Listing Title must be unique.'
            )
        );

        $this->jsPhp->addConstants(
            \M2E\Kaufland\Helper\Data::getClassConstants(\M2E\Kaufland\Helper\Component\Kaufland::class)
        );

        $component = \M2E\Kaufland\Helper\Component\Kaufland::NICK;

        $this->js->add(
            <<<JS
    require([
        'Kaufland/Kaufland/Listing/Grid',
        'Kaufland/Listing/EditTitle',
        'Kaufland/Listing/EditStoreView'
    ], function(){
        window.KauflandListingGridObj = new KauflandListingGrid('{$this->getId()}');
        window.EditListingTitleObj = new ListingEditListingTitle('{$this->getId()}', '{$component}');
        window.EditListingStoreViewObj = new ListingEditListingStoreView('{$this->getId()}');
    });
JS
        );

        return parent::_toHtml();
    }
}
