<?php

namespace M2E\Kaufland\Model;

use M2E\Kaufland\Model\ResourceModel\ExternalChange as ExternalChangeResource;

class ExternalChange extends \M2E\Kaufland\Model\ActiveRecord\AbstractModel
{
    public function _construct(): void
    {
        parent::_construct();
        $this->_init(ExternalChangeResource::class);
    }

    public function init(
        \M2E\Kaufland\Model\Account $account,
        \M2E\Kaufland\Model\Storefront $storefront,
        string $offerId,
        int $unitId
    ): self {
        $this
            ->setData(ExternalChangeResource::COLUMN_ACCOUNT_ID, $account->getId())
            ->setData(ExternalChangeResource::COLUMN_STOREFRONT_ID, $storefront->getId())
            ->setData(ExternalChangeResource::COLUMN_OFFER_ID, $offerId)
            ->setData(ExternalChangeResource::COLUMN_UNIT_ID, $unitId);

        return $this;
    }

    public function getId(): int
    {
        return (int)parent::getId();
    }

    public function getAccountId(): int
    {
        return (int)$this->getData(ExternalChangeResource::COLUMN_ACCOUNT_ID);
    }

    public function getStorefrontId(): string
    {
        return $this->getData(ExternalChangeResource::COLUMN_STOREFRONT_ID);
    }

    public function getOfferId(): string
    {
        return $this->getData(ExternalChangeResource::COLUMN_OFFER_ID);
    }

    public function getUnitId(): string
    {
        return $this->getData(ExternalChangeResource::COLUMN_UNIT_ID);
    }

    public function getCreateDate(): \DateTime
    {
        return \M2E\Core\Helper\Date::createDateGmt(
            $this->getData(ExternalChangeResource::COLUMN_CREATE_DATE),
        );
    }
}
