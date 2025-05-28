<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Product\Action\Async\Processing;

class Params
{
    private int $listingProductId;
    private int $actionLogId;
    private int $actionLog;
    private int $initiator;
    private string $actionNick;
    private array $actionStartParams;
    private array $requestMetadata;
    private array $requestData;
    private array $configuratorData;
    private array $warningMessages;
    private int $statusChanger;

    public function toArray(): array
    {
        return [
            'listing_product_id' => $this->listingProductId,
            'action_log_id' => $this->getActionLogId(),
            'action_log' => $this->getActionLog(),
            'initiator' => $this->getInitiator(),
            'action_nick' => $this->getActionNick(),
            'action_start_params' => $this->getActionStartParams(),
            'request_metadata' => $this->getRequestMetadata(),
            'request_data' => $this->getRequestData(),
            'configurator_data' => $this->getConfiguratorData(),
            'warning_messages' => $this->getWarningMessages(),
            'status_changer' => $this->getStatusChanger()
        ];
    }

    public static function tryFromArray(array $data): self
    {
        if (
            !isset(
                $data['listing_product_id'],
                $data['action_log_id'],
                $data['action_log'],
                $data['initiator'],
                $data['action_nick'],
                $data['action_start_params'],
                $data['request_metadata'],
                $data['request_data'],
                $data['configurator_data'],
                $data['status_changer']
            )
        ) {
            throw new \M2E\Kaufland\Model\Exception\Logic('Processing params are not valid.');
        }

        return new self(
            (int)$data['listing_product_id'],
            (int)$data['action_log_id'],
            (int)$data['action_log'],
            (int)$data['initiator'],
            $data['action_nick'],
            $data['action_start_params'],
            $data['request_metadata'],
            $data['request_data'],
            $data['configurator_data'],
            $data['warning_messages'] ?? [],
            $data['status_changer']
        );
    }

    public function __construct(
        int $listingProductId,
        int $actionLogId,
        int $actionLog,
        int $initiator,
        string $actionNick,
        array $actionStartParams,
        array $requestMetadata,
        array $requestData,
        array $configuratorData,
        array $warningMessages,
        int $statusChanger
    ) {
        $this->listingProductId = $listingProductId;
        $this->actionLogId = $actionLogId;
        $this->actionLog = $actionLog;
        $this->initiator = $initiator;
        $this->actionNick = $actionNick;
        $this->actionStartParams = $actionStartParams;
        $this->requestMetadata = $requestMetadata;
        $this->requestData = $requestData;
        $this->configuratorData = $configuratorData;
        $this->warningMessages = $warningMessages;
        $this->statusChanger = $statusChanger;
    }

    public function getListingProductId(): int
    {
        return $this->listingProductId;
    }

    public function getActionLogId(): int
    {
        return $this->actionLogId;
    }

    public function getActionLog(): int
    {
        return $this->actionLog;
    }

    public function getInitiator(): int
    {
        return $this->initiator;
    }

    public function getActionNick(): string
    {
        return $this->actionNick;
    }

    public function getActionStartParams(): array
    {
        return $this->actionStartParams;
    }

    public function getRequestMetadata(): array
    {
        return $this->requestMetadata;
    }

    public function getRequestData(): array
    {
        return $this->requestData;
    }

    public function getConfiguratorData(): array
    {
        return $this->configuratorData;
    }

    public function getWarningMessages(): array
    {
        return $this->warningMessages;
    }

    public function getStatusChanger(): int
    {
        return $this->statusChanger;
    }
}
