<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Product\Action\DataBuilder;

class Images extends AbstractDataBuilder
{
    public const NICK = 'Images';

    private array $onlineData = [];

    public function getBuilderData(): array
    {
        $listingProduct = $this->getListingProduct();

        $productImageSet = $listingProduct->getDescriptionTemplateSource()->getImageSet();

        $result = [];

        foreach ($productImageSet->getAll() as $productImage) {
            $result[] = $productImage->getUrl();
        }

        $this->onlineData = $result;

        return $result;
    }

    public function getMetaData(): array
    {
        $images = implode(",", $this->onlineData);
        return [
            self::NICK => ['online_image' => $images]
        ];
    }
}
