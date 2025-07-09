<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model;

use M2E\Kaufland\Model\ResourceModel\ShippingGroup as ShippingGroupResource;

class ShippingGroup extends \M2E\Kaufland\Model\ActiveRecord\AbstractModel
{
    private Account\Repository $accountRepository;
    private \M2E\Kaufland\Model\Account $account;
    public function __construct(
        \M2E\Kaufland\Model\Account\Repository $accountRepository,
        \M2E\Kaufland\Model\Factory $modelFactory,
        \M2E\Kaufland\Model\ActiveRecord\Factory $activeRecordFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null
    ) {
        parent::__construct(
            $context,
            $registry,
            $modelFactory,
            $activeRecordFactory,
            $resource,
            $resourceCollection,
        );
        $this->accountRepository = $accountRepository;
    }
    public function _construct()
    {
        parent::_construct();
        $this->_init(ShippingGroupResource::class);
    }

    public function create(
        Account $account,
        Storefront $storefront,
        int $shippingGroupId,
        string $shippingGroupName,
        string $shippingGroupType,
        bool $isDefault,
        string $currency,
        array $regions
    ): self {
        $this->setData(ShippingGroupResource::COLUMN_ACCOUNT_ID, $account->getId())
             ->setData(ShippingGroupResource::COLUMN_STOREFRONT_ID, $storefront->getId())
             ->setData(ShippingGroupResource::COLUMN_SHIPPING_GROUP_ID, $shippingGroupId)
             ->setName($shippingGroupName)
             ->setType($shippingGroupType)
             ->setIsDefault($isDefault)
             ->setCurrency($currency)
             ->setRegions($regions);

        return $this;
    }

    public function setAccount(Account $account): void
    {
        $this->account = $account;
    }

    /**
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    public function getAccount(): \M2E\Kaufland\Model\Account
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->account)) {
            return $this->account;
        }

        $account = $this->accountRepository->find($this->getAccountId());

        if ($account === null) {
            throw new \M2E\Kaufland\Model\Exception\Logic('Account must be created');
        }

        return $this->account = $account;
    }

    public function getAccountId(): int
    {
        return (int)$this->getData(ShippingGroupResource::COLUMN_ACCOUNT_ID);
    }

    public function getStorefrontId(): int
    {
        return (int)$this->getData(ShippingGroupResource::COLUMN_STOREFRONT_ID);
    }

    public function setStorefrontId(int $id): self
    {
        $this->setData(ShippingGroupResource::COLUMN_STOREFRONT_ID, $id);

        return $this;
    }

    public function getShippingGroupId(): int
    {
        return (int)$this->getData(ShippingGroupResource::COLUMN_SHIPPING_GROUP_ID);
    }

    public function getName(): string
    {
        return (string)$this->getData(ShippingGroupResource::COLUMN_NAME);
    }

    public function setName(string $name): self
    {
        $this->setData(ShippingGroupResource::COLUMN_NAME, $name);

        return $this;
    }

    public function getType(): string
    {
        return (string)$this->getData(ShippingGroupResource::COLUMN_TYPE);
    }

    public function setType(string $type): self
    {
        $this->setData(ShippingGroupResource::COLUMN_TYPE, $type);

        return $this;
    }

    public function isDefault(): bool
    {
        return (bool)$this->getData(ShippingGroupResource::COLUMN_IS_DEFAULT);
    }

    public function setIsDefault(bool $value): self
    {
        $this->setData(ShippingGroupResource::COLUMN_IS_DEFAULT, (int)$value);

        return $this;
    }

    public function getCurrency(): string
    {
        return (string)$this->getData(ShippingGroupResource::COLUMN_CURRENCY);
    }

    public function setCurrency(string $currency): self
    {
        $this->setData(ShippingGroupResource::COLUMN_CURRENCY, $currency);

        return $this;
    }

    public function getRegions(): string
    {
        return (string)$this->getData(ShippingGroupResource::COLUMN_REGIONS);
    }

    public function setRegions(array $regions): self
    {
        $this->setData(ShippingGroupResource::COLUMN_REGIONS, json_encode($regions, JSON_THROW_ON_ERROR));

        return $this;
    }
}
