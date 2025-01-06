<?php

declare(strict_types=1);

namespace M2E\Kaufland\Ui\Product\Component\Listing\Column;

use M2E\Kaufland\Model\Product;
use M2E\Kaufland\Model\Product\Ui\RuntimeStorage;
use M2E\Kaufland\Model\ScheduledAction\Repository;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

class Status extends Column
{
    private RuntimeStorage $productUiRuntimeStorage;
    private Repository $scheduledActionRepository;

    public function __construct(
        RuntimeStorage $productUiRuntimeStorage,
        Repository $scheduledActionRepository,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->productUiRuntimeStorage = $productUiRuntimeStorage;
        $this->scheduledActionRepository = $scheduledActionRepository;
    }

    public function prepareDataSource(array $dataSource): array
    {
        if (empty($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$row) {
            $product = $this->productUiRuntimeStorage->findProduct((int)$row['product_id']);
            if (empty($product)) {
                continue;
            }

            $html = '';
            $html .= $this->getCurrentStatus($product);
            $html .= $this->getScheduledTag($product);

            $row['product_status'] = $html;
        }

        return $dataSource;
    }

    private function getCurrentStatus(Product $product): string
    {
        if ($product->isIncomplete()) {
            return '<span style="color: gray;">' . Product::getIncompleteStatusTitle() . '</span>';
        }

        if ($product->isStatusNotListed()) {
            return '<span style="color: gray;">' . Product::getStatusTitle(Product::STATUS_NOT_LISTED) . '</span>';
        }

        if ($product->isStatusListed()) {
            return '<span style="color: green;">' . Product::getStatusTitle(Product::STATUS_LISTED) . '</span>';
        }

        if ($product->isStatusInactive()) {
            return '<span style="color: red;">' . Product::getStatusTitle(Product::STATUS_INACTIVE) . '</span>';
        }

        return '';
    }

    private function getScheduledTag(Product $product): string
    {
        $scheduledAction = $this->scheduledActionRepository->findByListingProductId($product->getId());
        if ($scheduledAction === null) {
            return '';
        }

        $html = '';

        switch ($scheduledAction->getActionType()) {
            case [Product::ACTION_LIST_UNIT, Product::ACTION_LIST_PRODUCT]:
                $html .= '<br/><span style="color: #605fff">[List is Scheduled...]</span>';
                break;

            case Product::ACTION_RELIST_UNIT:
                $html .= '<br/><span style="color: #605fff">[Relist is Scheduled...]</span>';
                break;

            case [Product::ACTION_REVISE_UNIT, Product::ACTION_REVISE_PRODUCT]:
                $html .= '<br/><span style="color: #605fff">[Revise is Scheduled...]</span>';
                break;

            case Product::ACTION_STOP_UNIT:
                $html .= '<br/><span style="color: #605fff">[Stop is Scheduled...]</span>';
                break;

            case Product::ACTION_DELETE_UNIT:
                $html .= '<br/><span style="color: #605fff">[Delete is Scheduled...]</span>';
                break;

            default:
                break;
        }

        return $html;
    }
}
