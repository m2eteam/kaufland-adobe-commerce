<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Listing\Product\Action\DataBuilder;

class Title extends AbstractDataBuilder
{
    public const NICK = 'Title';

    private ?string $onlineTitle = null;

    public function getBuilderData(): array
    {
        $title = $this->getListingProduct()->getDescriptionTemplateSource()->getTitle();

        $this->onlineTitle = $title;

        return [
            'title' => $title,
        ];
    }

    public function getMetaData(): array
    {
        return [
            self::NICK => ['online_title' => $this->onlineTitle],
        ];
    }
}
