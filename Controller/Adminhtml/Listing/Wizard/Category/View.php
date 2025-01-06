<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Listing\Wizard\Category;

use M2E\Kaufland\Block\Adminhtml\Listing\Wizard\SelectMode;
use M2E\Kaufland\Model\Listing\Wizard\StepDeclarationCollectionFactory;

class View extends \M2E\Kaufland\Controller\Adminhtml\Listing\Wizard\StepAbstract
{
    use \M2E\Kaufland\Controller\Adminhtml\Listing\Wizard\WizardTrait;

    private \M2E\Kaufland\Model\Category\Dictionary\Repository $dictionaryRepository;

    public function __construct(
        \M2E\Kaufland\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory,
        \M2E\Kaufland\Model\Listing\Ui\RuntimeStorage $uiListingRuntimeStorage,
        \M2E\Kaufland\Model\Listing\Wizard\Ui\RuntimeStorage $uiWizardRuntimeStorage,
        \M2E\Kaufland\Model\Category\Dictionary\Repository $dictionaryRepository
    ) {
        parent::__construct($wizardManagerFactory, $uiListingRuntimeStorage, $uiWizardRuntimeStorage);
        $this->dictionaryRepository = $dictionaryRepository;
    }

    protected function getStepNick(): string
    {
        return StepDeclarationCollectionFactory::STEP_GENERAL_SELECT_CATEGORY_STEP;
    }

    protected function process(\M2E\Kaufland\Model\Listing $listing)
    {
        if ($this->isNeedSkipStep()) {
            $this->getWizardManager()
                 ->completeStep(StepDeclarationCollectionFactory::STEP_GENERAL_SELECT_CATEGORY_STEP, true);

            return $this->redirectToIndex($this->getWizardManager()->getWizardId());
        }

        $manager = $this->getWizardManager();
        $selectedMode = $manager->getStepData(StepDeclarationCollectionFactory::STEP_GENERAL_SELECT_CATEGORY_MODE);

        $mode = $selectedMode['mode'];

        if ($mode === SelectMode::MODE_SAME) {
            return $this->stepSelectCategoryModeSame();
        }

        if ($mode === SelectMode::MODE_MANUALLY) {
            return $this->stepSelectCategoryModeManually();
        }

        throw new \LogicException('Category mode unknown.');
    }

    private function isNeedSkipStep(): bool
    {
        return !$this->getWizardManager()->isEnabledCreateNewProductMode() && !$this->getWizardManager()->isWizardTypeUnmanaged();
    }

    private function stepSelectCategoryModeSame()
    {
        $this->addContent(
            $this->getLayout()->createBlock(
                \M2E\Kaufland\Block\Adminhtml\Listing\Wizard\Category\Same::class,
            ),
        );

        $this->getResultPage()
             ->getConfig()
             ->getTitle()
             ->prepend(__('Set Category (All Products same Category)'));

        return $this->getResult();
    }

    private function stepSelectCategoryModeManually()
    {
        $manager = $this->getWizardManager();
        $wizardProducts = $manager->getNotProcessedProducts();
        $categoriesData = $this->getCategoriesData($wizardProducts);

        $block = $this
            ->getLayout()
            ->createBlock(
                \M2E\Kaufland\Block\Adminhtml\Listing\Wizard\Category\Manually::class,
                '',
                [
                    'categoriesData' => $categoriesData,
                ]
            );

        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->setAjaxContent($block->getChildBlock('grid')->toHtml());

            return $this->getResult();
        }

        $this->addContent($block);

        $this->getResultPage()->getConfig()->getTitle()->prepend(
            __('Set Category (Manually for each Product)')
        );

        return $this->getResult();
    }

    /**
     * @param \M2E\Kaufland\Model\Listing\Wizard\Product[] $wizardProduct
     *
     * @return array
     */
    private function getCategoriesData(array $wizardProduct): array
    {
        $categoriesData = [];
        foreach ($wizardProduct as $product) {
            $dictionaryId = $product->getCategoryDictionaryId();

            if (empty($dictionaryId) || $this->dictionaryRepository->find($dictionaryId) === null) {
                $categoriesData[$product->getMagentoProductId()] = [
                    'products_id' => $product->getMagentoProductId(),
                ];
            } else {
                $categoryDictionary = $this->dictionaryRepository->get($dictionaryId);

                $categoriesData[$product->getMagentoProductId()] = [
                    'value' => $categoryDictionary->getCategoryId(),
                    'path' => $categoryDictionary->getPath(),
                    'products_id' => $product->getMagentoProductId(),
                ];
            }
        }

        return $categoriesData;
    }
}
