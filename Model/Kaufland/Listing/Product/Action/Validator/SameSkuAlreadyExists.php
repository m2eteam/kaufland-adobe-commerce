<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Validator;

class SameSkuAlreadyExists implements \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Validator\ValidatorInterface
{
    private \M2E\Kaufland\Model\Listing\Other\Repository $otherRepository;
    private \M2E\Kaufland\Model\Product\Repository $productRepository;

    public function __construct(
        \M2E\Kaufland\Model\Listing\Other\Repository $otherRepository,
        \M2E\Kaufland\Model\Product\Repository $productRepository
    ) {
        $this->otherRepository = $otherRepository;
        $this->productRepository = $productRepository;
    }

    public function validate(\M2E\Kaufland\Model\Product $product): ?string
    {
        $offerId = $product->getKauflandOfferId();
        if ($offerId === '') {
            return null;
        }

        $existUnmanagedProduct = $this->otherRepository->findByOfferIds(
            [$offerId],
            $product->getAccount()->getId(),
            $product->getListing()->getStorefrontId(),
        );

        if (!empty($existUnmanagedProduct)) {
            return (string)__(
                'Product with the same SKU already exists in Unmanaged Items.
                 Once the Item is mapped to a Magento Product, it can be moved to an M2E Listing.'
            );
        }

        $existListProducts = $this->productRepository->findByKauflandOfferIds(
            [$offerId],
            $product->getAccount()->getId(),
            $product->getListing()->getStorefrontId(),
        );

        if (!empty($existListProducts)) {
            $existListProduct = reset($existListProducts);
            if ($existListProduct->getId() !== $product->getId()) {
                return (string)__(
                    'Product with the same SKU already exists in your %listing_title Listing with the same Kaufland Offer ID.',
                    [
                        'listing_title' => $existListProduct->getListing()->getTitle(),
                    ]
                );
            }
        }

        return null;
    }
}
