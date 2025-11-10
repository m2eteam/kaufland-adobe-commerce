<?php

declare(strict_types=1);

namespace M2E\Kaufland\Block\Adminhtml\Kaufland\Listing\AutoAction\Mode;

class Category extends \M2E\Kaufland\Block\Adminhtml\Listing\AutoAction\Mode\AbstractCategory
{
    protected function prepareGroupsGrid(): \M2E\Kaufland\Block\Adminhtml\Listing\AutoAction\Mode\Category\Group\AbstractGrid
    {
        $groupGrid = $this
            ->getLayout()
            ->createBlock(
                \M2E\Kaufland\Block\Adminhtml\Kaufland\Listing\AutoAction\Mode\Category\Group\Grid::class
            )
            ->prepareGrid();

        $this->setChild('group_grid', $groupGrid);

        return $groupGrid;
    }

    //########################################

    protected function _afterToHtml($html)
    {
        $this->jsPhp->addConstants(
            \M2E\Kaufland\Helper\Data::getClassConstants(\M2E\Kaufland\Model\Listing::class)
        );

        return parent::_afterToHtml($html);
    }

    protected function _toHtml()
    {
        $helpBlockContent = (string)__(
            '<p>These rules apply when Products are added to or removed from the selected ' .
            'Magento Categories available for the Store View of this Listing.</p><br>' .
            '<p>If automatic adding is enabled, Products added to the selected Categories will be ' .
            'automatically added to this Listing.</p><br>' .
            '<p>Combine Magento Categories into groups to apply Auto Add/Remove Rules. You can create multiple ' .
            'groups, but each Category can belong to only one Rule.</p><br>' .
            '<p>Products already listed under the same Channel account and storefront ' .
            'won’t be added again to avoid duplicates.</p><br>' .
            '<p>If a Product in this Listing is removed from the selected Category, it will also be removed ' .
            'from the Listing and its sale on the Channel will stop.</p><br>' .
            '<p>For more details, see the <a href="%url" target="_blank">documentation</a>.</p>',
            ['url' => 'https://docs-m2.m2epro.com/docs/kaufland-auto-add-remove-rules/']
        );

        $helpBlock = $this
            ->getLayout()
            ->createBlock(\M2E\Kaufland\Block\Adminhtml\HelpBlock::class)
            ->setData(['content' => $helpBlockContent]);

        return $helpBlock->toHtml() . parent::_toHtml();
    }
}
