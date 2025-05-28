<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Product\Action\Type;

abstract class AbstractRequest extends \M2E\Kaufland\Model\Product\Action\AbstractRequest
{
    /** @var \M2E\Kaufland\Model\Product\Action\DataBuilder\AbstractDataBuilder[] */
    private array $dataBuilders = [];
    /** @var \M2E\Kaufland\Model\Product\Action\DataBuilder\Factory */
    private \M2E\Kaufland\Model\Product\Action\DataBuilder\Factory $dataBuilderFactory;

    public function __construct(
        \M2E\Kaufland\Model\Product\Action\DataBuilder\Factory $dataBuilderFactory
    ) {
        $this->dataBuilderFactory = $dataBuilderFactory;
    }

    /**
     * @return array
     */
    public function getRequestData(): array
    {
        $data = $this->getActionData();
        $this->collectMetadata();

        $data = $this->prepareFinalData($data);

        $this->afterBuildDataEvent($data);
        $this->collectDataBuildersWarningMessages();

        return $data;
    }

    protected function collectMetadata()
    {
        foreach ($this->dataBuilders as $dataBuilder) {
            $this->metaData = array_merge($this->metaData, $dataBuilder->getMetaData());
        }
    }

    // ---------------------------------------

    abstract protected function getActionData(): array;

    //########################################

    protected function beforeBuildDataEvent()
    {
        return null;
    }

    protected function afterBuildDataEvent(array $data)
    {
        $this->addMetaData('is_listing_type_fixed', true);
    }

    // ---------------------------------------

    protected function prepareFinalData(array $data): array
    {
        return $data;
    }

    private function collectDataBuildersWarningMessages()
    {
        foreach ($this->dataBuilders as $dataBuilder) {
            $messages = $dataBuilder->getWarningMessages();

            foreach ($messages as $message) {
                $this->addWarningMessage($message);
            }
        }
    }

    /***
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    private function getDataBuilder(
        string $nick
    ): \M2E\Kaufland\Model\Product\Action\DataBuilder\AbstractDataBuilder {
        if (!isset($this->dataBuilders[$nick])) {
            $dataBuilder = $this->dataBuilderFactory->create($nick);

            $dataBuilder->setParams($this->getParams());
            $dataBuilder->setListingProduct($this->getListingProduct());
            $dataBuilder->setCachedData($this->getCachedData());

            $this->dataBuilders[$nick] = $dataBuilder;
        }

        return $this->dataBuilders[$nick];
    }

    protected function getQtyData(): int
    {
        /** @var \M2E\Kaufland\Model\Product\Action\DataBuilder\Qty $dataBuilder */
        $dataBuilder = $this->getDataBuilder(\M2E\Kaufland\Model\Product\Action\DataBuilder\Qty::NICK);
        $qtyData = $dataBuilder->getBuilderData();

        return $qtyData['qty'];
    }

    protected function getPriceData(): float
    {
        /** @var \M2E\Kaufland\Model\Product\Action\DataBuilder\Price $dataBuilder */
        $dataBuilder = $this->getDataBuilder(\M2E\Kaufland\Model\Product\Action\DataBuilder\Price::NICK);
        $priceData = $dataBuilder->getBuilderData();

        return $priceData['amount'];
    }

    protected function getTitleData(): string
    {
        /** @var \M2E\Kaufland\Model\Product\Action\DataBuilder\Title $dataBuilder */
        $dataBuilder = $this->getDataBuilder(\M2E\Kaufland\Model\Product\Action\DataBuilder\Title::NICK);
        $titleData = $dataBuilder->getBuilderData();

        return $titleData['title'];
    }

    protected function getDescriptionData(): string
    {
        /** @var \M2E\Kaufland\Model\Product\Action\DataBuilder\Description $dataBuilder */
        $dataBuilder = $this->getDataBuilder(\M2E\Kaufland\Model\Product\Action\DataBuilder\Description::NICK);
        $descriptionData = $dataBuilder->getBuilderData();
        $description = $descriptionData['description'];

        return $description;
    }

    protected function getImagesData(): array
    {
        /** @var \M2E\Kaufland\Model\Product\Action\DataBuilder\Images $dataBuilder */
        $dataBuilder = $this->getDataBuilder(\M2E\Kaufland\Model\Product\Action\DataBuilder\Images::NICK);
        $imagesData = $dataBuilder->getBuilderData();

        return $imagesData;
    }

    protected function getAttributesData(): array
    {
        /** @var \M2E\Kaufland\Model\Product\Action\DataBuilder\Attributes $dataBuilder */
        $dataBuilder = $this->getDataBuilder(\M2E\Kaufland\Model\Product\Action\DataBuilder\Attributes::NICK);
        $attributesData = $dataBuilder->getBuilderData();

        return $attributesData;
    }
}
