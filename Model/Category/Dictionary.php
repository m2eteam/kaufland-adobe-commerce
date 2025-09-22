<?php

namespace M2E\Kaufland\Model\Category;

use M2E\Kaufland\Model\ResourceModel\Category\Dictionary as DictionaryResource;
use M2E\Kaufland\Model\Category\Attribute;
use M2E\Kaufland\Model\Category\Dictionary\AbstractAttribute as DictionaryAbstractAttribute;

class Dictionary extends \M2E\Kaufland\Model\ActiveRecord\AbstractModel
{
    public const DRAFT_STATE = 1;
    public const SAVED_STATE = 2;
    public const VALUE_MODE_NONE = 0;
    public const VALUE_MODE_CUSTOM_ATTRIBUTE = 3;
    public const RENDER_TYPE_SELECT_MULTIPLE_OR_TEXT = 'select_multiple_or_text';
    public const CATEGORY_MODE_ATTRIBUTE = 2;
    public const VALUE_MODE_KAUFLAND_RECOMMENDED = 1;
    public const RENDER_TYPE_SELECT_ONE = 'select_one';
    public const MODE_ITEM_SPECIFICS = 1;
    public const CATEGORY_MODE_NONE = 0;
    public const VALUE_MODE_CUSTOM_VALUE = 2;
    public const RENDER_TYPE_SELECT_MULTIPLE = 'select_multiple';
    public const VALUE_MODE_CUSTOM_LABEL_ATTRIBUTE = 4;
    public const RENDER_TYPE_TEXT = 'text';
    public const CATEGORY_MODE_TTS = 1;

    private \M2E\Kaufland\Model\Storefront\Repository $storefrontRepository;
    private \M2E\Kaufland\Model\Category\Attribute\Repository $attributeRepository;
    private \M2E\Kaufland\Model\Category\Dictionary\Attribute\Serializer $attributeSerializer;
    private \M2E\Kaufland\Model\ResourceModel\Product\CollectionFactory $listingProductCollectionFactory;

    public function __construct(
        \M2E\Kaufland\Model\ResourceModel\Product\CollectionFactory $listingProductCollectionFactory,
        \M2E\Kaufland\Model\Storefront\Repository $storefrontRepository,
        \M2E\Kaufland\Model\Category\Attribute\Repository $attributeRepository,
        \M2E\Kaufland\Model\Category\Dictionary\Attribute\Serializer $attributeSerializer,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ?\M2E\Kaufland\Model\Factory $modelFactory = null,
        ?\M2E\Kaufland\Model\ActiveRecord\Factory $activeRecordFactory = null,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $modelFactory,
            $activeRecordFactory,
            $resource,
            $resourceCollection,
            $data
        );
        $this->storefrontRepository = $storefrontRepository;
        $this->attributeRepository = $attributeRepository;
        $this->attributeSerializer = $attributeSerializer;
        $this->listingProductCollectionFactory = $listingProductCollectionFactory;
    }

    public function _construct(): void
    {
        parent::_construct();
        $this->_init(DictionaryResource::class);
    }

    public function create(
        int $storefrontId,
        int $categoryId,
        string $path,
        array $productAttributes,
        int $totalProductAttributes,
        bool $hasRequiredProductAttributes
    ): Dictionary {
        $this->setState(self::DRAFT_STATE);

        $this->setStorefrontId($storefrontId);
        $this->setCategoryId($categoryId);
        $this->setPath($path);
        $this->setProductAttributes($productAttributes);
        $this->setTotalProductAttributes($totalProductAttributes);
        $this->setHasRequiredProductAttributes($hasRequiredProductAttributes);

        return $this;
    }

    public function isAllRequiredAttributesFilled(): bool
    {
        $allAttributes = array_merge(
            $this->getProductAttributes(),
        );

        $requiredAttributeIds = array_map(
            fn(DictionaryAbstractAttribute $attribute) => $attribute->getId(),
            array_filter(
                $allAttributes,
                fn(DictionaryAbstractAttribute $attribute) => $attribute->isRequired()
            )
        );

        $filledAttributeIds = array_map(
            fn(\M2E\Kaufland\Model\Category\Attribute $attribute) => $attribute->getAttributeId(),
            array_filter(
                $this->getRelatedAttributes(),
                fn(\M2E\Kaufland\Model\Category\Attribute $attribute) => !$attribute->isValueModeNone()
            )
        );

        return count(array_diff($requiredAttributeIds, $filledAttributeIds)) === 0;
    }

    /**
     * @return \M2E\Kaufland\Model\Category\Attribute[]
     */
    public function getRelatedAttributes(): array
    {
        return $this->attributeRepository->findByDictionaryId($this->getId());
    }

    public function hasRecordsOfAttributes(): bool
    {
        return $this->attributeRepository->getCountByDictionaryId($this->getId()) > 0;
    }

    /**
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    public function getStorefront(): \M2E\Kaufland\Model\Storefront
    {
        $storefront = $this->storefrontRepository->find($this->getStorefrontId());

        if ($storefront === null) {
            throw new \M2E\Kaufland\Model\Exception\Logic(
                sprintf('Not found storefront by id [%d]', $this->getStorefrontId())
            );
        }

        return $storefront;
    }

    public function setStorefrontId(int $storefrontId): void
    {
        $this->setData(DictionaryResource::COLUMN_STOREFRONT_ID, $storefrontId);
    }

    public function getStorefrontId(): int
    {
        return (int)$this->getData(DictionaryResource::COLUMN_STOREFRONT_ID);
    }

    public function setCategoryId(int $categoryId): void
    {
        $this->setData(DictionaryResource::COLUMN_CATEGORY_ID, $categoryId);
    }

    public function getCategoryId(): int
    {
        return $this->getData(DictionaryResource::COLUMN_CATEGORY_ID);
    }

    public function setState(int $state): void
    {
        $this->setData(DictionaryResource::COLUMN_STATE, $state);
    }

    public function getState(): int
    {
        return (int)$this->getData(DictionaryResource::COLUMN_STATE);
    }

    public function setPath(string $path): void
    {
        $this->setData(DictionaryResource::COLUMN_PATH, $path);
    }

    public function getPath(): string
    {
        return $this->getData(DictionaryResource::COLUMN_PATH);
    }

    /**
     * @param \M2E\Kaufland\Model\Category\Dictionary\Attribute\ProductAttribute[] $productAttributes
     */
    public function setProductAttributes(array $productAttributes)
    {
        $this->setData(
            DictionaryResource::COLUMN_PRODUCT_ATTRIBUTES,
            $this->attributeSerializer->serializeProductAttributes($productAttributes)
        );
    }

    /**
     * @return \M2E\Kaufland\Model\Category\Dictionary\Attribute\ProductAttribute[]
     */
    public function getProductAttributes(): array
    {
        return $this->attributeSerializer->unSerializeProductAttributes(
            $this->getData(DictionaryResource::COLUMN_PRODUCT_ATTRIBUTES)
        );
    }

    public function getTotalProductAttributes(): int
    {
        return (int)$this->getData(DictionaryResource::COLUMN_TOTAL_PRODUCT_ATTRIBUTES);
    }

    public function setTotalProductAttributes(int $totalProductAttributes): void
    {
        $this->setData(DictionaryResource::COLUMN_TOTAL_PRODUCT_ATTRIBUTES, $totalProductAttributes);
    }

    public function setUsedProductAttributes(int $count): void
    {
        $this->setData(DictionaryResource::COLUMN_USED_PRODUCT_ATTRIBUTES, $count);
    }

    public function getUsedProductAttributes(): int
    {
        return (int)$this->getData(DictionaryResource::COLUMN_USED_PRODUCT_ATTRIBUTES);
    }

    public function hasRequiredProductAttributes(): bool
    {
        return (bool)$this->getData(DictionaryResource::COLUMN_HAS_REQUIRED_PRODUCT_ATTRIBUTES);
    }

    public function setHasRequiredProductAttributes(bool $hasRequiredProductAttributes): void
    {
        $this->setData(DictionaryResource::COLUMN_HAS_REQUIRED_PRODUCT_ATTRIBUTES, $hasRequiredProductAttributes);
    }

    public function setCreateDate(\DateTime $dateTime)
    {
        $this->setData(
            DictionaryResource::COLUMN_CREATE_DATE,
            $dateTime->format('Y-m-d H:i:s')
        );
    }

    public function getCreateDate(): \DateTime
    {
        return \M2E\Core\Helper\Date::createDateGmt(
            $this->getData(DictionaryResource::COLUMN_CREATE_DATE)
        );
    }

    public function setUpdateDate(\DateTime $dateTime): void
    {
        $this->setData(
            DictionaryResource::COLUMN_UPDATE_DATE,
            $dateTime->format('Y-m-d H:i:s')
        );
    }

    public function getUpdateDate(): \DateTime
    {
        return \M2E\Core\Helper\Date::createDateGmt(
            $this->getData(DictionaryResource::COLUMN_UPDATE_DATE)
        );
    }

    // ----------------------------------------

    public function isStateSaved(): bool
    {
        return $this->getData(DictionaryResource::COLUMN_STATE) === self::SAVED_STATE;
    }

    public function installStateSaved(): void
    {
        $this->setData(DictionaryResource::COLUMN_STATE, self::SAVED_STATE);
    }

    public function getPathWithCategoryId(): string
    {
        return sprintf('%s (%s)', $this->getPath(), $this->getCategoryId());
    }

    public function isLocked(): bool
    {
        $collection = $this->listingProductCollectionFactory->create();
        $collection->getSelect()->where('template_category_id = ?', $this->getId());

        return (bool)$collection->getSize();
    }

    public function delete(): void
    {
        foreach ($this->getRelatedAttributes() as $attribute) {
            $attribute->delete();
        }

        parent::delete();
    }

    public function getTrackedAttributes(): array
    {
        $trackedAttributes = [];
        foreach ($this->getRelatedAttributes() as $attribute) {
            if (!$attribute->isValueModeCustomAttribute()) {
                continue;
            }

            $trackedAttributes[] = $attribute->getCustomAttributeValue();
        }

        return array_unique(array_filter($trackedAttributes));
    }
}
