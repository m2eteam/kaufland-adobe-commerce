<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Listing\Wizard\Description;

use M2E\Kaufland\Model\Listing\Wizard\StepDeclarationCollectionFactory;

class View extends \M2E\Kaufland\Controller\Adminhtml\Listing\Wizard\StepAbstract
{
    use \M2E\Kaufland\Controller\Adminhtml\Listing\Wizard\WizardTrait;

    public function __construct(
        \M2E\Kaufland\Model\Listing\Ui\RuntimeStorage $uiListingRuntimeStorage,
        \M2E\Kaufland\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory,
        \M2E\Kaufland\Model\Listing\Wizard\Ui\RuntimeStorage $uiWizardRuntimeStorage
    ) {
        parent::__construct($wizardManagerFactory, $uiListingRuntimeStorage, $uiWizardRuntimeStorage);
    }

    protected function getStepNick(): string
    {
        return StepDeclarationCollectionFactory::STEP_DESCRIPTION_POLICY;
    }

    protected function process(\M2E\Kaufland\Model\Listing $listing)
    {
        if ($this->isNeedSkipStep()) {
            $this->getWizardManager()
                 ->completeStep(StepDeclarationCollectionFactory::STEP_DESCRIPTION_POLICY, true);

            return $this->redirectToIndex($this->getWizardManager()->getWizardId());
        }

        $this->getResultPage()
             ->getConfig()
             ->getTitle()->prepend(__('Add Description Policy'));

        $this->addContent(
            $this->getLayout()->createBlock(
                \M2E\Kaufland\Block\Adminhtml\Listing\Wizard\Description\View::class,
                '',
                ['listing' => $listing,
                    'id' => $listing->getId()
                ],
            ),
        );

        return $this->getResult();
    }

    private function isNeedSkipStep(): bool
    {
        if (!$this->getWizardManager()->isEnabledCreateNewProductMode()) {
            return true;
        }

        if ($this->getWizardManager()->getListing()->hasDescriptionPolicy()) {
            return true;
        }

        return false;
    }
}
