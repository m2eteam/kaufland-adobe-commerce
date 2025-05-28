<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Product\Action\DataBuilder;

use M2E\Kaufland\Model\Product\Action\DataBuilder\AbstractDataBuilder;

class Description extends AbstractDataBuilder
{
    public const NICK = 'Description';

    private string $onlineDescription;

    public function getBuilderData(): array
    {
        $listingProduct = $this->getListingProduct();

        $data = $listingProduct->getRenderedDescription();

        $this->onlineDescription = \M2E\Kaufland\Model\Product::createOnlineDescription($data);

        return [
            'description' => $data,
        ];
    }

    public function getMetaData(): array
    {
        return [
            self::NICK => ['online_description' => $this->onlineDescription],
        ];
    }
}
