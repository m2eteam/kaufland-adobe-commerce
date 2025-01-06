<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\ControlPanel;

class Index extends AbstractMain
{
    public function execute()
    {
        $this->init();

        $block = $this->getLayout()->createBlock(\M2E\Kaufland\Block\Adminhtml\ControlPanel\Tabs::class, '');
        $block->setData('tab', 'summary');
        $this->addContent($block);

        return $this->getResult();
    }
}
