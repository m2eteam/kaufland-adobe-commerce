<?php

namespace M2E\Kaufland\Observer\Order\Quote\Address\Collect\Totals;

use Magento\Framework\Pricing\PriceCurrencyInterface;

class After extends \M2E\Kaufland\Observer\AbstractObserver
{
    /** @var PriceCurrencyInterface */
    private $priceCurrency;

    /**
     * @param \M2E\Kaufland\Model\ActiveRecord\Factory $activeRecordFactory
     * @param \M2E\Kaufland\Model\Factory $modelFactory
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        \M2E\Kaufland\Model\ActiveRecord\Factory $activeRecordFactory,
        \M2E\Kaufland\Model\Factory $modelFactory,
        PriceCurrencyInterface $priceCurrency
    ) {
        parent::__construct($activeRecordFactory, $modelFactory);

        $this->priceCurrency = $priceCurrency;
    }

    /**
     * @return void
     */
    public function process(): void
    {
        /** @var \Magento\Quote\Model\Quote\Address\Total $total */
        /** @var \Magento\Quote\Model\Quote $quote */
        $total = $this->getEvent()->getTotal();
        $quote = $this->getEvent()->getQuote();

        if ($quote->getIsKauflandQuote() && $quote->getUseKauflandDiscount()) {
            $discountAmount = $this->priceCurrency->convert($quote->getCoinDiscount());

            if ($total->getTotalAmount('subtotal')) {
                $total->setTotalAmount('subtotal', $total->getTotalAmount('subtotal') - $discountAmount);
            }

            if ($total->getBaseTotalAmount('subtotal')) {
                $total->setTotalAmount('subtotal', $total->getBaseTotalAmount('subtotal') - $discountAmount);
            }

            if ($total->hasData('grand_total') && $total->getGrandTotal()) {
                $total->setGrandTotal($total->getGrandTotal() - $discountAmount);
            }

            if ($total->hasData('base_grand_total') && $total->getBaseGrandTotal()) {
                $total->setBaseGrandTotal($total->getBaseGrandTotal() - $discountAmount);
            }
        }
    }
}
