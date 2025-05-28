<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Listing;

class View extends \M2E\Kaufland\Controller\Adminhtml\Kaufland\AbstractListing
{
    private \M2E\Kaufland\Helper\Data\GlobalData $globalData;
    private \M2E\Kaufland\Helper\Data\Session $sessionHelper;
    private \M2E\Kaufland\Model\Listing\Repository $listingRepository;
    private \M2E\Kaufland\Model\Listing\Ui\RuntimeStorage $uiListingRuntimeStorage;
    private \M2E\Kaufland\Model\Listing\Wizard\Repository $wizardRepository;
    private \M2E\Kaufland\Model\Channel\Magento\Product\RuleFactory $productRuleFactory;

    public function __construct(
        \M2E\Kaufland\Model\Listing\Repository $listingRepository,
        \M2E\Kaufland\Model\Listing\Ui\RuntimeStorage $uiListingRuntimeStorage,
        \M2E\Kaufland\Helper\Data\GlobalData $globalData,
        \M2E\Kaufland\Helper\Data\Session $sessionHelper,
        \M2E\Kaufland\Model\Listing\Wizard\Repository $wizardRepository,
        \M2E\Kaufland\Model\Channel\Magento\Product\RuleFactory $productRuleFactory
    ) {
        parent::__construct();

        $this->globalData = $globalData;
        $this->sessionHelper = $sessionHelper;
        $this->listingRepository = $listingRepository;
        $this->uiListingRuntimeStorage = $uiListingRuntimeStorage;
        $this->wizardRepository = $wizardRepository;
        $this->productRuleFactory = $productRuleFactory;
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');

        try {
            $listing = $this->listingRepository->get((int)$id);
        } catch (\M2E\Kaufland\Model\Exception\Logic $exception) {
            $this->getMessageManager()->addError(__('Listing does not exist.'));

            return $this->_redirect('*/fkaufland_listing/index');
        }

        $this->uiListingRuntimeStorage->setListing($listing);

        if ($this->getRequest()->getQuery('ajax')) {
            // Set rule model
            // ---------------------------------------
            $this->setRuleData('kaufland_rule_view_listing', $listing);
            // ---------------------------------------

            $this->setAjaxContent(
                $this->getLayout()
                     ->createBlock(
                         \M2E\Kaufland\Block\Adminhtml\Kaufland\Listing\View::class,
                         '',
                         ['listing' => $listing],
                     )
                     ->getGridHtml()
            );

            return $this->getResult();
        }

        if ((bool)$this->getRequest()->getParam('do_list', false)) {
            $this->sessionHelper->setValue(
                'products_ids_for_list',
                implode(',', $this->sessionHelper->getValue('added_products_ids'))
            );

            return $this->_redirect('*/*/*', [
                '_current' => true,
                'do_list' => null,
                'view_mode' => \M2E\Kaufland\Block\Adminhtml\Kaufland\Listing\View\Switcher::VIEW_MODE_KAUFLAND,
            ]);
        }

        $existWizard = $this->wizardRepository->findNotCompletedByListingAndType($listing, \M2E\Kaufland\Model\Listing\Wizard::TYPE_GENERAL);

        if (($existWizard !== null) && (!$existWizard->isCompleted())) {
            $this->getMessageManager()->addNoticeMessage(
                __(
                    'Please make sure you finish adding new Products before moving to the next step.',
                ),
            );

            return $this->_redirect('*/listing_wizard/index', ['id' => $existWizard->getId()]);
        }

        // Set rule model
        // ---------------------------------------
        $this->setRuleData('kaufland_rule_view_listing', $listing);
        // ---------------------------------------

        $this->setPageHelpLink('https://docs-m2.m2epro.com/m2e-kaufland-listings');

        $this->getResultPage()
             ->getConfig()
             ->getTitle()
             ->prepend(
                 (string)__(
                     '%extension_title Listing "%listing_title"',
                     [
                         'listing_title' => $listing->getTitle(),
                         'extension_title' => \M2E\Kaufland\Helper\Module::getExtensionTitle(),
                     ]
                 )
             );

        $this->addContent(
            $this->getLayout()->createBlock(
                \M2E\Kaufland\Block\Adminhtml\Kaufland\Listing\View::class,
                '',
                ['listing' => $listing],
            )
        );

        return $this->getResult();
    }

    protected function setRuleData($prefix, \M2E\Kaufland\Model\Listing $listing)
    {
        $storeId = $listing->getStoreId();
        $prefix .= $listing->getId();

        $this->globalData->setValue('rule_prefix', $prefix);

        $ruleModel = $this->productRuleFactory->create()->setData(
            [
                'prefix' => $prefix,
                'store_id' => $storeId,
            ]
        );

        $ruleParam = $this->getRequest()->getPost('rule');
        if (!empty($ruleParam)) {
            $this->sessionHelper->setValue(
                $prefix,
                $ruleModel->getSerializedFromPost($this->getRequest()->getPostValue())
            );
        } elseif ($ruleParam !== null) {
            $this->sessionHelper->setValue($prefix, []);
        }

        $sessionRuleData = $this->sessionHelper->getValue($prefix);
        if (!empty($sessionRuleData)) {
            $ruleModel->loadFromSerialized($sessionRuleData);
        }

        $this->globalData->setValue('rule_model', $ruleModel);
    }
}
