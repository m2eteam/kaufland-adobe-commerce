<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Cron\Runner;

use M2E\Kaufland\Model\Cron\AbstractRunner;

class Developer extends AbstractRunner
{
    private array $allowedTasks;
    private \M2E\Kaufland\Model\Cron\TaskRepository $taskRepository;

    public function __construct(
        \M2E\Kaufland\Model\Cron\TaskRepository $taskRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \M2E\Kaufland\Model\Lock\Transactional\ManagerFactory $lockTransactionManagerFactory,
        \M2E\Kaufland\Helper\Module\Exception $exceptionHelper,
        \M2E\Core\Helper\Magento $magentoHelper,
        \M2E\Kaufland\Model\Config\Manager $config,
        \M2E\Kaufland\Helper\Module $moduleHelper,
        \M2E\Kaufland\Helper\Module\Maintenance $maintenanceHelper,
        \M2E\Kaufland\Helper\Module\Cron $cronHelper,
        \M2E\Kaufland\Model\Cron\OperationHistoryFactory $operationHistoryFactory,
        \M2E\Core\Helper\Client\MemoryLimit $memoryLimit,
        \M2E\Kaufland\Model\Cron\Strategy $strategy
    ) {
        parent::__construct(
            $storeManager,
            $lockTransactionManagerFactory,
            $exceptionHelper,
            $magentoHelper,
            $config,
            $moduleHelper,
            $maintenanceHelper,
            $cronHelper,
            $operationHistoryFactory,
            $memoryLimit,
            $strategy,
        );

        $this->taskRepository = $taskRepository;
    }

    public function getNick(): ?string
    {
        return null;
    }

    public function getInitiator(): int
    {
        return \M2E\Core\Helper\Data::INITIATOR_DEVELOPER;
    }

    public function process(): void
    {
        // @codingStandardsIgnoreLine
        session_write_close();
        parent::process();
    }

    protected function getStrategy(): \M2E\Kaufland\Model\Cron\Strategy
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (!isset($this->allowedTasks)) {
            $this->allowedTasks = $this->taskRepository->getRegisteredTasks();
        }

        $strategy = parent::getStrategy();
        $strategy->setAllowedTasks($this->allowedTasks);

        return $strategy;
    }

    /**
     * @param array $tasks
     *
     * @return $this
     */
    public function setAllowedTasks(array $tasks): self
    {
        $this->allowedTasks = $tasks;

        return $this;
    }

    protected function isPossibleToRun(): bool
    {
        return true;
    }

    protected function canProcessRunner(): bool
    {
        return true;
    }

    protected function setLastRun(): void
    {
    }

    protected function setLastAccess(): void
    {
    }
}
