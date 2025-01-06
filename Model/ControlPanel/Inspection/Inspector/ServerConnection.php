<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\ControlPanel\Inspection\Inspector;

use M2E\Kaufland\Model\ControlPanel\Inspection\InspectorInterface;
use M2E\Kaufland\Model\Exception\Connection;
use M2E\Kaufland\Model\ControlPanel\Inspection\Issue\Factory as IssueFactory;

class ServerConnection implements InspectorInterface
{
    private IssueFactory $issueFactory;

    public function __construct(
        IssueFactory $issueFactory
    ) {
        $this->issueFactory = $issueFactory;
    }

    public function process(): array
    {
        $issues = [];

        try {
            throw new \LogicException('Not implemented');
        } catch (Connection $exception) {
            $issues[] = $this->issueFactory->create(
                $exception->getMessage(),
                $exception->getCurlInfo()
            );
        }

        return $issues;
    }
}
