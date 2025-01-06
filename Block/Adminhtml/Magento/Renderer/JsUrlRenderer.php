<?php

namespace M2E\Kaufland\Block\Adminhtml\Magento\Renderer;

use M2E\Kaufland\Block\Adminhtml\Magento\Renderer\AbstractRenderer;

/**
 * Class \M2E\Kaufland\Block\Adminhtml\Magento\Renderer\JsUrlRenderer
 */
class JsUrlRenderer extends AbstractRenderer
{
    protected $jsUrls = [];

    public function add($url, $alias = null)
    {
        if ($alias === null) {
            $alias = $url;
        }
        $this->jsUrls[$alias] = $url;

        return $this;
    }

    public function addUrls(array $urls)
    {
        $this->jsUrls = array_merge($this->jsUrls, $urls);

        return $this;
    }

    public function render()
    {
        if (empty($this->jsUrls)) {
            return '';
        }

        $urls = \M2E\Core\Helper\Json::encode($this->jsUrls);

        return "Kaufland.url.add({$urls});";
    }
}
