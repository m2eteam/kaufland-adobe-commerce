<?php

declare(strict_types=1);

namespace M2E\Kaufland\Block\Adminhtml\Kaufland\Listing\Product\Add;

use M2E\Kaufland\Block\Adminhtml\Kaufland\Listing\Product\Add\SourceMode as SourceModeBlock;

class Review extends \M2E\Kaufland\Block\Adminhtml\Magento\AbstractContainer
{
    private ?string $source = null;

    public function _construct(): void
    {
        parent::_construct();

        $this->setId('listingProductReview');
        $this->setTemplate('kaufland/listing/product/review.phtml');
    }

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        // ---------------------------------------
        $url = $this->getUrl('*/kaufland_listing/view', [
            'id' => $this->getRequest()->getParam('id'),
        ]);
        $buttonBlock = $this->getLayout()->createBlock(\M2E\Kaufland\Block\Adminhtml\Magento\Button::class)
                            ->setData([
                                'id' => __('go_to_the_listing'),
                                'label' => __('Go To The Listing'),
                                'onclick' => 'setLocation(\'' . $url . '\');',
                                'class' => 'primary',
                            ]);
        $this->setChild('review', $buttonBlock);
        // ---------------------------------------

        // ---------------------------------------
        $url = $this->getUrl('*/kaufland_listing/view', [
            'id' => $this->getRequest()->getParam('id'),
            'do_list' => true,
        ]);
        $buttonBlock = $this->getLayout()
                            ->createBlock(\M2E\Kaufland\Block\Adminhtml\Magento\Button::class)
                            ->setData([
                                'label' => __('List Added Products Now'),
                                'onclick' => 'setLocation(\'' . $url . '\');',
                                'class' => 'primary',
                            ]);
        if ($this->getRequest()->getParam('disable_list', false)) {
            $buttonBlock->setData('style', 'display: none');
        }

        $this->setChild('save_and_list', $buttonBlock);
        // ---------------------------------------

        // ---------------------------------------
        if ($this->getSource() === SourceModeBlock::MODE_OTHER) {
            $url = $this->getUrl('*/product_grid/unmanaged');
            $buttonBlock = $this->getLayout()->createBlock(\M2E\Kaufland\Block\Adminhtml\Magento\Button::class)
                                ->setData([
                                    'label' => __('Back to Unmanaged Items'),
                                    'onclick' => 'setLocation(\'' . $url . '\');',
                                    'class' => 'primary go',
                                ]);
            $this->setChild('back_to_listing_other', $buttonBlock);
        }
        // ---------------------------------------
    }

    public function setSource(string $value): void
    {
        $this->source = $value;
    }

    public function getSource()
    {
        return $this->source;
    }
}
