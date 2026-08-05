<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Template;

class RefreshShippingGroups extends \M2E\Kaufland\Controller\Adminhtml\Kaufland\AbstractTemplate
{
    private \M2E\Kaufland\Model\Account\Repository $accountRepository;
    private \M2E\Kaufland\Model\ShippingGroup\SynchronizeService $shippingGroupSynchronizeService;
    private \M2E\Kaufland\Model\ShippingGroup\Repository $shippingGroupRepository;
    private \M2E\Kaufland\Model\Storefront\Repository $storefrontRepository;

    public function __construct(
        \M2E\Kaufland\Model\Account\Repository $accountRepository,
        \M2E\Kaufland\Model\ShippingGroup\SynchronizeService $shippingGroupSynchronizeService,
        \M2E\Kaufland\Model\Template\Manager $templateManager,
        \M2E\Kaufland\Model\ShippingGroup\Repository $shippingGroupRepository,
        \M2E\Kaufland\Model\Storefront\Repository $storefrontRepository
    ) {
        parent::__construct($templateManager);
        $this->accountRepository = $accountRepository;
        $this->shippingGroupSynchronizeService = $shippingGroupSynchronizeService;
        $this->shippingGroupRepository = $shippingGroupRepository;
        $this->storefrontRepository = $storefrontRepository;
    }

    public function execute()
    {
        $account = $this->getAccountFromRequest();
        $storefront = $this->getStorefrontFromRequest();

        $this->shippingGroupSynchronizeService->updateShippingGroups($account, $storefront);

        $shippingGroups = $this->shippingGroupRepository->findByStorefrontId($storefront->getId());

        $arrayShippingGroups = [];
          /** @var \M2E\Kaufland\Model\ShippingGroup $shippingGroup */
        foreach ($shippingGroups as $shippingGroup) {
            $arrayShippingGroups[] = [
                'shipping_group_id' => $shippingGroup->getId(),
                'name' => $shippingGroup->getName(),
            ];
        }

        $this->setJsonContent($arrayShippingGroups);

        return $this->getResult();
    }

    private function getAccountFromRequest(): \M2E\Kaufland\Model\Account
    {
        $accountId = (int)$this->getRequest()->getParam('account_id');

        return $this->accountRepository->get($accountId);
    }

    private function getStorefrontFromRequest(): \M2E\Kaufland\Model\Storefront
    {
        $storefrontId = (int)$this->getRequest()->getParam('storefront_id');

        return $this->storefrontRepository->get($storefrontId);
    }
}
