<?php

declare(strict_types=1);

namespace M2E\Kaufland\Block\Adminhtml\ControlPanel\Info;

use M2E\Kaufland\Block\Adminhtml\Magento\AbstractBlock;

class Actual extends AbstractBlock
{
    private \M2E\Core\Helper\Client $clientHelper;
    private \M2E\Core\Helper\Magento $magentoHelper;
    private \M2E\Kaufland\Helper\Module $moduleHelper;
    private \M2E\Kaufland\Helper\Module\Maintenance $maintenanceHelper;

    /** @var string */
    public $systemName;
    /** @var int|string */
    public $systemTime;
    /** @var string */
    public $magentoInfo;
    /** @var string */
    public $publicVersion;
    /** @var mixed */
    public $setupVersion;
    /** @var mixed|null */
    public $moduleEnvironment;
    /** @var bool */
    public $maintenanceMode;
    /** @var false|mixed|string */
    public $coreResourceVersion;
    /** @var false|mixed|string */
    public $coreResourceDataVersion;
    /** @var array|string */
    public $phpVersion;
    /** @var string */
    public $phpApi;
    /** @var float|int */
    public $memoryLimit;
    /** @var false|string */
    public $maxExecutionTime;
    /** @var string|null */
    public $mySqlVersion;
    /** @var string */
    public $mySqlDatabaseName;
    /** @var string */
    public $mySqlPrefix;
    private \M2E\Kaufland\Model\Module $module;

    public function __construct(
        \M2E\Core\Helper\Client $clientHelper,
        \M2E\Core\Helper\Magento $magentoHelper,
        \M2E\Kaufland\Helper\Module $moduleHelper,
        \M2E\Core\Helper\Client\MemoryLimit $memoryLimit,
        \M2E\Kaufland\Helper\Module\Maintenance $maintenanceHelper,
        \M2E\Kaufland\Model\Module $module,
        \M2E\Kaufland\Block\Adminhtml\Magento\Context\Template $context,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->clientHelper = $clientHelper;
        $this->magentoHelper = $magentoHelper;
        $this->moduleHelper = $moduleHelper;
        $this->maintenanceHelper = $maintenanceHelper;
        $this->memoryLimit = $memoryLimit;
        $this->module = $module;
    }

    public function _construct()
    {
        parent::_construct();

        $this->setId('controlPanelSummaryInfo');
        $this->setTemplate('control_panel/info/actual.phtml');
    }

    protected function _beforeToHtml()
    {
        $this->systemName = \M2E\Core\Helper\Client::getSystem();
        $this->systemTime = \M2E\Core\Helper\Date::createCurrentGmt()->format('Y-m-d H:i:s');

        $this->magentoInfo = __(ucwords($this->magentoHelper->getEditionName())) .
            ' (' . $this->magentoHelper->getVersion() . ')';

        $this->publicVersion = $this->module->getPublicVersion();
        $this->setupVersion = $this->module->getSetupVersion();
        $this->moduleEnvironment = $this->moduleHelper->getEnvironment();

        $this->maintenanceMode = $this->maintenanceHelper->isEnabled();
        $this->coreResourceVersion = $this->module->getSchemaVersion();
        $this->coreResourceDataVersion = $this->module->getDataVersion();

        $this->phpVersion = \M2E\Core\Helper\Client::getPhpVersion();
        $this->phpApi = \M2E\Core\Helper\Client::getPhpApiName();

        $this->memoryLimit = $this->memoryLimit->get();
        $this->maxExecutionTime = ini_get('max_execution_time');

        $this->mySqlVersion = $this->clientHelper->getMysqlVersion();
        $this->mySqlDatabaseName = $this->magentoHelper->getDatabaseName();
        $this->mySqlPrefix = $this->magentoHelper->getDatabaseTablesPrefix();
        if (empty($this->mySqlPrefix)) {
            $this->mySqlPrefix = __('disabled');
        }

        return parent::_beforeToHtml();
    }
}
