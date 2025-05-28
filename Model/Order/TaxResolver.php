<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Order;

class TaxResolver
{
    private const TAX_VAT_DE = 19;
    private const TAX_VAT_SK = 20;
    private const TAX_VAT_CZ = 21;
    private const TAX_VAT_AT = 20;
    private const TAX_VAT_PL = 23;
    private const TAX_VAT_IT = 22;
    private const TAX_VAT_FR = 20;

    private \Magento\Tax\Model\Calculation $taxCalculator;

    public function __construct(\Magento\Tax\Model\Calculation $taxCalculator)
    {
        $this->taxCalculator = $taxCalculator;
    }

    public function getVatbyStorefrontCode(string $storefrontCode): int
    {
        $map = [
            'de' => self::TAX_VAT_DE,
            'cz' => self::TAX_VAT_CZ,
            'sk' => self::TAX_VAT_SK,
            'pl' => self::TAX_VAT_PL,
            'at' => self::TAX_VAT_AT,
            'it' => self::TAX_VAT_IT,
            'fr' => self::TAX_VAT_FR,
        ];

        if (!isset($map[$storefrontCode])) {
            throw new \M2E\Kaufland\Model\Exception\Logic(
                (string)__('Tax Vat for %code storefront not defined.', ['code' => $storefrontCode]),
            );
        }

        return $map[$storefrontCode];
    }

    public function getOrderTax(array $items, float $taxRate): float
    {
        $taxAmount = 0.0;
        foreach ($items as $item) {
            $taxAmount += $this->getTaxAmount((float)$item['price'], $taxRate);
        }

        return $taxAmount;
    }

    public function getTaxAmount(float $price, float $taxRate): float
    {
        return $this->taxCalculator->calcTaxAmount($price, $taxRate, true, false);
    }
}
