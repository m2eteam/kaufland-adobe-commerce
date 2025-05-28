<?php

namespace M2E\Kaufland\Model\Product\Action\DataBuilder;

use M2E\Kaufland\Model\Magento\Product as MagentoProduct;

class Qty extends AbstractDataBuilder
{
    public const NICK = 'Qty';
    private int $qty;

    public function getBuilderData(): array
    {
        $qty = $this->getListingProduct()->getQty();
        $this->qty = $qty;

        $data = [
            'qty' => $qty,
        ];

        $this->checkQtyWarnings();

        return $data;
    }

    /**
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    protected function checkQtyWarnings()
    {
        $qtyMode = $this->getListingProduct()->getSellingFormatTemplate()->getQtyMode();
        if (
            $qtyMode == \M2E\Kaufland\Model\Template\SellingFormat::QTY_MODE_PRODUCT_FIXED ||
            $qtyMode == \M2E\Kaufland\Model\Template\SellingFormat::QTY_MODE_PRODUCT
        ) {
            $listingProductId = $this->getListingProduct()->getId();
            $productId = $this->getListingProduct()->getMagentoProductId();
            $storeId = $this->getListingProduct()->getListing()->getStoreId();

            if (!empty(MagentoProduct::$statistics[$listingProductId][$productId][$storeId]['qty'])) {
                $qtys = MagentoProduct::$statistics[$listingProductId][$productId][$storeId]['qty'];
                foreach ($qtys as $type => $override) {
                    $this->addQtyWarnings($type);
                }
            }
        }
    }

    /**
     * @param int $type
     */
    protected function addQtyWarnings($type)
    {
        if ($type === MagentoProduct::FORCING_QTY_TYPE_MANAGE_STOCK_NO) {
            $this->addWarningMessage(
                'During the Quantity Calculation the Settings in the "Manage Stock No" ' .
                'field were taken into consideration.'
            );
        }

        if ($type === MagentoProduct::FORCING_QTY_TYPE_BACKORDERS) {
            $this->addWarningMessage(
                'During the Quantity Calculation the Settings in the "Backorders" ' .
                'field were taken into consideration.'
            );
        }
    }

    public function getMetaData(): array
    {
        return [
            self::NICK => [
                'qty' => $this->qty,
            ],
        ];
    }
}
