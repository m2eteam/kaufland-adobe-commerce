<?php

namespace M2E\Kaufland\Block\Adminhtml\Kaufland\Template\Edit;

class Form extends \M2E\Kaufland\Block\Adminhtml\Magento\Form\AbstractForm
{
    /** @var \M2E\Kaufland\Helper\Data\GlobalData */
    private $globalDataHelper;

    public function __construct(
        \M2E\Kaufland\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \M2E\Kaufland\Helper\Data\GlobalData $globalDataHelper,
        array $data = []
    ) {
        $this->globalDataHelper = $globalDataHelper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    public function _construct()
    {
        parent::_construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('KauflandTemplateEditForm');
        // ---------------------------------------

        $this->css->addFile('kaufland/template.css');
    }

    protected function _prepareForm()
    {
        $form = $this->_formFactory->create([
            'data' => [
                'id' => 'edit_form',
                'action' => 'javascript:void(0)',
                'method' => 'post',
                'enctype' => 'multipart/form-data',
            ],
        ]);

        $fieldset = $form->addFieldset(
            'general_fieldset',
            ['legend' => __('General'), 'collapsable' => false]
        );

        $templateData = $this->getTemplateData();

        $fieldset->addField(
            'title',
            'text',
            [
                'name' => 'title',
                'label' => __('Title'),
                'title' => __('Title'),
                'value' => $templateData['title'],
                'class' => 'input-text validate-title-uniqueness',
                'required' => true,
            ]
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return $this;
    }

    public function getTemplateData()
    {
        $nick = $this->getTemplateNick();
        $templateData = $this->globalDataHelper->getValue("kaufland_template_{$nick}");

        return array_merge([
            'title' => '',
        ], $templateData->getData());
    }

    public function getTemplateNick()
    {
        return $this->getParentBlock()->getTemplateNick();
    }

    public function getTemplateId()
    {
        $template = $this->getParentBlock()->getTemplateObject();

        return $template ? $template->getId() : null;
    }

    protected function _toHtml()
    {
        $nick = $this->getTemplateNick();
        $this->jsUrl->addUrls([
            'kaufland_template/getTemplateHtml' => $this->getUrl(
                '*/kaufland_template/getTemplateHtml',
                [
                    'account_id' => null,
                    'id' => $this->getTemplateId(),
                    'nick' => $nick,
                    'mode' => \M2E\Kaufland\Model\Kaufland\Template\Manager::MODE_TEMPLATE,
                    'data_force' => true,
                    'storefront_id' => (int)$this->getRequest()->getParam('storefront_id'),
                ]
            ),
            'kaufland_template/isTitleUnique' => $this->getUrl(
                '*/kaufland_template/isTitleUnique',
                [
                    'id' => $this->getTemplateId(),
                    'nick' => $nick,
                ]
            ),
            'deleteAction' => $this->getUrl(
                '*/Kaufland_template/delete',
                [
                    'id' => $this->getTemplateId(),
                    'nick' => $nick,
                ]
            ),
        ]);

        $this->jsTranslator->addTranslations([
            'Policy Title is not unique.' => __('Policy Title is not unique.'),
            'Do not show any more' => __('Do not show this message anymore'),
            'Save Policy' => __('Save Policy'),
        ]);

        $this->jsPhp->addConstants(
            [
                '\M2E\Kaufland\Model\Kaufland\Template\Manager::TEMPLATE_SYNCHRONIZATION' => \M2E\Kaufland\Model\Kaufland\Template\Manager::TEMPLATE_SYNCHRONIZATION,
            ]
        );

        $this->js->addRequireJs(
            [
                'form' => 'Kaufland/Kaufland/Template/Edit/Form',
                'jquery' => 'jquery',
            ],
            <<<JS

        window.KauflandTemplateEditObj = new KauflandTemplateEdit();
        KauflandTemplateEditObj.templateNick = '{$this->getTemplateNick()}';
        KauflandTemplateEditObj.initObservers();
JS
        );

        return parent::_toHtml();
    }
}
