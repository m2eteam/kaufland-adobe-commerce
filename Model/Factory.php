<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model;

class Factory
{
    protected $objectManager;

    //########################################

    /**
     * Construct
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    //########################################

    /**
     * @param $modelName
     * @param array $arguments
     *
     * @return \M2E\Kaufland\Model\AbstractModel
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    public function getObject($modelName, array $arguments = [])
    {
        // fix for Magento2 sniffs that forcing to use ::class
        $modelName = str_replace('_', '\\', $modelName);

        $model = $this->objectManager->create('\M2E\Kaufland\Model\\' . $modelName, $arguments);

        return $model;
    }
}
