<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Connector\Client;

class Config implements \M2E\Core\Model\Connector\Client\ConfigInterface
{
    public const CONFIG_GROUP_SERVER = '/server/';
    public const CONFIG_KEY_APPLICATION_KEY = 'application_key';

    private \M2E\Kaufland\Model\Config\Manager $config;
    private \M2E\Core\Model\LicenseService $licenseService;
    private \M2E\Core\Model\Connector\Client\ConfigManager $connectorConfig;

    public function __construct(
        \M2E\Kaufland\Model\Config\Manager $config,
        \M2E\Core\Model\Connector\Client\ConfigManager $connectorConfig,
        \M2E\Core\Model\LicenseService $licenseService
    ) {
        $this->config = $config;
        $this->licenseService = $licenseService;
        $this->connectorConfig = $connectorConfig;
    }

    public function getHost(): string
    {
        return $this->connectorConfig->getHost();
    }

    public function getConnectionTimeout(): int
    {
        return 15;
    }

    public function getTimeout(): int
    {
        return 300;
    }

    public function getApplicationKey(): string
    {
        return (string)$this->config->getGroupValue(self::CONFIG_GROUP_SERVER, self::CONFIG_KEY_APPLICATION_KEY);
    }

    public function getLicenseKey(): ?string
    {
        $license = $this->licenseService->get();

        return $license->hasKey() ? $license->getKey() : null;
    }
}
