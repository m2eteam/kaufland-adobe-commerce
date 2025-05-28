<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Product\Action\Type\ListProduct;

use M2E\Kaufland\Model\Product\Action\Type\AbstractValidator;

class ProcessStart extends \M2E\Kaufland\Model\Product\Action\Async\AbstractProcessStart
{
    private \M2E\Kaufland\Model\Product\Action\AbstractRequest $request;
    private RequestFactory $requestFactory;
    private \M2E\Kaufland\Model\Product\Action\Type\AbstractValidatorFactory $actionValidatorFactory;
    private \M2E\Kaufland\Model\Product\Action\Type\AbstractValidator $actionValidator;
    private \M2E\Kaufland\Model\Product\Action\RequestData $requestData;
    private \M2E\Kaufland\Model\Product\Repository $productRepository;

    public function __construct(
        RequestFactory $requestFactory,
        ValidatorFactory $actionValidatorFactory,
        \M2E\Kaufland\Model\Product\Repository $productRepository,
        \M2E\Kaufland\Model\Product\Action\TagManager $tagManager
    ) {
        parent::__construct($tagManager);

        $this->requestFactory = $requestFactory;
        $this->actionValidatorFactory = $actionValidatorFactory;
        $this->productRepository = $productRepository;
    }

    protected function getActionNick(): string
    {
        return \M2E\Kaufland\Model\Product\Action\DefinitionsCollection::ACTION_PRODUCT_LIST;
    }

    protected function getProductLockType(): string
    {
        return \M2E\Kaufland\Model\Product\Lock::TYPE_PRODUCT;
    }

    protected function getActionValidator(): AbstractValidator
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->actionValidator)) {
            return $this->actionValidator;
        }
        return $this->actionValidator = $this->actionValidatorFactory->create(
            $this->getListingProduct(),
            $this->getActionConfigurator(),
            $this->getParams()
        );
    }

    protected function getCommand(): \M2E\Core\Model\Connector\CommandProcessingInterface
    {
        $this->requestData = $this->getRequest()->build();

        return new \M2E\Kaufland\Model\Channel\Connector\Product\ListProductCommand(
            $this->getAccount()->getServerHash(),
            $this->requestData->getData(),
        );
    }

    protected function getRequestMetadata(): array
    {
        return $this->getRequest()->getMetaData();
    }

    protected function getRequestData(): array
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->requestData)) {
            return $this->requestData->getData();
        }

        $this->requestData = $this->getRequest()->build();

        return $this->requestData->getData();
    }

    protected function prepareProduct(): void
    {
        $product = $this->getListingProduct();
        $offerId = $product->getKauflandOfferId();

        if (empty($offerId)) {
            $skuGenerator = $product->getSkuGenerator();
            $product->setKauflandOfferId($skuGenerator->retrieveSku());
            $this->productRepository->save($product);
        }
    }

    private function getRequest(): \M2E\Kaufland\Model\Product\Action\AbstractRequest
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (!isset($this->request)) {
            $this->request = $this->requestFactory->create(
                $this->getListingProduct(),
                $this->getActionConfigurator(),
                $this->getLogBuffer(),
                $this->getParams()
            );
        }

        return $this->request;
    }
}
