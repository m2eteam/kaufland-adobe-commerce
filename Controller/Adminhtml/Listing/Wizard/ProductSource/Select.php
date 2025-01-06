<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Listing\Wizard\ProductSource;

use M2E\Kaufland\Model\Listing\Wizard\StepDeclarationCollectionFactory;
use Magento\Framework\App\Action\HttpPostActionInterface;

class Select extends \M2E\Kaufland\Controller\Adminhtml\AbstractListing implements HttpPostActionInterface
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

        $source = $this->getRequest()->getPost('source');
        $allowedSources = [
            \M2E\Kaufland\Block\Adminhtml\Listing\Wizard\ProductSourceSelect::MODE_PRODUCT,
            \M2E\Kaufland\Block\Adminhtml\Listing\Wizard\ProductSourceSelect::MODE_CATEGORY,
        ];

        if (!in_array($source, $allowedSources)) {
            return $this->redirectToIndex($id);
        }

        $wizardManager = $this->wizardManagerFactory->createById($id);
        if (!$wizardManager->isCurrentStepIs(StepDeclarationCollectionFactory::STEP_SELECT_PRODUCT_SOURCE)) {
            return $this->redirectToIndex($id);
        }

        $wizardManager->setStepData(
            StepDeclarationCollectionFactory::STEP_SELECT_PRODUCT_SOURCE,
            [
                'source' => $source,
            ],
        );

        $wizardManager->completeStep(StepDeclarationCollectionFactory::STEP_SELECT_PRODUCT_SOURCE);

        return $this->redirectToIndex($id);
    }
}
