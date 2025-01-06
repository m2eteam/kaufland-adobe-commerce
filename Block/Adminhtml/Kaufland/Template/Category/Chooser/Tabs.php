<?php

declare(strict_types=1);

namespace M2E\Kaufland\Block\Adminhtml\Kaufland\Template\Category\Chooser;

class Tabs extends \M2E\Kaufland\Block\Adminhtml\Magento\Tabs\AbstractHorizontalTabs
{
    private const TAB_ID_RECENT = 'recent';
    private const TAB_ID_BROWSE = 'browse';
    private const TAB_ID_SEARCH = 'search';

    public function _construct()
    {
        parent::_construct();
        $this->setId('kauflandTemplateCategoryChooserTabs');
        $this->setDestElementId('chooser_tabs_container');
    }

    protected function _prepareLayout()
    {
        $this->addTab(self::TAB_ID_RECENT, [
            'label' => __('Saved Categories'),
            'title' => __('Saved Categories'),
            'content' => $this->getLayout()->createBlock(
                \M2E\Kaufland\Block\Adminhtml\Kaufland\Template\Category\Chooser\Tabs\Recent::class,
            )->toHtml(),
            'active' => true,
        ]);

        $browseContent = $this->getLayout()->createBlock(
            \M2E\Kaufland\Block\Adminhtml\Kaufland\Template\Category\Chooser\Tabs\Browse::class,
        )->toHtml();
        $this->addTab(self::TAB_ID_BROWSE, [
            'label' => __('Browse'),
            'title' => __('Browse'),
            'content' => $browseContent,
            'active' => false,
        ]);

        $searchContent = $this->getLayout()->createBlock(
            \M2E\Kaufland\Block\Adminhtml\Category\Chooser\Tab\Search::class,
        )->toHtml();
        $this->addTab(self::TAB_ID_SEARCH, [
            'label' => __('Search'),
            'title' => __('Search'),
            'content' => $searchContent,
            'active' => false,
        ]);

        return parent::_prepareLayout();
    }
}
