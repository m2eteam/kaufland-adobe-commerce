<?php

namespace M2E\Kaufland\Model\Channel\Connector\Order\Units\Ship;

class Response
{
    /** @var \M2E\Kaufland\Model\Channel\Order\Units\Ship\Error[] */
    private array $errors;

    /**
     * @param \M2E\Kaufland\Model\Channel\Order\Units\Ship\Error[] $errors
     */
    public function __construct(array $errors)
    {
        $this->errors = $errors;
    }

    /**
     * @return \M2E\Kaufland\Model\Channel\Order\Units\Ship\Error[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
