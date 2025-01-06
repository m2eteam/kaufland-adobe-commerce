<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Listing\Other\Updater;

class ServerToKauflandProductConverterFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(
        \M2E\Kaufland\Model\Account $account,
        \M2E\Kaufland\Model\Storefront $storefront
    ): ServerToKauflandProductConverter {
        return $this->objectManager->create(
            ServerToKauflandProductConverter::class,
            [
                'account' => $account,
                'storefront' => $storefront,
            ],
        );
    }
}
