<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Listing\Wizard;

class EnableCreateProductMode extends \M2E\Kaufland\Controller\Adminhtml\AbstractListing
{
    private \M2E\Kaufland\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory;

    public function __construct(
        \M2E\Kaufland\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory
    ) {
        parent::__construct();
        $this->wizardManagerFactory = $wizardManagerFactory;
    }

    public function execute()
    {
        $wizardId = (int)$this->getRequest()->getParam('id');

        $manager = $this->wizardManagerFactory->createById($wizardId);
        $manager->enableCreateNewProductMode();

        return $this->getResult();
    }
}
