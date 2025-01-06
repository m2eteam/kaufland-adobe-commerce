<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Listing;

class ExportCsvListingGrid extends \M2E\Kaufland\Controller\Adminhtml\Kaufland\AbstractMain
{
    private \M2E\Kaufland\Helper\Data\FileExport $fileExportHelper;
    private \M2E\Kaufland\Model\Listing\Repository $listingRepository;

    public function __construct(
        \M2E\Kaufland\Model\Listing\Repository $listingRepository,
        \M2E\Kaufland\Helper\Data\FileExport $fileExportHelper
    ) {
        parent::__construct();

        $this->fileExportHelper = $fileExportHelper;
        $this->listingRepository = $listingRepository;
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $listing = $this->listingRepository->get((int)$id);

        $gridName = $listing->getTitle();

        $content = $this->_view
            ->getLayout()
            ->createBlock(
                \M2E\Kaufland\Block\Adminhtml\Kaufland\Listing\View\Kaufland\Grid::class,
                '',
                ['data' => ['listing' => $listing]],
            )
            ->getCsv();

        return $this->fileExportHelper->createFile($gridName, $content);
    }
}
