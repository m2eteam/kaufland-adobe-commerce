<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Instruction\SynchronizationTemplate\Checker;

class CheckerFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(string $checkClassName, \M2E\Kaufland\Model\Instruction\Handler\Input $input): AbstractChecker
    {
        $object = $this->objectManager->create($checkClassName, ['input' => $input]);

        if (!$object instanceof AbstractChecker) {
            throw new \M2E\Kaufland\Model\Exception\Logic(
                sprintf(
                    'Checker model "%s" does not extends "%s" class',
                    $checkClassName,
                    \M2E\Kaufland\Model\Instruction\SynchronizationTemplate\Checker\AbstractChecker::class
                )
            );
        }

        return $object;
    }
}
