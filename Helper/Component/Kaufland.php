<?php

declare(strict_types=1);

namespace M2E\Kaufland\Helper\Component;

class Kaufland
{
    public const NICK = 'Kaufland';

    public const MAX_LENGTH_FOR_OPTION_VALUE = 50;
    public const ITEM_SKU_MAX_LENGTH = 50;

    private \M2E\Kaufland\Helper\Data\Cache\Permanent $cachePermanent;

    public function __construct(
        \M2E\Kaufland\Helper\Data\Cache\Permanent $cachePermanent
    ) {
        $this->cachePermanent = $cachePermanent;
    }

    // ----------------------------------------

    public function getTitle(): string
    {
        return (string)__('Kaufland');
    }

    public function getChannelTitle(): string
    {
        return (string)__('Kaufland');
    }

    // ----------------------------------------

    /**
     * @return string[]
     */
    public function getCarriers(): array
    {
        return [
            'dhl' => 'DHL',
            'fedex' => 'FedEx',
            'ups' => 'UPS',
            'usps' => 'USPS',
        ];
    }

    /**
     * @param array $options
     *
     * @return array
     */
    public function prepareOptionsForOrders(array $options): array
    {
        foreach ($options as &$singleOption) {
            if ($singleOption instanceof \Magento\Catalog\Model\Product) {
                $reducedName = trim(
                    \M2E\Core\Helper\Data::reduceWordsInString(
                        $singleOption->getName(),
                        self::MAX_LENGTH_FOR_OPTION_VALUE
                    )
                );
                $singleOption->setData('name', $reducedName);

                continue;
            }

            foreach ($singleOption['values'] as &$singleOptionValue) {
                foreach ($singleOptionValue['labels'] as &$singleOptionLabel) {
                    $singleOptionLabel = trim(
                        \M2E\Core\Helper\Data::reduceWordsInString(
                            $singleOptionLabel,
                            self::MAX_LENGTH_FOR_OPTION_VALUE
                        )
                    );
                }
            }
        }

        if (isset($options['additional']['attributes'])) {
            foreach ($options['additional']['attributes'] as $code => &$title) {
                $title = trim($title);
            }
            unset($title);
        }

        return $options;
    }

    // ----------------------------------------

    /**
     * @return void
     */
    public function clearCache(): void
    {
        $this->cachePermanent->removeTagValues(self::NICK);
    }
}
