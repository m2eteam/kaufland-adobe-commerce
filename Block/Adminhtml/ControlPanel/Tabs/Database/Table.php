<?php

declare(strict_types=1);

namespace M2E\Kaufland\Block\Adminhtml\ControlPanel\Tabs\Database;

class Table extends \M2E\Kaufland\Block\Adminhtml\Magento\Grid\AbstractContainer
{
    protected \M2E\Kaufland\Helper\View\ControlPanel $controlPanelHelper;
    private string $tableName;

    public function __construct(
        \M2E\Kaufland\Helper\View\ControlPanel $controlPanelHelper,
        \M2E\Kaufland\Block\Adminhtml\Magento\Context\Widget $context,
        string $tableName
    ) {
        $this->controlPanelHelper = $controlPanelHelper;
        $this->tableName = $tableName;
        parent::__construct($context);
    }

    public function _construct()
    {
        parent::_construct();

        $this->setId('controlPanelDatabaseTable');
        $this->_controller = 'adminhtml_controlPanel_tabs_database_table';

        $title = sprintf('Manage Table "%s"', $this->tableName);

        $this->pageConfig->getTitle()->prepend($title);
        $this->_headerText = (string)__($title);

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $url = $this->controlPanelHelper->getPageDatabaseTabUrl();
        $this->addButton('back', [
            'label' => __('Back'),
            'onclick' => "window.open('{$url}','_blank')",
            'class' => 'back',
        ]);

        $url = $this->getUrl('*/controlPanel_tools/magento', ['action' => 'clearMagentoCache']);
        $this->addButton('additional-actions', [
            'label' => __('Additional Actions'),
            'onclick' => '',
            'class' => 'action-secondary',
            'sort_order' => 100,
            'class_name' => \M2E\Kaufland\Block\Adminhtml\Magento\Button\DropDown::class,
            'options' => [
                'clear-cache' => [
                    'label' => __('Flush Cache'),
                    'onclick' => "window.open('{$url}', '_blank');",
                ],
            ],
        ]);

        $this->addButton('add_row', [
            'label' => __('Append Row'),
            'onclick' => 'ControlPanelDatabaseGridObj.openTableCellsPopup(\'add\')',
            'class' => 'action-success',
            'sort_order' => 90,
        ]);
    }

    protected function _prepareLayout()
    {
        /** @var \M2E\Kaufland\Block\Adminhtml\ControlPanel\Tabs\Database\Table\Grid $grid */
        $grid = $this->getLayout()->createBlock(
            \M2E\Kaufland\Block\Adminhtml\ControlPanel\Tabs\Database\Table\Grid::class,
            '',
            [
                'tableName' => $this->tableName,
            ],
        );
        $this->setChild('grid', $grid);

        return parent::_prepareLayout();
    }
}
