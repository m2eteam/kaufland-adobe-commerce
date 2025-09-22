<?php

declare(strict_types=1);

namespace M2E\Kaufland\Block\Adminhtml\Category\Attributes\Validation;

class Popup extends \M2E\Kaufland\Block\Adminhtml\Magento\AbstractContainer
{
    protected $_template = 'M2E_Kaufland::category/attributes/validation_popup.phtml';

    public function getModalOpenUrl(): string
    {
        return $this->getUrl('*/kaufland_category_attribute_validation_modal/open');
    }
}
