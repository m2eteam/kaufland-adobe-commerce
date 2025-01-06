<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Product\UpdateFromChannel;

use M2E\Kaufland\Model\Product;

class Processor
{
    private \M2E\Kaufland\Model\Product $product;
    private \M2E\Kaufland\Model\Listing\Other\KauflandProduct $channelProduct;
    private Product\CalculateStatusByChannel $calculateStatusByChannel;

    private array $instructionsData = [];
    /** @var \M2E\Kaufland\Model\Listing\Log\Record[] */
    private array $logs = [];

    public function __construct(
        \M2E\Kaufland\Model\Product $product,
        \M2E\Kaufland\Model\Listing\Other\KauflandProduct $channelProduct,
        \M2E\Kaufland\Model\Product\CalculateStatusByChannel $calculateStatusByChannel
    ) {
        $this->product = $product;
        $this->channelProduct = $channelProduct;
        $this->calculateStatusByChannel = $calculateStatusByChannel;
    }

    public function processChanges(): ChangeResult
    {
        $isChangedProduct = $this->processProduct();

        return new ChangeResult(
            $this->product,
            $isChangedProduct,
            array_values($this->instructionsData),
            array_values($this->logs),
        );
    }

    private function isNeedUpdateQty(): bool
    {
        if ($this->product->getOnlineQty() === $this->channelProduct->getQty()) {
            return false;
        }

        return !$this->isNeedSkipQtyChange($this->product->getOnlineQty(), $this->channelProduct->getQty());
    }

    private function isNeedSkipQtyChange(int $currentQty, int $channelQty): bool
    {
        if ($channelQty > $currentQty) {
            return false;
        }

        return $currentQty < 5;
    }

    private function isNeedUpdatePrice(): bool
    {
        return $this->product->getOnlineCurrentPrice() !== $this->channelProduct->getPrice();
    }

    private function isNeedUpdateUnitId(): bool
    {
        if ($this->product->isStatusNotListed()) {
            return false;
        }

        return $this->product->getUnitId() !== $this->channelProduct->getUnitId();
    }

    private function isNeedChangeStatus(): bool
    {
        return $this->product->getStatus() !== $this->channelProduct->getStatus();
    }

    private function isNeedChangeValid(): bool
    {
        return $this->product->isIncomplete() === $this->channelProduct->isValid();
    }

    private function processProduct(): bool
    {
        $isChangedProduct = false;

        if ($this->isNeedChangeStatus()) {
            $this->addInstructionData(
                Product::INSTRUCTION_TYPE_CHANNEL_STATUS_CHANGED,
                80,
            );

            $calculatedStatus = $this->calculateStatusByChannel->calculate(
                $this->product,
                $this->channelProduct->getStatus(),
            );
            if ($calculatedStatus === null) {
                throw new \M2E\Kaufland\Model\Exception\Logic(
                    'Unable calculate status of channel product.',
                    [
                        'product' => $this->product->getId(),
                        'extension_status' => $this->product->getStatus(),
                        'channel_status' => $this->channelProduct->getStatus(),
                    ],
                );
            }

            $this->addLog($this->processNewStatus($calculatedStatus));

            $isChangedProduct = true;
        }

        if ($this->isNeedChangeValid()) {
            if (!$this->channelProduct->isValid()) {
                $this->product->makeIncomplete($this->channelProduct->getChannelProductEmptyAttributes());
                $this->addLog(
                    \M2E\Kaufland\Model\Listing\Log\Record::createSuccess(
                        (string)__(
                            'Product Status was changed from %from to %to.',
                            [
                                'from' => \M2E\Kaufland\Model\Product::getStatusTitle($this->product->getStatus()),
                                'to' => Product::getIncompleteStatusTitle(),
                            ],
                        ),
                    ),
                );
            }

            if ($this->channelProduct->isValid()) {
                $this->product->makeComplete();
            }

            $isChangedProduct = true;
        }

        if ($this->isNeedUpdateQty()) {
            $this->addInstructionData(
                Product::INSTRUCTION_TYPE_CHANNEL_QTY_CHANGED,
                80,
            );

            $this->addLog(
                \M2E\Kaufland\Model\Listing\Log\Record::createSuccess(
                    (string)__(
                        'Product QTY was changed from %from to %to.',
                        [
                            'from' => $this->product->getOnlineQty(),
                            'to' => $this->channelProduct->getQty(),
                        ],
                    ),
                ),
            );

            $this->product->setOnlineQty($this->channelProduct->getQty());

            $isChangedProduct = true;
        }

        if ($this->isNeedUpdatePrice()) {
            $this->addInstructionData(
                Product::INSTRUCTION_TYPE_CHANNEL_PRICE_CHANGED,
                60,
            );

            $this->addLog(
                \M2E\Kaufland\Model\Listing\Log\Record::createSuccess(
                    (string)__(
                        'Product Price was changed from %from to %to.',
                        [
                            'from' => $this->product->getOnlineCurrentPrice(),
                            'to' => $this->channelProduct->getPrice(),
                        ],
                    ),
                ),
            );

            $this->product->setOnlinePrice($this->channelProduct->getPrice());

            $isChangedProduct = true;
        }

        if ($this->isNeedUpdateUnitId()) {
            $this->addLog(
                \M2E\Kaufland\Model\Listing\Log\Record::createSuccess(
                    (string)__(
                        'Unit ID of the Item was changed from %from to %to.',
                        [
                            'from' => $this->product->getUnitId(),
                            'to' => $this->channelProduct->getUnitId(),
                        ],
                    ),
                ),
            );

            $this->product->setUnitId($this->channelProduct->getUnitId());

            $isChangedProduct = true;
        }

        return $isChangedProduct;
    }

    private function processNewStatus(
        \M2E\Kaufland\Model\Product\CalculateStatusByChannel\Result $calculatedStatus
    ): \M2E\Kaufland\Model\Listing\Log\Record {
        switch ($calculatedStatus->getStatus()) {
            case \M2E\Kaufland\Model\Product::STATUS_NOT_LISTED:
                $this->product->setStatusNotListed()
                              ->setStatusChanger($calculatedStatus->getStatusChanger());
                break;

            default:
                $this->product->setStatus($calculatedStatus->getStatus())
                              ->setStatusChanger($calculatedStatus->getStatusChanger());
        }

        return $calculatedStatus->getMessageAboutChange();
    }

    private function addInstructionData(string $type, int $priority): void
    {
        $this->instructionsData[$type] = [
            'listing_product_id' => $this->product->getId(),
            'type' => $type,
            'priority' => $priority,
            'initiator' => 'channel_changes_synchronization',
        ];
    }

    private function addLog(\M2E\Kaufland\Model\Listing\Log\Record $record): void
    {
        $this->logs[$record->getMessage()] = $record;
    }
}
