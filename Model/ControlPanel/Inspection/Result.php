<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\ControlPanel\Inspection;

class Result
{
    /** @var bool */
    private $status;

    /** @var string */
    private $errorMessage;

    /** @var \M2E\Kaufland\Model\ControlPanel\Inspection\Issue[] */
    private $issues;

    public function __construct($status, $errorMessage, $issues)
    {
        $this->status = $status;
        $this->errorMessage = $errorMessage;
        $this->issues = $issues;
    }

    /**
     * @return bool
     */
    public function isSuccess()
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * @return \M2E\Kaufland\Model\ControlPanel\Inspection\Issue[]
     */
    public function getIssues()
    {
        return $this->issues;
    }
}
