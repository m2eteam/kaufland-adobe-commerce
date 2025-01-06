<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Template\Category;

class Index extends \M2E\Kaufland\Controller\Adminhtml\Kaufland\Template\AbstractCategory
{
    public function execute()
    {
        $this->getResultPage()
             ->getConfig()
             ->getTitle()->prepend(__('Categories'));

        $content = $this->getLayout()->createBlock(
            \M2E\Kaufland\Block\Adminhtml\Kaufland\Template\Category::class
        );
        $this->addContent($content);

        return $this->getResultPage();
    }
}
