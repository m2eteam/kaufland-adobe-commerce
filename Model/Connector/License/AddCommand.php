<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Connector\License;

class AddCommand implements \M2E\Core\Model\Connector\CommandInterface
{
    private \M2E\Kaufland\Model\Connector\License\Add\Request $request;

    public function __construct(\M2E\Kaufland\Model\Connector\License\Add\Request $request)
    {
        $this->request = $request;
    }

    public function getCommand(): array
    {
        return ['license', 'add', 'record'];
    }

    public function getRequestData(): array
    {
        return [
            'domain' => $this->request->getDomain(),
            'directory' => $this->request->getdirectory(),
            'email' => $this->request->getEmail(),
            'first_name' => $this->request->getFirstName(),
            'last_name' => $this->request->getLastName(),
            'phone' => $this->request->getPhone(),
            'country' => $this->request->getCountry(),
            'city' => $this->request->getCity(),
            'postal_code' => $this->request->getPostalCode(),
        ];
    }

    public function parseResponse(\M2E\Core\Model\Connector\Response $response): object
    {
        return new \M2E\Kaufland\Model\Connector\License\Add\Response($response->getResponseData()['key']);
    }
}
