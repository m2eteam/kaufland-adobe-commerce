<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Cron;

class Manager
{
    private \M2E\Kaufland\Model\Registry\Manager $registryManager;

    public function __construct(\M2E\Kaufland\Model\Registry\Manager $registryManager)
    {
        $this->registryManager = $registryManager;
    }

    public function getLastAccess(string $taskName): ?\DateTime
    {
        return $this->getValue($this->createLastAccessKey($taskName));
    }

    public function setLastAccess(string $taskName): void
    {
        $this->setValue($this->createLastAccessKey($taskName));
    }

    private function createLastAccessKey(string $taskName): string
    {
        return rtrim($taskName, '/') . '/last_access/';
    }

    public function getLastRun(string $taskName): ?\DateTime
    {
        return $this->getValue($this->createLastRunKey($taskName));
    }

    public function setLastRun(string $taskName): void
    {
        $this->setValue($this->createLastRunKey($taskName));
    }

    private function createLastRunKey(string $taskName): string
    {
        return rtrim($taskName, '/') . '/last_run/';
    }

    private function getValue(string $key): ?\DateTime
    {
        $value = $this->registryManager->getValue($key);
        if ($value === null) {
            return null;
        }

        return \M2E\Core\Helper\Date::createDateGmt($value);
    }

    private function setValue(string $key): void
    {
        $this->registryManager->setValue($key, \M2E\Core\Helper\Date::createCurrentGmt()->format('Y-m-d H:i:s'));
    }
}
