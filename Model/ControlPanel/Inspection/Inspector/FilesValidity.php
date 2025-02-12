<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\ControlPanel\Inspection\Inspector;

class FilesValidity implements \M2E\Core\Model\ControlPanel\Inspection\InspectorInterface
{
    private \Magento\Backend\Model\UrlInterface $urlBuilder;
    private \Magento\Framework\Component\ComponentRegistrarInterface $componentRegistrar;
    private \Magento\Framework\Filesystem\Driver\File $fileDriver;
    private \Magento\Framework\Filesystem\File\ReadFactory $readFactory;
    private \M2E\Core\Model\ControlPanel\Inspection\IssueFactory $issueFactory;
    private \M2E\Kaufland\Model\Connector\Client\Single $serverClient;

    public function __construct(
        \Magento\Backend\Model\UrlInterface $urlBuilder,
        \Magento\Framework\Filesystem\File\ReadFactory $readFactory,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        \Magento\Framework\Component\ComponentRegistrarInterface $componentRegistrar,
        \M2E\Core\Model\ControlPanel\Inspection\IssueFactory $issueFactory,
        \M2E\Kaufland\Model\Connector\Client\Single $serverClient
    ) {
        $this->serverClient = $serverClient;
        $this->urlBuilder = $urlBuilder;
        $this->readFactory = $readFactory;
        $this->fileDriver = $fileDriver;
        $this->componentRegistrar = $componentRegistrar;
        $this->issueFactory = $issueFactory;
    }

    /**
     * @return array|\M2E\Core\Model\ControlPanel\Inspection\Issue[]
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function process(): array
    {
        $issues = [];

        try {
            $serverFiles = $this->receiveFilesFromServer();
        } catch (\Throwable $exception) {
            $issues[] = $this->issueFactory->create($exception->getMessage());

            return $issues;
        }

        if (empty($serverFiles)) {
            $issues[] = $this->issueFactory->create('No info for this Kaufland version');

            return $issues;
        }

        $problems = [];
        $basePath = $this->componentRegistrar->getPath(
            \Magento\Framework\Component\ComponentRegistrar::MODULE,
            \M2E\Kaufland\Helper\Module::IDENTIFIER
        );

        $clientFiles = $this->getClientFiles($basePath);

        foreach ($clientFiles as $path => $hash) {
            if (!isset($serverFiles[$path])) {
                $problems[] = [
                    'path' => $path,
                    'reason' => 'New file detected',
                ];
            }
        }

        foreach ($serverFiles as $path => $hash) {
            if (!isset($clientFiles[$path])) {
                $problems[] = [
                    'path' => $path,
                    'reason' => 'File is missing',
                ];
                continue;
            }

            if ($clientFiles[$path] != $hash) {
                $problems[] = [
                    'path' => $path,
                    'reason' => 'Hash mismatch',
                ];
            }
        }

        if (!empty($problems)) {
            $issues[] = $this->issueFactory->create(
                'Wrong files validity',
                $this->renderMetadata($problems)
            );
        }

        return $issues;
    }

    private function receiveFilesFromServer(): array
    {
        $command = new \M2E\Core\Model\Server\Connector\System\FilesGetInfoCommand();
        /** @var \M2E\Core\Model\Server\Connector\System\FilesGetInfo\Response $response */
        $response = $this->serverClient->process($command);

        return $response->getFilesOptions();
    }

    private function getClientFiles(string $basePath): array
    {
        $clientFiles = [];
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($basePath));

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $path = str_replace($basePath, '', $file->getPathname());

                /** @var \Magento\Framework\Filesystem\File\Read $fileReader */
                $fileReader = $this->readFactory->create($basePath . $path, $this->fileDriver);

                $fileContent = trim($fileReader->readAll());
                $fileContent = str_replace(["\r\n", "\n\r", PHP_EOL], chr(10), $fileContent);

                $clientFiles[$path] = \M2E\Core\Helper\Data::md5String($fileContent);
            }
        }

        return $clientFiles;
    }

    private function renderMetadata(array $data): string
    {
        $html = <<<HTML
<table>
    <tr>
        <th>Path</th>
        <th>Reason</th>
        <th>Action</th>
    </tr>
HTML;
        foreach ($data as $item) {
            $url = $this->urlBuilder->getUrl(
                '*/controlPanel_tools_kaufland/install',
                ['action' => 'filesDiff', 'filePath' => base64_encode($item['path'])]
            );

            $link = ($item['reason'] === 'New file detected') ? '' : "<a href='$url' target='_blank'>Diff</a>";

            $html .= <<<HTML
<tr>
    <td>
        {$item['path']}
    </td>
    <td>
        {$item['reason']}
    </td>
    <td style="text-align: center;">
        {$link}
    </td>
</tr>

HTML;
        }
        $html .= '</table>';

        return $html;
    }
}
