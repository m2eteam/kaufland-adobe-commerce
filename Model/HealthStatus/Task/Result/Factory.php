<?php

namespace M2E\Kaufland\Model\HealthStatus\Task\Result;

use M2E\Kaufland\Model\HealthStatus\Task\Result as TaskResult;

/**
 * Class \M2E\Kaufland\Model\HealthStatus\Task\Result\Factory
 */
class Factory
{
    /** @var \M2E\Kaufland\Model\HealthStatus\Task\Result\LocationResolver */
    protected $locationResolver;

    /** @var \Magento\Framework\ObjectManagerInterface */
    protected $_objectManager = null;

    protected $modelFactory;

    //########################################

    public function __construct(
        \M2E\Kaufland\Model\HealthStatus\Task\Result\LocationResolver $locationResolver,
        \M2E\Kaufland\Model\Factory $modelFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->locationResolver = $locationResolver;
        $this->_objectManager = $objectManager;
        $this->modelFactory = $modelFactory;
    }

    //########################################

    /**
     * @param \M2E\Kaufland\Model\HealthStatus\Task\AbstractModel $task
     *
     * @return \M2E\Kaufland\Model\HealthStatus\Task\Result
     */
    public function create(\M2E\Kaufland\Model\HealthStatus\Task\AbstractModel $task)
    {
        return $this->_objectManager->create(TaskResult::class, [
            'taskHash' => \M2E\Core\Helper\Client::getClassName($task),
            'taskType' => $task->getType(),
            'taskMustBeShownIfSuccess' => $task->mustBeShownIfSuccess(),
            'tabName' => $this->locationResolver->resolveTabName($task),
            'fieldSetName' => $this->locationResolver->resolveFieldSetName($task),
            'fieldName' => $this->locationResolver->resolveFieldName($task),
        ]);
    }

    //########################################
}
