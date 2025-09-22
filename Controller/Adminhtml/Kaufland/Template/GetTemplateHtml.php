<?php

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Template;

class GetTemplateHtml extends \M2E\Kaufland\Controller\Adminhtml\Kaufland\AbstractTemplate
{
    /** @var \M2E\Kaufland\Helper\Component\Kaufland\Template\Switcher\DataLoader */
    private $componentKauflandTemplateSwitcherDataLoader;

    public function __construct(
        \M2E\Kaufland\Helper\Component\Kaufland\Template\Switcher\DataLoader $componentKauflandTemplateSwitcherDataLoader,
        \M2E\Kaufland\Model\Template\Manager $templateManager
    ) {
        parent::__construct($templateManager);

        $this->componentKauflandTemplateSwitcherDataLoader = $componentKauflandTemplateSwitcherDataLoader;
    }

    public function execute()
    {
        try {
            $dataLoader = $this->componentKauflandTemplateSwitcherDataLoader;
            $dataLoader->load($this->getRequest());

            $templateNick = $this->getRequest()->getParam('nick');
            $templateDataForce = (bool)$this->getRequest()->getParam('data_force', false);

            /** @var \M2E\Kaufland\Block\Adminhtml\Kaufland\Listing\Template\Switcher $switcherBlock */
            $switcherBlock = $this
                ->getLayout()
                ->createBlock(\M2E\Kaufland\Block\Adminhtml\Kaufland\Listing\Template\Switcher::class);

            $switcherBlock->setData(['template_nick' => $templateNick]);
            // ---------------------------------------

            $this->setAjaxContent($switcherBlock->getFormDataBlockHtml($templateDataForce));
        } catch (\Exception $e) {
            $this->setJsonContent(['error' => $e->getMessage()]);
        }

        return $this->getResult();
    }
}
