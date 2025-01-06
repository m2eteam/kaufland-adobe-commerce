<?php

namespace M2E\Kaufland\Model\Kaufland\Connector\Order\Units;

class ShipCommand implements \M2E\Core\Model\Connector\CommandInterface
{
    private \M2E\Kaufland\Model\Account $account;
    /** @var \M2E\Kaufland\Model\Kaufland\Connector\Order\Units\Ship\Unit[] */
    private array $units;

    /**
     * @param \M2E\Kaufland\Model\Kaufland\Connector\Order\Units\Ship\Unit[] $units
     */
    public function __construct(
        \M2E\Kaufland\Model\Account $account,
        array $units
    ) {
        $this->account = $account;
        $this->units = $units;
    }

    public function getCommand(): array
    {
        return ['order', 'send', 'entity'];
    }

    public function getRequestData(): array
    {
        return [
            'account' => $this->account->getServerHash(),
            'units' => $this->units,
        ];
    }

    public function parseResponse(\M2E\Core\Model\Connector\Response $response): object
    {
        $responseData = $response->getResponseData();

        $errors = [];

        foreach ($responseData['orders'] as $data) {
            if ($data['is_success'] === true) {
                continue;
            }

            foreach ($data['messages'] as $messageData) {
                $errors[] = new \M2E\Kaufland\Model\Kaufland\Connector\Order\Units\Ship\Error(
                    $data['order_unit_id'],
                    $messageData['text'],
                );
            }
        }

        return new \M2E\Kaufland\Model\Kaufland\Connector\Order\Units\Ship\Response($errors);
    }
}
