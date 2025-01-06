<?php

namespace M2E\Kaufland\Block\Adminhtml\Traits;

trait BlockTrait
{
    protected function getBlockClass($block)
    {
        // fix for Magento2 sniffs that forcing to use ::class
        $block = str_replace('_', '\\', $block);

        return 'M2E\Kaufland\Block\Adminhtml\\' . $block;
    }

    /**
     * @param $block
     * @param $name
     * @param $arguments
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    public function createBlock($block, $name = '', array $arguments = [])
    {
        return $this->getLayout()->createBlock($this->getBlockClass($block), $name, $arguments);
    }

    public function __(...$args): string
    {
        return (string)__(...$args);
    }

    public function getTooltipHtml($content, $directionToRight = false, array $customClasses = [])
    {
        $directionToRightClass = $directionToRight ? 'Kaufland-field-tooltip-right' : '';

        $customClasses = !empty($customClasses) ? implode(' ', $customClasses) : '';

        return <<<HTML
<div class="Kaufland-field-tooltip admin__field-tooltip {$directionToRightClass} {$customClasses}">
    <a class="admin__field-tooltip-action" href="javascript://"></a>
    <div class="admin__field-tooltip-content">
        {$content}
    </div>
</div>
HTML;
    }

    public function appendHelpBlock($data)
    {
        return $this->getLayout()->addBlock($this->getBlockClass('HelpBlock'), '', 'main.top')->setData($data);
    }

    /**
     * @param $block
     * @param string $name
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function setPageActionsBlock($block, $name = '')
    {
        return $this->getLayout()->addBlock($this->getBlockClass($block), $name, 'page.main.actions');
    }
}
