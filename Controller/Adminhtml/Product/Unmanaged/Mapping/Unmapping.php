<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Product\Unmanaged\Mapping;

class Unmapping extends \M2E\Kaufland\Controller\Adminhtml\AbstractListing
{
    private \M2E\Kaufland\Model\Listing\Other\Repository $listingOtherRepository;
    private \Magento\Ui\Component\MassAction\Filter $massActionFilter;
    private \M2E\Kaufland\Model\Listing\Other\MappingService $mappingService;

    public function __construct(
        \Magento\Ui\Component\MassAction\Filter $massActionFilter,
        \M2E\Kaufland\Model\Listing\Other\Repository $listingOtherRepository,
        \M2E\Kaufland\Model\Listing\Other\MappingService $mappingService,
        $context = null
    ) {
        parent::__construct($context);
        $this->listingOtherRepository = $listingOtherRepository;
        $this->massActionFilter = $massActionFilter;
        $this->mappingService = $mappingService;
    }

    public function execute()
    {
        $products = $this->listingOtherRepository->findForUnmappingByMassActionSelectedProducts(
            $this->massActionFilter
        );

        if (empty($products)) {
            return $this->_redirect('*/product_grid/unmanaged/');
        }

        $this->mappingService->unMap($products);

        return $this->_redirect('*/product_grid/unmanaged/');
    }
}
