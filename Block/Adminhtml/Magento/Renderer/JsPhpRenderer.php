<?php

namespace M2E\Kaufland\Block\Adminhtml\Magento\Renderer;

use M2E\Kaufland\Block\Adminhtml\Magento\Renderer\AbstractRenderer;

class JsPhpRenderer extends AbstractRenderer
{
    protected $jsPhp = [];

    public function addConstants($constants)
    {
        $this->jsPhp = array_merge($this->jsPhp, $constants);

        return $this;
    }

    public function render()
    {
        if (empty($this->jsPhp)) {
            return '';
        }

        $constants = \M2E\Core\Helper\Json::encode($this->jsPhp);

        return "Kaufland.php.add({$constants});";
    }
}
