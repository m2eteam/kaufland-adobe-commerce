<?php

declare(strict_types=1);

namespace M2E\Kaufland\Ui\Product\Component\Unmanaged\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

class Title extends \Magento\Ui\Component\Listing\Columns\Column
{
    private \M2E\Kaufland\Model\Storefront\Repository $storefrontRepository;

    public function __construct(
        \M2E\Kaufland\Model\Storefront\Repository $storefrontRepository,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->storefrontRepository = $storefrontRepository;
    }

    public function prepareDataSource(array $dataSource): array
    {
        if (empty($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$row) {
            $productTitle = $row['title'];

            $html = sprintf('<p>%s</p>', $productTitle);

            $html .= $this->renderLine((string)__('SKU'), $row['offer_id']);
            if ($row['category_title'] !== null) {
                $html .= $this->renderLine(
                    (string)__(
                        '%channel_title Category',
                        ['channel_title' => \M2E\Kaufland\Helper\Module::getChannelTitle()]
                    ),
                    $row['category_title']
                );
            }
            $storefrontTitle = $this->getStorefrontTitle((int)$row['storefront_id']);
            $html .= $this->renderLine((string)__('Storefront'), $storefrontTitle);

            $row['title'] = $html;
        }

        return $dataSource;
    }

    private function renderLine(string $label, string $value): string
    {
        return sprintf('<p style="margin: 0"><strong>%s:</strong> %s</p>', $label, $value);
    }

    private function getStorefrontTitle(int $storefrontId): string
    {
        return $this->storefrontRepository->get($storefrontId)->getTitle();
    }
}
