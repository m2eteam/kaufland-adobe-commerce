<?php

declare(strict_types=1);

namespace M2E\Kaufland\Block\Adminhtml\ControlPanel\Inspection;

class VersionInfo extends AbstractInspection
{
    public string $latestPublicVersion = '';
    private \M2E\Kaufland\Model\Module $module;

    /**
     * @param \M2E\Kaufland\Model\Module $module
     * @param \M2E\Kaufland\Block\Adminhtml\Magento\Context\Template $context
     * @param array $data
     */
    public function __construct(
        \M2E\Kaufland\Model\Module $module,
        \M2E\Kaufland\Block\Adminhtml\Magento\Context\Template $context,
        array $data = []
    ) {
        $this->module = $module;
        parent::__construct($context, $data);
    }

    public function _construct()
    {
        parent::_construct();

        $this->setId('controlPanelInspectionVersionInfo');
        $this->setTemplate('control_panel/inspection/versionInfo.phtml');

        $this->prepareInfo();
    }

    protected function prepareInfo()
    {
        if ($this->module->hasLatestVersion()) {
            $this->latestPublicVersion = $this->module->getLatestVersion();
        }
    }
}
