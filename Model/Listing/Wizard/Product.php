<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Listing\Wizard;

use M2E\Kaufland\Model\ResourceModel\Listing\Wizard\Product as WizardProductResource;

class Product extends \M2E\Kaufland\Model\ActiveRecord\AbstractModel
{
    public const SEARCH_STATUS_NONE = 0;
    public const SEARCH_STATUS_COMPLETED = 1;

    protected ?\M2E\Kaufland\Model\Magento\Product\Cache $magentoProductModel = null;
    private \M2E\Kaufland\Model\Magento\Product\CacheFactory $magentoProductFactory;
    private \M2E\Kaufland\Model\Listing\Wizard\Repository $wizardRepository;
    private \M2E\Kaufland\Model\Listing\Wizard $wizard;

    public function __construct(
        \M2E\Kaufland\Model\Magento\Product\CacheFactory $magentoProductFactory,
        \M2E\Kaufland\Model\Listing\Wizard\Repository $wizardRepository,
        \M2E\Kaufland\Model\Factory $modelFactory = null,
        \M2E\Kaufland\Model\ActiveRecord\Factory $activeRecordFactory = null,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $modelFactory,
            $activeRecordFactory,
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
        $this->magentoProductFactory = $magentoProductFactory;
        $this->wizardRepository = $wizardRepository;
    }

    public function _construct(): void
    {
        parent::_construct();

        $this->_init(WizardProductResource::class);
    }

    public function init(\M2E\Kaufland\Model\Listing\Wizard $wizard, int $magentoProductId): self
    {
        $this
            ->setData(WizardProductResource::COLUMN_WIZARD_ID, $wizard->getId())
            ->setData(WizardProductResource::COLUMN_MAGENTO_PRODUCT_ID, $magentoProductId);

        return $this;
    }

    public function initWizard(\M2E\Kaufland\Model\Listing\Wizard $wizard): void
    {
        $this->wizard = $wizard;
    }

    public function getId(): int
    {
        return (int)parent::getId();
    }

    public function getWizard(): \M2E\Kaufland\Model\Listing\Wizard
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (!isset($this->wizard)) {
            $this->wizard = $this->wizardRepository->get($this->getWizardId());
        }

        return $this->wizard;
    }

    public function getWizardId(): int
    {
        return (int)$this->getData(WizardProductResource::COLUMN_WIZARD_ID);
    }

    public function getMagentoProductId(): int
    {
        return (int)$this->getData(WizardProductResource::COLUMN_MAGENTO_PRODUCT_ID);
    }

    public function getKauflandProductId(): string
    {
        return (string)$this->getData(WizardProductResource::COLUMN_KAUFLAND_PRODUCT_ID);
    }

    public function setKauflandProductId(string $productId): self
    {
        $this->setData(WizardProductResource::COLUMN_KAUFLAND_PRODUCT_ID, $productId);
        $this->markProductIdIsSearched();

        return $this;
    }

    public function markProductIdIsSearched(): self
    {
        $this->setData(WizardProductResource::COLUMN_PRODUCT_ID_SEARCH_STATUS, self::SEARCH_STATUS_COMPLETED);

        return $this;
    }

    public function getUnmanagedProductId(): ?int
    {
        $value = $this->getData(WizardProductResource::COLUMN_UNMANAGED_PRODUCT_ID);

        if ($value === null) {
            return null;
        }

        return (int)$value;
    }

    public function setUnmanagedProductId(int $value): self
    {
        $this->setData(WizardProductResource::COLUMN_UNMANAGED_PRODUCT_ID, $value);

        return $this;
    }

    public function setCategoryId(int $value): self
    {
        $this->setData(WizardProductResource::COLUMN_CATEGORY_ID, $value);

        return $this;
    }

    public function setCategoryTitle(string $value): self
    {
        $this->setData(WizardProductResource::COLUMN_CATEGORY_TITLE, $value);

        return $this;
    }

    public function getCategoryDictionaryId(): ?int
    {
        $value = $this->getData(WizardProductResource::COLUMN_CATEGORY_ID);
        if ($value === null) {
            return null;
        }

        return (int)$value;
    }

    public function getCategoryTitle(): ?string
    {
        $value = $this->getData(WizardProductResource::COLUMN_CATEGORY_TITLE);
        if ($value === null) {
            return null;
        }

        return $value;
    }

    public function isProcessed(): bool
    {
        return (bool)$this->getData(WizardProductResource::COLUMN_IS_PROCESSED);
    }

    public function processed(): self
    {
        $this->setData(WizardProductResource::COLUMN_IS_PROCESSED, 1);

        return $this;
    }

    /**
     * @return \M2E\Kaufland\Model\Magento\Product\Cache
     */
    public function getMagentoProduct(): \M2E\Kaufland\Model\Magento\Product\Cache
    {
        if ($this->magentoProductModel === null) {
            $this->magentoProductModel = $this->magentoProductFactory->create();
            $this->magentoProductModel->setProductId($this->getMagentoProductId());
        }

        return $this->prepareMagentoProduct($this->magentoProductModel);
    }

    protected function prepareMagentoProduct(
        \M2E\Kaufland\Model\Magento\Product\Cache $instance
    ): \M2E\Kaufland\Model\Magento\Product\Cache {
        $instance->setStoreId($this->getWizard()->getListing()->getStoreId());
        $instance->setStatisticId($this->getId());

        return $instance;
    }
}
