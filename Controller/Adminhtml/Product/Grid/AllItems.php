<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Product\Grid;

use M2E\Kaufland\Controller\Adminhtml\AbstractListing;

class AllItems extends AbstractListing
{
    public function execute()
    {
        $this->getResultPage()
             ->getConfig()
             ->getTitle()
             ->prepend(__('All Items'));

        return $this->getResult();
    }
}
