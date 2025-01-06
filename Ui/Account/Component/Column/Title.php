<?php

declare(strict_types=1);

namespace M2E\Kaufland\Ui\Account\Component\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Title extends Column
{
    private \M2E\Kaufland\Model\Account\Repository $accountRepository;
    public function __construct(
        \M2E\Kaufland\Model\Account\Repository $accountRepository,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);

        $this->accountRepository = $accountRepository;
    }

    public function prepareDataSource(array $dataSource): array
    {
        if (empty($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$row) {
            $accountTitle = $row['title'];

            $html = sprintf('<p>%s</p>', \M2E\Core\Helper\Data::escapeHtml($accountTitle));

            $storefronts = $this->accountRepository->find((int)$row['id'])->getStorefronts();
            $storefrontTitles = $this->getStorefrontTitles($storefronts);

            $html .= $this->renderLine((string)\__('Storefront'), $storefrontTitles);

            $row['title'] = $html;
        }

        return $dataSource;
    }

    private function renderLine(string $label, string $value): string
    {
        return sprintf('<p style="margin: 0">%s: %s</p>', $label, $value);
    }

    private function getStorefrontTitles(array $storefronts)
    {
        $storefrontTitles = [];

        foreach ($storefronts as $storefront) {
            $storefrontTitles[] = $storefront->getTitle();
        }

        return implode(', ', $storefrontTitles);
    }
}
