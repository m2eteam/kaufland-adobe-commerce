<?php

namespace M2E\Kaufland\Block\Adminhtml\Kaufland\Grid\Column\Renderer;

use M2E\Kaufland\Model\Product;
use M2E\Kaufland\Model\ResourceModel\Product as ProductResource;

class Status extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Options
{
    use \M2E\Kaufland\Block\Adminhtml\Traits\BlockTrait;

    /** @var \M2E\Kaufland\Helper\View */
    private $viewHelper;
    private \M2E\Kaufland\Model\ScheduledAction\Repository $scheduledActionRepository;
    private \M2E\Kaufland\Model\Product\LockRepository $lockRepository;

    public function __construct(
        \M2E\Kaufland\Model\ScheduledAction\Repository $scheduledActionRepository,
        \M2E\Kaufland\Model\Product\LockRepository $lockRepository,
        \M2E\Kaufland\Helper\View $viewHelper,
        \Magento\Backend\Block\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->viewHelper = $viewHelper;
        $this->scheduledActionRepository = $scheduledActionRepository;
        $this->lockRepository = $lockRepository;
    }

    public function render(\Magento\Framework\DataObject $row): string
    {
        $html = '';
        $listingProductId = (int)$row->getData('listing_product_id');

        if ($this->getColumn()->getData('showLogIcon')) {
            /** @var \M2E\Kaufland\Block\Adminhtml\Grid\Column\Renderer\ViewLogIcon\Listing $viewLogIcon */
            $viewLogIcon = $this->getLayout()->createBlock(
                \M2E\Kaufland\Block\Adminhtml\Grid\Column\Renderer\ViewLogIcon\Listing::class,
                '',
                [
                    'data' => ['jsHandler' => 'KauflandListingViewKauflandGridObj'],
                ]
            );
            $html = $viewLogIcon->render($row);

            $additionalData = (array)\M2E\Core\Helper\Json::decode($row->getData('additional_data'));
            $synchNote = (isset($additionalData['synch_template_list_rules_note']))
                ? $additionalData['synch_template_list_rules_note']
                : [];
            if (!empty($synchNote)) {
                $synchNote = $this->viewHelper->getModifiedLogMessage($synchNote);

                if (empty($html)) {
                    $html = <<<HTML
<span class="fix-magento-tooltip m2e-tooltip-grid-warning" style="float:right;">
    {$this->getTooltipHtml($synchNote, 'map_link_error_icon_' . $row->getId())}
</span>
HTML;
                } else {
                    $html .= <<<HTML
<div id="synch_template_list_rules_note_{$listingProductId}" style="display: none">{$synchNote}</div>
HTML;
                }
            }
        }

        if ($this->isNeedShowIncompleteTooltip($row)) {
            $tooltipText = $this->getTooltip($row);
            $html .= '<div class="kaufland-status-wrapper">' . $this->getCurrentStatus($row) . $this->getTooltipHtml($tooltipText) . '</<div>';
        } else {
            $html .= $this->getCurrentStatus($row);
        }

        $html .= $this->getScheduledTag($row);
        $html .= $this->getProgressTag($row);

        return $html;
    }

    // ----------------------------------------

    protected function getCurrentStatus($row): string
    {
        $html = '';

        if ($row->getData(ProductResource::COLUMN_IS_INCOMPLETE)) {
            $html .= '<span style="color: orange;">' . Product::getIncompleteStatusTitle() . '</span>';

            return $html;
        }

        switch ($row->getData('status')) {
            case Product::STATUS_NOT_LISTED:
                $html .= '<span style="color: gray;">' . Product::getStatusTitle(Product::STATUS_NOT_LISTED) . '</span>';
                break;

            case Product::STATUS_LISTED:
                $html .= '<span style="color: green;">' . Product::getStatusTitle(Product::STATUS_LISTED) . '</span>';
                break;

            case Product::STATUS_INACTIVE:
                $html .= '<span style="color: red;">' . Product::getStatusTitle(Product::STATUS_INACTIVE) . '</span>';
                break;

            default:
                break;
        }

        return $html;
    }

    private function getProgressTag($row): string
    {
        $html = '';

        $productLock = $this->lockRepository->findByProductId((int)$row->getData('id'));
        if ($productLock === null) {
            return $html;
        }

        switch ($productLock->getInitiator()) {
            case Product\Action\DefinitionsCollection::ACTION_UNIT_LIST:
            case Product\Action\DefinitionsCollection::ACTION_PRODUCT_LIST:
                $html .= '<br/><span style="color: #605fff">[List is in progress...]</span>';
                break;

            case Product\Action\DefinitionsCollection::ACTION_UNIT_RELIST:
                $html .= '<br/><span style="color: #605fff">[Relist is in progress...]</span>';
                break;

            case Product\Action\DefinitionsCollection::ACTION_UNIT_REVISE:
            case Product\Action\DefinitionsCollection::ACTION_PRODUCT_REVISE:
                $html .= '<br/><span style="color: #605fff">[Revise is in progress...]</span>';
                break;

            case Product\Action\DefinitionsCollection::ACTION_UNIT_STOP:
                $html .= '<br/><span style="color: #605fff">[Stop is in progress...]</span>';
                break;

            case Product\Action\DefinitionsCollection::ACTION_UNIT_DELETE:
                $html .= '<br/><span style="color: #605fff">[Delete is in progress...]</span>';
                break;

            default:
                break;
        }

        return $html;
    }

    private function getScheduledTag($row): string
    {
        $html = '';

        $scheduledAction = $this->scheduledActionRepository->findByListingProductId((int)$row->getData('id'));
        if ($scheduledAction === null) {
            return $html;
        }

        switch ($scheduledAction->getActionType()) {
            case \M2E\Kaufland\Model\Product::ACTION_LIST_UNIT:
            case \M2E\Kaufland\Model\Product::ACTION_LIST_PRODUCT:
                $html .= '<br/><span style="color: #605fff">[List is Scheduled...]</span>';
                break;

            case \M2E\Kaufland\Model\Product::ACTION_RELIST_UNIT:
                $html .= '<br/><span style="color: #605fff">[Relist is Scheduled...]</span>';
                break;

            case \M2E\Kaufland\Model\Product::ACTION_REVISE_UNIT:
            case \M2E\Kaufland\Model\Product::ACTION_REVISE_PRODUCT:
                $html .= '<br/><span style="color: #605fff">[Revise is Scheduled...]</span>';
                break;

            case \M2E\Kaufland\Model\Product::ACTION_STOP_UNIT:
                $html .= '<br/><span style="color: #605fff">[Stop is Scheduled...]</span>';
                break;

            case \M2E\Kaufland\Model\Product::ACTION_DELETE_UNIT:
                $html .= '<br/><span style="color: #605fff">[Delete is Scheduled...]</span>';
                break;

            default:
                break;
        }

        return $html;
    }

    public function renderExport(\Magento\Framework\DataObject $row): string
    {
        return strip_tags($this->getCurrentStatus($row));
    }

    private function isNeedShowIncompleteTooltip(\Magento\Framework\DataObject $row): bool
    {
        if ($row->getData('channel_product_empty_attributes') !== null) {
            $emptyAttributes = json_decode($row->getData('channel_product_empty_attributes'), true);

            return $row->getData(ProductResource::COLUMN_IS_INCOMPLETE)
                && !empty($emptyAttributes);
        }

        return false;
    }

    private function getTooltip(\Magento\Framework\DataObject $row): string
    {
        $emptyAttributes = json_decode($row->getData('channel_product_empty_attributes'), true);
        $emptyAttributes = array_map('ucwords', $emptyAttributes);
        $attributesList = implode(', ', $emptyAttributes);

        return (string)__(
            'The product is not valid on the channel. To restore it, please revise the following
        fields that are currently incomplete or missing: %attributes.',
            ['attributes' => $attributesList]
        );
    }
}
