<?php

declare(strict_types=1);

namespace M2E\Kaufland\Block\Adminhtml\Listing\Wizard\Description;

class View extends \M2E\Kaufland\Block\Adminhtml\Magento\AbstractContainer
{
    use \M2E\Kaufland\Block\Adminhtml\Listing\Wizard\WizardTrait;

    private \M2E\Kaufland\Model\Listing\Ui\RuntimeStorage $uiListingRuntimeStorage;
    private \M2E\Kaufland\Model\Listing\Wizard\Ui\RuntimeStorage $uiWizardRuntimeStorage;

    public function __construct(
        \M2E\Kaufland\Model\Listing\Ui\RuntimeStorage $uiListingRuntimeStorage,
        \M2E\Kaufland\Model\Listing\Wizard\Ui\RuntimeStorage $uiWizardRuntimeStorage,
        \M2E\Kaufland\Block\Adminhtml\Magento\Context\Widget $context,
        array $data = []
    ) {
        $this->uiListingRuntimeStorage = $uiListingRuntimeStorage;
        $this->uiWizardRuntimeStorage = $uiWizardRuntimeStorage;

        parent::__construct($context, $data);
    }

    public function _construct(): void
    {
        parent::_construct();

        $this->setId('DescriptionForListingProducts');

        $urlSave = $this->getUrl(
            '*/listing_wizard_description/save',
            [
                'id' => $this->uiListingRuntimeStorage->getListing()->getId(),
                'wizard_id' => $this->getWizardIdFromRequest(),
            ]
        );

        $this->prepareButtons(
            [
                'class' => 'action-primary forward',
                'label' => __('Continue'),
                'onclick' => 'KauflandListingSettingsObj.saveClick(\'' . $urlSave . '\')',
            ],
            $this->uiWizardRuntimeStorage->getManager()
        );
    }

    protected function _toHtml()
    {
        $block = $this
            ->getLayout()
            ->createBlock(
                \M2E\Kaufland\Block\Adminhtml\Kaufland\Listing\Edit::class,
                '',
                [
                    'listing' => $this->uiListingRuntimeStorage->getListing(),
                    'isDescriptionRequired' => true,
                ],
            );

        return parent::_toHtml()
            . $block->toHtml();
    }
}
