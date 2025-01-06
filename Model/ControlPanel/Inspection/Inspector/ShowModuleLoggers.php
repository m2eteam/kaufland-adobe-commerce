<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\ControlPanel\Inspection\Inspector;

use M2E\Kaufland\Model\ControlPanel\Inspection\InspectorInterface;
use M2E\Kaufland\Model\ControlPanel\Inspection\Issue\Factory as IssueFactory;

class ShowModuleLoggers implements InspectorInterface
{
    private array $loggers = [];

    private IssueFactory $issueFactory;
    private \M2E\Core\Helper\Client $clientHelper;

    public function __construct(
        IssueFactory $issueFactory,
        \M2E\Core\Helper\Client $clientHelper
    ) {
        $this->issueFactory = $issueFactory;
        $this->clientHelper = $clientHelper;
    }

    public function process(): array
    {
        $issues = [];
        $this->searchLoggers();

        if (!empty($this->loggers)) {
            $issues[] = $this->issueFactory->create(
                'Kaufland loggers were found in magento files',
                $this->loggers,
            );
        }

        return $issues;
    }

    private function searchLoggers(): void
    {
        $recursiveIteratorIterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $this->clientHelper->getBaseDirectory() . 'vendor',
                \FilesystemIterator::FOLLOW_SYMLINKS,
            ),
        );

        foreach ($recursiveIteratorIterator as $splFileInfo) {
            /**@var \SplFileInfo $splFileInfo */

            if (
                !$splFileInfo->isFile()
                || !in_array($splFileInfo->getExtension(), ['php', 'phtml'])
            ) {
                continue;
            }

            if (strpos($splFileInfo->getRealPath(), 'M2E' . DIRECTORY_SEPARATOR . 'Kaufland') !== false) {
                continue;
            }

            $splFileObject = $splFileInfo->openFile();
            if (!$splFileObject->getSize()) {
                continue;
            }

            $content = $splFileObject->fread($splFileObject->getSize());
            if (strpos($content, 'Module\Logger') === false) {
                continue;
            }

            $content = explode("\n", $content);
            foreach ($content as $line => $contentRow) {
                if (strpos($contentRow, 'Module\Logger') === false) {
                    continue;
                }

                $this->loggers[] = $splFileObject->getRealPath() . ' in line ' . $line;
            }
        }
    }
}
