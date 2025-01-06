<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Listing\Wizard\Category;

use M2E\Kaufland\Controller\Adminhtml\Kaufland\AbstractListing;

class SaveModeManually extends AbstractListing
{
    use \M2E\Kaufland\Controller\Adminhtml\Listing\Wizard\WizardTrait;

    private \M2E\Core\Helper\Magento\Category $magentoCategoryHelper;
    private \M2E\Kaufland\Model\ResourceModel\Product\CollectionFactory $listingProductCollectionFactory;
    private \M2E\Kaufland\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory;
    private \M2E\Kaufland\Model\Listing\Wizard\Repository $wizardRepository;

    public function __construct(
        \M2E\Core\Helper\Magento\Category $magentoCategoryHelper,
        \M2E\Kaufland\Model\ResourceModel\Product\CollectionFactory $listingProductCollectionFactory,
        \M2E\Kaufland\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory,
        \M2E\Kaufland\Model\Listing\Wizard\Repository $wizardRepository
    ) {
        parent::__construct();

        $this->magentoCategoryHelper = $magentoCategoryHelper;
        $this->listingProductCollectionFactory = $listingProductCollectionFactory;
        $this->wizardManagerFactory = $wizardManagerFactory;
        $this->wizardRepository = $wizardRepository;
    }

    public function execute()
    {
        $id = $this->getWizardIdFromRequest();
        $manager = $this->wizardManagerFactory->createById($id);

        $templateData = $this->getRequest()->getParam('template_data');
        $templateData = (array)\M2E\Core\Helper\Json::decode($templateData);

        foreach ($this->getRequestIds('products_id') as $productsId) {
            $wizardProduct = $manager->findProductById((int) $productsId);

            if ($wizardProduct === null) {
                continue;
            }

            $wizardProduct->setCategoryId($templateData['dictionary_id']);
            $wizardProduct->setCategoryTitle($templateData['path']);
            $this->wizardRepository->saveProduct($wizardProduct);
        }

        return $this->getResult();
    }
}
