<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Category\Dictionary;

use M2E\Kaufland\Model\Category\Dictionary;

class Manager
{
    private \M2E\Kaufland\Model\Category\Dictionary\Repository $dictionaryRepository;
    private \M2E\Kaufland\Model\Storefront\Repository $storefrontRepository;
    private \M2E\Kaufland\Model\Category\Dictionary\CreateService $createService;

    public function __construct(
        \M2E\Kaufland\Model\Category\Dictionary\Repository $dictionaryRepository,
        \M2E\Kaufland\Model\Storefront\Repository $storefrontRepository,
        \M2E\Kaufland\Model\Category\Dictionary\CreateService $createService
    ) {
        $this->dictionaryRepository = $dictionaryRepository;
        $this->storefrontRepository = $storefrontRepository;
        $this->createService = $createService;
    }

    /**
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    public function getOrCreateDictionary(int $storefrontId, int $categoryId): Dictionary
    {
        $entity = $this->dictionaryRepository->findByStorefrontAndCategoryId($storefrontId, $categoryId);
        if ($entity !== null) {
            return $entity;
        }

        $storefront = $this->storefrontRepository->find($storefrontId);
        if ($storefront === null) {
            throw new \M2E\Kaufland\Model\Exception\Logic(
                sprintf('Not found storefront by id [%d]', $storefrontId)
            );
        }

        return $this->createService->create($storefront, $categoryId);
    }
}
