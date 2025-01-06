<?php

declare(strict_types=1);

namespace M2E\Kaufland\Block\Adminhtml\Category\Chooser;

class Prepare extends \M2E\Kaufland\Block\Adminhtml\Magento\AbstractBlock
{
    private \M2E\Kaufland\Model\Listing\Ui\RuntimeStorage $uiListingRuntimeStorage;

    public function __construct(
        \M2E\Kaufland\Model\Listing\Ui\RuntimeStorage $uiListingRuntimeStorage,
        \M2E\Kaufland\Block\Adminhtml\Magento\Context\Template $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->uiListingRuntimeStorage = $uiListingRuntimeStorage;
    }

    public function _construct(): void
    {
        parent::_construct();
        $this->setTemplate('category/chooser/prepare.phtml');

        $this->jsPhp->addConstants(
            \M2E\Kaufland\Helper\Data::getClassConstants(\M2E\Kaufland\Model\Category\Dictionary::class),
        );

        $urlBuilder = $this->_urlBuilder;

        $this->jsUrl->addUrls(
            [
                'kaufland_category/editCategory' => $urlBuilder->getUrl(
                    '*/kaufland_category/editCategory'
                ),
                'kaufland_category/getCategoryAttributesHtml' => $urlBuilder->getUrl(
                    '*/kaufland_category/getCategoryAttributesHtml'
                ),
                'kaufland_category/getChildCategories' => $urlBuilder->getUrl(
                    '*/kaufland_category/getChildCategories'
                ),
                'kaufland_category/getChooserEditHtml' => $urlBuilder->getUrl(
                    '*/kaufland_category/getChooserEditHtml'
                ),
                'kaufland_category/getCountsOfAttributes' => $urlBuilder->getUrl(
                    '*/kaufland_category/getCountsOfAttributes'
                ),
                'kaufland_category/getEditedCategoryInfo' => $urlBuilder->getUrl(
                    '*/kaufland_category/getEditedCategoryInfo'
                ),
                'kaufland_category/getRecent' => $urlBuilder->getUrl(
                    '*/kaufland_category/getRecent'
                ),
                'kaufland_category/getSelectedCategoryDetails' => $urlBuilder->getUrl(
                    '*/kaufland_category/getSelectedCategoryDetails'
                ),
                'kaufland_category/saveCategoryAttributes' => $urlBuilder->getUrl(
                    '*/kaufland_category/saveCategoryAttributes'
                ),
                'kaufland_category/saveCategoryAttributesAjax' => $urlBuilder->getUrl(
                    '*/kaufland_category/saveCategoryAttributesAjax'
                ),
            ],
        );

        $this->jsTranslator->addTranslations([
            'Select' => __('Select'),
            'Reset' => __('Reset'),
            'No recently used Categories' => __('No recently used Categories'),
            'Change Category' => __('Change Category'),
            'Edit' => __('Edit'),
            'Category' => __('Category'),
            'Not Selected' => __('Not Selected'),
            'No results' => __('No results'),
            'No saved Categories' => __('No saved Categories'),
            'Category Settings' => __('Category Settings'),
            'Specifics' => __('Specifics'),
        ]);
    }

    public function getAccountId(): int
    {
        return $this->uiListingRuntimeStorage->getListing()->getAccountId();
    }

    public function getStorefrontId(): int
    {
        return $this->uiListingRuntimeStorage->getListing()->getStorefrontId();
    }

    public function getSearchUrl(): string
    {
        return $this->getUrl('*/category/search');
    }
}
