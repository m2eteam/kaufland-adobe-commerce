<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Product\Action\Type;

abstract class AbstractValidatorFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(
        \M2E\Kaufland\Model\Product $listingProduct,
        \M2E\Kaufland\Model\Product\Action\Configurator $configurator,
        array $params
    ): AbstractValidator {
        /** @var AbstractValidator $obj */
        $obj = $this->objectManager->create($this->getValidatorClass());
        $obj->setListingProduct($listingProduct);
        $obj->setConfigurator($configurator);
        $obj->setParams($params);

        return $obj;
    }

    abstract protected function getValidatorClass(): string;
}
