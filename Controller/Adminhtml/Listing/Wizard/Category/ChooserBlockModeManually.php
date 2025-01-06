<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Listing\Wizard\Category;

use M2E\Kaufland\Controller\Adminhtml\Kaufland\AbstractListing;

class ChooserBlockModeManually extends AbstractListing
{
    use \M2E\Kaufland\Controller\Adminhtml\Listing\Wizard\WizardTrait;

    private \M2E\Kaufland\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory;
    private \M2E\Kaufland\Model\Category\Dictionary\Repository $categoryDictionaryRepository;
    private \M2E\Kaufland\Model\Category\Dictionary\Repository $dictionaryRepository;

    public function __construct(
        \M2E\Kaufland\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory,
        \M2E\Kaufland\Model\Category\Dictionary\Repository $categoryDictionaryRepository,
        \M2E\Kaufland\Model\Category\Dictionary\Repository $dictionaryRepository
    ) {
        parent::__construct();

        $this->wizardManagerFactory = $wizardManagerFactory;
        $this->categoryDictionaryRepository = $categoryDictionaryRepository;
        $this->dictionaryRepository = $dictionaryRepository;
    }

    public function execute()
    {
        $id = $this->getWizardIdFromRequest();
        $manager = $this->wizardManagerFactory->createById($id);

        $selectedProduct = $this->getRequest()->getParam('products_ids');

        $wizardProduct = $manager->findProductById((int) $selectedProduct);
        $categoryId = $this->getWizardProductCategoryId($wizardProduct);

        /** @var \M2E\Kaufland\Block\Adminhtml\Category\CategoryChooser $chooserBlock */
        $chooserBlock = $this
            ->getLayout()
            ->createBlock(
                \M2E\Kaufland\Block\Adminhtml\Category\CategoryChooser::class,
                '',
                [
                    'listing' => $manager->getListing(),
                    'selectedCategory' => $categoryId ?? null
                ]
            );

        $this->setAjaxContent($chooserBlock->toHtml());

        return $this->getResult();
    }

    private function getWizardProductCategoryId(\M2E\Kaufland\Model\Listing\Wizard\Product $wizardProduct): ?int
    {
        $dictionaryId = $wizardProduct->getCategoryDictionaryId();
        if (empty($dictionaryId) || $this->dictionaryRepository->find($dictionaryId) === null) {
            return null;
        }

        return $this->dictionaryRepository->find($dictionaryId)->getCategoryId();
    }
}
