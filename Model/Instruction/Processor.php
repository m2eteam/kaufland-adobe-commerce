<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Instruction;

class Processor
{
    private \M2E\Kaufland\Model\ResourceModel\Product\CollectionFactory $listingProductCollectionFactory;
    private \M2E\Kaufland\Helper\Module\Exception $exceptionHelper;
    private \M2E\Kaufland\Model\Instruction\Handler\InputFactory $handlerInputFactory;
    private SynchronizationTemplate\Handler $synchronizationTemplateHandler;
    private \M2E\Kaufland\Model\Instruction\Repository $instructionRepository;
    /** @var \M2E\Kaufland\Model\Instruction\Processor\Config */
    private Processor\Config $processorConfig;
    private \M2E\Kaufland\Model\Product\Repository $productRepository;

    public function __construct(
        \M2E\Kaufland\Model\Instruction\Processor\Config $processorConfig,
        \M2E\Kaufland\Model\Instruction\Repository $instructionRepository,
        \M2E\Kaufland\Model\Product\Repository $productRepository,
        SynchronizationTemplate\Handler $synchronizationTemplateHandler,
        \M2E\Kaufland\Model\ResourceModel\Product\CollectionFactory $listingProductCollectionFactory,
        \M2E\Kaufland\Helper\Module\Exception $exceptionHelper,
        \M2E\Kaufland\Model\Instruction\Handler\InputFactory $handlerInputFactory
    ) {
        $this->synchronizationTemplateHandler = $synchronizationTemplateHandler;
        $this->listingProductCollectionFactory = $listingProductCollectionFactory;
        $this->exceptionHelper = $exceptionHelper;
        $this->handlerInputFactory = $handlerInputFactory;
        $this->instructionRepository = $instructionRepository;
        $this->processorConfig = $processorConfig;
        $this->productRepository = $productRepository;
    }

    public function process(): void
    {
        $this->deleteInstructionsOlderThenWeek();
        $this->deleteInstructionsWithoutListingProducts();

        $listingsProductsById = $this->loadProductsWithInstructions();
        if (empty($listingsProductsById)) {
            return;
        }

        $instructionsGroupedByListingProductId = $this->loadInstructions($listingsProductsById);
        if (empty($instructionsGroupedByListingProductId)) {
            return;
        }

        foreach ($instructionsGroupedByListingProductId as $listingProductId => $listingProductInstructions) {
            try {
                $handlerInput = $this->handlerInputFactory->create(
                    $listingsProductsById[$listingProductId],
                    $listingProductInstructions,
                );

                $this->synchronizationTemplateHandler->process($handlerInput);

                if ($handlerInput->getListingProduct()->isDeleted()) {
                    break;
                }
            } catch (\Throwable $exception) {
                $this->exceptionHelper->process($exception);
            }

            $this->instructionRepository->removeByIds(array_keys($listingProductInstructions));
        }
    }

    /**
     * @return \M2E\Kaufland\Model\Product[]
     * @throws \Exception
     */
    private function loadProductsWithInstructions(): array
    {
        $maxListingsProductsCount = $this->processorConfig->getMaxProductsForProcess();

        $ids = $this->instructionRepository->findProductsIdsByPriority($maxListingsProductsCount, null);

        $result = [];
        foreach ($this->productRepository->findByIds($ids) as $product) {
            $result[(int)$product->getId()] = $product;
        }

        return $result;
    }

    /**
     * @param \M2E\Kaufland\Model\Product[] $listingsProductsById
     *
     * @return \M2E\Kaufland\Model\Instruction[][]
     */
    private function loadInstructions(array $listingsProductsById): array
    {
        if (empty($listingsProductsById)) {
            return [];
        }

        $instructions = $this->instructionRepository->findByListingProducts(array_keys($listingsProductsById), null);

        $instructionsByListingsProducts = [];
        foreach ($instructions as $instruction) {
            $listingProduct = $listingsProductsById[$instruction->getListingProductId()];
            $instruction->setListingProduct($listingProduct);

            $instructionsByListingsProducts[$instruction->getListingProductId()][$instruction->getId()] = $instruction;
        }

        return $instructionsByListingsProducts;
    }

    private function deleteInstructionsWithoutListingProducts(): void
    {
        $this->instructionRepository->removeWithoutListingProduct();
    }

    private function deleteInstructionsOlderThenWeek(): void
    {
        $greaterThenDate = \M2E\Core\Helper\Date::createCurrentGmt();
        $greaterThenDate->modify('-7 day');

        $this->instructionRepository->removeOld($greaterThenDate);
    }
}
