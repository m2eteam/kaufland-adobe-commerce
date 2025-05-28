<?php

namespace M2E\Kaufland\Model;

use M2E\Kaufland\Model\ResourceModel\Storefront as StorefrontResource;

class Storefront extends \M2E\Kaufland\Model\ActiveRecord\AbstractModel
{
    public const LOCK_NICK = 'storefront';

    public const STOREFRONT_DE = 'Germany';
    public const STOREFRONT_CZ = 'Czech Republic';
    public const STOREFRONT_SK = 'Slovakia';
    public const STOREFRONT_PL = 'Poland';
    public const STOREFRONT_AT = 'Austria';
    public const STOREFRONT_IT = 'Italy';
    public const STOREFRONT_FR = 'France';

    public const STOREFRONT_CURRENCIES_MAP = [
        'de' => \M2E\Kaufland\Model\Currency::CURRENCY_EUR,
        'cz' => \M2E\Kaufland\Model\Currency::CURRENCY_KC,
        'sk' => \M2E\Kaufland\Model\Currency::CURRENCY_EUR,
        'pl' => \M2E\Kaufland\Model\Currency::CURRENCY_PLZ,
        'at' => \M2E\Kaufland\Model\Currency::CURRENCY_EUR,
        'it' => \M2E\Kaufland\Model\Currency::CURRENCY_EUR,
        'fr' => \M2E\Kaufland\Model\Currency::CURRENCY_EUR,
    ];

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
        $this->_init(StorefrontResource::class);
    }

    public function init(
        \M2E\Kaufland\Model\Account $account,
        string $storefrontCode
    ): self {
        $this
            ->setData(StorefrontResource::COLUMN_ACCOUNT_ID, $account->getId())
            ->setData(StorefrontResource::COLUMN_STOREFRONT_CODE, $storefrontCode);

        $this->loadAccount($account);

        return $this;
    }

    public function loadAccount(Account $account): void
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
        return (int)$this->getData(StorefrontResource::COLUMN_ACCOUNT_ID);
    }

    public function getStorefrontCode(): string
    {
        return $this->getData(StorefrontResource::COLUMN_STOREFRONT_CODE);
    }

    public function setInventoryLastSyncDate(\DateTimeInterface $date): self
    {
        $this->setData(StorefrontResource::COLUMN_INVENTORY_LAST_SYNC, $date->format('Y-m-d H:i:s'));

        return $this;
    }

    public function resetInventoryLastSyncDate(): self
    {
        $this->setData(StorefrontResource::COLUMN_INVENTORY_LAST_SYNC);

        return $this;
    }

    public function getInventoryLastSyncDate(): ?\DateTimeImmutable
    {
        $value = $this->getData(StorefrontResource::COLUMN_INVENTORY_LAST_SYNC);
        if (empty($value)) {
            return null;
        }

        return \DateTimeImmutable::createFromMutable(\M2E\Core\Helper\Date::createDateGmt($value));
    }

    public function getUpdateDate(): \DateTime
    {
        return \M2E\Core\Helper\Date::createDateGmt(
            $this->getData(StorefrontResource::COLUMN_UPDATE_DATE),
        );
    }

    public function getCreateDate(): \DateTime
    {
        return \M2E\Core\Helper\Date::createDateGmt(
            $this->getData(StorefrontResource::COLUMN_CREATE_DATE),
        );
    }

    public function getTitle(): string
    {
        $storefrontCode = $this->getStorefrontCode();
        $map = [
            'de' => self::STOREFRONT_DE,
            'cz' => self::STOREFRONT_CZ,
            'sk' => self::STOREFRONT_SK,
            'pl' => self::STOREFRONT_PL,
            'at' => self::STOREFRONT_AT,
            'it' => self::STOREFRONT_IT,
            'fr' => self::STOREFRONT_FR,
        ];

        return $map[$storefrontCode] ?? $storefrontCode;
    }

    public function getCurrencyCode(): string
    {
        $storefrontCode = $this->getStorefrontCode();
        if (!isset(self::STOREFRONT_CURRENCIES_MAP[$storefrontCode])) {
            throw new \M2E\Kaufland\Model\Exception\Logic(
                (string)__('Currency for %code storefront not defined.', ['code' => $storefrontCode]),
            );
        }

        return self::STOREFRONT_CURRENCIES_MAP[$storefrontCode];
    }
}
