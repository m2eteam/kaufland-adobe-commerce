<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Product\Action\DataBuilder;

class Price extends AbstractDataBuilder
{
    public const NICK = 'Price';
    private float $price;

    public function getBuilderData(): array
    {
        $price = (float)$this->getListingProduct()->getFixedPrice();
        $this->price = $price;

        return [
            'amount' => $price,
        ];
    }

    public function getMetaData(): array
    {
        return [
            self::NICK => [
                'amount' => $this->price,
            ],
        ];
    }
}
