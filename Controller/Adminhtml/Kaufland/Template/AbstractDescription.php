<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Template;

use M2E\Kaufland\Controller\Adminhtml\Kaufland\AbstractTemplate;

abstract class AbstractDescription extends AbstractTemplate
{
    /** @var \Magento\Framework\HTTP\PhpEnvironment\Request */
    protected $phpEnvironmentRequest;

    /** @var \Magento\Catalog\Model\Product */
    protected $productModel;

    public function __construct(
        \Magento\Framework\HTTP\PhpEnvironment\Request $phpEnvironmentRequest,
        \Magento\Catalog\Model\Product $productModel,
        \M2E\Kaufland\Model\Template\Manager $templateManager
    ) {
        parent::__construct($templateManager);
        $this->phpEnvironmentRequest = $phpEnvironmentRequest;
        $this->productModel = $productModel;
    }

    protected function isMagentoProductExists($id)
    {
        $productCollection = $this->productModel
            ->getCollection()
            ->addIdFilter($id);

        return (bool)$productCollection->getSize();
    }
}
