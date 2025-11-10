<?php

declare(strict_types=1);

namespace M2E\Kaufland\Block\Adminhtml\Listing\AutoAction\Mode;

abstract class AbstractGlobalMode extends \M2E\Kaufland\Block\Adminhtml\Magento\Form\AbstractForm
{
    public array $formData = [];

    private \M2E\Kaufland\Model\Listing $listing;

    public function __construct(
        \M2E\Kaufland\Model\Listing $listing,
        \M2E\Kaufland\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        $this->listing = $listing;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    public function _construct()
    {
        parent::_construct();

        $this->setId('listingAutoActionModeGlobal');
        $this->formData = $this->getFormData();
    }

    public function hasFormData(): bool
    {
        return $this->listing->getData('auto_mode') == \M2E\Kaufland\Model\Listing::AUTO_MODE_GLOBAL;
    }

    public function getFormData(): array
    {
        $formData = $this->listing->getData();
        $default = $this->getDefault();

        return array_merge($default, $formData);
    }

    public function getDefault(): array
    {
        return [
            'auto_global_adding_mode' => \M2E\Kaufland\Model\Listing::ADDING_MODE_ADD,
            'auto_global_adding_add_not_visible' => \M2E\Kaufland\Model\Listing::AUTO_ADDING_ADD_NOT_VISIBLE_YES,
            'auto_global_deleting_mode' => \M2E\Kaufland\Model\Listing::DELETING_MODE_STOP_REMOVE,
        ];
    }

    protected function _afterToHtml($html)
    {
        $this->jsPhp->addConstants(
            \M2E\Kaufland\Helper\Data::getClassConstants(\M2E\Kaufland\Model\Listing::class)
        );

        $hasFormData = $this->hasFormData() ? 'true' : 'false';

        $this->js->add(
            <<<JS
        $('auto_global_adding_mode')
            .observe('change', ListingAutoActionObj.addingModeChange)
            .simulate('change');

        if ({$hasFormData}) {
            $('global_reset_button').show();
        }
JS
        );

        return parent::_afterToHtml($html);
    }

    protected function _toHtml()
    {
        $title = sprintf(
            '<div id="additional_autoaction_title_text" style="display: none">%s</div>',
            $this->getBlockTitle()
        );

        $content = sprintf(
            '<div id="block-content-wrapper"><div id="data_container">%s</div></div>',
            parent::_toHtml()
        );

        return $title . $content;
    }

    // ---------------------------------------

    private function getBlockTitle(): string
    {
        return (string)__('Global all Products');
    }
}
