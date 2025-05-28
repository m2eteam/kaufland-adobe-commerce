<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Storefront;

class UpdateService
{
    private \M2E\Kaufland\Model\StorefrontFactory $storefrontFactory;
    private \M2E\Kaufland\Model\Storefront\Repository $storefrontRepository;

    public function __construct(
        \M2E\Kaufland\Model\StorefrontFactory $storefrontFactory,
        \M2E\Kaufland\Model\Storefront\Repository $storefrontRepository
    ) {
        $this->storefrontFactory = $storefrontFactory;
        $this->storefrontRepository = $storefrontRepository;
    }

    /**
     * @param \M2E\Kaufland\Model\Account $account
     * @param \M2E\Kaufland\Model\Channel\Storefront\Item[] $kauflandStorefronts
     *
     * @return void
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    public function process(\M2E\Kaufland\Model\Account $account, array $kauflandStorefronts): void
    {
        $existStorefronts = [];
        foreach ($account->getStorefronts() as $storefront) {
            $existStorefronts[$storefront->getStorefrontCode()] = $storefront;
        }

        foreach ($kauflandStorefronts as $responseStorefronts) {
            if (isset($existStorefronts[$responseStorefronts->getStorefrontCode()])) {
                continue;
            }

            $storefront = $this->storefrontFactory->create();
            $storefront->init(
                $account,
                $responseStorefronts->getStorefrontCode()
            );
            $this->storefrontRepository->create($storefront);

            $existStorefronts[$storefront->getStorefrontCode()] = $storefront;
        }

        $account->setStorefronts(array_values($existStorefronts));
    }
}
