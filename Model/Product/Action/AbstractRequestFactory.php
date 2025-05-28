<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Product\Action;

abstract class AbstractRequestFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(
        \M2E\Kaufland\Model\Product $listingProduct,
        \M2E\Kaufland\Model\Product\Action\Configurator $configurator,
        \M2E\Kaufland\Model\Product\Action\LogBuffer $logBuffer,
        array $params = []
    ): AbstractRequest {
        /** @var AbstractRequest $obj */
        $obj = $this->objectManager->create($this->getRequestClass());

        $obj->setParams($params);
        $obj->setListingProduct($listingProduct);
        $obj->setConfigurator($configurator);
        $obj->setLogBuffer($logBuffer);
        $obj->setCachedData([]);

        return $obj;
    }

    abstract protected function getRequestClass(): string;
}
