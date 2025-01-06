<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Product;

use M2E\Kaufland\Model\Product;

class UpdateFromChannel
{
    private \M2E\Kaufland\Model\Product\Repository $repository;
    private \M2E\Kaufland\Model\InstructionService $instructionService;
    private \M2E\Kaufland\Model\Listing\LogService $logService;
    private \M2E\Kaufland\Model\Product\UpdateFromChannel\ProcessorFactory $changesProcessorFactory;
    private int $logActionId;

    public function __construct(
        Repository $repository,
        \M2E\Kaufland\Model\InstructionService $instructionService,
        \M2E\Kaufland\Model\Listing\LogService $logService,
        \M2E\Kaufland\Model\Product\UpdateFromChannel\ProcessorFactory $changesProcessorFactory
    ) {
        $this->repository = $repository;
        $this->instructionService = $instructionService;
        $this->logService = $logService;
        $this->changesProcessorFactory = $changesProcessorFactory;
    }

    public function process(
        \M2E\Kaufland\Model\Listing\Other\KauflandProductCollection $channelProductCollection,
        \M2E\Kaufland\Model\Account $account,
        \M2E\Kaufland\Model\Storefront $storefront
    ): void {
        if ($channelProductCollection->empty()) {
            return;
        }

        $existed = $this->repository->findByKauflandOfferIds(
            $channelProductCollection->getOfferIds(),
            $account->getId(),
            $storefront->getId(),
        );

        foreach ($existed as $product) {
            $channelProduct = $channelProductCollection->get($product->getKauflandOfferId());

            $changesProcessor = $this->changesProcessorFactory->create($product, $channelProduct);

            $changeResult = $changesProcessor->processChanges();

            if ($changeResult->isChangedProduct()) {
                $this->repository->save($product);
            }

            $this->writeInstructions($changeResult->getInstructionsData());
            $this->writeLogs($product, $changeResult->getLogs());
        }
    }

    private function writeInstructions(array $instructionsData): void
    {
        if (empty($instructionsData)) {
            return;
        }

        $this->instructionService->createBatch($instructionsData);
    }

    /**
     * @param \M2E\Kaufland\Model\Product $product
     * @param \M2E\Kaufland\Model\Listing\Log\Record[] $records
     *
     * @return void
     */
    private function writeLogs(Product $product, array $records): void
    {
        if (empty($records)) {
            return;
        }

        foreach ($records as $record) {
            $this->logService->addRecordToProduct(
                $record,
                $product,
                \M2E\Core\Helper\Data::INITIATOR_EXTENSION,
                \M2E\Kaufland\Model\Listing\Log::ACTION_CHANNEL_CHANGE,
                $this->getLogActionId(),
            );
        }
    }

    private function getLogActionId(): int
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        return $this->logActionId ?? ($this->logActionId = $this->logService->getNextActionId());
    }
}
