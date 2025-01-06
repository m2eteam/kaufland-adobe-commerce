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
        \M2E\Kaufland\Model\Kaufland\Template\Manager $templateManager,
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
        $accounts = $this->accountRepository->getAll();
        $storefrontId = (int)$this->getRequest()->getParam('storefront_id');

        if ($storefrontId) {
            $storefront = $this->storefrontRepository->get($storefrontId);
            foreach ($accounts as $account) {
                $this->shippingGroupSynchronizeService->updateShippingGroups($account, $storefront);
            }

            $shippingGroups = $this->shippingGroupRepository->findByStorefrontId($storefrontId);
        } else {
            $storefronts = $this->storefrontRepository->getAll();

            foreach ($accounts as $account) {
                foreach ($storefronts as $storefront) {
                    $this->shippingGroupSynchronizeService->updateShippingGroups($account, $storefront);
                }
            }

            $shippingGroups = $this->shippingGroupRepository->getAll();
        }

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
}
