<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Listing\AutoAction;

class GetCategoryGroupGrid extends \M2E\Kaufland\Controller\Adminhtml\Kaufland\Listing\AutoAction
{
    public function execute()
    {
        $grid = $this
            ->getLayout()
            ->createBlock(\M2E\Kaufland\Block\Adminhtml\Kaufland\Listing\AutoAction\Mode\Category\Group\Grid::class);

        $this->setAjaxContent($grid);

        return $this->getResult();
    }
}
