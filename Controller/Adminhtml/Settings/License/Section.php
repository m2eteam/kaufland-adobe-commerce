<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Settings\License;

class Section extends \M2E\Kaufland\Controller\Adminhtml\AbstractBase
{
    public function execute()
    {
        $content = $this->getLayout()
                        ->createBlock(\M2E\Kaufland\Block\Adminhtml\System\Config\Sections\License::class);
        $this->setAjaxContent($content);

        return $this->getResult();
    }
}
