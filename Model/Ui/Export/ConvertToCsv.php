<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Ui\Export;

class ConvertToCsv
{
    private const DEFAULT_PAGE_SIZE = 200;
    private const EXPORT_DIRECTORY = 'export';

    private \Magento\Framework\Filesystem $filesystem;
    private \M2E\Kaufland\Model\Ui\Export\MetadataProvider $metadataProvider;
    private \Magento\Ui\Component\MassAction\Filter $filter;
    private int $pageSize;

    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \M2E\Kaufland\Model\Ui\Export\MetadataProvider $metadataProvider,
        int $pageSize = self::DEFAULT_PAGE_SIZE
    ) {
        $this->filter = $filter;
        $this->metadataProvider = $metadataProvider;
        $this->pageSize = $pageSize;
        $this->filesystem = $filesystem;
    }

    public function getCsvFile(): array
    {
        $component = $this->initializeComponent();
        $filename = $this->getFilename($component->getName());
        $stream = $this->initializeStream($filename);
        $this->writeData($stream, $component);

        return [
            'type' => 'filename',
            'value' => $filename,
            'rm' => true,
        ];
    }

    private function initializeComponent(): \Magento\Ui\Component\AbstractComponent
    {
        $component = $this->filter->getComponent();
        $this->filter->prepareComponent($component);
        $this->filter->applySelectionOnTargetProvider();

        return $component;
    }

    private function getFilename(string $componentName): string
    {
        return self::EXPORT_DIRECTORY . '/' . $componentName . hash('md5', (string)microtime()) . '.csv';
    }

    private function initializeStream(string $filename): \Magento\Framework\Filesystem\File\WriteInterface
    {
        $directory = $this->filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR);
        $directory->create(self::EXPORT_DIRECTORY);
        $stream = $directory->openFile($filename, 'w+');
        $stream->lock();

        return $stream;
    }

    private function writeData(
        \Magento\Framework\Filesystem\File\WriteInterface $stream,
        \Magento\Ui\Component\AbstractComponent $component
    ): void {
        $stream->writeCsv($this->metadataProvider->getHeaders($component));

        $fields = $this->metadataProvider->getFields($component);
        $options = $this->metadataProvider->getOptions();

        $dataProvider = $component->getContext()->getDataProvider();
        $searchCriteria = $dataProvider->getSearchCriteria();
        $totalCount = (int)$dataProvider->getSearchResult()->getTotalCount();
        $totalPages = (int)ceil($totalCount / $this->pageSize);

        for ($page = 1; $page <= $totalPages; $page++) {
            $searchCriteria->setCurrentPage($page)->setPageSize($this->pageSize);
            $searchResult = $dataProvider->getSearchResult();
            $searchResult->setTotalCount($totalCount);

            foreach ($searchResult->getItems() as $item) {
                $this->metadataProvider->convertDate($item, $component->getName());
                $stream->writeCsv($this->metadataProvider->getRowData($item, $fields, $options));
            }
        }

        $stream->unlock();
        $stream->close();
    }
}
