<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Listing\Ui\SmartTitle;

class UrlBuilder implements \M2E\Core\Model\Ui\Widget\SmartTitle\UrlBuilderInterface
{
    private const ROUTE_PATH = 'm2e_kaufland/kaufland_listing/view';

    private \Magento\Framework\UrlInterface $url;

    public function __construct(
        \Magento\Framework\UrlInterface $url
    ) {
        $this->url = $url;
    }

    public function getUrl(int $id): string
    {
        return $this->url->getUrl(self::ROUTE_PATH, [
            '_current' => true,
            'id' => $id,
        ]);
    }
}
