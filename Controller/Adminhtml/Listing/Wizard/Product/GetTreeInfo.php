<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Listing\Wizard\Product;

class GetTreeInfo extends \M2E\Kaufland\Controller\Adminhtml\AbstractListing
{
    use \M2E\Kaufland\Controller\Adminhtml\Listing\Wizard\WizardTrait;

    private \M2E\Kaufland\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory;

    public function __construct(
        \M2E\Kaufland\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory
    ) {
        parent::__construct();

        $this->wizardManagerFactory = $wizardManagerFactory;
    }

    public function execute()
    {
        $id = $this->getWizardIdFromRequest();
        $manager = $this->wizardManagerFactory->createById($id);
        $stepData = $manager->getStepData(\M2E\Kaufland\Model\Listing\Wizard\StepDeclarationCollectionFactory::STEP_SELECT_PRODUCTS);

        $productsIds = $stepData['products_ids'] ?? [];

        /** @var \M2E\Kaufland\Block\Adminhtml\Listing\Wizard\Category\Add\Tree $treeBlock */
        $treeBlock = $this->getLayout()
                          ->createBlock(
                              \M2E\Kaufland\Block\Adminhtml\Listing\Wizard\Category\Add\Tree::class
                          );
        $treeBlock->setSelectedIds($productsIds);

        $this->setAjaxContent($treeBlock->getInfoJson(), false);

        return $this->getResult();
    }
}
