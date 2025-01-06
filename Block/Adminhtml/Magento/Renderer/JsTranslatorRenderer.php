<?php

namespace M2E\Kaufland\Block\Adminhtml\Magento\Renderer;

use M2E\Kaufland\Block\Adminhtml\Magento\Renderer\AbstractRenderer;

class JsTranslatorRenderer extends AbstractRenderer
{
    protected $jsTranslations = [];

    public function add($alias, $translation)
    {
        $this->jsTranslations[$alias] = $translation;

        return $this;
    }

    public function addTranslations(array $translations)
    {
        $this->jsTranslations = array_merge($this->jsTranslations, $translations);

        return $this;
    }

    public function render()
    {
        if (empty($this->jsTranslations)) {
            return '';
        }

        $translations = \M2E\Core\Helper\Json::encode($this->jsTranslations);

        return "Kaufland.translator.add({$translations});";
    }
}
