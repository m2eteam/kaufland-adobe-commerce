<?php

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Template;

class EditListingProductsPolicy extends \M2E\Kaufland\Controller\Adminhtml\Kaufland\AbstractTemplate
{
    private \M2E\Kaufland\Helper\Component\Kaufland\Template\Switcher\DataLoader $templateSwitcherDataLoader;
    private \M2E\Kaufland\Helper\Data\GlobalData $helperDataGlobal;
    private \M2E\Kaufland\Model\ResourceModel\Product\CollectionFactory $listingProductCollectionFactory;

    public function __construct(
        \M2E\Kaufland\Model\ResourceModel\Product\CollectionFactory $listingProductCollectionFactory,
        \M2E\Kaufland\Helper\Data\GlobalData $helperDataGlobal,
        \M2E\Kaufland\Helper\Component\Kaufland\Template\Switcher\DataLoader $templateSwitcherDataLoader,
        \M2E\Kaufland\Model\Template\Manager $templateManager
    ) {
        parent::__construct($templateManager);

        $this->templateSwitcherDataLoader = $templateSwitcherDataLoader;
        $this->helperDataGlobal = $helperDataGlobal;
        $this->listingProductCollectionFactory = $listingProductCollectionFactory;
    }

    public function execute()
    {
        $ids = $this->getRequestIds('products_id');

        if (empty($ids)) {
            $this->setAjaxContent('0', false);

            return $this->getResult();
        }

        // ---------------------------------------
        $collection = $this->listingProductCollectionFactory
            ->create()
            ->addFieldToFilter('id', ['in' => $ids]);
        // ---------------------------------------

        if ($collection->getSize() == 0) {
            $this->setAjaxContent('0', false);

            return $this->getResult();
        }

        // ---------------------------------------
        $dataLoader = $this->templateSwitcherDataLoader;
        $dataLoader->load($collection);
        // ---------------------------------------

        $this->helperDataGlobal->setValue('products_ids', $ids);

        $content = $this->getLayout()
                        ->createBlock(\M2E\Kaufland\Block\Adminhtml\Kaufland\Listing\Product\Template\Edit::class);

        $this->setAjaxContent($content->toHtml());

        return $this->getResult();
    }
}
