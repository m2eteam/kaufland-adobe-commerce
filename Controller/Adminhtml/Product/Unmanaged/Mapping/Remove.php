<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Product\Unmanaged\Mapping;

class Remove extends \M2E\Kaufland\Controller\Adminhtml\AbstractListing
{
    private \M2E\Kaufland\Model\Listing\Other\Repository $listingOtherRepository;
    private \Magento\Ui\Component\MassAction\Filter $massActionFilter;
    private \M2E\Kaufland\Model\Listing\Other\RemoveFromChanel $removeFromChanel;

    public function __construct(
        \Magento\Ui\Component\MassAction\Filter $massActionFilter,
        \M2E\Kaufland\Model\Listing\Other\Repository $listingOtherRepository,
        \M2E\Kaufland\Model\Listing\Other\RemoveFromChanel $removeFromChanel,
        $context = null
    ) {
        parent::__construct($context);
        $this->listingOtherRepository = $listingOtherRepository;
        $this->massActionFilter = $massActionFilter;
        $this->removeFromChanel = $removeFromChanel;
    }

    public function execute()
    {
        $accountId = (int)$this->getRequest()->getParam('account_id');

        $products = $this->listingOtherRepository
            ->findForRemoveByMassActionSelectedProducts($this->massActionFilter, $accountId);

        if (!empty($products)) {
            $this->removeFromChanel->execute($products);
        }

        return $this->_redirect('*/product_grid/unmanaged/', ['account' => $accountId]);
    }
}
