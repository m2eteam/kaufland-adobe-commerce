<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type;

trait ResponseProductTrait
{
    public function isSuccess(array $response): bool
    {
        return $response['status'];
    }

    /**
     * @param array $response
     *
     * @return \M2E\Core\Model\Connector\Response\Message[]
     */
    public function getMessages(array $response): array
    {
        $result = [];
        foreach ($response['messages'] as $messageData) {
            $message = new \M2E\Core\Model\Connector\Response\Message();
            $message->initFromResponseData($messageData);

            $result[] = $message;
        }

        return $result;
    }
}
