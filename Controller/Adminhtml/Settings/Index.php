<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Settings;

class Index extends \M2E\Kaufland\Controller\Adminhtml\Kaufland\AbstractSettings
{
    protected function getLayoutType()
    {
        return self::LAYOUT_TWO_COLUMNS;
    }

    public function execute()
    {
        /** @var \M2E\Kaufland\Block\Adminhtml\Settings\Tabs $tabsBlock */
        $tabsBlock = $this->getLayout()->createBlock(
            \M2E\Kaufland\Block\Adminhtml\Settings\Tabs::class,
        );

        if ($this->isAjax()) {
            $this->setAjaxContent(
                $tabsBlock->getTabContent($tabsBlock->getActiveTab())
            );

            return $this->getResult();
        }

        $this->addLeft($tabsBlock);
        $this->addContent($this->getLayout()->createBlock(\M2E\Kaufland\Block\Adminhtml\Settings::class));

        $this->getResult()->getConfig()->getTitle()->prepend(__('Settings'));

        return $this->getResult();
    }
}
