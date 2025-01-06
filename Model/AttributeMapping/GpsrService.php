<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\AttributeMapping;

use M2E\Kaufland\Model\AttributeMapping\Gpsr\CategoryModifier;
use M2E\Kaufland\Model\AttributeMapping\Gpsr\Pair;
use M2E\Kaufland\Model\AttributeMapping\Gpsr\Provider;
use M2E\Kaufland\Model\AttributeMapping\Gpsr\Update;

class GpsrService
{
    public const MAPPING_TYPE = 'gpsr';

    /** @var \M2E\Kaufland\Model\AttributeMapping\Gpsr\Provider */
    private Provider $attributeProvider;
    /** @var \M2E\Kaufland\Model\AttributeMapping\Gpsr\Update */
    private Update $update;
    /** @var \M2E\Kaufland\Model\AttributeMapping\Gpsr\CategoryModifier */
    private CategoryModifier $categoryModifier;

    public function __construct(
        Gpsr\Provider $attributeProvider,
        Gpsr\Update $update,
        Gpsr\CategoryModifier $categoryModifier
    ) {
        $this->attributeProvider = $attributeProvider;
        $this->update = $update;
        $this->categoryModifier = $categoryModifier;
    }

    /**
     * @return Pair[]
     */
    public function getAll(): array
    {
        return $this->attributeProvider->getAll();
    }

    /**
     * @return Pair[]
     */
    public function getConfigured(): array
    {
        return $this->attributeProvider->getConfigured();
    }

    /**
     * @param Pair[] $attributesMapping
     *
     * @return int - processed (updated or created) count
     */
    public function save(array $attributesMapping): int
    {
        return $this->update->process($attributesMapping);
    }

    public function setToCategories(): void
    {
        $this->categoryModifier->process($this->getConfigured());
    }
}
