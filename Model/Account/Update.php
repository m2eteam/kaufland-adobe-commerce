<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Account;

use M2E\Kaufland\Model\ResourceModel\Account as AccountResource;

class Update
{
    private \M2E\Kaufland\Model\Warehouse\SynchronizeService $warehouseSynchronizeService;
    private \M2E\Kaufland\Model\ShippingGroup\SynchronizeService $shippingGroupSynchronizeService;
    private \M2E\Kaufland\Model\Channel\Account\Update\Processor $updateProcessor;
    private \M2E\Kaufland\Model\Storefront\UpdateService $storefrontUpdateService;
    private \M2E\Kaufland\Model\Channel\Storefront\Processor $storefrontProcessor;
    private \M2E\Kaufland\Helper\Data\Cache\Permanent $cache;
    /** @var \M2E\Kaufland\Model\Account\Repository */
    private Repository $accountRepository;

    public function __construct(
        \M2E\Kaufland\Model\Channel\Storefront\Processor $storefrontProcessor,
        \M2E\Kaufland\Model\Channel\Account\Update\Processor $updateProcessor,
        \M2E\Kaufland\Model\Storefront\UpdateService $storefrontUpdateService,
        \M2E\Kaufland\Model\Account\Repository $accountRepository,
        \M2E\Kaufland\Model\Warehouse\SynchronizeService $warehouseSynchronizeService,
        \M2E\Kaufland\Model\ShippingGroup\SynchronizeService $shippingGroupSynchronizeService,
        \M2E\Kaufland\Helper\Data\Cache\Permanent $cache
    ) {
        $this->updateProcessor = $updateProcessor;
        $this->storefrontUpdateService = $storefrontUpdateService;
        $this->warehouseSynchronizeService = $warehouseSynchronizeService;
        $this->shippingGroupSynchronizeService = $shippingGroupSynchronizeService;
        $this->cache = $cache;
        $this->accountRepository = $accountRepository;
        $this->storefrontProcessor = $storefrontProcessor;
    }

    public function updateSettings(
        \M2E\Kaufland\Model\Account $account,
        string $title,
        \M2E\Kaufland\Model\Account\Settings\UnmanagedListings $unmanagedListingsSettings,
        \M2E\Kaufland\Model\Account\Settings\Order $orderSettings,
        \M2E\Kaufland\Model\Account\Settings\InvoicesAndShipment $invoicesAndShipmentSettings
    ): void {
        $account->setTitle($title)
                ->setUnmanagedListingSettings($unmanagedListingsSettings)
                ->setOrdersSettings($orderSettings)
                ->setInvoiceAndShipmentSettings($invoicesAndShipmentSettings);

        $this->accountRepository->save($account);
    }

    public function updateCredentials(\M2E\Kaufland\Model\Account $account, string $clientKey, string $secretKey): void
    {
        $title = $account->getTitle();
        $response = $this->updateOnServer($account, $title, $clientKey, $secretKey);

        if (!$account->hasIdentifier()) {
            $account->setData(AccountResource::COLUMN_IDENTIFIER, $response->getIdentifier());
            $this->accountRepository->save($account);
        }

        $this->storefrontUpdateService->process($account, $response->getStorefronts());
        $this->warehouseSynchronizeService->sync($account, $response->getWarehouses());
        $this->shippingGroupSynchronizeService->sync($account, $response->getShippingGroups());
    }

    public function updateOnServer(
        \M2E\Kaufland\Model\Account $account,
        string $title,
        string $clientKey,
        string $secretKey
    ): \M2E\Kaufland\Model\Channel\Connector\Account\Update\Response {
        return $this->updateProcessor->process($account, $title, $clientKey, $secretKey);
    }

    public function updateStorefronts(\M2E\Kaufland\Model\Account $account): void
    {
        $response = $this->storefrontProcessor->process($account);
        $this->storefrontUpdateService->process($account, $response->getStorefronts());
    }
}
