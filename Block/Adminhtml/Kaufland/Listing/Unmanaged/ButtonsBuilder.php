<?php

declare(strict_types=1);

namespace M2E\Kaufland\Block\Adminhtml\Kaufland\Listing\Unmanaged;

use M2E\Kaufland\Block\Adminhtml\Kaufland\Listing\Unmanaged\ButtonsBlock;

class ButtonsBuilder extends \M2E\Kaufland\Block\Adminhtml\Magento\AbstractContainer
{
    public function _construct(): void
    {
        parent::_construct();

        $this->addButton('buttons_block', ['class_name' => ButtonsBlock::class]);
    }
}
