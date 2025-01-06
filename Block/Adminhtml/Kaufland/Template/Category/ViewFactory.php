<?php

declare(strict_types=1);

namespace M2E\Kaufland\Block\Adminhtml\Kaufland\Template\Category;

class ViewFactory
{
    public function create(
        \Magento\Framework\View\LayoutInterface $layout,
        \M2E\Kaufland\Model\Category\Dictionary $dictionary
    ): View {
        /** @var View $block */
        $block = $layout->createBlock(
            View::class,
            '',
            ['dictionary' => $dictionary]
        );

        return $block;
    }
}
