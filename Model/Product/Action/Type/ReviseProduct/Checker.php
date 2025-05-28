<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Product\Action\Type\ReviseProduct;

class Checker
{
    /**
     * @var \M2E\Kaufland\Model\Product\Action\DataBuilder\Factory
     */
    private \M2E\Kaufland\Model\Product\Action\DataBuilder\Factory $dataBuilderFactory;

    public function __construct(
        \M2E\Kaufland\Model\Product\Action\DataBuilder\Factory $dataBuilderFactory
    ) {
        $this->dataBuilderFactory = $dataBuilderFactory;
    }

    public function isNeedReviseForTitle(\M2E\Kaufland\Model\Product $listingProduct): bool
    {
        if (!$this->isTitleReviseEnabled($listingProduct)) {
            return false;
        }

        return $listingProduct->getDescriptionTemplateSource()->getTitle() !== $listingProduct->getOnlineTitle();
    }

    public function isNeedReviseForDescription(\M2E\Kaufland\Model\Product $listingProduct): bool
    {
        if (!$this->isDescriptionReviseEnabled($listingProduct)) {
            return false;
        }

        $newOnlineDescription = \M2E\Kaufland\Model\Product::createOnlineDescription(
            $listingProduct->getRenderedDescription(),
        );

        return $newOnlineDescription !== $listingProduct->getOnlineDescription();
    }

    public function isNeedReviseForImages(\M2E\Kaufland\Model\Product $listingProduct): bool
    {
        if (!$this->isImagesReviseEnabled($listingProduct)) {
            return false;
        }

        $actionDataBuilder = $this->dataBuilderFactory->create(\M2E\Kaufland\Model\Product\Action\DataBuilder\Images::NICK);
        $actionDataBuilder->setListingProduct($listingProduct);

        $actionDataBuilder->getBuilderData();

        $images = $actionDataBuilder->getMetaData()[\M2E\Kaufland\Model\Product\Action\DataBuilder\Images::NICK]['online_image'];

        return $images !== $listingProduct->getOnlineImage();
    }

    public function isNeedReviseForCategories(
        \M2E\Kaufland\Model\Product $listingProduct
    ): bool {
        if (!$this->isCategoriesReviseEnabled($listingProduct)) {
            return false;
        }

        /** @var \M2E\Kaufland\Model\Product\Action\DataBuilder\Attributes $actionDataBuilder */
        $actionDataBuilder = $this->dataBuilderFactory->create(
            \M2E\Kaufland\Model\Product\Action\DataBuilder\Attributes::NICK
        );
        $actionDataBuilder->setListingProduct($listingProduct);

        $actionDataBuilder->getBuilderData();

        $metadata = $actionDataBuilder->getMetaData()[\M2E\Kaufland\Model\Product\Action\DataBuilder\Attributes::NICK];

        if ($metadata['online_category_id'] !== (int)$listingProduct->getOnlineCategoryId()) {
            return true;
        }

        if ($metadata['online_category_attribute_data'] !== $listingProduct->getOnlineCategoryAttributesData()) {
            return true;
        }

        return false;
    }

    private function isTitleReviseEnabled(\M2E\Kaufland\Model\Product $listingProduct): bool
    {
        return $listingProduct->getSynchronizationTemplate()->isReviseUpdateTitle();
    }

    private function isDescriptionReviseEnabled(\M2E\Kaufland\Model\Product $listingProduct): bool
    {
        return $listingProduct->getSynchronizationTemplate()->isReviseUpdateDescription();
    }

    private function isImagesReviseEnabled(\M2E\Kaufland\Model\Product $listingProduct): bool
    {
        return $listingProduct->getSynchronizationTemplate()->isReviseUpdateImages();
    }

    private function isCategoriesReviseEnabled(\M2E\Kaufland\Model\Product $listingProduct): bool
    {
        return $listingProduct->getSynchronizationTemplate()->isReviseUpdateCategories();
    }
}
