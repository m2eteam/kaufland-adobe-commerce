<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Listing\Wizard;

class CompleteProcessor
{
    private \M2E\Kaufland\Model\Listing\AddProductsService $addProductsService;
    private \M2E\Kaufland\Model\Category\Dictionary\Repository $categoryDictionary;
    /** @var \M2E\Kaufland\Model\Listing\Wizard\Repository */
    private Repository $wizardRepository;
    private \M2E\Kaufland\Model\Listing\Other\Repository $listingOtherRepository;

    /**@var array<string, \M2E\Kaufland\Model\Category\Dictionary> */
    private array $categoryTemplateCache = [];

    public function __construct(
        Repository $wizardRepository,
        \M2E\Kaufland\Model\Listing\AddProductsService $addProductsService,
        \M2E\Kaufland\Model\Category\Dictionary\Repository $categoryDictionary,
        \M2E\Kaufland\Model\Listing\Other\Repository $listingOtherRepository
    ) {
        $this->addProductsService = $addProductsService;
        $this->categoryDictionary = $categoryDictionary;
        $this->wizardRepository = $wizardRepository;
        $this->listingOtherRepository = $listingOtherRepository;
    }

    public function process(Manager $wizardManager): array
    {
        $listing = $wizardManager->getListing();

        if (!$wizardManager->isEnabledCreateNewProductMode()) {
            $this->markAsProcessNotValidProducts($wizardManager);
        }

        $processedWizardProductIds = [];
        $listingProducts = [];
        foreach ($wizardManager->getNotProcessedProducts() as $wizardProduct) {
            $listingProduct = null;

            $processedWizardProductIds[] = $wizardProduct->getId();

            $categoryTemplate = null;
            if ($wizardProduct->getCategoryDictionaryId() !== null) {
                $categoryTemplate = $this->findCategoryTemplate($wizardProduct->getCategoryDictionaryId());
            }

            if ($wizardManager->isWizardTypeGeneral()) {
                $kauflandProductId = null;
                if ($wizardProduct->getKauflandProductId()) {
                    $kauflandProductId = $wizardProduct->getKauflandProductId();
                }

                $listingProduct = $this->addProductsService
                    ->addProduct(
                        $listing,
                        $wizardProduct->getMagentoProductId(),
                        $categoryTemplate,
                        $kauflandProductId,
                        \M2E\Core\Helper\Data::INITIATOR_USER,
                    );
            } elseif ($wizardManager->isWizardTypeUnmanaged()) {
                $unmanagedProduct = $this->listingOtherRepository->findById($wizardProduct->getUnmanagedProductId());
                if ($unmanagedProduct === null) {
                    continue;
                }

                if (!$unmanagedProduct->getMagentoProduct()->exists()) {
                    continue;
                }

                $listingProduct = $this->addProductsService
                    ->addFromUnmanaged(
                        $listing,
                        $unmanagedProduct,
                        $categoryTemplate,
                        \M2E\Core\Helper\Data::INITIATOR_USER,
                    );

                if ($listingProduct === null) {
                    continue;
                }
            }

            $listingProducts[] = $listingProduct;

            if (count($processedWizardProductIds) % 100 === 0) {
                $wizardManager->markProductsAsProcessed($processedWizardProductIds);
                $processedWizardProductIds = [];
            }
        }

        if (!empty($processedWizardProductIds)) {
            $wizardManager->markProductsAsProcessed($processedWizardProductIds);
        }

        return $listingProducts;
    }

    private function markAsProcessNotValidProducts(Manager $wizardManager): void
    {
        $ids = $this->wizardRepository->getNotValidWizardProductsIds($wizardManager->getWizardId());
        if (!empty($ids)) {
            $this->wizardRepository->markProductsAsCompleted($wizardManager->getWizard(), $ids);
        }
    }

    private function findCategoryTemplate(
        int $categoryDictionaryId
    ): ?\M2E\Kaufland\Model\Category\Dictionary {
        $cacheKey = 'id-' . $categoryDictionaryId;

        if (!array_key_exists($cacheKey, $this->categoryTemplateCache)) {
            $categoryTemplate = $this->categoryDictionary->find($categoryDictionaryId);

            $this->categoryTemplateCache[$cacheKey] = $categoryTemplate;
        }

        return $this->categoryTemplateCache[$cacheKey];
    }
}
