<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Registry;

class Manager
{
    private \M2E\Core\Model\Registry\AdapterFactory $registryAdapterFactory;

    private \M2E\Core\Model\Registry\Adapter $adapter;

    public function __construct(
        \M2E\Core\Model\Registry\AdapterFactory $registryAdapterFactory
    ) {
        $this->registryAdapterFactory = $registryAdapterFactory;
    }

    public function setValue(string $key, $value): void
    {
        if (is_array($value)) {
            $value = json_encode($value, JSON_THROW_ON_ERROR);
        }

        $this->getAdapter()->set($key, $value);
    }

    public function getValue(string $key): ?string
    {
        return $this->getAdapter()->get($key);
    }

    public function getValueFromJson($key): ?array
    {
        $value = $this->getValue($key);

        if ($value === null) {
            return [];
        }

        return json_decode($value, true);
    }

    public function deleteValue($key): void
    {
        $this->getAdapter()->delete($key);
    }

    public function getAdapter(): \M2E\Core\Model\Registry\Adapter
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (!isset($this->adapter)) {
            $this->adapter = $this->registryAdapterFactory->create(
                \M2E\Kaufland\Helper\Module::IDENTIFIER
            );
        }

        return $this->adapter;
    }
}
