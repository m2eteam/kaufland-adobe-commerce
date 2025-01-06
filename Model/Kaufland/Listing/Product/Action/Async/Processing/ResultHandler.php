<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Async\Processing;

class ResultHandler implements \M2E\Kaufland\Model\Processing\SingleResultHandlerInterface
{
    public const NICK = 'product_action_async';

    private \M2E\Kaufland\Model\Product\Repository $listingProductRepository;
    private \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\ProcessorAsyncFactory $processorAsyncFactory;
    private \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\ConfiguratorFactory $configuratorFactory;

    private int $initiator;
    private int $listingProductId;
    private int $actionLogId;
    private int $actionLog;
    private string $actionNick;
    private array $actionStartParams;
    private array $requestMetadata;
    private array $requestData;
    private array $configuratorData;
    private array $warningMessages;

    public function __construct(
        \M2E\Kaufland\Model\Product\Repository $listingProductRepository,
        \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\ProcessorAsyncFactory $processorAsyncFactory,
        \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\ConfiguratorFactory $configuratorFactory
    ) {
        $this->listingProductRepository = $listingProductRepository;
        $this->processorAsyncFactory = $processorAsyncFactory;
        $this->configuratorFactory = $configuratorFactory;
    }

    public function initialize(array $params): void
    {
        $processingParams = Params::tryFromArray($params);

        $this->listingProductId = $processingParams->getListingProductId();
        $this->actionLogId = $processingParams->getActionLogId();
        $this->actionLog = $processingParams->getActionLog();
        $this->initiator = $processingParams->getInitiator();
        $this->actionNick = $processingParams->getActionNick();
        $this->actionStartParams = $processingParams->getActionStartParams();
        $this->requestMetadata = $processingParams->getRequestMetadata();
        $this->requestData = $processingParams->getRequestData();
        $this->configuratorData = $processingParams->getConfiguratorData();
        $this->warningMessages = $processingParams->getWarningMessages();
    }

    public function processSuccess(array $resultData, array $messages): void
    {
        $listingProduct = $this->listingProductRepository->find($this->listingProductId);
        if ($listingProduct === null) {
            return;
        }

        $configurator = $this->configuratorFactory->create();
        $configurator->setUnserializedData($this->configuratorData);
        $listingProduct->setActionConfigurator($configurator);

        $endProcessor = $this->processorAsyncFactory->createProcessEnd(
            $this->actionNick,
            $listingProduct,
            $this->initiator,
            $this->actionLogId,
            $this->actionLog,
            $this->actionStartParams,
            $this->requestMetadata,
            $this->requestData,
            $this->warningMessages
        );

        $endProcessor->process($resultData, $messages);
    }

    public function processExpire(): void
    {
        // do nothing
    }

    public function clearLock(\M2E\Kaufland\Model\Processing\LockManager $lockManager): void
    {
        // Lock was acquired in the Start action; will be release in the End action.
    }
}
