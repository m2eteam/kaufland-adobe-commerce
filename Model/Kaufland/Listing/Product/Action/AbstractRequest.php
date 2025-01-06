<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Listing\Product\Action;

abstract class AbstractRequest
{
    protected array $cachedData = [];
    private array $params = [];
    private Configurator $configurator;
    protected array $metaData = [];
    private \M2E\Kaufland\Model\Product $listingProduct;
    private RequestData $requestData;
    private LogBuffer $logBuffer;

    //########################################

    public function setCachedData(array $data): void
    {
        $this->cachedData = $data;
    }

    /**
     * @return array
     */
    public function getCachedData(): array
    {
        return $this->cachedData;
    }

    //########################################

    public function setParams(array $params = []): void
    {
        $this->params = $params;
    }

    /**
     * @return array
     */
    protected function getParams(): array
    {
        return $this->params;
    }

    // ---------------------------------------

    public function setConfigurator(Configurator $object): void
    {
        $this->configurator = $object;
    }

    protected function getConfigurator(): Configurator
    {
        return $this->configurator;
    }

    public function setLogBuffer(LogBuffer $logBuffer): void
    {
        $this->logBuffer = $logBuffer;
    }

    protected function getLogBuffer(): LogBuffer
    {
        return $this->logBuffer;
    }

    //########################################

    protected function addWarningMessage($message)
    {
        $this->getLogBuffer()->addWarning($message);
    }

    //########################################

    protected function addMetaData($key, $value)
    {
        $this->metaData[$key] = $value;
    }

    public function getMetaData(): array
    {
        return $this->metaData;
    }

    public function setMetaData($value): AbstractRequest
    {
        $this->metaData = $value;

        return $this;
    }

    //########################################

    public function setListingProduct(\M2E\Kaufland\Model\Product $object)
    {
        $this->listingProduct = $object;
    }

    protected function getListingProduct(): \M2E\Kaufland\Model\Product
    {
        return $this->listingProduct;
    }

    //########################################

    public function build(): RequestData
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->requestData)) {
            return $this->requestData;
        }

        $data = $this->getRequestData();

        $requestData = new RequestData();
        $requestData->setListingProduct($this->getListingProduct());
        $requestData->setData($data);

        return $this->requestData = $requestData;
    }

    abstract public function getRequestData(): array;

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

    // ---------------------------------------

    protected function getListing(): \M2E\Kaufland\Model\Listing
    {
        return $this->getListingProduct()->getListing();
    }
}
