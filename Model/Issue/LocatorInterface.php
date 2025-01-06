<?php

namespace M2E\Kaufland\Model\Issue;

interface LocatorInterface
{
    /**
     * @return DataObject[]
     */
    public function getIssues(): array;
}
