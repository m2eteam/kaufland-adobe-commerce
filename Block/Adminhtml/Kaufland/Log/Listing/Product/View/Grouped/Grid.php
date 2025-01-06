<?php

namespace M2E\Kaufland\Block\Adminhtml\Kaufland\Log\Listing\Product\View\Grouped;

use M2E\Kaufland\Block\Adminhtml\Log\Listing\Product\View\Grouped\AbstractGrid;

class Grid extends AbstractGrid
{
    protected function getExcludedActionTitles()
    {
        return [
            \M2E\Kaufland\Model\Listing\Log::ACTION_DELETE_AND_REMOVE_PRODUCT => '',
            \M2E\Kaufland\Model\Listing\Log::ACTION_DELETE_PRODUCT_FROM_COMPONENT => '',
            \M2E\Kaufland\Model\Listing\Log::ACTION_SWITCH_TO_AFN_ON_COMPONENT => '',
            \M2E\Kaufland\Model\Listing\Log::ACTION_SWITCH_TO_MFN_ON_COMPONENT => '',
            \M2E\Kaufland\Model\Listing\Log::ACTION_CHANGE_PRODUCT_TIER_PRICE => '',
            \M2E\Kaufland\Model\Listing\Log::ACTION_RESET_BLOCKED_PRODUCT => '',
        ];
    }
}
