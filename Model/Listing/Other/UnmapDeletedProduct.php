<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Listing\Other;

class UnmapDeletedProduct
{
    private \M2E\Kaufland\Model\Listing\Other\Repository $otherRepository;
    public function __construct(
        \M2E\Kaufland\Model\Listing\Other\Repository $otherRepository
    ) {
        $this->otherRepository = $otherRepository;
    }

    /**
     * @param \Magento\Catalog\Model\Product|int $magentoProduct
     *
     * @return void
     */
    public function process($magentoProduct): void
    {
        $magentoProductId = $magentoProduct instanceof \Magento\Catalog\Model\Product
            ? (int)$magentoProduct->getId()
            : (int)$magentoProduct;

        $unmanagedProducts = $this->otherRepository->findByMagentoProductId($magentoProductId);
        foreach ($unmanagedProducts as $unmanagedProduct) {
            $unmanagedProduct->unmapMagentoProduct();
            $this->otherRepository->save($unmanagedProduct);
        }
    }
}
