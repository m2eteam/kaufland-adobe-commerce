<?php

declare(strict_types=1);

namespace M2E\Kaufland\Block\Adminhtml\Listing\AutoAction\Mode\Category\Group;

abstract class AbstractGrid extends \M2E\Kaufland\Block\Adminhtml\Magento\Grid\AbstractGrid
{
    private bool $isGridPrepared = false;

    private \M2E\Core\Helper\Magento\Category $magentoCategoryHelper;
    private \M2E\Core\Helper\Data $dataHelper;
    private \M2E\Kaufland\Model\ResourceModel\Listing\Auto\Category\Group\CollectionFactory $autoCategoryGroupCollectionFactory;
    private \M2E\Kaufland\Model\ResourceModel\Listing\Auto\Category $autoCategoryResource;

    public function __construct(
        \M2E\Kaufland\Model\ResourceModel\Listing\Auto\Category $autoCategoryResource,
        \M2E\Kaufland\Model\ResourceModel\Listing\Auto\Category\Group\CollectionFactory $autoCategoryGroupCollectionFactory,
        \M2E\Core\Helper\Magento\Category $magentoCategoryHelper,
        \M2E\Kaufland\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \M2E\Core\Helper\Data $dataHelper,
        array $data = []
    ) {
        $this->magentoCategoryHelper = $magentoCategoryHelper;
        $this->dataHelper = $dataHelper;
        parent::__construct($context, $backendHelper, $data);
        $this->autoCategoryResource = $autoCategoryResource;
        $this->autoCategoryGroupCollectionFactory = $autoCategoryGroupCollectionFactory;
    }

    public function _construct()
    {
        parent::_construct();

        $this->setId('listingAutoActionModeCategoryGroupGrid');

        $this->setDefaultSort('create_date');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    //########################################

    protected function _prepareGrid()
    {
        if (!$this->isGridPrepared) {
            parent::_prepareGrid();
            $this->isGridPrepared = true;
        }

        return $this;
    }

    public function prepareGrid()
    {
        return $this->_prepareGrid();
    }

    //########################################

    protected function _prepareCollection()
    {
        $collection = $this->autoCategoryGroupCollectionFactory->create();
        $collection->addFieldToFilter(
            \M2E\Kaufland\Model\ResourceModel\Listing\Auto\Category\Group::COLUMN_LISTING_ID,
            ['eq' => (int)$this->getRequest()->getParam('id')]
        );

        $categoriesSubQuery = $collection
            ->getConnection()
            ->select()
            ->from(['auto_categories' => $this->autoCategoryResource->getMainTable()], [])
            ->columns(
                new \Zend_Db_Expr(
                    sprintf(
                        'GROUP_CONCAT(%s)',
                        \M2E\Kaufland\Model\ResourceModel\Listing\Auto\Category::COLUMN_CATEGORY_ID
                    )
                )
            )
            ->where(
                sprintf(
                    'auto_categories.%s = main_table.%s',
                    \M2E\Kaufland\Model\ResourceModel\Listing\Auto\Category::COLUMN_GROUP_ID,
                    \M2E\Kaufland\Model\ResourceModel\Listing\Auto\Category\Group::COLUMN_ID
                )
            );

        $collection
            ->getSelect()
            ->columns(['categories' => $categoriesSubQuery]);

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    //########################################

    protected function _setCollectionOrder($column)
    {
        // We need to sort by id to maintain the correct sequence of records
        $collection = $this->getCollection();
        if ($collection) {
            $columnIndex = $column->getFilterIndex() ? $column->getFilterIndex() : $column->getIndex();
            $collection->getSelect()->order($columnIndex . ' ' . strtoupper($column->getDir()))->order('id DESC');
        }

        return $this;
    }

    //########################################

    protected function _prepareColumns()
    {
        $this->addColumn('title', [
            'header' => __('Title'),
            'align' => 'left',
            'type' => 'text',
            'escape' => true,
            'index' => 'title',
            'filter_index' => 'title',
        ]);

        $this->addColumn('categories', [
            'header' => __('Categories'),
            'align' => 'left',
            'type' => 'text',
            'sortable' => false,
            'filter' => false,
            'frame_callback' => [$this, 'callbackColumnCategories'],
        ]);

        $this->addColumn('action', [
            'header' => __('Actions'),
            'align' => 'left',
            'type' => 'text',
            'sortable' => false,
            'filter' => false,
            'actions' => [
                0 => [
                    'label' => __('Edit Rule'),
                    'value' => 'categoryStepOne',
                ],
                1 => [
                    'label' => __('Delete Rule'),
                    'value' => 'categoryDeleteGroup',
                ],
            ],
            'frame_callback' => [$this, 'callbackColumnActions'],
        ]);

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        // Set massaction identifiers
        // ---------------------------------------
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('ids');
        // ---------------------------------------
    }

    //########################################

    public function callbackColumnCategories($value, $row, $column, $isExport)
    {
        $groupId = (int)$row->getData('id');
        $categories = array_filter(explode(',', $row->getData('categories')));
        $count = count($categories);

        if ($count == 0 || $count > 3) {
            $total = __('Total');
            $html = "<strong>{$total}:&nbsp;</strong>&nbsp;{$count}";

            if (count($categories) > 3) {
                $details = __('details');
                $html .= <<<HTML
&nbsp;
[<a href="javascript: void(0);" onclick="ListingAutoActionObj.categoryStepOne({$groupId});">{$details}</a>]
HTML;
            }

            return $html;
        }

        $html = '';
        foreach ($categories as $categoryId) {
            $path = $this->magentoCategoryHelper->getPath($categoryId);

            if (empty($path)) {
                continue;
            }

            if ($html != '') {
                $html .= '<br/>';
            }

            $path = implode(' > ', $path);
            $html .= '<span style="font-style: italic;">' . $this->dataHelper->escapeHtml($path) . '</span>';
        }

        return $html;
    }

    public function callbackColumnActions($value, $row, $column, $isExport)
    {
        $actions = $column->getActions();
        $id = (int)$row->getData('id');

        if (count($actions) == 1) {
            $action = reset($actions);
            $onclick = 'ListingAutoActionObj[\'' . $action['value'] . '\'](' . $id . ');';

            return '<a href="javascript: void(0);" onclick="' . $onclick . '">' . $action['label'] . '</a>';
        }

        $optionsHtml = '<option></option>';

        foreach ($actions as $option) {
            $optionsHtml .= <<<HTML
            <option value="{$option['value']}">{$option['label']}</option>
HTML;
        }

        return <<<HTML
<div style="padding: 5px;">
    <select class="admin__control-select"
            style="margin: auto; display: block;"
            onchange="ListingAutoActionObj[this.value]({$id});">
        {$optionsHtml}
    </select>
</div>
HTML;
    }

    //########################################

    public function getRowUrl($item)
    {
        return false;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/listing_autoAction/getCategoryGroupGrid', ['_current' => true]);
    }

    //########################################
}
