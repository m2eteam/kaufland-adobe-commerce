<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\ExternalChange;

use M2E\Kaufland\Model\Product;

class Processor
{
    private Repository $externalChangesRepository;
    private \M2E\Kaufland\Model\ExternalChangeFactory $externalChangeFactory;
    private \M2E\Kaufland\Model\Product\Repository $productRepository;
    private \M2E\Kaufland\Model\Listing\Other\Repository $otherRepository;
    private \M2E\Kaufland\Model\Listing\Other\DeleteService $unmanagedProductDeleteService;
    private \M2E\Kaufland\Model\InstructionService $instructionService;
    private \M2E\Kaufland\Model\Listing\LogService $logService;

    private int $logActionId;

    public function __construct(
        Repository $externalChangeRepository,
        \M2E\Kaufland\Model\ExternalChangeFactory $externalChangeFactory,
        \M2E\Kaufland\Model\Product\Repository $productRepository,
        \M2E\Kaufland\Model\Listing\Other\Repository $otherRepository,
        \M2E\Kaufland\Model\Listing\Other\DeleteService $unmanagedProductDeleteService,
        \M2E\Kaufland\Model\InstructionService $instructionService,
        \M2E\Kaufland\Model\Listing\LogService $logService
    ) {
        $this->externalChangesRepository = $externalChangeRepository;
        $this->externalChangeFactory = $externalChangeFactory;
        $this->productRepository = $productRepository;
        $this->otherRepository = $otherRepository;
        $this->unmanagedProductDeleteService = $unmanagedProductDeleteService;
        $this->instructionService = $instructionService;
        $this->logService = $logService;
    }

    public function processReceivedProducts(
        \M2E\Kaufland\Model\Account $account,
        \M2E\Kaufland\Model\Storefront $storefront,
        \M2E\Kaufland\Model\Listing\Other\KauflandProductCollection $productCollection
    ): void {
        foreach ($productCollection->getAll() as $item) {
            $externalChange = $this->externalChangeFactory->create();
            $externalChange->init(
                $account,
                $storefront,
                $item->getOfferId(),
                $item->getUnitId(),
            );

            $this->externalChangesRepository->create($externalChange);
        }
    }

    public function processDeletedProducts(
        \M2E\Kaufland\Model\Account $account,
        \M2E\Kaufland\Model\Storefront $storefront,
        \DateTime $inventorySyncProcessingStartDate
    ): void {
        $this->processNotReceivedProducts($account, $storefront, $inventorySyncProcessingStartDate);
        $this->removeNotReceivedOtherListings($account, $storefront);

        $this->externalChangesRepository
            ->removeAllByAccountAndStorefront($account->getId(), $storefront->getId());
    }

    private function processNotReceivedProducts(
        \M2E\Kaufland\Model\Account $account,
        \M2E\Kaufland\Model\Storefront $storefront,
        \DateTime $inventorySyncProcessingStartDate
    ): void {
        $removedProducts = $this->productRepository->findRemovedFromChannel(
            $account->getId(),
            $storefront->getId(),
            $inventorySyncProcessingStartDate
        );

        foreach ($removedProducts as $product) {
            $product->setStatusNotListed(\M2E\Kaufland\Model\Product::STATUS_CHANGER_COMPONENT);

            $this->productRepository->save($product);

            $this->logService->addRecordToProduct(
                \M2E\Kaufland\Model\Listing\Log\Record::createSuccess(
                    (string)__('Product was deleted and is no longer available on the channel'),
                ),
                $product,
                \M2E\Core\Helper\Data::INITIATOR_EXTENSION,
                \M2E\Kaufland\Model\Listing\Log::ACTION_CHANNEL_CHANGE,
                $this->getLogActionId(),
            );

            $this->instructionService->create(
                (int)$product->getId(),
                \M2E\Kaufland\Model\Product::INSTRUCTION_TYPE_CHANNEL_STATUS_CHANGED,
                'channel_changes_synchronization',
                80,
            );
        }
    }

    private function removeNotReceivedOtherListings(
        \M2E\Kaufland\Model\Account $account,
        \M2E\Kaufland\Model\Storefront $storefront
    ): void {
        $otherListings = $this->otherRepository->findRemovedFromChannel($account->getId(), $storefront->getId());

        foreach ($otherListings as $other) {
            $this->unmanagedProductDeleteService->process($other);
        }
    }

    private function getLogActionId(): int
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        return $this->logActionId ?? ($this->logActionId = $this->logService->getNextActionId());
    }
}
