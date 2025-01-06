<?php

declare(strict_types=1);

namespace M2E\Kaufland\Block\Adminhtml\Listing\Wizard;

trait ReviewTrait
{
    private function addGoToListingButton(): void
    {
        $buttonBlock = $this->getLayout()->createBlock(\M2E\Kaufland\Block\Adminhtml\Magento\Button::class)
                            ->setData(
                                [
                                    'id' => __('go_to_the_listing'),
                                    'label' => __('Go To The Listing'),
                                    'onclick' => 'setLocation(\'' . $this->generateCompleteUrl(false, $this->generateListingViewUrl(false)) . '\');',
                                    'class' => 'primary',
                                ],
                            );

        $this->setChild('go_to_listing', $buttonBlock);
    }

    private function generateListingViewUrl(bool $listProducts): string
    {
        $params = [
            'id' => $this->uiWizardRuntimeStorage->getManager()->getListing()->getId(),
        ];

        if ($listProducts) {
            $params['do_list'] = true;
        }

        return $this->getUrl(
            '*/kaufland_listing/view',
            $params,
        );
    }

    private function generateCompleteUrl(bool $listProducts, string $nextUrl): string
    {
        $params = [
            'id' => $this->uiWizardRuntimeStorage->getManager()->getWizardId(),
            'next_url' => base64_encode($nextUrl),
        ];
        if ($listProducts) {
            $params['do_list'] = true;
        }

        return $this->getUrl(
            '*/listing_wizard_review/complete',
            $params,
        );
    }
}
