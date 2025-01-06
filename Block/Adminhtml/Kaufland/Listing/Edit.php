<?php

namespace M2E\Kaufland\Block\Adminhtml\Kaufland\Listing;

class Edit extends \M2E\Kaufland\Block\Adminhtml\Magento\Form\AbstractContainer
{
    private \M2E\Kaufland\Model\Listing $listing;
    private \M2E\Core\Helper\Url $urlHelper;
    private bool $isDescriptionRequired;

    public function __construct(
        \M2E\Core\Helper\Url $urlHelper,
        \M2E\Kaufland\Model\Listing $listing,
        \M2E\Kaufland\Block\Adminhtml\Magento\Context\Widget $context,
        bool $isDescriptionRequired = false,
        array $data = []
    ) {
        $this->urlHelper = $urlHelper;
        $this->listing = $listing;
        $this->isDescriptionRequired = $isDescriptionRequired;
        parent::__construct($context, $data);
    }

    public function _construct()
    {
        parent::_construct();

        $this->setId('kauflandListingEdit');
        $this->_controller = 'adminhtml_kaufland_listing';
        $this->_mode = 'create_templates';

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        if ($this->getRequest()->getParam('back')) {
            $url = $this->urlHelper->getBackUrl();
            $this->addButton(
                'back',
                [
                    'label' => __('Back'),
                    'onclick' => 'KauflandListingSettingsObj.backClick(\'' . $url . '\')',
                    'class' => 'back',
                ]
            );
        }

        $backUrl = $this->urlHelper->getBackUrlParam('list');

        $url = $this->getUrl(
            '*/kaufland_listing/save',
            [
                'id' => $this->listing->getId(),
                'back' => $backUrl,
            ]
        );
        $saveButtonsProps = [
            'save' => [
                'label' => __('Save And Back'),
                'onclick' => 'KauflandListingSettingsObj.saveClick(\'' . $url . '\')',
                'class' => 'save primary',
            ],
        ];

        $editBackUrl = $this->urlHelper->makeBackUrlParam(
            $this->getUrl(
                '*/kaufland_listing/edit',
                [
                    'id' => $this->listing->getId(),
                    'back' => $backUrl,
                ]
            )
        );
        $url = $this->getUrl(
            '*/kaufland_listing/save',
            [
                'id' => $this->listing->getId(),
                'back' => $editBackUrl,
            ]
        );
        $saveButtons = [
            'id' => 'save_and_continue',
            'label' => __('Save And Continue Edit'),
            'class' => 'add',
            'button_class' => '',
            'onclick' => 'KauflandListingSettingsObj.saveAndEditClick(\'' . $url . '\', 1)',
            'class_name' => \M2E\Kaufland\Block\Adminhtml\Magento\Button\SplitButton::class,
            'options' => $saveButtonsProps,
        ];

        $this->addButton('save_buttons', $saveButtons);
    }

    protected function _prepareLayout()
    {
        $this->getRequest()->setParam('id', $this->listing->getId());
        $this->getRequest()->setParam('isDescriptionRequired', $this->isDescriptionRequired);

        return parent::_prepareLayout();
    }
}
