<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Processing;

class Runner
{
    public const MAX_LIFETIME = 86400;

    private \M2E\Kaufland\Model\Connector\Client\Single $connector;
    private ResultHandlerCollection $resultHandlerCollection;
    private CreateService $processingCreate;
    private LockManagerFactory $lockManagerFactory;

    public function __construct(
        \M2E\Kaufland\Model\Processing\CreateService $processingCreate,
        \M2E\Kaufland\Model\Connector\Client\Single $connector,
        \M2E\Kaufland\Model\Processing\ResultHandlerCollection $resultHandlerCollection,
        \M2E\Kaufland\Model\Processing\LockManagerFactory $lockManagerFactory
    ) {
        $this->connector = $connector;
        $this->resultHandlerCollection = $resultHandlerCollection;
        $this->processingCreate = $processingCreate;
        $this->lockManagerFactory = $lockManagerFactory;
    }

    public function run(SingleInitiatorInterface $initiator): void
    {
        $this->validateHandler($initiator);

        $command = $initiator->getInitCommand();
        /** @var \M2E\Core\Model\Connector\Response\Processing $response */
        $response = $this->connector->process($command);

        $processing = $this->createProcessing($response->getHash(), $initiator);

        $lockManager = $this->lockManagerFactory->create($processing);

        $initiator->initLock($lockManager);
    }

    private function validateHandler(SingleInitiatorInterface $initiator): void
    {
        if (!$this->resultHandlerCollection->has($initiator->getResultHandlerNick())) {
            throw new \M2E\Kaufland\Model\Exception\Logic(
                "Processing handler '{$initiator->getResultHandlerNick()}' not found.",
            );
        }

        $handlerClass = $this->resultHandlerCollection->get($initiator->getResultHandlerNick());
        if ($initiator instanceof \M2E\Kaufland\Model\Processing\PartialInitiatorInterface) {
            if (!is_a($handlerClass, \M2E\Kaufland\Model\Processing\PartialResultHandlerInterface::class, true)) {
                throw new \M2E\Kaufland\Model\Exception\Logic('Result handler is not valid for this processing.');
            }

            return;
        }

        if (!is_a($handlerClass, \M2E\Kaufland\Model\Processing\SingleResultHandlerInterface::class, true)) {
            throw new \M2E\Kaufland\Model\Exception\Logic('Result handler is not valid for this processing.');
        }
    }

    private function createProcessing(
        string $hash,
        SingleInitiatorInterface $initiator
    ): \M2E\Kaufland\Model\Processing {
        $expireDate = \M2E\Core\Helper\Date::createCurrentGmt()
                                                 ->modify('+ ' . self::MAX_LIFETIME . ' seconds');

        if ($initiator instanceof PartialInitiatorInterface) {
            return $this->processingCreate->createPartial(
                $hash,
                $initiator->getResultHandlerNick(),
                $initiator->generateProcessParams(),
                $expireDate,
            );
        }

        return $this->processingCreate->createSingle(
            $hash,
            $initiator->getResultHandlerNick(),
            $initiator->generateProcessParams(),
            $expireDate,
        );
    }
}
