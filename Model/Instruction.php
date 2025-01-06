<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model;

use M2E\Kaufland\Model\ResourceModel\Instruction as InstructionResource;

class Instruction extends \M2E\Kaufland\Model\ActiveRecord\AbstractModel
{
    private Product $listingProduct;

    public function _construct(): void
    {
        parent::_construct();
        $this->_init(ResourceModel\Instruction::class);
    }

    public function create(
        int $listingProductId,
        string $type,
        string $initiator,
        int $priority,
        ?\DateTime $skipUntil
    ): self {
        $this
            ->setData(InstructionResource::COLUMN_LISTING_PRODUCT_ID, $listingProductId)
            ->setData(InstructionResource::COLUMN_TYPE, $type)
            ->setData(InstructionResource::COLUMN_INITIATOR, $initiator)
            ->setData(InstructionResource::COLUMN_PRIORITY, $priority)
            ->setData(InstructionResource::COLUMN_SKIP_UNTIL, $skipUntil === null ? null : $skipUntil->format('Y-m-d H:i:s'));

        return $this;
    }

    public function setListingProduct(Product $listingProduct): void
    {
        $this->listingProduct = $listingProduct;
    }

    public function getListingProduct(): Product
    {
        return $this->listingProduct;
    }

    public function getListingProductId(): int
    {
        return (int)$this->getData(InstructionResource::COLUMN_LISTING_PRODUCT_ID);
    }

    public function getType()
    {
        return $this->getData(InstructionResource::COLUMN_TYPE);
    }

    public function getInitiator(): string
    {
        return (string)$this->getData(InstructionResource::COLUMN_INITIATOR);
    }

    public function getPriority(): int
    {
        return (int)$this->getData(InstructionResource::COLUMN_PRIORITY);
    }

    public function getSkipUntil(): ?\DateTime
    {
        $value = $this->getData(InstructionResource::COLUMN_SKIP_UNTIL);
        if (empty($value)) {
            return null;
        }

        return \M2E\Core\Helper\Date::createDateGmt($value);
    }
}
