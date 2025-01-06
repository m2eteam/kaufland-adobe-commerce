<?php

namespace M2E\Kaufland\Block\Adminhtml\Log\Order;

abstract class AbstractContainer extends \M2E\Kaufland\Block\Adminhtml\Magento\Grid\AbstractContainer
{
    public function _construct()
    {
        parent::_construct();

        $this->_controller = 'adminhtml_Kaufland_log_order';

        $this->setId('KauflandOrderLog');

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');
    }

    protected function _toHtml()
    {
        $filtersHtml = $this->getFiltersHtml();

        if (empty($filtersHtml)) {
            return parent::_toHtml();
        }

        $filtersHtml = <<<HTML
<div class="page-main-actions">
    <div class="filter_block">
        {$filtersHtml}
    </div>
</div>
HTML;

        return $filtersHtml . parent::_toHtml();
    }

    protected function getFiltersHtml()
    {
        return $this->createAccountSwitcherBlock()->toHtml()
            . $this->createUniqueMessageFilterBlock()->toHtml();
    }

    protected function getStaticFilterHtml(string $label, string $value): string
    {
        return <<<HTML
<p class="static-switcher">
    <span>$label:</span>
    <span>$value</span>
</p>
HTML;
    }

    protected function createAccountSwitcherBlock()
    {
        return $this->getLayout()->createBlock(\M2E\Kaufland\Block\Adminhtml\Account\Switcher::class);
    }

    protected function createUniqueMessageFilterBlock()
    {
        return $this->getLayout()->createBlock(\M2E\Kaufland\Block\Adminhtml\Log\UniqueMessageFilter::class)->setData(
            [
                'route' => "*/kaufland_log_order/",
                'title' => __('Only messages with a unique Order ID'),
            ]
        );
    }
}
