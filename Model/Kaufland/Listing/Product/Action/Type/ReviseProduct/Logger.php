<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\ReviseProduct;

class Logger
{
    private array $logs = [];
    private string $onlineTitle;
    private string $onlineDescription;
    private string $onlineImage;
    private int $onlineCategoryId;
    private string $onlineCategoryAttributesData;

    public function saveProductDataBeforeUpdate(\M2E\Kaufland\Model\Product $product): void
    {
        $this->onlineTitle = (string)$product->getOnlineTitle();
        $this->onlineDescription = (string)$product->getOnlineDescription();
        $this->onlineImage = (string)$product->getOnlineImage();
        $this->onlineCategoryId = (int)$product->getOnlineCategoryId();
        $this->onlineCategoryAttributesData = $product->getOnlineCategoryAttributesData();
    }

    public function calculateLogs(\M2E\Kaufland\Model\Product $product): array
    {
        $this->processTitleLogs($product);
        $this->processDescriptionLogs($product);
        $this->processImagesLogs($product);
        $this->processCategoriesLogs($product);

        return $this->logs;
    }

    private function processTitleLogs(\M2E\Kaufland\Model\Product $product): void
    {
        if ($product->getOnlineTitle() !== $this->onlineTitle) {
            $message = 'Item was revised: Product Title was updated.';
            $this->createSuccessMessage($message);
        }
    }

    private function processDescriptionLogs(\M2E\Kaufland\Model\Product $product): void
    {
        if ($product->getOnlineDescription() !== $this->onlineDescription) {
            $message = 'Item was revised: Product Description was updated.';
            $this->createSuccessMessage($message);
        }
    }

    private function processImagesLogs(\M2E\Kaufland\Model\Product $product): void
    {
        if ($product->getOnlineImage() !== $this->onlineImage) {
            $message = 'Item was revised: Product Images were updated.';
            $this->createSuccessMessage($message);
        }
    }

    private function processCategoriesLogs(\M2E\Kaufland\Model\Product $product): void
    {
        $categoryUpdated = false;

        if (
            $product->getOnlineCategoryId() !== $this->onlineCategoryId
            || $product->getOnlineCategoryAttributesData() !== $this->onlineCategoryAttributesData
        ) {
            $categoryUpdated = true;
        }

        if ($categoryUpdated) {
            $message = 'Item was revised: Product Categories were updated.';
            $this->createSuccessMessage($message);
        }
    }

    private function createSuccessMessage(string $message): void
    {
        $this->logs[] = \M2E\Core\Model\Response\Message::createSuccess($message);
    }
}
