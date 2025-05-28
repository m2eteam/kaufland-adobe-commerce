<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Product\Action\Type;

trait ResponseUnitTrait
{
    public function isSuccess(array $response): bool
    {
        $unit = reset($response['units']);

        return $unit['is_success'];
    }

    /**
     * @param array $response
     *
     * @return \M2E\Core\Model\Connector\Response\Message[]
     */
    public function getMessages(array $response): array
    {
        $unit = reset($response['units']);

        $result = [];
        foreach ($unit['messages'] as $messageData) {
            $message = new \M2E\Core\Model\Connector\Response\Message();
            $message->initFromResponseData($messageData);

            $result[] = $message;
        }

        return $result;
    }
}
