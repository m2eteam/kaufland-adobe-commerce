<?php

namespace M2E\Kaufland\Model\Order\Tax;

interface PriceTaxRateInterface
{
    /**
     * @return float|int
     */
    public function getValue();

    /**
     * @return float|int
     */
    public function getNotRoundedValue();

    public function isEnabledRoundingOfValue(): bool;
}
