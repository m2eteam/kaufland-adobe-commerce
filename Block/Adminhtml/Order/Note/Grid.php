<?php

namespace M2E\Kaufland\Block\Adminhtml\Order\Note;

use M2E\Kaufland\Block\Adminhtml\Magento\Grid\AbstractGrid;

class Grid extends AbstractGrid
{
    private \M2E\Kaufland\Model\ResourceModel\Order\Note\CollectionFactory $orderNoteCollectionFactory;

    public function __construct(
        \M2E\Kaufland\Model\ResourceModel\Order\Note\CollectionFactory $orderNoteCollectionFactory,
        \M2E\Kaufland\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->orderNoteCollectionFactory = $orderNoteCollectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    public function _construct(): void
    {
        parent::_construct();

        $this->setId('orderNoteGrid');

        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setFilterVisibility(false);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $collection = $this->orderNoteCollectionFactory->create();
        $collection->addFieldToFilter('order_id', $this->getRequest()->getParam('id'));

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id', [
            'header' => __('Description'),
            'align' => 'left',
            'width' => '*',
            'type' => 'text',
            'sortable' => false,
            'filter_index' => 'id',
            'index' => 'note',
        ]);

        $this->addColumn('create_date', [
            'header' => __('Create Date'),
            'align' => 'left',
            'width' => '165px',
            'type' => 'datetime',
            'format' => \IntlDateFormatter::MEDIUM,
            'index' => 'create_date',
        ]);

        $this->addColumn('actions', [
            'header' => __('Actions'),
            'align' => 'left',
            'width' => '150px',
            'type' => 'action',
            'filter' => false,
            'sortable' => false,
            'getter' => 'getId',
            'renderer' => \M2E\Kaufland\Block\Adminhtml\Magento\Grid\Column\Renderer\Action::class,
            'actions' => [
                [
                    'caption' => __('Edit'),
                    'onclick_action' => "OrderNoteObj.openEditNotePopup",
                    'field' => 'id',
                ],
                [
                    'caption' => __('Delete'),
                    'onclick_action' => "OrderNoteObj.deleteNote",
                    'field' => 'id',
                ],
            ],
        ]);

        return parent::_prepareColumns();
    }

    //########################################

    public function getRowUrl($item)
    {
        return '';
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/order/noteGrid', ['_current' => true]);
    }

    //########################################
}
