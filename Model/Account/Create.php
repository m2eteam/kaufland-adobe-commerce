<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Account;

class Create
{
    private \M2E\Kaufland\Model\Channel\Account\Add\Processor $addProcessor;
    private Repository $accountRepository;
    private \M2E\Kaufland\Model\AccountFactory $accountFactory;
    private \M2E\Core\Helper\Magento\Store $storeHelper;
    private \M2E\Kaufland\Model\Storefront\UpdateService $storefrontUpdateService;
    private \M2E\Kaufland\Model\Warehouse\SynchronizeService $warehouseSynchronizeService;
    private \M2E\Kaufland\Model\ShippingGroup\SynchronizeService $shippingGroupSynchronizeService;

    public function __construct(
        \M2E\Kaufland\Model\AccountFactory $accountFactory,
        \M2E\Kaufland\Model\Channel\Account\Add\Processor $addProcessor,
        \M2E\Kaufland\Model\Storefront\UpdateService $storefrontUpdateService,
        \M2E\Kaufland\Model\Warehouse\SynchronizeService $warehouseSynchronizeService,
        \M2E\Kaufland\Model\ShippingGroup\SynchronizeService $shippingGroupSynchronizeService,
        \M2E\Kaufland\Model\Account\Repository $accountRepository,
        \M2E\Core\Helper\Magento\Store $storeHelper
    ) {
        $this->addProcessor = $addProcessor;
        $this->accountRepository = $accountRepository;
        $this->accountFactory = $accountFactory;
        $this->storeHelper = $storeHelper;
        $this->storefrontUpdateService = $storefrontUpdateService;
        $this->warehouseSynchronizeService = $warehouseSynchronizeService;
        $this->shippingGroupSynchronizeService = $shippingGroupSynchronizeService;
    }

    public function create(string $title, string $privateKey, string $secretKey): \M2E\Kaufland\Model\Account
    {
        $serverResponse = $this->createOnServer($title, $privateKey, $secretKey);

        if ($this->isExistWithIdentifier($serverResponse->getIdentifier())) {
            throw new \M2E\Kaufland\Model\Exception\Logic(
                'An account with these credentials already exists.'
            );
        }

        $account = $this->accountFactory->create();

        $account->init(
            $title,
            $serverResponse->getHash(),
            $serverResponse->getIdentifier(),
            new \M2E\Kaufland\Model\Account\Settings\UnmanagedListings(),
            (new \M2E\Kaufland\Model\Account\Settings\Order())
                ->createWith(
                    ['listing_other' => ['store_id' => $this->storeHelper->getDefaultStoreId()]],
                ),
            new \M2E\Kaufland\Model\Account\Settings\InvoicesAndShipment(),
        );

        $this->accountRepository->create($account);
        $this->storefrontUpdateService->process($account, $serverResponse->getStorefronts());
        $this->warehouseSynchronizeService->sync($account, $serverResponse->getWarehouses());
        $this->shippingGroupSynchronizeService->sync($account, $serverResponse->getShippingGroups());

        return $account;
    }

    private function createOnServer(
        string $title,
        string $privateKey,
        string $secretKey
    ): \M2E\Kaufland\Model\Channel\Connector\Account\Add\Response {
        return $this->addProcessor->process($title, $privateKey, $secretKey);
    }

    /**
     * @param string $identifier
     *
     * @return bool
     */
    private function isExistWithIdentifier(string $identifier): bool
    {
        $accounts = $this->accountRepository->findByIdentifier($identifier);

        return !empty($accounts);
    }
}
