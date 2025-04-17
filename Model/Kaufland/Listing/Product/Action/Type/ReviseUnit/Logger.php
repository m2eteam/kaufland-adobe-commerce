<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\ReviseUnit;

use M2E\Kaufland\Model\Kaufland\Listing\Product\Action\DataBuilder;

class Logger
{
    private array $logs = [];
    private \Magento\Framework\Locale\CurrencyInterface $localeCurrency;
    private float $price;
    private int $qty;
    private int $handlingTime;

    public function __construct(
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency
    ) {
        $this->localeCurrency = $localeCurrency;
    }

    public function saveProductDataBeforeUpdate(\M2E\Kaufland\Model\Product $product): void
    {
        $this->price = $product->getOnlineCurrentPrice();
        $this->qty = $product->getOnlineQty();
        $this->handlingTime = $product->getOnlineHandlingTime();
    }

    public function calculateLogs(\M2E\Kaufland\Model\Product $product): array
    {
        $this->processSuccessRevisePrice($product);
        $this->processSuccessReviseQty($product);
        $this->processSuccessReviseHandlingTime($product);

        return $this->logs;
    }

    private function processSuccessRevisePrice(\M2E\Kaufland\Model\Product $product): void
    {
        if ($product->getOnlineCurrentPrice() === $this->price) {
            return;
        }

        $storefront = $product->getListing()->getStorefront();

        $currencyCode = $storefront->getCurrencyCode();
        $currency = $this->localeCurrency->getCurrency($currencyCode);

        $message = sprintf(
            'Product Price was revised from %s to %s',
            $currency->toCurrency($this->price),
            $currency->toCurrency($product->getOnlineCurrentPrice())
        );

        $this->createSuccessMessage($message);
    }

    private function processSuccessReviseQty(\M2E\Kaufland\Model\Product $product): void
    {
        if ($product->getOnlineQty() === $this->qty) {
            return;
        }

        $message = sprintf(
            'Product QTY was revised from %s to %s',
            $this->qty,
            $product->getOnlineQty()
        );

        $this->createSuccessMessage($message);
    }

    private function processSuccessReviseHandlingTime(\M2E\Kaufland\Model\Product $product): void
    {
        if ($product->getOnlineHandlingTime() === $this->handlingTime) {
            return;
        }

        $message = sprintf(
            'Product Handling Time was revised from %s to %s',
            $this->handlingTime,
            $product->getOnlineHandlingTime()
        );

        $this->createSuccessMessage($message);
    }

    private function createSuccessMessage(string $message): void
    {
        $this->logs[] = \M2E\Core\Model\Response\Message::createSuccess($message);
    }
}
