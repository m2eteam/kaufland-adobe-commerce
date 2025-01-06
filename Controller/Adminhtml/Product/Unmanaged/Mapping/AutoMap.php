<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Product\Unmanaged\Mapping;

class AutoMap extends \M2E\Kaufland\Controller\Adminhtml\AbstractListing
{
    private \M2E\Kaufland\Model\Listing\Other\Repository $listingOtherRepository;
    private \M2E\Kaufland\Model\Listing\Other\MappingService $mappingService;
    private \Magento\Ui\Component\MassAction\Filter $massActionFilter;

    public function __construct(
        \Magento\Ui\Component\MassAction\Filter $massActionFilter,
        \M2E\Kaufland\Model\Listing\Other\MappingService $mappingService,
        \M2E\Kaufland\Model\Listing\Other\Repository $listingOtherRepository,
        $context = null
    ) {
        parent::__construct($context);
        $this->listingOtherRepository = $listingOtherRepository;
        $this->mappingService = $mappingService;
        $this->massActionFilter = $massActionFilter;
    }

    public function execute()
    {
        $products = $this->listingOtherRepository->findForAutoMappingByMassActionSelectedProducts(
            $this->massActionFilter
        );

        if (empty($products)) {
            $this->getMessageManager()->addErrorMessage('You should select one or more Products');

            return $this->_redirect('*/product_grid/unmanaged/');
        }

        if (!$this->mappingService->autoMapOtherListingsProducts($products)) {
            $this->getMessageManager()->addErrorMessage(
                'Some Items were not linked. Please edit Product Linking Settings under Configuration > Account > Unmanaged Listings or try to link manually.'
            );

            return $this->_redirect('*/product_grid/unmanaged/');
        }

        return $this->_redirect('*/product_grid/unmanaged/');
    }
}
