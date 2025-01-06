<?php

declare(strict_types=1);

namespace M2E\Kaufland\Block\Adminhtml\Kaufland\Template\Category\Chooser\Specific;

class Info extends \M2E\Kaufland\Block\Adminhtml\Widget\Info
{
    protected function _prepareLayout()
    {
        $this->setInfo(
            [
                [
                    'label' => __('Category'),
                    'value' => $this->getData('path'),
                ],
            ]
        );

        return parent::_prepareLayout();
    }
}
