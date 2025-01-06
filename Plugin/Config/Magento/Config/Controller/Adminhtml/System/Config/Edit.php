<?php

declare(strict_types=1);

namespace M2E\Kaufland\Plugin\Config\Magento\Config\Controller\Adminhtml\System\Config;

class Edit extends \M2E\Kaufland\Plugin\AbstractPlugin
{
    protected function canExecute(): bool
    {
        /** @var \M2E\Kaufland\Helper\Module\Maintenance $maintenanceHelper */
        $maintenanceHelper = \Magento\Framework\App\ObjectManager::getInstance()->get(
            \M2E\Kaufland\Helper\Module\Maintenance::class
        );

        if ($maintenanceHelper->isEnabled()) {
            return false;
        }

        return true;
    }

    public function aroundExecute($interceptor, \Closure $callback, ...$arguments)
    {
        return $this->execute('execute', $interceptor, $callback, $arguments);
    }

    protected function processExecute($interceptor, \Closure $callback, array $arguments)
    {
        $result = $callback(...$arguments);

        if ($result instanceof \Magento\Backend\Model\View\Result\Redirect) {
            return $result;
        }

        $result->getConfig()->addPageAsset('M2E_Kaufland::css/help_block.css');
        $result->getConfig()->addPageAsset('M2E_Kaufland::css/system/config.css');

        return $result;
    }
}
