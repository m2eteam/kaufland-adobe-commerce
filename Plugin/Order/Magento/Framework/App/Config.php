<?php

namespace M2E\Kaufland\Plugin\Order\Magento\Framework\App;

class Config extends \M2E\Kaufland\Plugin\AbstractPlugin
{
    /** @var \M2E\Kaufland\Model\Magento\Config\Mutable */
    private $mutableConfig;

    /** @var \M2E\Kaufland\Helper\Data\GlobalData */
    private $globalDataHelper;

    public function __construct(
        \M2E\Kaufland\Model\Magento\Config\Mutable $mutableConfig,
        \M2E\Kaufland\Helper\Data\GlobalData $globalDataHelper
    ) {
        $this->mutableConfig = $mutableConfig;
        $this->globalDataHelper = $globalDataHelper;
    }

    /**
     * @return bool
     */
    protected function canExecute(): bool
    {
        if (!$this->globalDataHelper->getValue('use_mutable_config')) {
            return false;
        }

        return parent::canExecute();
    }

    public function aroundGetValue($interceptor, \Closure $callback, ...$arguments)
    {
        return $this->execute('getValue', $interceptor, $callback, $arguments);
    }

    protected function processGetValue($interceptor, \Closure $callback, array $arguments)
    {
        $path = isset($arguments[0]) ? $arguments[0] : null;
        $scope = isset($arguments[1]) ? $arguments[1] : null;
        $scopeCode = isset($arguments[2]) ? $arguments[2] : null;

        if (!is_string($path) || !is_string($scope)) {
            return $callback(...$arguments);
        }

        $overriddenValue = $this->mutableConfig->getValue($path, $scope, $scopeCode);
        if ($overriddenValue !== null) {
            return $overriddenValue;
        }

        return $callback(...$arguments);
    }
}
