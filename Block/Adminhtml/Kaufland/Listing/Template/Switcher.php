<?php

declare(strict_types=1);

namespace M2E\Kaufland\Block\Adminhtml\Kaufland\Listing\Template;

class Switcher extends \M2E\Kaufland\Block\Adminhtml\Magento\AbstractBlock
{
    private \M2E\Kaufland\Helper\Data\GlobalData $globalDataHelper;

    public function __construct(
        \M2E\Kaufland\Helper\Data\GlobalData $globalDataHelper,
        \M2E\Kaufland\Block\Adminhtml\Magento\Context\Template $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->globalDataHelper = $globalDataHelper;
    }

    public function _construct()
    {
        $this->setId('kauflandListingTemplateSwitcher');
        parent::_construct();
    }

    // ----------------------------------------

    public function getTemplateNick(): string
    {
        if (!isset($this->_data['template_nick'])) {
            throw new \M2E\Kaufland\Model\Exception\Logic('Template nick is not defined.');
        }

        return (string)$this->_data['template_nick'];
    }

    public function getTemplateObject()
    {
        $template = $this->globalDataHelper
            ->getValue('kaufland_template_' . $this->getTemplateNick());

        if ($template !== null && $template->getId() !== null) {
            return $template;
        }

        return null;
    }

    // ---------------------------------------

    public function getFormDataBlockHtml($templateDataForce = false): string
    {
        $nick = $this->getTemplateNick();

        if ($this->isTemplateModeCustom() || $templateDataForce) {
            $formHtml = $this->getFormDataBlock()->toHtml();
            $style = '';
        } else {
            $formHtml = '';
            $style = 'display: none;';
        }

        $html = sprintf(
            '<div id="template_%s_data_container" class="template-data-container" style="%s">',
            $nick,
            $style
        );

        $html .= $formHtml;
        $html .= '</div>';

        return $html;
    }

    // ----------------------------------------

    private function isTemplateModeCustom(): bool
    {
        return $this->getTemplateMode() === \M2E\Kaufland\Model\Template\Manager::MODE_CUSTOM;
    }

    private function getFormDataBlock(): \Magento\Framework\View\Element\BlockInterface
    {
        $blockName = null;

        switch ($this->getTemplateNick()) {
            case \M2E\Kaufland\Model\Template\Manager::TEMPLATE_SELLING_FORMAT:
                $blockName = \M2E\Kaufland\Block\Adminhtml\Kaufland\Template\SellingFormat\Edit\Form\Data::class;
                break;
            case \M2E\Kaufland\Model\Template\Manager::TEMPLATE_SYNCHRONIZATION:
                $blockName = \M2E\Kaufland\Block\Adminhtml\Kaufland\Template\Synchronization\Edit\Form\Data::class;
                break;
            case \M2E\Kaufland\Model\Template\Manager::TEMPLATE_SHIPPING:
                $blockName = \M2E\Kaufland\Block\Adminhtml\Kaufland\Template\Shipping\Edit\Form\Data::class;
                break;
            case \M2E\Kaufland\Model\Template\Manager::TEMPLATE_DESCRIPTION:
                $blockName = \M2E\Kaufland\Block\Adminhtml\Kaufland\Template\Description\Edit\Form\Data::class;
                break;
        }

        if ($blockName === null) {
            throw new \M2E\Kaufland\Model\Exception\Logic(
                sprintf('Form data Block for Template nick "%s" is unknown.', $this->getTemplateNick())
            );
        }

        $parameters = [
            'is_custom' => false,
            'custom_title' => $this->globalDataHelper->getValue('kaufland_custom_template_title'),
            'policy_localization' => $this->getData('policy_localization'),
        ];

        return $this->getLayout()->createBlock($blockName, '', ['data' => $parameters]);
    }

    private function getTemplateMode(): int
    {
        $templateMode = $this->globalDataHelper
            ->getValue('kaufland_template_mode_' . $this->getTemplateNick());

        if ($templateMode === null) {
            throw new \M2E\Kaufland\Model\Exception\Logic('Template Mode is not initialized.');
        }

        return (int)$templateMode;
    }
}
