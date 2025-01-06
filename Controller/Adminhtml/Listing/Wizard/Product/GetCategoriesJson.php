<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Listing\Wizard\Product;

class GetCategoriesJson extends \M2E\Kaufland\Controller\Adminhtml\AbstractListing
{
    use \M2E\Kaufland\Controller\Adminhtml\Listing\Wizard\WizardTrait;

    private \M2E\Kaufland\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory;
    private \M2E\Kaufland\Model\Listing\Ui\RuntimeStorage $uiListingRuntimeStorage;

    public function __construct(
        \M2E\Kaufland\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory,
        \M2E\Kaufland\Model\Listing\Ui\RuntimeStorage $uiListingRuntimeStorage
    ) {
        parent::__construct();

        $this->wizardManagerFactory = $wizardManagerFactory;
        $this->uiListingRuntimeStorage = $uiListingRuntimeStorage;
    }

    public function execute()
    {
        $id = $this->getWizardIdFromRequest();
        $manager = $this->wizardManagerFactory->createById($id);

        $this->uiListingRuntimeStorage->setListing($manager->getListing());

        $stepData = $manager->getStepData(\M2E\Kaufland\Model\Listing\Wizard\StepDeclarationCollectionFactory::STEP_SELECT_PRODUCTS);
        $selectedProductsIds = $stepData['products_ids'] ?? [];

        /** @var \M2E\Kaufland\Block\Adminhtml\Listing\Wizard\Category\Add\Tree $treeBlock */
        $treeBlock = $this->getLayout()
                          ->createBlock(
                              \M2E\Kaufland\Block\Adminhtml\Listing\Wizard\Category\Add\Tree::class
                          );
        $treeBlock->setSelectedIds($selectedProductsIds);

        $this->setAjaxContent($treeBlock->getCategoryChildrenJson($this->getRequest()->getParam('category')), false);

        return $this->getResult();
    }
}
