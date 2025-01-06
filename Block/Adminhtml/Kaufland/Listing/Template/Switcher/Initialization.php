<?php

namespace M2E\Kaufland\Block\Adminhtml\Kaufland\Listing\Template\Switcher;

class Initialization extends \M2E\Kaufland\Block\Adminhtml\Magento\AbstractBlock
{
    /** @var \M2E\Core\Helper\Data */
    private $dataHelper;
    /** @var \M2E\Kaufland\Helper\Data\GlobalData */
    private $globalDataHelper;

    public function __construct(
        \M2E\Kaufland\Block\Adminhtml\Magento\Context\Template $context,
        \M2E\Core\Helper\Data $dataHelper,
        \M2E\Kaufland\Helper\Data\GlobalData $globalDataHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->dataHelper = $dataHelper;
        $this->globalDataHelper = $globalDataHelper;
    }

    public function _construct()
    {
        parent::_construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('KauflandListingTemplateSwitcherInitialization');
        // ---------------------------------------
    }

    protected function _toHtml()
    {
        // ---------------------------------------
        $urls = [];

        // initiate account param
        // ---------------------------------------
        $account = $this->globalDataHelper->getValue('kaufland_account');
        $params['account_id'] = $account->getId();

        // initiate attribute sets param
        // ---------------------------------------
        if (
            $this->getMode(
            ) == \M2E\Kaufland\Block\Adminhtml\Kaufland\Listing\Template\Switcher::MODE_LISTING_PRODUCT
        ) {
            $attributeSets = $this->globalDataHelper->getValue('kaufland_attribute_sets');
            $params['attribute_sets'] = implode(',', $attributeSets);
        }
        // ---------------------------------------

        // initiate display use default option param
        // ---------------------------------------
        $displayUseDefaultOption = $this->globalDataHelper->getValue('kaufland_display_use_default_option');
        $params['display_use_default_option'] = (int)(bool)$displayUseDefaultOption;
        // ---------------------------------------

        $path = 'kaufland_template/getTemplateHtml';
        $urls[$path] = $this->getUrl('*/' . $path, $params);
        //------------------------------

        //------------------------------
        $path = 'kaufland_template/isTitleUnique';
        $urls[$path] = $this->getUrl('*/' . $path);

        $path = 'kaufland_template/newTemplateHtml';
        $urls[$path] = $this->getUrl('*/' . $path);

        $path = 'kaufland_template/edit';
        $urls[$path] = $this->getUrl(
            '*/kaufland_template/edit',
            ['wizard' => (bool)$this->getRequest()->getParam('wizard', false)]
        );
        //------------------------------

        $this->jsUrl->addUrls($urls);
        $this->jsUrl->add(
            $this->getUrl(
                '*/template/checkMessages',
                ['component_mode' => \M2E\Kaufland\Helper\Component\Kaufland::NICK]
            ),
            'templateCheckMessages'
        );

        $this->jsPhp->addConstants(
            \M2E\Kaufland\Helper\Data::getClassConstants(\M2E\Kaufland\Model\Kaufland\Template\Manager::class)
        );

        $this->jsTranslator->addTranslations([
            'Customized' => __('Customized'),
            'Policies' => __('Policies'),
            'Policy with the same Title already exists.' => __('Policy with the same Title already exists.'),
            'Please specify Policy Title' => __('Please specify Policy Title'),
            'Save New Policy' => __('Save New Policy'),
            'Save as New Policy' => __('Save as New Policy'),
        ]);

        $store = $this->globalDataHelper->getValue('kaufland_store');

        $this->js->add(
            <<<JS
    define('Switcher/Initialization',[
        'Kaufland/Kaufland/Listing/Template/Switcher',
        'Kaufland/TemplateManager'
    ], function(){
        window.TemplateManagerObj = new TemplateManager();

        window.KauflandListingTemplateSwitcherObj = new KauflandListingTemplateSwitcher();
        KauflandListingTemplateSwitcherObj.storeId = {$store->getId()};
        KauflandListingTemplateSwitcherObj.listingProductIds = '{$this->getRequest()->getParam('ids')}';

    });
JS
        );

        return parent::_toHtml();
    }
}
