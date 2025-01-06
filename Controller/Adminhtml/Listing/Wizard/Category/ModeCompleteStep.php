<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Listing\Wizard\Category;

use M2E\Kaufland\Block\Adminhtml\Listing\Wizard\SelectMode;
use M2E\Kaufland\Model\Listing\Wizard\StepDeclarationCollectionFactory;

class ModeCompleteStep extends \M2E\Kaufland\Controller\Adminhtml\AbstractListing
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

        $mode = $this->getRequest()->getParam('mode');
        if (empty($mode)) {
            return $this->redirectToIndex($id);
        }

        if (!in_array($mode, [SelectMode::MODE_SAME, SelectMode::MODE_MANUALLY])) {
            throw new \LogicException(sprintf('Category mode %s not valid.', $mode));
        }

        $manager->setStepData(StepDeclarationCollectionFactory::STEP_GENERAL_SELECT_CATEGORY_MODE, [
            'mode' => $mode,
        ]);

        $manager->completeStep(StepDeclarationCollectionFactory::STEP_GENERAL_SELECT_CATEGORY_MODE);

        return $this->redirectToIndex($id);
    }
}
