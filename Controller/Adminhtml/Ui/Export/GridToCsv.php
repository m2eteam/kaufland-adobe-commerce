<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Ui\Export;

class GridToCsv extends \M2E\Kaufland\Controller\Adminhtml\AbstractMain
{
    private const EXPORT_FILE_NAME = 'export.csv';

    private \M2E\Kaufland\Model\Ui\Export\ConvertToCsv $converter;
    private \Magento\Framework\App\Response\Http\FileFactory $fileFactory;

    public function __construct(
        \M2E\Kaufland\Model\Ui\Export\ConvertToCsv $converter,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory
    ) {
        parent::__construct();

        $this->converter = $converter;
        $this->fileFactory = $fileFactory;
    }

    public function execute()
    {
        return $this->fileFactory->create(
            self::EXPORT_FILE_NAME,
            $this->converter->getCsvFile(),
            \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR
        );
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('M2E_Kaufland::listings');
    }
}
