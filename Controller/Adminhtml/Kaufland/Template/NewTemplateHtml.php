<?php

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Template;

class NewTemplateHtml extends \M2E\Kaufland\Controller\Adminhtml\Kaufland\AbstractTemplate
{
    public function execute()
    {
        $nick = $this->getRequest()->getParam('nick');

        $this->setAjaxContent(
            $this->getLayout()->createBlock(
                \M2E\Kaufland\Block\Adminhtml\Kaufland\Listing\Template\NewTemplate\Form::class
            )
                 ->setData('nick', $nick)
        );

        return $this->getResult();
    }
}
