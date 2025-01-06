<?php

namespace M2E\Kaufland\Block\Adminhtml\Listing\View;

abstract class Grid extends \M2E\Kaufland\Block\Adminhtml\Magento\Product\Grid
{
    /** @var \M2E\Kaufland\Model\Listing */
    protected $listing;
    /** @var \M2E\Kaufland\Helper\Data\GlobalData */
    protected $globalDataHelper;

    public function __construct(
        \M2E\Kaufland\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \M2E\Kaufland\Helper\Data $dataHelper,
        \M2E\Kaufland\Helper\Data\GlobalData $globalDataHelper,
        \M2E\Kaufland\Helper\Data\Session $sessionHelper,
        array $data = []
    ) {
        $this->globalDataHelper = $globalDataHelper;
        parent::__construct($globalDataHelper, $sessionHelper, $context, $backendHelper, $dataHelper, $data);
    }

    public function _construct()
    {
        parent::_construct();

        $this->listing = $this->globalDataHelper->getValue('view_listing');
    }

    public function setCollection($collection)
    {
        if ($this->listing) {
            $collection->setStoreId($this->listing['store_id']);
        }

        parent::setCollection($collection);
    }

    protected function _prepareLayout()
    {
        $this->css->addFile('listing/view/grid.css');

        return parent::_prepareLayout();
    }

    public function getStoreId()
    {
        return (int)$this->listing['store_id'];
    }

    protected function _toHtml()
    {
        // ---------------------------------------

        if ($this->getRequest()->isXmlHttpRequest()) {
            return parent::_toHtml();
        }

        // ---------------------------------------
        $this->jsTranslator->addTranslations([
            'Are you sure you want to create empty Listing?' => \M2E\Core\Helper\Data::escapeJs(
                (string)__('Are you sure you want to create empty Listing?')
            ),
        ]);

        // ---------------------------------------

        return parent::_toHtml();
    }
}
