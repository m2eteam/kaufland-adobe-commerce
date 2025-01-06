<?php

namespace M2E\Kaufland\Block\Adminhtml\Kaufland\Log\Listing\Product;

use M2E\Kaufland\Block\Adminhtml\Log\Listing\Product\AbstractView;

class View extends AbstractView
{
    protected function getComponentMode()
    {
        return 'Kaufland';
    }

    protected function _toHtml()
    {
        $message = (string)__(
            'This Log contains information about the actions applied to M2E Kaufland Listings and related Items.'
        );
        $helpBlock = $this
            ->getLayout()
            ->createBlock(\M2E\Kaufland\Block\Adminhtml\HelpBlock::class)->setData([
                'content' => $message,
            ]);

        return $helpBlock->toHtml() . parent::_toHtml();
    }
}
