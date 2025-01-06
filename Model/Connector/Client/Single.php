<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Connector\Client;

class Single
{
    private \M2E\Core\Model\Connector\Client\Single $client;
    private \M2E\Kaufland\Model\Connector\Protocol $protocol;
    private Config $config;
    private \M2E\Kaufland\Helper\Module\Exception $exceptionLogger;
    private \M2E\Core\Model\Connector\Client\SingleFactory $coreClientFactory;
    private \M2E\Kaufland\Model\Connector\Client\ModuleInfo $moduleInfo;

    public function __construct(
        \M2E\Core\Model\Connector\Client\SingleFactory $coreClientFactory,
        \M2E\Kaufland\Model\Connector\Client\Config $config,
        \M2E\Kaufland\Model\Connector\Client\ModuleInfo $moduleInfo,
        \M2E\Kaufland\Model\Connector\Protocol $protocol,
        \M2E\Kaufland\Helper\Module\Exception $exceptionLogger
    ) {
        $this->protocol = $protocol;
        $this->config = $config;
        $this->exceptionLogger = $exceptionLogger;
        $this->coreClientFactory = $coreClientFactory;
        $this->moduleInfo = $moduleInfo;
    }

    /**
     * @param \M2E\Core\Model\Connector\CommandInterface $command
     *
     * @return object
     * @throws \M2E\Core\Model\Exception
     * @throws \M2E\Core\Model\Exception\Connection
     */
    public function process(\M2E\Core\Model\Connector\CommandInterface $command): object
    {
        try {
            $commandResponseResult = $this->getClient()
                                          ->process($command);
        } catch (\Throwable $e) {
            $this->exceptionLogger->process($e, ['command' => $command->getCommand()]);

            throw $e;
        }

        return $commandResponseResult;
    }

    private function getClient(): \M2E\Core\Model\Connector\Client\Single
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (!isset($this->client)) {
            $this->client = $this->coreClientFactory->create(
                $this->protocol,
                $this->config,
                $this->moduleInfo
            );
        }

        return $this->client;
    }
}
