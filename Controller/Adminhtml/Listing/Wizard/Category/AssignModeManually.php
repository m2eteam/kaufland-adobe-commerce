<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Listing\Wizard\Category;

use M2E\Kaufland\Model\Listing\Wizard\StepDeclarationCollectionFactory;

class AssignModeManually extends \M2E\Kaufland\Controller\Adminhtml\AbstractListing
{
    use \M2E\Kaufland\Controller\Adminhtml\Listing\Wizard\WizardTrait;

    private \M2E\Kaufland\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory;
    private \M2E\Kaufland\Model\Listing\AddProductsService $addProductsService;
    private \M2E\Kaufland\Model\Product\AssignCategoryTemplateService $assignCategoryTemplateService;
    private \M2E\Kaufland\Model\Category\Dictionary\Repository $categoryRepository;

    public function __construct(
        \M2E\Kaufland\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory,
        \M2E\Kaufland\Model\Listing\AddProductsService $addProductsService,
        \M2E\Kaufland\Model\Product\AssignCategoryTemplateService $assignCategoryTemplateService,
        \M2E\Kaufland\Model\Category\Dictionary\Repository $categoryRepository
    ) {
        parent::__construct();
        $this->wizardManagerFactory = $wizardManagerFactory;
        $this->addProductsService = $addProductsService;
        $this->assignCategoryTemplateService = $assignCategoryTemplateService;
        $this->categoryRepository = $categoryRepository;
    }
    public function execute()
    {
        $id = $this->getWizardIdFromRequest();
        $manager = $this->wizardManagerFactory->createById($id);
        $manager->completeStep(\M2E\Kaufland\Model\Listing\Wizard\StepDeclarationCollectionFactory::STEP_GENERAL_SELECT_CATEGORY_STEP);

        return $this->redirectToIndex($id);
    }
}
