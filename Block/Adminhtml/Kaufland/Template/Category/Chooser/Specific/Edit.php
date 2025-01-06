<?php

declare(strict_types=1);

namespace M2E\Kaufland\Block\Adminhtml\Kaufland\Template\Category\Chooser\Specific;

use M2E\Kaufland\Model\Category\Dictionary;

class Edit extends \M2E\Kaufland\Block\Adminhtml\Magento\Form\AbstractContainer
{
    private \M2E\Kaufland\Model\Category\Dictionary $dictionary;
    private \M2E\Kaufland\Block\Adminhtml\Kaufland\Template\Category\DictionaryMapper $dictionaryMapper;

    public function __construct(
        \M2E\Kaufland\Model\Category\Dictionary                                         $dictionary,
        \M2E\Kaufland\Block\Adminhtml\Kaufland\Template\Category\DictionaryMapper $dictionaryMapper,
        \M2E\Kaufland\Block\Adminhtml\Magento\Context\Widget                        $context,
        array                                                                         $data = []
    ) {
        parent::__construct($context, $data);

        $this->dictionary = $dictionary;
        $this->dictionaryMapper = $dictionaryMapper;
    }

    public function _construct(): void
    {
        parent::_construct();

        $this->setId('kauflandTemplateCategoryChooserSpecificEdit');

        $this->_controller = 'adminhtml_Kaufland_template_category_chooser_specific';
        $this->_mode = 'edit';

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');
    }

    public function prepareFormData(): void
    {
        $realAttributes = $this->dictionaryMapper->getProductAttributes($this->dictionary);

        $formData = [
            'real_attributes' => $realAttributes,
        ];

        $this->getChildBlock('form')
             ->setData('form_data', $formData);
    }

    protected function _toHtml()
    {
        $infoBlock = $this->getLayout()->createBlock(
            \M2E\Kaufland\Block\Adminhtml\Kaufland\Template\Category\Chooser\Specific\Info::class,
            '',
            ['data' => ['path' => $this->dictionary->getPathWithCategoryId()]]
        );

        $this->jsTranslator->addTranslations(
            [
                'Item Specifics cannot have the same Labels.' => __(
                    'Item Specifics cannot have the same Labels.'
                ),
            ]
        );
        $this->jsPhp->addConstants(
            [
                '\M2E\Kaufland\Model\Category\Dictionary::VALUE_MODE_KAUFLAND_RECOMMENDED' =>
                    Dictionary::VALUE_MODE_KAUFLAND_RECOMMENDED,
                '\M2E\Kaufland\Model\Category\Dictionary::VALUE_MODE_CUSTOM_VALUE' =>
                    Dictionary::VALUE_MODE_CUSTOM_VALUE,
                '\M2E\Kaufland\Model\Category\Dictionary::VALUE_MODE_CUSTOM_ATTRIBUTE' =>
                    Dictionary::VALUE_MODE_CUSTOM_ATTRIBUTE,
                '\M2E\Kaufland\Model\Category\Dictionary::VALUE_MODE_CUSTOM_LABEL_ATTRIBUTE' =>
                    Dictionary::VALUE_MODE_CUSTOM_LABEL_ATTRIBUTE,
            ]
        );

        $this->js->add(
            <<<JS
    require([
        'Kaufland/Kaufland/Template/Category/Specifics'
    ], function(){
        window.KauflandTemplateCategorySpecificsObj = new KauflandTemplateCategorySpecifics();
    });
JS
        );

        $parentHtml = parent::_toHtml();

        return <<<HTML
<div id="chooser_container_specific">

    <div style="margin-top: 15px;">
        {$infoBlock->_toHtml()}
    </div>

    <div id="Kaufland-category-chooser-specific" overflow: auto;">
        {$parentHtml}
    </div>

</div>
HTML;
    }
}
