<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type;

abstract class AbstractResponseFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(
        \M2E\Kaufland\Model\Product $listingProduct,
        \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Configurator $configurator,
        \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\RequestData $requestData,
        array $params,
        array $requestMetadata = []
    ): AbstractResponse {
        /** @var AbstractResponse $obj */
        $obj = $this->objectManager->create($this->getResponseClass());
        $obj->setListingProduct($listingProduct);
        $obj->setConfigurator($configurator);
        $obj->setRequestData($requestData);
        $obj->setParams($params);
        $obj->setRequestMetaData($requestMetadata);

        return $obj;
    }

    abstract protected function getResponseClass(): string;
}
