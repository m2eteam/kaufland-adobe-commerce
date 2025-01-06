<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Listing\InventorySync\Processing;

class ResultHandler implements \M2E\Kaufland\Model\Processing\PartialResultHandlerInterface
{
    public const NICK = 'listing_inventory_sync';

    private \M2E\Kaufland\Model\Account\Repository $accountRepository;

    private \M2E\Kaufland\Model\Account $account;
    private \M2E\Kaufland\Model\Storefront\Repository $storefrontRepository;
    private \M2E\Kaufland\Model\Storefront $storefront;
    private \M2E\Kaufland\Model\Listing\InventorySync\AccountLockManager $accountLockManager;
    private \M2E\Kaufland\Model\Listing\Other\UpdaterFactory $listingOtherUpdaterFactory;
    private \M2E\Kaufland\Model\Listing\Other\Updater\ServerToKauflandProductConverterFactory $otherConverterFactory;
    private \M2E\Kaufland\Model\Product\UpdateFromChannel $productUpdateFromChannelProcessor;
    private \M2E\Kaufland\Model\ExternalChange\Processor $externalChangeProcessor;

    private \DateTime $fromDate;

    public function __construct(
        \M2E\Kaufland\Model\Account\Repository $accountRepository,
        \M2E\Kaufland\Model\Storefront\Repository $storefrontRepository,
        \M2E\Kaufland\Model\Listing\InventorySync\AccountLockManager $accountLockManager,
        \M2E\Kaufland\Model\Listing\Other\UpdaterFactory $listingOtherUpdaterFactory,
        \M2E\Kaufland\Model\Listing\Other\Updater\ServerToKauflandProductConverterFactory $otherConverterFactory,
        \M2E\Kaufland\Model\Product\UpdateFromChannel $productUpdateFromChannelProcessor,
        \M2E\Kaufland\Model\ExternalChange\Processor $externalChangeProcessor
    ) {
        $this->accountRepository = $accountRepository;
        $this->storefrontRepository = $storefrontRepository;
        $this->accountLockManager = $accountLockManager;
        $this->listingOtherUpdaterFactory = $listingOtherUpdaterFactory;
        $this->otherConverterFactory = $otherConverterFactory;
        $this->productUpdateFromChannelProcessor = $productUpdateFromChannelProcessor;
        $this->externalChangeProcessor = $externalChangeProcessor;
    }

    public function initialize(array $params): void
    {
        if (!isset($params['account_id'])) {
            throw new \M2E\Kaufland\Model\Exception\Logic('Processing params is not valid.');
        }

        $account = $this->accountRepository->find($params['account_id']);
        if ($account === null) {
            throw new \M2E\Kaufland\Model\Exception('Account not found');
        }

        $this->account = $account;

        $storefront = null;
        foreach ($this->account->getStorefronts() as $accountStorefront) {
            if ($accountStorefront->getStorefrontCode() === $params['storefront']) {
                $storefront = $accountStorefront;
                break;
            }
        }

        if ($storefront === null) {
            throw new \M2E\Kaufland\Model\Exception('Storefront not found');
        }

        $this->storefront = $storefront;

        if (isset($params['current_date'])) {
            $this->fromDate = \M2E\Core\Helper\Date::createDateGmt($params['current_date']);
        }
    }

    public function processPartialResult(array $partialData): void
    {
        $itemConverter = $this->otherConverterFactory->create($this->account, $this->storefront);
        $itemsCollection = $itemConverter->convert($partialData);

        $this->externalChangeProcessor->processReceivedProducts($this->account, $this->storefront, $itemsCollection);

        $existInListingCollection = $this->listingOtherUpdaterFactory
            ->create($this->account, $this->storefront)
            ->process($itemsCollection);
        if ($existInListingCollection === null) {
            return;
        }

        $this->productUpdateFromChannelProcessor
            ->process($existInListingCollection, $this->account, $this->storefront);
    }

    public function processSuccess(array $resultData, array $messages): void
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->fromDate)) {
            $this->storefront->setInventoryLastSyncDate(clone $this->fromDate);

            $this->storefrontRepository->save($this->storefront);
            $inventorySyncProcessingStartDate = $this->fromDate;
        } else {
            $inventorySyncProcessingStartDate = \M2E\Core\Helper\Date::createCurrentGmt();
        }

        $this->externalChangeProcessor
            ->processDeletedProducts($this->account, $this->storefront, $inventorySyncProcessingStartDate);
    }

    public function processExpire(): void
    {
        // do nothing
    }

    public function clearLock(\M2E\Kaufland\Model\Processing\LockManager $lockManager): void
    {
        $lockManager->delete(\M2E\Kaufland\Model\Storefront::LOCK_NICK, $this->storefront->getId());
        $this->accountLockManager->remove($this->storefront);
    }
}
