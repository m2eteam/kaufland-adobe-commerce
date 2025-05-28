<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Product\Action\Validator;

class SameSkuAlreadyExists implements \M2E\Kaufland\Model\Product\Action\Validator\ValidatorInterface
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

    public function validate(\M2E\Kaufland\Model\Product $product): ?ValidatorMessage
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
            return new ValidatorMessage(
                (string)__(
                    'Product with the same SKU already exists in Unmanaged Items.
                 Once the Item is mapped to a Magento Product, it can be moved to an M2E Listing.'
                ),
                \M2E\Kaufland\Model\Tag\ValidatorIssues::ERROR_DUPLICATE_SKU_IN_UNMANAGED
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
                return new ValidatorMessage(
                    (string)__(
                        'Product with the same SKU already exists in your %listing_title Listing with the same %channel_title Offer ID.',
                        [
                            'listing_title' => $existListProduct->getListing()->getTitle(),
                            'channel_title' => \M2E\Kaufland\Helper\Module::getChannelTitle(),
                        ]
                    ),
                    \M2E\Kaufland\Model\Tag\ValidatorIssues::ERROR_DUPLICATE_SKU_IN_LISTING
                );
            }
        }

        return null;
    }
}
