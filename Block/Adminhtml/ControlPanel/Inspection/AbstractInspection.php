<?php

declare(strict_types=1);

namespace M2E\Kaufland\Block\Adminhtml\ControlPanel\Inspection;

use M2E\Kaufland\Block\Adminhtml\Magento\AbstractBlock;

abstract class AbstractInspection extends AbstractBlock
{
    public function isShown(): bool
    {
        return true;
    }
}
