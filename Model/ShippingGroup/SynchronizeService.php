<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\ShippingGroup;

class SynchronizeService
{
    private Repository $repository;
    private \M2E\Kaufland\Model\Storefront\Repository $storefrontRepository;
    private \M2E\Kaufland\Model\ShippingGroupFactory $shippingGroupFactory;
    /** @var \M2E\Kaufland\Model\ShippingGroup\Get */
    private Get $getShippingGroup;

    public function __construct(
        \M2E\Kaufland\Model\ShippingGroupFactory $shippingGroupFactory,
        Repository $repository,
        \M2E\Kaufland\Model\Storefront\Repository $storefrontRepository,
        \M2E\Kaufland\Model\ShippingGroup\Get $getShippingGroup
    ) {
        $this->shippingGroupFactory = $shippingGroupFactory;
        $this->repository = $repository;
        $this->storefrontRepository = $storefrontRepository;
        $this->getShippingGroup = $getShippingGroup;
    }

    /**
     * @param \M2E\Kaufland\Model\Storefront $storefront
     * @param \M2E\Kaufland\Model\Kaufland\Connector\Account\ShippingGroup[] $shippingGroups
     *
     * @return void
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    public function sync(
        \M2E\Kaufland\Model\Account $account,
        array $shippingGroups
    ): void {

        /** @var \M2E\Kaufland\Model\ShippingGroup[] $exists */
        $exists = [];
        foreach ($account->getShippingGroups() as $shippingGroup) {
            $exists[$shippingGroup->getShippingGroupId()] = $shippingGroup;
        }

        foreach ($shippingGroups as $responseShippingGroup) {
            $storefront = $this->storefrontRepository->getByCode($responseShippingGroup->getStorefront());

            if (isset($exists[$responseShippingGroup->getShippingGroupId()])) {
                $exist = $exists[$responseShippingGroup->getShippingGroupId()];

                if (
                    $responseShippingGroup->getName() !== $exist->getName()
                    || $responseShippingGroup->isDefault() !== $exist->isDefault()
                    || $responseShippingGroup->getType() !== $exist->getType()
                    || $responseShippingGroup->getCurrency() !== $exist->getCurrency()
                    || $responseShippingGroup->getStorefront() !== $storefront->getStorefrontCode()
                    || $responseShippingGroup->getRegions() !== $exist->getRegions()
                ) {
                    $exist->setName($responseShippingGroup->getName())
                          ->setStorefrontId($storefront->getId())
                          ->setIsDefault($responseShippingGroup->isDefault())
                          ->setType($responseShippingGroup->getType())
                          ->setCurrency($responseShippingGroup->getCurrency())
                          ->setRegions($responseShippingGroup->getRegions());

                    $this->repository->save($exist);
                }

                continue;
            }

            $shippingGroup = $this->shippingGroupFactory->create();
            $shippingGroup->create(
                $account,
                $storefront,
                $responseShippingGroup->getShippingGroupId(),
                $responseShippingGroup->getName(),
                $responseShippingGroup->getType(),
                $responseShippingGroup->isDefault(),
                $responseShippingGroup->getCurrency(),
                $responseShippingGroup->getRegions()
            );

            $this->repository->create($shippingGroup);

            $exists[$shippingGroup->getShippingGroupId()] = $shippingGroup;
        }

        $account->setShippingGroups(array_values($exists));
    }

    public function updateShippingGroups(\M2E\Kaufland\Model\Account $account, \M2E\Kaufland\Model\Storefront $storefront)
    {
        $shippingGroups = $this->getShippingGroup->getShippingGroups($account, $storefront);
        $this->sync($account, $shippingGroups);
    }
}
