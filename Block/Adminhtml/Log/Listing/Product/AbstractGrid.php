<?php

namespace M2E\Kaufland\Block\Adminhtml\Log\Listing\Product;

abstract class AbstractGrid extends \M2E\Kaufland\Block\Adminhtml\Log\Listing\AbstractGrid
{
    private \M2E\Kaufland\Model\ResourceModel\Account $accountResource;

    public function __construct(
        \M2E\Kaufland\Model\ResourceModel\Account $accountResource,
        \M2E\Kaufland\Model\Config\Manager $config,
        \M2E\Kaufland\Model\ResourceModel\Collection\WrapperFactory $wrapperCollectionFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \M2E\Kaufland\Helper\View $viewHelper,
        \M2E\Kaufland\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \M2E\Core\Helper\Data $dataHelper,
        array $data = []
    ) {
        $this->accountResource = $accountResource;
        parent::__construct(
            $config,
            $wrapperCollectionFactory,
            $resourceConnection,
            $viewHelper,
            $context,
            $backendHelper,
            $dataHelper,
            $data
        );
    }

    public function _construct(): void
    {
        parent::_construct();
        $this->setId('LogListingGrid' . $this->getEntityId());

        $this->setDefaultSort('create_date');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);

        $this->entityIdFieldName = self::LISTING_PRODUCT_ID_FIELD;
        $this->logModelName = 'Listing_Log';
    }

    /**
     * @param \M2E\Kaufland\Model\Listing\Log|\Magento\Framework\DataObject $type DataObject for grouped mode
     *
     * @return int
     */
    protected function getLogHash($type)
    {
        return crc32("{$type->getActionId()}_{$type->getListingId()}_{$type->getListingProductId()}");
    }

    /**
     * @param \Magento\Framework\Data\Collection $collection
     */
    protected function applyFilters($collection)
    {
        // Set listing filter
        // ---------------------------------------
        if ($this->getEntityId()) {
            $collection->addFieldToFilter($this->getEntityField(), $this->getEntityId());
        }
        // ---------------------------------------

        if ($accountId = $this->getRequest()->getParam('Account')) {
            $collection->addFieldToFilter('main_table.account_id', $accountId);
        } else {
            $collection->getSelect()->joinLeft(
                [
                    'account_table' => $this->accountResource->getMainTable(),
                ],
                'main_table.account_id = account_table.id',
                ['real_account_id' => 'account_table.id']
            );
            $collection->addFieldToFilter('account_table.id', ['notnull' => true]);
        }

        if ($storefrontId = $this->getRequest()->getParam('Storefront')) {
            $collection->addFieldToFilter('main_table.storefront_id', $storefrontId);
        }
    }

    protected function _prepareColumns()
    {
        $this->addColumn('create_date', [
            'header' => (string)__('Creation Date'),
            'align' => 'left',
            'type' => 'datetime',
            'filter' => \M2E\Kaufland\Block\Adminhtml\Magento\Grid\Column\Filter\Datetime::class,
            'filter_time' => true,
            'filter_index' => 'main_table.create_date',
            'index' => 'create_date',
            'frame_callback' => [$this, 'callbackColumnCreateDate'],
        ]);

        $this->addColumn('action', [
            'header' => __('Action'),
            'align' => 'left',
            'type' => 'options',
            'index' => 'action',
            'sortable' => false,
            'filter_index' => 'main_table.action',
            'options' => $this->getActionTitles(),
        ]);

        if (!$this->getEntityId()) {
            $this->addColumn('listing_title', [
                'header' => __('Listing'),
                'align' => 'left',
                'type' => 'text',
                'index' => 'listing_title',
                'filter_index' => 'main_table.listing_title',
                'frame_callback' => [$this, 'callbackColumnListingTitleID'],
                'filter_condition_callback' => [$this, 'callbackFilterListingTitleID'],
            ]);
        }

        if (!$this->isListingProductLog()) {
            $this->addColumn('product_title', [
                'header' => __('Magento Product'),
                'align' => 'left',
                'type' => 'text',
                'index' => 'product_title',
                'filter_index' => 'main_table.product_title',
                'frame_callback' => [$this, 'callbackColumnProductTitleID'],
                'filter_condition_callback' => [$this, 'callbackFilterProductTitleID'],
            ]);
        }

        $this->addColumn('description', [
            'header' => __('Message'),
            'align' => 'left',
            'type' => 'text',
            'index' => 'description',
            'filter_index' => 'main_table.description',
            'frame_callback' => [$this, 'callbackColumnDescription'],
        ]);

        $this->addColumn('initiator', [
            'header' => __('Run Mode'),
            'index' => 'initiator',
            'align' => 'right',
            'type' => 'options',
            'sortable' => false,
            'options' => $this->_getLogInitiatorList(),
            'frame_callback' => [$this, 'callbackColumnInitiator'],
        ]);

        $this->addColumn('type', [
            'header' => __('Type'),
            'index' => 'type',
            'align' => 'right',
            'type' => 'options',
            'sortable' => false,
            'options' => $this->_getLogTypeList(),
            'frame_callback' => [$this, 'callbackColumnType'],
        ]);

        return parent::_prepareColumns();
    }

    public function callbackColumnListingTitleID($value, $row, $column, $isExport)
    {
        if (strlen($value) > 50) {
            $value = $this->filterManager->truncate($value, ['length' => 50]);
        }

        $value = \M2E\Core\Helper\Data::escapeHtml($value);
        $productId = (int)$row->getData('product_id');

        $urlData = [
            'id' => $row->getData('listing_id'),
            'filter' => base64_encode("product_id[from]={$productId}&product_id[to]={$productId}"),
        ];

        $manageUrl = $this->getUrl('*/kaufland_listing/view', $urlData);
        if ($row->getData('listing_id')) {
            $url = $this->getUrl(
                '*/kaufland_listing/view',
                ['id' => $row->getData('listing_id')]
            );

            $value = '<a target="_blank" href="' . $url . '">' .
                $value .
                '</a><br/>ID: ' . $row->getData('listing_id');

            if ($productId) {
                $value .= '<br/>Product:<br/>' .
                    '<a target="_blank" href="' . $manageUrl . '">' . $row->getData('product_title') . '</a>';
            }
        }

        return $value;
    }

    public function callbackColumnProductTitleID($value, $row, $column, $isExport)
    {
        if (!$row->getData('product_id')) {
            return $value;
        }

        $url = $this->getUrl('catalog/product/edit', ['id' => $row->getData('product_id')]);
        $value = '<a target="_blank" href="' . $url . '" target="_blank">' .
            \M2E\Core\Helper\Data::escapeHtml($value) .
            '</a><br/>ID: ' . $row->getData('product_id');

        return $value;
    }

    public function callbackColumnCreateDate($value, $row, $column, $isExport)
    {
        $logHash = $this->getLogHash($row);

        if ($logHash !== null) {
            return "{$value}<div class='no-display log-hash'>{$logHash}</div>";
        }

        return $value;
    }

    protected function callbackFilterListingTitleID($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $where = 'listing_title LIKE ' . $collection->getSelect()->getAdapter()->quote('%' . $value . '%');
        is_numeric($value) && $where .= ' OR listing_id = ' . $value;

        $collection->getSelect()->where($where);
    }

    protected function callbackFilterProductTitleID($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $where = 'product_title LIKE ' . $collection->getSelect()->getAdapter()->quote('%' . $value . '%');
        is_numeric($value) && $where .= ' OR product_id = ' . $value;

        $collection->getSelect()->where($where);
    }

    protected function callbackFilterAttributes($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $collection->getSelect()->where('main_table.additional_data LIKE ?', '%' . $value . '%');
    }

    /**
     * Implements by using traits
     */
    abstract protected function getExcludedActionTitles();

    // ---------------------------------------

    protected function getActionTitles(): array
    {
        $allActions = \M2E\Kaufland\Helper\Module\Log::getActionsTitlesByClass(
            \M2E\Kaufland\Model\Listing\Log::class,
        );

        return array_diff_key($allActions, $this->getExcludedActionTitles());
    }
}
