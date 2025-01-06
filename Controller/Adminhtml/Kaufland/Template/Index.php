<?php

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Template;

class Index extends \M2E\Kaufland\Controller\Adminhtml\Kaufland\AbstractTemplate
{
    public function execute()
    {
        $content = $this->getLayout()->createBlock(\M2E\Kaufland\Block\Adminhtml\Kaufland\Template::class);

        $this->getResult()->getConfig()->getTitle()->prepend('Policies');
        $this->addContent($content);

        return $this->getResult();
    }
}
