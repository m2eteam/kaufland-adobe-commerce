<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type;

abstract class AbstractResponse
{
    private array $params = [];
    private \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\RequestData $requestData;
    private array $requestMetaData = [];
    private \M2E\Kaufland\Model\Product $listingProduct;
    private \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Configurator $configurator;

    abstract public function processSuccess(array $response, array $responseParams = []): void;

    public function setParams(array $params = []): void
    {
        $this->params = $params;
    }

    protected function getParams(): array
    {
        return $this->params;
    }

    // ---------------------------------------

    public function setListingProduct(\M2E\Kaufland\Model\Product $product): void
    {
        $this->listingProduct = $product;
    }

    protected function getListingProduct(): \M2E\Kaufland\Model\Product
    {
        return $this->listingProduct;
    }

    // ---------------------------------------

    public function setConfigurator(\M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Configurator $object): void
    {
        $this->configurator = $object;
    }

    protected function getConfigurator(): \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Configurator
    {
        return $this->configurator;
    }

    // ---------------------------------------

    public function setRequestData(\M2E\Kaufland\Model\Kaufland\Listing\Product\Action\RequestData $object): void
    {
        $this->requestData = $object;
    }

    protected function getRequestData(): \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\RequestData
    {
        return $this->requestData;
    }

    // ---------------------------------------

    public function getRequestMetaData(): array
    {
        return $this->requestMetaData;
    }

    public function setRequestMetaData(array $value): self
    {
        $this->requestMetaData = $value;

        return $this;
    }

    //########################################

    protected function getListing(): \M2E\Kaufland\Model\Listing
    {
        return $this->getListingProduct()->getListing();
    }

    // ---------------------------------------

    protected function getAccount(): \M2E\Kaufland\Model\Account
    {
        return $this->getListing()->getAccount();
    }

    // ---------------------------------------

    protected function getMagentoProduct(): \M2E\Kaufland\Model\Magento\Product\Cache
    {
        return $this->getListingProduct()->getMagentoProduct();
    }
}
