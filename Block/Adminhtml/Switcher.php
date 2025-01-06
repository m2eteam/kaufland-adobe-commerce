<?php

namespace M2E\Kaufland\Block\Adminhtml;

abstract class Switcher extends Magento\AbstractBlock
{
    public const SIMPLE_STYLE = 0;
    public const ADVANCED_STYLE = 1;

    protected $items = null;

    protected $paramName = null;

    protected bool $hasDefaultOption = true;

    protected function _construct()
    {
        parent::_construct();

        if ($this->getStyle() === self::ADVANCED_STYLE) {
            $this->setTemplate('switcher/advanced.phtml');
        } else {
            $this->setTemplate('switcher/simple.phtml');
        }
        $this->css->addFile('switcher.css');
    }

    /**
     * @return string
     */
    abstract public function getLabel();

    /**
     * @return void
     */
    abstract protected function loadItems();

    public function getItems()
    {
        if ($this->items === null) {
            $this->loadItems();
        }

        return $this->items;
    }

    public function isEmpty()
    {
        return empty($this->getItems());
    }

    public function getSwitchUrl()
    {
        $controllerName = $this->getData('controller_name') ? $this->getData('controller_name') : '*';

        return $this->getUrl(
            "*/$controllerName/*",
            ['_current' => true, $this->getParamName() => $this->getParamPlaceHolder()]
        );
    }

    public function getSwitchCallbackName()
    {
        $callback = 'switch';
        $callback .= ucfirst($this->paramName);

        return $callback;
    }

    public function getSwitchCallback()
    {
        return <<<JS
var switchUrl = '{$this->getSwitchUrl()}';
var paramName = '{$this->getParamName()}';
var paramPlaceHolder = '{$this->getParamPlaceHolder()}';

if (this.value == '{$this->getDefaultOptionValue()}') {
    switchUrl = switchUrl.replace(paramName + '/' + paramPlaceHolder + '/', '');
} else {
    switchUrl = switchUrl.replace(paramPlaceHolder, this.value);
}

setLocation(switchUrl);
JS;
    }

    //########################################

    public function getParamName()
    {
        return $this->paramName;
    }

    public function getParamPlaceHolder()
    {
        // can't use special chars like # or % cause magento's getUrl method decoding them
        return 'PLH' . $this->getParamName() . 'PLH';
    }

    public function getDefaultParam()
    {
        return null;
    }

    public function getSelectedParam()
    {
        return $this->getRequest()->getParam($this->getParamName(), $this->getDefaultParam());
    }

    //########################################

    public function getStyle()
    {
        return self::SIMPLE_STYLE;
    }

    public function getTooltip()
    {
        return null;
    }

    public function hasDefaultOption(): bool
    {
        if ($this->getData('has_default_option') !== null) {
            $this->hasDefaultOption = (bool)$this->getData('has_default_option');
        }

        return $this->hasDefaultOption;
    }

    public function getDefaultOptionName()
    {
        return (string)__('All');
    }

    public function getDefaultOptionValue()
    {
        return 'all';
    }

    //########################################
}
