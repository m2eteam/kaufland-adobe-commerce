<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model;

use M2E\Kaufland\Model\ResourceModel\Warehouse as WarehouseResource;

class Warehouse extends \M2E\Kaufland\Model\ActiveRecord\AbstractModel
{
    private Account\Repository $accountRepository;
    private \M2E\Kaufland\Model\Account $account;

    public function __construct(
        \M2E\Kaufland\Model\Account\Repository $accountRepository,
        \M2E\Kaufland\Model\Factory $modelFactory,
        \M2E\Kaufland\Model\ActiveRecord\Factory $activeRecordFactory,
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
            $data,
        );
        $this->accountRepository = $accountRepository;
    }

    public function _construct(): void
    {
        parent::_construct();
        $this->_init(WarehouseResource::class);
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

    public function create(
        Account $account,
        int $warehouseId,
        string $name,
        string $type,
        bool $isDefault,
        array $address
    ): self {
        $this->setData(WarehouseResource::COLUMN_ACCOUNT_ID, $account->getId())
             ->setData(WarehouseResource::COLUMN_WAREHOUSE_ID, $warehouseId)
             ->setName($name)
             ->setType($type)
             ->setIsDefault($isDefault)
             ->setAddress($address);

         $this->setAccount($account);

        return $this;
    }

    public function getId(): ?int
    {
        if ($this->getDataByKey(WarehouseResource::COLUMN_ID) === null) {
            return null;
        }

        return (int)$this->getDataByKey(WarehouseResource::COLUMN_ID);
    }

    public function getWarehouseId(): int
    {
        return (int)$this->getData(WarehouseResource::COLUMN_WAREHOUSE_ID);
    }

    public function setName(string $name): self
    {
        $this->setData(WarehouseResource::COLUMN_NAME, $name);

        return $this;
    }

    public function getName(): string
    {
        return (string)$this->getData(WarehouseResource::COLUMN_NAME);
    }

    public function isDefault(): bool
    {
        return (bool)$this->getData(WarehouseResource::COLUMN_IS_DEFAULT);
    }

    public function setIsDefault(bool $value): self
    {
        $this->setData(WarehouseResource::COLUMN_IS_DEFAULT, (int)$value);

        return $this;
    }

    public function setType(string $type): self
    {
        $this->setData(WarehouseResource::COLUMN_TYPE, $type);

        return $this;
    }

    public function getType(): string
    {
        return (string)$this->getData(WarehouseResource::COLUMN_TYPE);
    }

    public function getAddress(): string
    {
        return (string)$this->getData(WarehouseResource::COLUMN_ADDRESS);
    }

    public function setAddress(array $address): self
    {
        $this->setData(WarehouseResource::COLUMN_ADDRESS, json_encode($address, JSON_THROW_ON_ERROR));

        return $this;
    }

    public function getUpdateDate(): \DateTime
    {
        return \M2E\Core\Helper\Date::createDateGmt(
            $this->getData(WarehouseResource::COLUMN_UPDATE_DATE),
        );
    }

    public function getCreateDate(): \DateTime
    {
        return \M2E\Core\Helper\Date::createDateGmt(
            $this->getData(WarehouseResource::COLUMN_CREATE_DATE),
        );
    }
}
