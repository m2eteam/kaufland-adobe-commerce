<?php

namespace M2E\Kaufland\Controller\Adminhtml\Product\Unmanaged\Moving;

class PrepareMoveToListing extends \M2E\Kaufland\Controller\Adminhtml\AbstractListing
{
    private \M2E\Kaufland\Helper\Data\Session $sessionHelper;
    private \M2E\Kaufland\Model\Listing\Other\Repository $otherRepository;

    public function __construct(
        \M2E\Kaufland\Model\Listing\Other\Repository $otherRepository,
        \M2E\Kaufland\Helper\Data\Session $sessionHelper,
        \M2E\Kaufland\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($context);
        $this->sessionHelper = $sessionHelper;
        $this->otherRepository = $otherRepository;
    }

    public function execute()
    {
        $accountId = (int)$this->getRequest()->getParam('account_id');
        $selectedProductsIds = (array)$this->getRequest()->getParam('other_product_ids');

        $sessionKey = \M2E\Kaufland\Helper\View::MOVING_LISTING_OTHER_SELECTED_SESSION_KEY;
        $this->sessionHelper->setValue($sessionKey, $selectedProductsIds);

        $row = $this->otherRepository->findStorefrontIdByUnmanagedIdsAndAccount($selectedProductsIds, $accountId);

        if ($row !== false) {
            $response = [
                'result' => true,
                'storefrontId' => (int)$row['storefront_id']
            ];
        } else {
            $response = [
                'result' => false,
                'message' => __('Magento product not found. Please reload the page.'),
            ];
        }

        $this->setJsonContent($response);

        return $this->getResult();
    }
}
