<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Listing\Wizard\Category;

class AssignModeSame extends \M2E\Kaufland\Controller\Adminhtml\AbstractListing
{
    use \M2E\Kaufland\Controller\Adminhtml\Listing\Wizard\WizardTrait;

    private \M2E\Kaufland\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory;
    private \M2E\Kaufland\Model\Category\Dictionary\Repository $categoryRepository;
    private \M2E\Kaufland\Model\Listing\Repository $listingRepository;

    public function __construct(
        \M2E\Kaufland\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory,
        \M2E\Kaufland\Model\Category\Dictionary\Repository $categoryRepository,
        \M2E\Kaufland\Model\Listing\Repository $listingRepository
    ) {
        parent::__construct();
        $this->wizardManagerFactory = $wizardManagerFactory;
        $this->categoryRepository = $categoryRepository;
        $this->listingRepository = $listingRepository;
    }

    public function execute()
    {
        $id = $this->getWizardIdFromRequest();
        $manager = $this->wizardManagerFactory->createById($id);

        $categoryData = [];
        if ($param = $this->getRequest()->getParam('category_data')) {
            $categoryData = json_decode($param, true);
        }

        $dictionaryId = (int)$categoryData['dictionary_id'];
        if (empty($dictionaryId)) {
            return $this->redirectToIndex($id);
        }

        $manager->setProductsCategoryIdSame($dictionaryId);
        $manager->setProductsCategoryTitleSame($categoryData['path']);

        $manager->completeStep(\M2E\Kaufland\Model\Listing\Wizard\StepDeclarationCollectionFactory::STEP_GENERAL_SELECT_CATEGORY_STEP);

        return $this->redirectToIndex($id);
    }
}
