<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Listing\Wizard;

class SearchChannelProductManager
{
    private const MAX_PRODUCT_COUNT_FOR_SEARCH = 20;

    private \M2E\Kaufland\Model\Product\Repository $productRepository;
    private \M2E\Kaufland\Model\Product\SearchChannelProductsService $searchProductService;
    private \M2E\Kaufland\Helper\Component\Kaufland\Configuration $configuration;
    private \M2E\Kaufland\Model\Listing\Wizard\Repository $wizardRepository;

    public function __construct(
        \M2E\Kaufland\Model\Product\Repository $productRepository,
        \M2E\Kaufland\Model\Product\SearchChannelProductsService $searchProductService,
        \M2E\Kaufland\Helper\Component\Kaufland\Configuration $configuration,
        \M2E\Kaufland\Model\Listing\Wizard\Repository $wizardRepository
    ) {
        $this->productRepository = $productRepository;
        $this->searchProductService = $searchProductService;
        $this->configuration = $configuration;
        $this->wizardRepository = $wizardRepository;
    }

    public function isAllFound(\M2E\Kaufland\Model\Listing\Wizard\Manager $manager): bool
    {
        $allProductsIds = $manager->getProductsIds();
        if (empty($allProductsIds)) {
            return true;
        }

        $searchStatistic = $this->productRepository->getStatisticForSearchChannelId(
            $manager->getWizardId(),
            $allProductsIds,
        );

        return empty($searchStatistic[\M2E\Kaufland\Model\Listing\Wizard\Product::SEARCH_STATUS_NONE]);
    }

    public function find(\M2E\Kaufland\Model\Listing\Wizard\Manager $manager): ?SearchChannelProductManager\FindResult
    {
        $allProductsIds = $manager->getProductsIds();
        if (empty($allProductsIds)) {
            return null;
        }

        $products = $manager->findProductsForSearchChannelId(self::MAX_PRODUCT_COUNT_FOR_SEARCH);
        if (empty($products)) {
            return null;
        }

        ['skip' => $skipProducts, 'search' => $groupByEanProducts] = $this->groupProductsForProcess($products);

        if (!empty($skipProducts)) {
            $this->processSkip($skipProducts);
        }

        if (empty($groupByEanProducts)) {
            return new \M2E\Kaufland\Model\Listing\Wizard\SearchChannelProductManager\FindResult(
                $this->isAllFound($manager),
                count($allProductsIds),
                self::MAX_PRODUCT_COUNT_FOR_SEARCH
            );
        }

        $channelProductIdsGroupByEan = $this->searchChannelProductIds(array_keys($groupByEanProducts), $manager->getListing());

        $skipProducts = [];
        foreach ($groupByEanProducts as $ean => $products) {
            if (!isset($channelProductIdsGroupByEan[$ean])) {
                array_push($skipProducts, ...$products);

                continue;
            }

            /** @var \M2E\Kaufland\Model\Listing\Wizard\Product $product */
            foreach ($products as $product) {
                $product->setKauflandProductId($channelProductIdsGroupByEan[$ean]['id']);
                $product->setCategoryTitle($channelProductIdsGroupByEan[$ean]['category_title']);
                $this->wizardRepository->saveProduct($product);
            }
        }

        $this->processSkip($skipProducts);

        return new \M2E\Kaufland\Model\Listing\Wizard\SearchChannelProductManager\FindResult(
            $this->isAllFound($manager),
            count($allProductsIds),
            self::MAX_PRODUCT_COUNT_FOR_SEARCH
        );
    }

    private function groupProductsForProcess(array $products): array
    {
        $productsForSearchByEan = [];
        $productsForSkip = [];

        $eanAttributeCode = $this->configuration->getIdentifierCodeCustomAttribute();
        /** @var \M2E\Kaufland\Model\Listing\Wizard\Product $product */
        foreach ($products as $product) {
            $magentoProduct = $product->getMagentoProduct();
            $ean = $magentoProduct->getAttributeValue($eanAttributeCode);
            if (empty($ean)) {
                $productsForSkip[] = $product;
                continue;
            }

            $productsForSearchByEan[$ean][] = $product;
        }

        return ['skip' => $productsForSkip, 'search' => $productsForSearchByEan];
    }

    /**
     * @param \M2E\Kaufland\Model\Listing\Wizard\Product[] $skipProducts
     *
     * @return void
     */
    private function processSkip(array $skipProducts): void
    {
        foreach ($skipProducts as $product) {
            $product->markProductIdIsSearched();

            $this->wizardRepository->saveProduct($product);
        }
    }

    private function searchChannelProductIds(array $eans, \M2E\Kaufland\Model\Listing $listing): array
    {
        $groupedProductIdsByEan = [];

        try {
            $channelProducts = $this->searchProductService->findByEans(
                $listing->getAccount(),
                $listing->getStorefront(),
                $eans,
            );

            foreach ($channelProducts as $channelProduct) {
                foreach ($channelProduct->getEans() as $ean) {
                    $data = [
                        'id' => $channelProduct->getId(),
                        'category_id' => $channelProduct->getCategoryId(),
                        'category_title' => $channelProduct->getCategoryTitle()
                    ];
                    $groupedProductIdsByEan[$ean] = $data;
                }
            }
        } catch (\Throwable $e) {
        }

        return $groupedProductIdsByEan;
    }
}
