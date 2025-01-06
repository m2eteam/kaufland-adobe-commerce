<?php

declare(strict_types=1);

namespace M2E\Kaufland\Block\Adminhtml\Kaufland\Template\Category\View;

class Info extends \M2E\Kaufland\Block\Adminhtml\Widget\Info
{
    private \M2E\Kaufland\Model\Category\Dictionary $dictionary;

    public function __construct(
        \M2E\Kaufland\Model\Category\Dictionary $dictionary,
        \Magento\Framework\Math\Random $random,
        \M2E\Kaufland\Block\Adminhtml\Magento\Context\Template $context,
        array $data = []
    ) {
        parent::__construct($random, $context, $data);

        $this->dictionary = $dictionary;
    }

    protected function _prepareLayout()
    {
        $this->setInfo(
            [
                [
                    'label' => __('Storefront'),
                    'value' => $this->dictionary->getStorefront()->getTitle(),
                ],
                [
                    'label' => __('Category'),
                    'value' => $this->dictionary->getPathWithCategoryId(),
                ],
            ]
        );

        return parent::_prepareLayout();
    }

    /*
     * To get "Category" block in center of screen
     */
    public function getInfoPartWidth($index)
    {
        if ($index === 0) {
            return '33%';
        }

        return '66%';
    }

    public function getInfoPartAlign($index)
    {
        return 'left';
    }
}
