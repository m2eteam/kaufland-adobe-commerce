<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Product;

use M2E\Kaufland\Model\Listing\Other\KauflandProduct as ChannelProduct;
use M2E\Kaufland\Model\Product as ExtensionProduct;

class CalculateStatusByChannel
{
    /**
     * @param \M2E\Kaufland\Model\Product $product
     * @param string|int $channelStatus
     *
     * @return \M2E\Kaufland\Model\Product\CalculateStatusByChannel\Result|null
     */
    public function calculate(ExtensionProduct $product, $channelStatus): ?CalculateStatusByChannel\Result
    {
        if (is_string($channelStatus)) {
            $extensionStatusFromChannel = ChannelProduct::convertChannelStatusToExtension(
                $channelStatus,
            );
        } else {
            $extensionStatusFromChannel = (int)$channelStatus;
        }

        if ($this->isStatusRight($product, $extensionStatusFromChannel)) {
            return null;
        }

        if (
            $extensionStatusFromChannel === \M2E\Kaufland\Model\Product::STATUS_NOT_LISTED // Deleted case
            && $product->getStatus() !== \M2E\Kaufland\Model\Product::STATUS_NOT_LISTED
        ) {
            $calculateStatus = \M2E\Kaufland\Model\Product::STATUS_NOT_LISTED;
            $actionMessage = \M2E\Kaufland\Model\Listing\Log\Record::createSuccess(
                (string)__('Product was deleted and is no longer available on the channel'),
            );
        } else {
            $calculateStatus = $extensionStatusFromChannel;
            $actionMessage = \M2E\Kaufland\Model\Listing\Log\Record::createSuccess(
                (string)__(
                    'Product Status was changed from %from to %to.',
                    [
                        'from' => $product->getProductStatusTitle(),
                        'to' => \M2E\Kaufland\Model\Product::getStatusTitle($extensionStatusFromChannel),
                    ],
                ),
            );
        }

        return new CalculateStatusByChannel\Result(
            $product,
            $calculateStatus,
            $actionMessage,
        );
    }

    private function isStatusRight(ExtensionProduct $product, int $channelStatus): bool
    {
        return $product->getStatus() === $channelStatus;
    }
}
