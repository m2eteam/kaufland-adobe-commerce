<?php

declare(strict_types=1);

namespace M2E\Kaufland\Block\Adminhtml\Settings;

class Tabs extends \M2E\Kaufland\Block\Adminhtml\Magento\Tabs\AbstractTabs
{
    public const TAB_ID_MAIN = 'main';
    public const TAB_ID_MAPPING_ATTRIBUTES = 'mapping';

    protected function _construct(): void
    {
        parent::_construct();
        $this->setId('configuration_settings_tabs');
        $this->setDestElementId('tabs_container');
    }

    protected function _prepareLayout()
    {
        $this->css->addFile('settings.css');

        // ---------------------------------------

        $this->addTab(self::TAB_ID_MAIN, [
            'label' => __('Main'),
            'title' => __('Main'),
            'content' => $this->getLayout()
                              ->createBlock(\M2E\Kaufland\Block\Adminhtml\Settings\Tabs\Main::class)
                              ->toHtml(),
        ]);

        // ---------------------------------------

        $this->addTab(self::TAB_ID_MAPPING_ATTRIBUTES, [
            'label' => __('Attribute Mapping'),
            'title' => __('Attribute Mapping'),
            'content' => $this->getLayout()
                              ->createBlock(\M2E\Kaufland\Block\Adminhtml\Settings\Tabs\AttributeMapping::class)
                              ->toHtml(),
        ]);

        // ---------------------------------------

        $this->setActiveTab($this->getData('active_tab'));

        return parent::_prepareLayout();
    }

    public function getActiveTab()
    {
        return $this->_tabs[self::TAB_ID_MAIN] ?? null;
    }

    protected function _beforeToHtml()
    {
        $urlForSetGpsrToCategory = $this->getUrl('*/settings_attributeMapping/setGpsrToCategory');

        $this->js->addRequireJs(
            [
                's' => 'Kaufland/Kaufland/Settings',
            ],
            <<<JS

        window.KauflandSettingsObj = new KauflandSettings("$urlForSetGpsrToCategory");
JS
        );

        return parent::_beforeToHtml();
    }
}
