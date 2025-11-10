<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Listing\Auto\Actions\Mode;

use M2E\Kaufland\Model\Listing\Auto\Actions\CategoryMode;
use M2E\Kaufland\Model\Listing\Auto\Actions\GlobalMode;
use M2E\Kaufland\Model\Listing\Auto\Actions\WebsiteMode;

class Factory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function createCategoryMode(\Magento\Catalog\Model\Product $product): CategoryMode
    {
        return $this->objectManager->create(
            CategoryMode::class,
            ['magentoProduct' => $product]
        );
    }

    public function createGlobalMode(\Magento\Catalog\Model\Product $product): GlobalMode
    {
        return $this->objectManager->create(
            GlobalMode::class,
            ['magentoProduct' => $product]
        );
    }

    public function createWebsiteMode(\Magento\Catalog\Model\Product $product): WebsiteMode
    {
        return $this->objectManager->create(
            WebsiteMode::class,
            ['magentoProduct' => $product]
        );
    }
}
