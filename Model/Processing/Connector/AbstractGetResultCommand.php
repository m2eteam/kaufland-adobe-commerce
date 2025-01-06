<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Processing\Connector;

abstract class AbstractGetResultCommand implements \M2E\Core\Model\Connector\CommandInterface
{
    public function getCommand(): array
    {
        return ['Processing', 'Get', 'Results'];
    }

    public function parseResponse(\M2E\Core\Model\Connector\Response $response): ResultCollection
    {
        $resultCollection = new ResultCollection();
        foreach ($response->getResponseData()['results'] ?? [] as $hash => $resultData) {
            $resultCollection->add(
                new Result(
                    $hash,
                    $resultData['status'],
                    $resultData['messages'],
                    $resultData['data'],
                    $resultData['next_part'] ?? null,
                )
            );
        }

        return $resultCollection;
    }
}
