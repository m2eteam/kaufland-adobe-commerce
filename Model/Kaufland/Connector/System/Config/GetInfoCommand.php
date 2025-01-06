<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Connector\System\Config;

class GetInfoCommand implements \M2E\Core\Model\Connector\CommandInterface
{
    public function getCommand(): array
    {
        return ['system', 'configs', 'getInfo'];
    }

    public function getRequestData(): array
    {
        return [];
    }

    public function parseResponse(
        \M2E\Core\Model\Connector\Response $response
    ): \M2E\Core\Model\Connector\Response {
        return $response;
    }
}
