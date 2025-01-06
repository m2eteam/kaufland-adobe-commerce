<?php

namespace M2E\Kaufland\Block\Adminhtml\Log;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

abstract class AbstractGrid extends \M2E\Kaufland\Block\Adminhtml\Magento\Grid\AbstractGrid
{
    public const LISTING_ID_FIELD = 'listing_id';
    public const LISTING_PRODUCT_ID_FIELD = 'listing_product_id';
    public const LISTING_PARENT_PRODUCT_ID_FIELD = 'parent_listing_product_id';
    public const ORDER_ID_FIELD = 'order_id';

    protected $resourceConnection;

    protected $messageCount = [];
    protected $entityIdFieldName;
    protected $logModelName;
    /** @var \M2E\Kaufland\Helper\View */
    protected $viewHelper;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \M2E\Kaufland\Helper\View $viewHelper,
        \M2E\Kaufland\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->viewHelper = $viewHelper;
        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct()
    {
        parent::_construct();

        $this->setCustomPageSize(true);
    }

    //########################################

    protected function getEntityId()
    {
        if ($this->isListingLog()) {
            return $this->getRequest()->getParam($this::LISTING_ID_FIELD);
        }

        if ($this->isListingProductLog()) {
            return $this->getRequest()->getParam($this::LISTING_PRODUCT_ID_FIELD);
        }

        return null;
    }

    protected function getEntityField()
    {
        if ($this->isListingLog()) {
            return $this::LISTING_ID_FIELD;
        }

        if ($this->isListingProductLog()) {
            return $this::LISTING_PRODUCT_ID_FIELD;
        }

        return null;
    }

    //########################################

    public function isListingLog()
    {
        $id = $this->getRequest()->getParam($this::LISTING_ID_FIELD);

        return !empty($id);
    }

    public function isListingProductLog()
    {
        $listingProductId = $this->getRequest()->getParam($this::LISTING_PRODUCT_ID_FIELD);

        return !empty($listingProductId);
    }

    public function isSingleOrderLog()
    {
        return $this->getRequest()->getParam(self::ORDER_ID_FIELD);
    }

    public function isNeedCombineMessages()
    {
        return !$this->isListingProductLog() && !$this->isSingleOrderLog() &&
            $this->getRequest()->getParam('only_unique_messages', true);
    }

    //########################################

    public function getListingProductId()
    {
        return $this->getRequest()->getParam($this::LISTING_PRODUCT_ID_FIELD, false);
    }

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

    protected function _getLogTypeList()
    {
        return [
            \M2E\Kaufland\Model\Log\AbstractModel::TYPE_INFO => (string)__('Info'),
            \M2E\Kaufland\Model\Log\AbstractModel::TYPE_SUCCESS => (string)__('Success'),
            \M2E\Kaufland\Model\Log\AbstractModel::TYPE_WARNING => (string)__('Warning'),
            \M2E\Kaufland\Model\Log\AbstractModel::TYPE_ERROR => (string)__('Error'),
        ];
    }

    protected function _getLogInitiatorList()
    {
        return [
            \M2E\Core\Helper\Data::INITIATOR_UNKNOWN => (string)__('Unknown'),
            \M2E\Core\Helper\Data::INITIATOR_USER => (string)__('Manual'),
            \M2E\Core\Helper\Data::INITIATOR_EXTENSION => (string)__('Automatic'),
        ];
    }

    //########################################

    public function callbackColumnType($value, $row, $column, $isExport)
    {
        switch ($row->getData('type')) {
            case \M2E\Kaufland\Model\Log\AbstractModel::TYPE_SUCCESS:
                $value = '<span style="color: green;">' . $value . '</span>';
                break;

            case \M2E\Kaufland\Model\Log\AbstractModel::TYPE_WARNING:
                $value = '<span style="color: orange; font-weight: bold;">' . $value . '</span>';
                break;

            case \M2E\Kaufland\Model\Synchronization\Log::TYPE_FATAL_ERROR:
            case \M2E\Kaufland\Model\Log\AbstractModel::TYPE_ERROR:
                $value = '<span style="color: red; font-weight: bold;">' . $value . '</span>';
                break;

            default:
                break;
        }

        return $value;
    }

    public function callbackColumnInitiator($value, $row, $column, $isExport)
    {
        $initiator = $row->getData('initiator');

        switch ($initiator) {
            case \M2E\Core\Helper\Data::INITIATOR_EXTENSION:
                $message = "<span style=\"text-decoration: underline;\">{$value}</span>";
                break;
            case \M2E\Core\Helper\Data::INITIATOR_UNKNOWN:
                $message = "<span style=\"font-style: italic; color: gray;\">{$value}</span>";
                break;
            case \M2E\Core\Helper\Data::INITIATOR_USER:
            default:
                $message = "<span>{$value}</span>";
                break;
        }

        return $message;
    }

    public function callbackColumnDescription($value, $row, $column, $isExport)
    {
        $fullDescription = str_replace(
            "\n",
            '<br>',
            $this->viewHelper->getModifiedLogMessage($row->getData('description'))
        );

        $renderedText = $this->stripTags($fullDescription, '<br>');
        if (strlen($renderedText) < 200) {
            $html = $fullDescription;
        } else {
            $renderedText = $this->filterManager->truncate($renderedText, ['length' => 200]);

            $moreText = __('more');
            $html = <<<HTML
{$renderedText}
<a href="javascript://" onclick="LogObj.showFullText(this);">
    $moreText
</a>
<div class="no-display">{$fullDescription}</div>
HTML;
        }

        $countHtml = '';

        if (isset($this->messageCount[$row[$this->entityIdFieldName]])) {
            $colorMap = [
                \M2E\Kaufland\Model\Log\AbstractModel::TYPE_INFO => 'gray',
                \M2E\Kaufland\Model\Log\AbstractModel::TYPE_SUCCESS => 'green',
                \M2E\Kaufland\Model\Log\AbstractModel::TYPE_WARNING => 'orange',
                \M2E\Kaufland\Model\Log\AbstractModel::TYPE_ERROR => 'red',
            ];

            $count = $this->messageCount[$row[$this->entityIdFieldName]][$row['description']]['count'];
            if ($count > 1) {
                $color = $colorMap[$row['type']];
                $countHtml = " <span style='color: {$color}; font-weight: bold'>({$count})</span>";
            }
        }

        return $html . $countHtml;
    }

    //########################################

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', [
            '_current' => true,
        ]);
    }

    protected function _prepareLayout()
    {
        $this->css->addFile('log/grid.css');

        parent::_prepareLayout();
    }

    protected function _toHtml()
    {
        $this->jsTranslator->addTranslations([
            'Description' => (string)__('Description'),
        ]);

        $this->js->addRequireJs(['l' => 'Kaufland/Log'], "window.LogObj = new Log();");

        return parent::_toHtml();
    }

    protected function prepareMessageCount(AbstractCollection $collection)
    {
    }
}
