<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Listing\Other;

class UpdaterFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(
        \M2E\Kaufland\Model\Account $account,
        \M2E\Kaufland\Model\Storefront $storefront
    ): \M2E\Kaufland\Model\Listing\Other\Updater {
        return $this->objectManager->create(
            \M2E\Kaufland\Model\Listing\Other\Updater::class,
            [
                'account' => $account,
                'storefront' => $storefront,
            ],
        );
    }
}
