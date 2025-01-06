<?php

declare(strict_types=1);

namespace M2E\Kaufland\Helper\Module;

class Support
{
    public const WEBSITE_PRIVACY_URL = 'https://m2epro.com/privacy';
    public const WEBSITE_TERMS_URL = 'https://m2epro.com/terms-and-conditions';
    public const ACCOUNTS_URL = 'https://accounts.m2e.cloud';
    public const YOUTUBE_CHANNEL_URL = 'https://www.youtube.com/channel/UChPCt1cp3Hp3u63f-lNBUnA';
    public const SUPPORT_CONTROLLER_NAME = 'support';
    public const SUPPORT_PAGE_ROUTE = 'Kaufland/' . self::SUPPORT_CONTROLLER_NAME . '/index';

    private \M2E\Core\Helper\Magento $magentoHelper;
    private \M2E\Core\Helper\Client $clientHelper;
    private \M2E\Kaufland\Model\Module $module;

    public function __construct(
        \M2E\Core\Helper\Magento $magentoHelper,
        \M2E\Core\Helper\Client $clientHelper,
        \M2E\Kaufland\Model\Module $module
    ) {
        $this->magentoHelper = $magentoHelper;
        $this->clientHelper = $clientHelper;
        $this->module = $module;
    }

    /**
     * @return string
     * @throws \M2E\Kaufland\Model\Exception
     */
    public function getSummaryInfo(): string
    {
        return <<<DATA
----- MAIN INFO -----
{$this->getMainInfo()}

---- LOCATION INFO ----
{$this->getLocationInfo()}

----- PHP INFO -----
{$this->getPhpInfo()}
DATA;
    }

    /**
     * @return string
     */
    public function getMainInfo(): string
    {
        $platformInfo = [
            'name' => $this->module->getName(),
            'edition' => $this->magentoHelper->getEditionName(),
            'version' => $this->magentoHelper->getVersion(),
        ];

        $extensionInfo = [
            'name' => $this->module->getName(),
            'version' => $this->module->getPublicVersion(),
        ];

        return <<<INFO
Platform: {$platformInfo['name']} {$platformInfo['edition']} {$platformInfo['version']}
---------------------------
Extension: {$extensionInfo['name']} {$extensionInfo['version']}
---------------------------
INFO;
    }

    /**
     * @return string
     * @throws \M2E\Kaufland\Model\Exception
     */
    public function getLocationInfo(): string
    {
        $locationInfo = [
            'domain' => $this->clientHelper->getDomain(),
            'ip' => $this->clientHelper->getIp(),
        ];

        return <<<INFO
Domain: {$locationInfo['domain']}
---------------------------
Ip: {$locationInfo['ip']}
---------------------------
INFO;
    }

    /**
     * @return string
     */
    public function getPhpInfo(): string
    {
        $phpInfo = $this->clientHelper->getPhpSettings();
        $phpInfo['api'] = \M2E\Core\Helper\Client::getPhpApiName();
        $phpInfo['version'] = \M2E\Core\Helper\Client::getPhpVersion();

        return <<<INFO
Version: {$phpInfo['version']}
---------------------------
Api: {$phpInfo['api']}
---------------------------
Memory Limit: {$phpInfo['memory_limit']}
---------------------------
Max Execution Time: {$phpInfo['max_execution_time']}
---------------------------
INFO;
    }
}
