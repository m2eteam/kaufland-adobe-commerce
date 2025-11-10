<?php

declare(strict_types=1);

namespace M2E\Kaufland\Block\Adminhtml\Kaufland\Listing\AutoAction\Mode\Category\Group;

class Grid extends \M2E\Kaufland\Block\Adminhtml\Listing\AutoAction\Mode\Category\Group\AbstractGrid
{
    public function getGridUrl()
    {
        return $this->getUrl('*/kaufland_listing_autoAction/getCategoryGroupGrid', ['_current' => true]);
    }
}
