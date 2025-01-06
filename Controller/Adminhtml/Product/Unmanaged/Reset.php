<?php

namespace M2E\Kaufland\Controller\Adminhtml\Product\Unmanaged;

class Reset extends \M2E\Kaufland\Controller\Adminhtml\Kaufland\AbstractListing
{
    private \M2E\Kaufland\Model\Listing\Other\Reset $listingOtherReset;
    private \M2E\Kaufland\Model\Account\Repository $accountRepository;

    public function __construct(
        \M2E\Kaufland\Model\Listing\Other\Reset $listingOtherReset,
        \M2E\Kaufland\Model\Account\Repository $accountRepository
    ) {
        parent::__construct();
        $this->accountRepository = $accountRepository;
        $this->listingOtherReset = $listingOtherReset;
    }

    public function execute()
    {
        $accountId = (int)$this->getRequest()->getParam('account_id');
        try {
            $account = $this->accountRepository->get($accountId);
        } catch (\Throwable $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());

            return $this->_redirect('*/product_grid/unmanaged');
        }

        $this->listingOtherReset->process($account);

        $this->messageManager->addSuccessMessage(
            __('Unmanaged Listings were reset.')
        );

        return $this->_redirect('*/product_grid/unmanaged', ['account' => $accountId]);
    }
}
