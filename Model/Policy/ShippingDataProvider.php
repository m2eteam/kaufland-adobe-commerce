<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Policy;

class ShippingDataProvider
{
    private \M2E\Kaufland\Model\Template\Shipping $shipping;
    private \M2E\Kaufland\Model\Product $product;

    public function __construct(
        \M2E\Kaufland\Model\Template\Shipping $shipping,
        \M2E\Kaufland\Model\Product $product
    ) {
        $this->shipping = $shipping;
        $this->product = $product;
    }

    public function getKauflandWarehouseId(): int
    {
        return $this->shipping->getKauflandSWarehouseId();
    }

    public function getKauflandShippingGroupId(): int
    {
        return $this->shipping->getKauflandShippingGroupId();
    }

    public function getHandlingTimeValue(): int
    {
        return $this->shipping->getHandlingTimeValue();
    }

    public function isHandlingTimeModeAttribute(): bool
    {
        return $this->shipping->isHandlingTimeModeAttribute();
    }

    public function getHandlingTimeAttribute(): string
    {
        return $this->shipping->getHandlingTimeAttribute();
    }

    /**
     * @return int|null
     */
    public function getHandlingTime(): ?int
    {
        if ($this->isHandlingTimeModeAttribute()) {
            $attributeValue = $this->product->getMagentoProduct()->getAttributeValue($this->getHandlingTimeAttribute());

            if (empty($attributeValue)) {
                return null;
            } else {
                return (int)$attributeValue;
            }
        }

        return $this->getHandlingTimeValue();
    }
}
