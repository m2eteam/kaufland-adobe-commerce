<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Listing\Wizard\Search;

use M2E\Kaufland\Model\Listing\Wizard\StepDeclarationCollectionFactory;

class View extends \M2E\Kaufland\Controller\Adminhtml\Listing\Wizard\StepAbstract
{
    use \M2E\Kaufland\Controller\Adminhtml\Listing\Wizard\WizardTrait;

    private \M2E\Kaufland\Helper\Component\Kaufland\Configuration $configuration;

    public function __construct(
        \M2E\Kaufland\Model\Listing\Ui\RuntimeStorage $uiListingRuntimeStorage,
        \M2E\Kaufland\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory,
        \M2E\Kaufland\Model\Listing\Wizard\Ui\RuntimeStorage $uiWizardRuntimeStorage,
        \M2E\Kaufland\Helper\Component\Kaufland\Configuration $configuration
    ) {
        parent::__construct($wizardManagerFactory, $uiListingRuntimeStorage, $uiWizardRuntimeStorage);
        $this->configuration = $configuration;
    }

    protected function getStepNick(): string
    {
        return StepDeclarationCollectionFactory::STEP_SEARCH_PRODUCTS_CHANNEL_ID;
    }

    protected function process(\M2E\Kaufland\Model\Listing $listing)
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $grid = $this->getLayout()
                         ->createBlock(
                             \M2E\Kaufland\Block\Adminhtml\Listing\Wizard\Product\SearchChannelIdStep\Grid::class,
                             '',
                             [ 'listing' => $this->uiListingRuntimeStorage->getListing(),
                               'wizardManager' => $this->uiWizardRuntimeStorage->getManager()],
                         );
            $this->setAjaxContent($grid);

            return;
        }

        if (!$this->configuration->getIdentifierCodeCustomAttribute()) {
            $this->getMessageManager()->addErrorMessage(
                __(
                    'Product search is unavailable due to a missing Product Identifier.
     Please make sure to set the EAN in %channel_title > Configuration > Settings > Main',
                    ['channel_title' => \M2E\Kaufland\Helper\Module::getChannelTitle()]
                )
            );
        }

        $this->getResultPage()
             ->getConfig()
             ->getTitle()->prepend(
                 __(
                     '%channel_title Product Search',
                     ['channel_title' => \M2E\Kaufland\Helper\Module::getChannelTitle()]
                 )
             );

        $this->addContent(
            $this->getLayout()->createBlock(
                \M2E\Kaufland\Block\Adminhtml\Listing\Wizard\Product\SearchChannelIdStep::class,
            ),
        );

        return $this->getResult();
    }
}
