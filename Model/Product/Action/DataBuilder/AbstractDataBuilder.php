<?php

namespace M2E\Kaufland\Model\Product\Action\DataBuilder;

abstract class AbstractDataBuilder
{
    protected array $cachedData = [];
    protected array $params = [];
    protected array $metaData = [];
    private array $warningMessages = [];
    protected \M2E\Kaufland\Model\Product $listingProduct;
    private \M2E\Core\Helper\Magento\Attribute $magentoAttributeHelper;

    public function __construct(\M2E\Core\Helper\Magento\Attribute $magentoAttributeHelper)
    {
        $this->magentoAttributeHelper = $magentoAttributeHelper;
    }

    abstract public function getBuilderData(): array;

    // ----------------------------------------

    public function setListingProduct(\M2E\Kaufland\Model\Product $listingProduct): self
    {
        $this->listingProduct = $listingProduct;

        return $this;
    }

    protected function getListingProduct(): \M2E\Kaufland\Model\Product
    {
        return $this->listingProduct;
    }

    public function setCachedData(array $data): self
    {
        $this->cachedData = $data;

        return $this;
    }

    public function setParams(array $params = []): self
    {
        $this->params = $params;

        return $this;
    }

    protected function addMetaData($key, $value)
    {
        $this->metaData[$key] = $value;
    }

    public function getMetaData(): array
    {
        return $this->metaData;
    }

    public function setMetaData($value): self
    {
        $this->metaData = $value;

        return $this;
    }

    // ----------------------------------------

    protected function getAccount(): \M2E\Kaufland\Model\Account
    {
        return $this->getListing()->getAccount();
    }

    // ---------------------------------------

    protected function getListing(): \M2E\Kaufland\Model\Listing
    {
        return $this->getListingProduct()->getListing();
    }

    // ---------------------------------------

    protected function getMagentoProduct(): \M2E\Kaufland\Model\Magento\Product
    {
        return $this->getListingProduct()->getMagentoProduct();
    }

    //########################################

    protected function searchNotFoundAttributes()
    {
        $this->getMagentoProduct()->clearNotFoundAttributes();
    }

    protected function processNotFoundAttributes($title): bool
    {
        $attributes = $this->getMagentoProduct()->getNotFoundAttributes();

        if (empty($attributes)) {
            return true;
        }

        $this->addNotFoundAttributesMessages($title, $attributes);

        return false;
    }

    // ---------------------------------------

    protected function addNotFoundAttributesMessages($title, array $attributes)
    {
        $attributesTitles = [];

        foreach ($attributes as $attribute) {
            $attributesTitles[] = $this->magentoAttributeHelper
                                       ->getAttributeLabel(
                                           $attribute,
                                           $this->getListing()->getStoreId()
                                       );
        }

        $this->addWarningMessage(
            (string)__(
                '%1: Attribute(s) %2 were not found' .
                ' in this Product and its value was not sent.',
                (string)__($title),
                implode(',', $attributesTitles)
            )
        );
    }

    protected function addNotFoundAttributesToWarning(
        \M2E\Kaufland\Model\Magento\Product\Attribute\RetrieveValue $attributeRetriever
    ): void {
        if (!$attributeRetriever->hasErrors()) {
            return;
        }

        $this->addWarningMessage($attributeRetriever->getErrorMessage());
    }

    protected function addWarningMessage($message): AbstractDataBuilder
    {
        $this->warningMessages[sha1($message)] = $message;

        return $this;
    }

    public function getWarningMessages(): array
    {
        return $this->warningMessages;
    }
}
